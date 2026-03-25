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
        if ($song->user_id !== Auth::id()) {
            abort(403);
        }
    }

    public function index(Song $song)
    {
        $this->authorizeOwner($song);
        $lyrics = $song->lyrics()->orderByDesc('created_at')->get();

        return view('artist.lyrics.index', compact('song', 'lyrics'));
    }

    public function store(Request $request, Song $song)
    {
        $this->authorizeOwner($song);

        $validated = $request->validate([
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
            $rawText = file_get_contents($request->file('lrc_file')->getRealPath());
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
            'language_code' => 'vi',
            'type' => $type,
            'source' => 'artist',
            'status' => 'draft',
            'raw_text' => $rawText,
            'is_default' => false,
        ]);

        if ($type === 'synced') {
            $this->parseAndInsertLrcLines($songLyric, $rawText);
        }

        // If it's a plain text upload, prompt them to verify directly (no sync preview)
        // If synced, redirect to the preview editor
        return redirect()->route('artist.songs.lyrics.preview', [$song, $songLyric])
            ->with('success', 'Bản lời nháp đã được tạo. Vui lòng xem trước và xác nhận.');
    }

    private function parseAndInsertLrcLines(SongLyric $songLyric, string $rawText)
    {
        $lines = explode("\n", $rawText);
        $lineOrder = 1;
        $linesToInsert = [];
        
        foreach ($lines as $line) {
            if (preg_match('/\[(\d{2,}):(\d{2})(?:\.(\d{1,3}))?\](.*)/', $line, $matches)) {
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

        // Turn off default status for all other versions
        SongLyric::where('song_id', $song->id)
            ->where('id', '!=', $lyric->id)
            ->update(['is_default' => false]);

        $lyric->update([
            'status' => 'verified',
            'is_default' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now()
        ]);

        $song->update([
            'has_lyrics' => true,
            'default_lyric_id' => $lyric->id
        ]);

        return redirect()->route('artist.songs.lyrics.index', $song)
            ->with('success', 'Bản lời đã được xác nhận (Verified) và đặt làm mặc định thành công!');
    }

    public function destroy(Song $song, SongLyric $lyric)
    {
        $this->authorizeOwner($song);
        if ($lyric->song_id !== $song->id) abort(404);

        // Reset default if it's the default
        if ($song->default_lyric_id === $lyric->id) {
            $song->update([
                'has_lyrics' => false,
                'default_lyric_id' => null
            ]);
        }

        $lyric->delete();

        return redirect()->route('artist.songs.lyrics.index', $song)
            ->with('success', 'Bản lời đã được xóa.');
    }
}
