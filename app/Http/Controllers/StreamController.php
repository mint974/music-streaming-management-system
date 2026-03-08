<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamController extends Controller
{
    /**
     * Stream file âm thanh với HTTP Range support (cho phép tua nhạc).
     *
     * Route: GET /stream/{song}
     *
     * Logic phân quyền:
     *   - Bài không phải VIP  → mọi người đều nghe được (kể cả khách)
     *   - Bài VIP             → cần đăng nhập + role premium/artist/admin
     *   - Bài không có file   → 404
     */
    public function stream(Request $request, Song $song): StreamedResponse
    {
        // ── Kiểm tra bài đã được duyệt xuất bản ──────────────────────────────
        if ($song->status !== 'published' && !$this->isOwnerOrAdmin($song)) {
            abort(403, 'Bài hát chưa được xuất bản.');
        }

        // ── Kiểm tra VIP ──────────────────────────────────────────────────────
        if ($song->is_vip) {
            if (!auth()->check()) {
                abort(401, 'Yêu cầu đăng nhập để nghe bài hát VIP.');
            }
            if (!in_array(auth()->user()->role, ['premium', 'artist', 'admin'])) {
                abort(403, 'Yêu cầu tài khoản Premium.');
            }
        }

        // ── Kiểm tra file tồn tại ─────────────────────────────────────────────
        if (empty($song->file_path)) {
            abort(404, 'Bài hát này không có file âm thanh.');
        }

        $path = storage_path('app/public/' . $song->file_path);

        if (!file_exists($path)) {
            abort(404, 'File âm thanh không tìm thấy trên server.');
        }

        // ── Ghi nhận lượt nghe (session debounce 60s) ─────────────────────────
        $this->recordListen($request, $song);

        // ── Chuẩn bị Response headers ─────────────────────────────────────────
        $fileSize = filesize($path);
        $mimeType = $song->file_mime ?? $this->detectMime($path);
        $etag     = md5($song->id . $song->updated_at);

        // ETag cache — trả 304 nếu client đã có bản mới nhất
        if ($request->header('If-None-Match') === $etag) {
            return response()->stream(fn () => null, 304);
        }

        $start  = 0;
        $end    = $fileSize - 1;
        $status = 200;

        $headers = [
            'Content-Type'           => $mimeType,
            'Accept-Ranges'          => 'bytes',
            'Content-Length'         => $fileSize,
            'ETag'                   => $etag,
            'Cache-Control'          => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ];

        // ── HTTP Range Request (tua nhạc) ─────────────────────────────────────
        if ($request->hasHeader('Range')) {
            preg_match('/bytes=(\d+)-(\d*)/', $request->header('Range'), $m);
            $start = isset($m[1]) ? (int) $m[1] : 0;
            $end   = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : $fileSize - 1;

            // Validate range
            if ($start > $end || $end >= $fileSize) {
                return response()->stream(fn () => null, 416, [
                    'Content-Range' => "bytes */{$fileSize}",
                ]);
            }

            $length = $end - $start + 1;
            $headers['Content-Range']  = "bytes {$start}-{$end}/{$fileSize}";
            $headers['Content-Length'] = $length;
            $status = 206; // Partial Content
        }

        // ── Stream ────────────────────────────────────────────────────────────
        $startCapture = $start;
        $endCapture   = $end;

        return response()->stream(function () use ($path, $startCapture, $endCapture) {
            $handle    = fopen($path, 'rb');
            $remaining = $endCapture - $startCapture + 1;

            fseek($handle, $startCapture);

            $chunkSize = 1024 * 64; // 64 KB per chunk
            while (!feof($handle) && $remaining > 0) {
                $bytes     = fread($handle, min($chunkSize, $remaining));
                $remaining -= strlen($bytes);
                echo $bytes;
                flush();
            }

            fclose($handle);
        }, $status, $headers);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function isOwnerOrAdmin(Song $song): bool
    {
        if (!auth()->check()) {
            return false;
        }
        $user = auth()->user();
        return $user->id === $song->user_id || $user->role === 'admin';
    }

    private function recordListen(Request $request, Song $song): void
    {
        $key = "listened_{$song->id}_" . ($request->ip() ?? 'unknown');

        if (!session()->has($key)) {
            Song::withoutTimestamps(fn () => $song->increment('listens'));
            session()->put($key, true);
        }
    }

    private function detectMime(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'mp3'  => 'audio/mpeg',
            'flac' => 'audio/flac',
            'wav'  => 'audio/wav',
            'ogg'  => 'audio/ogg',
            'm4a'  => 'audio/mp4',
            default => 'application/octet-stream',
        };
    }
}
