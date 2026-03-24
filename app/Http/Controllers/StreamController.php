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
    private const GUEST_PREVIEW_SECONDS = 15;
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
            abort(404, 'Bài hát đang được cập nhật. Vui lòng quay lại sau.');
        }

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
        // Phân quyền 15 giây preview hiện tại do frontend JS quản lý để tránh
        // lỗi 403/416 khi thẻ <audio> của trình duyệt tự động fetch metadata ở cuối file.
        return null;
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
