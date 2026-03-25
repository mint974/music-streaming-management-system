@extends('layouts.artist')

@section('title', 'Xem trước Phiên bản Lời – Artist Studio')
@section('page-title', 'Xem trước: ' . $song->title)
@section('page-subtitle', 'Kiểm tra độ chính xác của thời gian đồng bộ')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
         style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.28);color:#6ee7b7">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4 justify-content-center">
    <!-- CỘT CHÍNH: PREVIEW -->
    <div class="col-12 col-xl-8">
        <div class="card bg-dark border-secondary bg-opacity-50 mb-4">
            <div class="card-body text-center p-5">
                <img src="{{ $song->getCoverUrl() }}" alt="Cover" class="img-fluid rounded shadow-sm mb-4" style="max-width: 250px; border: 1px solid rgba(255,255,255,0.1);">
                <h4 class="text-white fw-bold mb-1">{{ $song->title }}</h4>
                <p class="text-muted mb-4">{{ $song->author ?? $song->artist->name }}</p>

                <!-- Audio Controller -->
                <audio id="preview-audio" src="{{ $song->getAudioUrl() }}" controls class="w-100 mb-4" style="height: 40px; border-radius: 8px;"></audio>

                <!-- Lyric Container -->
                <div id="preview-lyric-box" class="lyric-container p-4 rounded bg-black bg-opacity-25 border border-secondary" style="height: 400px; overflow-y: auto; scroll-behavior: smooth; position: relative;">
                    <div class="lyric-wrapper w-100">
                        @if($lyric->type === 'plain')
                            <div class="lyric-plain-text">{{ $lyric->raw_text }}</div>
                            <div class="text-muted mt-4 small"><i class="fa-solid fa-info-circle me-1"></i>Đây là lời bài hát dạng văn bản thuần, không có hiệu ứng chạy theo thời gian.</div>
                        @elseif($lyric->lines->isEmpty())
                            <div class="text-muted mt-5">Không có dữ liệu dòng thời gian.</div>
                        @else
                            @foreach($lyric->lines as $index => $line)
                                <div class="lyric-line mb-3"
                                     data-time="{{ $line->start_time_ms / 1000 }}"
                                     id="line-{{ $index }}">
                                    {{ $line->content }}
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer border-secondary p-4 d-flex justify-content-between align-items-center bg-black bg-opacity-25">
                <a href="{{ route('artist.songs.lyrics.index', $song) }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-2"></i>Làm lại (Trở về)
                </a>
                
                <form method="POST" action="{{ route('artist.songs.lyrics.verify', [$song, $lyric]) }}">
                    @csrf
                    <button type="submit" class="btn btn-success px-4" style="background:linear-gradient(135deg,#10b981,#059669);border:none">
                        <i class="fa-solid fa-check-double me-2"></i>Chính xác, Xác nhận và Cài làm Lời Chính thức
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const audio = document.getElementById('preview-audio');
    const lyricBox = document.getElementById('preview-lyric-box');
    const lines = Array.from(document.querySelectorAll('.lyric-line'));
    let activeIndex = -1;

    // Click to seek
    lines.forEach(line => {
        line.addEventListener('click', function() {
            const time = parseFloat(this.getAttribute('data-time'));
            if (!isNaN(time)) {
                audio.currentTime = time;
                audio.play();
            }
        });
    });

    audio.addEventListener('timeupdate', function() {
        const currentTime = audio.currentTime;
        let newActiveIndex = -1;

        // Find the line that matches current time
        for (let i = lines.length - 1; i >= 0; i--) {
            const lineTime = parseFloat(lines[i].getAttribute('data-time'));
            if (currentTime >= lineTime) {
                newActiveIndex = i;
                break;
            }
        }

        if (newActiveIndex !== activeIndex) {
            // Remove previous active state
            if (activeIndex !== -1 && lines[activeIndex]) {
                lines[activeIndex].classList.remove('active');
            }

            activeIndex = newActiveIndex;

            // Set new active state
            if (activeIndex !== -1 && lines[activeIndex]) {
                const activeLine = lines[activeIndex];
                activeLine.classList.add('active');
                
                // Auto scroll logic (center the active line)
                const containerHeight = lyricBox.clientHeight;
                const lineOffset = activeLine.offsetTop;
                const lineHeight = activeLine.clientHeight;
                
                const scrollPos = lineOffset - (containerHeight / 2) + (lineHeight / 2);
                lyricBox.scrollTo({
                    top: Math.max(0, scrollPos),
                    behavior: 'smooth'
                });
            }
        }
    });
});
</script>
@endsection
