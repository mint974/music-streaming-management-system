<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\ListeningHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamController extends Controller
{
    private const GUEST_PREVIEW_SECONDS = 45;
    private const GUEST_PREVIEW_FALLBACK_BYTES = 5_000_000;

    /**
     * Stream file âm thanh với HTTP Range support.
     *
     * Phân quyền playback:
     * - Guest: chỉ nghe preview 45 giây với bài non-premium
     * - Free: nghe full bài non-premium, có seek/volume/next-prev ở frontend
     * - Premium/Artist/Admin: nghe full cả bài premium
     */
    public function stream(Request $request, Song $song): StreamedResponse
    {
        if ($song->status !== 'published' && ! $this->isOwnerOrAdmin($song)) {
            abort(403, 'Bài hát chưa được xuất bản.');
        }

        if ($song->is_vip && ! $this->canAccessVip($song)) {
            if (! Auth::check()) {
                abort(401, 'Yêu cầu đăng nhập để nghe bài hát Premium.');
            }

            abort(403, 'Yêu cầu tài khoản Premium để nghe toàn bộ bài hát này.');
        }

        if (empty($song->file_path)) {
            abort(404, 'Bài hát này không có file âm thanh.');
        }

        $path = storage_path('app/public/' . $song->file_path);

        if (! file_exists($path)) {
            abort(404, 'File âm thanh không tìm thấy trên server.');
        }

        $this->recordListen($request, $song);

        $fileSize = filesize($path);
        $mimeType = $song->file_mime ?? $this->detectMime($path);
        $etag = md5($song->id . '|' . $song->updated_at);
        $previewEndByte = $this->resolvePreviewEndByte($song, $fileSize);

        if ($request->header('If-None-Match') === $etag) {
            return response()->stream(fn () => null, 304);
        }

        $start = 0;
        $end = $previewEndByte ?? ($fileSize - 1);
        $status = 200;

        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'ETag' => $etag,
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($previewEndByte !== null) {
            $headers['X-Preview-Mode'] = 'guest';
            $headers['X-Preview-Seconds'] = (string) self::GUEST_PREVIEW_SECONDS;
        }

        if ($request->hasHeader('Range')) {
            preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $matches);
            $start = isset($matches[1]) ? (int) $matches[1] : 0;
            $requestedEnd = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : ($fileSize - 1);
            $maxEnd = $previewEndByte ?? ($fileSize - 1);
            $end = min($requestedEnd, $maxEnd);

            if ($previewEndByte !== null && $start > $previewEndByte) {
                abort(403, 'Bản xem trước cho khách đã kết thúc. Vui lòng đăng ký để nghe trọn vẹn.');
            }

            if ($start > $end || $start >= $fileSize) {
                return response()->stream(fn () => null, 416, [
                    'Content-Range' => "bytes */{$fileSize}",
                ]);
            }

            $status = 206;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
        }

        $headers['Content-Length'] = max(0, $end - $start + 1);

        $startCapture = $start;
        $endCapture = $end;

        return response()->stream(function () use ($path, $startCapture, $endCapture) {
            $handle = fopen($path, 'rb');
            $remaining = $endCapture - $startCapture + 1;

            fseek($handle, $startCapture);

            $chunkSize = 1024 * 64;
            while (! feof($handle) && $remaining > 0) {
                $bytes = fread($handle, min($chunkSize, $remaining));
                $remaining -= strlen($bytes);
                echo $bytes;
                flush();
            }

            fclose($handle);
        }, $status, $headers);
    }

    private function canAccessVip(Song $song): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        return $user->id === $song->user_id || in_array($user->role, ['premium', 'artist', 'admin'], true);
    }

    private function isOwnerOrAdmin(Song $song): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        return $user->id === $song->user_id || $user->role === 'admin';
    }

    private function resolvePreviewEndByte(Song $song, int $fileSize): ?int
    {
        if (Auth::check() || $song->is_vip) {
            return null;
        }

        if ($song->duration > 0) {
            $ratio = min(1, self::GUEST_PREVIEW_SECONDS / max(1, $song->duration));
            return max(0, (int) floor(($fileSize - 1) * $ratio));
        }

        return min($fileSize - 1, self::GUEST_PREVIEW_FALLBACK_BYTES);
    }

    private function recordListen(Request $request, Song $song): void
    {
        $key = 'listened_' . $song->id . '_' . ($request->ip() ?? 'unknown');

        $lastListenedAt = session()->get($key);

        if (! $lastListenedAt || Carbon::parse($lastListenedAt)->diffInSeconds(now()) >= 60) {
            Song::withoutTimestamps(fn () => $song->increment('listens'));

            if (Auth::check()) {
                ListeningHistory::create([
                    'user_id'     => Auth::id(),
                    'song_id'     => $song->id,
                    'source'      => 'stream',
                    'listened_at' => now(),
                ]);
            }

            session()->put($key, now()->toDateTimeString());
        }
    }

    private function detectMime(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'mp3' => 'audio/mpeg',
            'flac' => 'audio/flac',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/mp4',
            default => 'application/octet-stream',
        };
    }
}
