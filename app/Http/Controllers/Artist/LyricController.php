<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Models\SongLyric;
use App\Models\SongLyricLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LyricController extends Controller
{
    private function authorizeOwner(Song $song): void
    {
        $artistProfileId = (int) (Auth::user()?->artistProfile?->id ?? 0);
        if ($artistProfileId <= 0 || (int) $song->artist_profile_id !== $artistProfileId) {
            abort(403);
        }
    }

    public function index(Song $song)
    {
        $this->authorizeOwner($song);
        $lyrics = $song->lyrics()->with('lines')->orderByDesc('created_at')->get();

        return view('artist.lyrics.index', compact('song', 'lyrics'));
    }

    public function store(Request $request, Song $song)
    {
        $this->authorizeOwner($song);

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'lyric_source' => 'required|in:plain,lrc_text,lrc_file',
            'raw_text' => 'nullable|string',
            'lrc_file' => 'nullable|file|mimetypes:text/plain|max:2048',
        ]);

        $rawText = '';
        $type = 'plain';

        if ($validated['lyric_source'] === 'lrc_file') {
            if (!$request->hasFile('lrc_file')) {
                return back()->withErrors(['lrc_file' => 'Vui lòng cung cấp file LRC.']);
            }
            $rawText = \file_get_contents($request->file('lrc_file')->getRealPath());
            $type = 'synced';
        } elseif ($validated['lyric_source'] === 'lrc_text') {
            if (empty($validated['raw_text'])) {
                return back()->withErrors(['raw_text' => 'Vui lòng dán văn bản LRC.']);
            }
            $rawText = $validated['raw_text'];
            $type = 'synced';
        } else {
            if (empty($validated['raw_text'])) {
                return back()->withErrors(['raw_text' => 'Vui lòng dán lời bài hát.']);
            }
            $rawText = $validated['raw_text'];
            $type = 'plain';
        }

        $songLyric = SongLyric::create([
            'song_id' => $song->id,
            'name' => trim((string) $validated['name']),
            'language_code' => 'vi',
            'source' => 'artist',
            'is_default' => false,
            'is_visible' => true,
        ]);

        if ($type === 'synced') {
            $this->parseAndInsertLrcLines($songLyric, $rawText);
        } else {
            $this->parseAndInsertPlainLines($songLyric, $rawText);
        }

        // If it's a plain text upload, prompt them to verify directly (no sync preview)
        // If synced, redirect to the preview editor
        return redirect()->route('artist.songs.lyrics.preview', [$song, $songLyric])
            ->with('success', 'Bản lời nháp đã được tạo. Vui lòng xem trước và xác nhận.');
    }

    private function parseAndInsertLrcLines(SongLyric $songLyric, string $rawText): void
    {
        $lines = explode("\n", $rawText);
        $lineOrder = 1;
        $linesToInsert = [];
        
        foreach ($lines as $line) {
            if (\preg_match('/\[(\d{2,}):(\d{2})(?:\.(\d{1,3}))?\](.*)/', $line, $matches)) {
                $min = (int) $matches[1];
                $sec = (int) $matches[2];
                $msStr = isset($matches[3]) && $matches[3] !== '' ? $matches[3] : '0';
                
                $msParts = (int) $msStr;
                if (strlen($msStr) === 1) {
                    $msParts *= 100;
                } elseif (strlen($msStr) === 2) {
                    $msParts *= 10;
                }
                
                $timeMs = ($min * 60 * 1000) + ($sec * 1000) + $msParts;
                $text = trim($matches[4]);

                if (!empty($text)) {
                    $linesToInsert[] = [
                        'song_lyric_id' => $songLyric->id,
                        'line_order' => $lineOrder++,
                        'start_time_ms' => $timeMs,
                        'end_time_ms' => null,
                        'content' => $text,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        if (!empty($linesToInsert)) {
            SongLyricLine::insert($linesToInsert);
        }
    }

    private function parseAndInsertPlainLines(SongLyric $songLyric, string $rawText): void
    {
        $rows = \preg_split('/\r\n|\r|\n/', $rawText) ?: [];
        $lineOrder = 1;
        $linesToInsert = [];

        foreach ($rows as $row) {
            $text = trim((string) $row);
            if ($text === '') {
                continue;
            }

            $linesToInsert[] = [
                'song_lyric_id' => $songLyric->id,
                'line_order' => $lineOrder++,
                'start_time_ms' => null,
                'end_time_ms' => null,
                'content' => $text,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($linesToInsert)) {
            SongLyricLine::insert($linesToInsert);
        }
    }

    public function preview(Song $song, SongLyric $lyric)
    {
        $this->authorizeOwner($song);
        if ($lyric->song_id !== $song->id) abort(404);

        $lyric->load('lines');

        return view('artist.lyrics.preview', compact('song', 'lyric'));
    }

    public function verify(Request $request, Song $song, SongLyric $lyric)
    {
        $this->authorizeOwner($song);
        if ($lyric->song_id !== $song->id) abort(404);

        SongLyric::where('song_id', $song->id)
            ->where('id', '!=', $lyric->id)
            ->update(['is_default' => false]);

        $lyric->update([
            'is_visible' => true,
            'is_default' => true,
        ]);

        $song->load('lyrics');

        return redirect()->route('artist.songs.lyrics.index', $song)
            ->with('success', 'Bản lời đã được đặt làm mặc định thành công!');
    }

    public function toggleVisibility(Song $song, SongLyric $lyric)
    {
        $this->authorizeOwner($song);
        if ($lyric->song_id !== $song->id) abort(404);

        $newVisible = ! (bool) $lyric->is_visible;

        // Luôn giữ ít nhất 1 phiên bản đang hiển thị.
        if (! $newVisible) {
            $visibleCount = SongLyric::where('song_id', $song->id)
                ->where('is_visible', true)
                ->count();

            if ($visibleCount <= 1) {
                return back()->with('error', 'Cần giữ ít nhất một phiên bản lời đang hiển thị.');
            }
        }

        $lyric->update(['is_visible' => $newVisible]);

        return back()->with('success', $newVisible
            ? 'Đã bật hiển thị phiên bản lời.'
            : 'Đã tắt hiển thị phiên bản lời.');
    }

    public function destroy(Song $song, SongLyric $lyric)
    {
        $this->authorizeOwner($song);
        if ($lyric->song_id !== $song->id) abort(404);

        // Không cho phép xóa nếu đây là phiên bản lời duy nhất
        $totalLyrics = SongLyric::where('song_id', $song->id)->count();
        if ($totalLyrics <= 1) {
            return back()->with('error', 'Không thể xóa phiên bản lời duy nhất. Bài hát phải có ít nhất một phiên bản lời.');
        }

        if ($lyric->is_default) {
            $fallbackLyric = SongLyric::query()
                ->where('song_id', $song->id)
                ->where('id', '!=', $lyric->id)
                ->orderByDesc('is_visible')
                ->orderByDesc('id')
                ->first();

            if ($fallbackLyric) {
                $fallbackLyric->update(['is_default' => true]);
            }
        }

        $lyric->delete();

        return redirect()->route('artist.songs.lyrics.index', $song)
            ->with('success', 'Bản lời đã được xóa.');
    }
}
