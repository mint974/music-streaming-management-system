@extends('layouts.main')

@section('title', 'Bài hát yêu thích')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="text-white mb-0">Bài hát yêu thích</h4>
        <a href="{{ route('listener.history') }}" class="btn btn-sm btn-outline-light">Lịch sử nghe</a>
    </div>

    <div class="card" style="background:#111827;border:1px solid #1f2937">
        <div class="card-body">
            @forelse($favorites as $item)
                @php $song = $item->song; @endphp
                @if($song)
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                    <div>
                        <div class="text-white small fw-semibold">
                            {{ $song->title }}
                            @if($song->is_vip)
                                <i class="fa-solid fa-crown ms-1" style="color:#fbbf24"></i>
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:.75rem">
                            {{ $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }} • {{ $item->created_at?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('songs.show', $song->id) }}" class="btn btn-sm btn-outline-secondary">Chi tiết</a>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary js-play-song"
                            data-song-id="{{ $song->id }}"
                            data-song-title="{{ e($song->title) }}"
                            data-song-artist="{{ e($song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                            data-song-cover="{{ $song->getCoverUrl() }}"
                            data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                            data-stream-url="{{ route('songs.stream', $song->id) }}">
                            Phát
                        </button>
                        <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">Bỏ thích</button>
                        </form>
                    </div>
                </div>
                @endif
            @empty
                <p class="text-muted mb-0">Bạn chưa yêu thích bài hát nào.</p>
            @endforelse

            <div class="mt-3">{{ $favorites->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>
@endsection
