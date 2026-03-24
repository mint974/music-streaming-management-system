@extends('layouts.main')

@section('title', 'Lịch sử nghe')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="text-white mb-0">Lịch sử nghe</h4>
        <form method="POST" action="{{ route('listener.history.clear') }}" onsubmit="return confirm('Xóa toàn bộ lịch sử nghe?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Xóa toàn bộ</button>
        </form>
    </div>

    <div class="card" style="background:#111827;border:1px solid #1f2937">
        <div class="card-body">
            @forelse($histories as $item)
                @php $song = $item->song; @endphp
                @if($song)
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                    <div>
                        <div class="text-white small fw-semibold">
                            {{ $song->title }}
                            @if($song->is_vip)
                                <i class="fa-solid fa-crown text-warning ms-1" style="font-size: 0.8rem;" title="Premium"></i>
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:.75rem">
                            {{ $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }} • {{ $item->listened_at?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
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
                        <form method="POST" action="{{ route('listener.history.remove', $item->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Xóa</button>
                        </form>
                    </div>
                </div>
                @endif
            @empty
                <p class="text-muted mb-0">Chưa có lịch sử nghe.</p>
            @endforelse

            <div class="mt-3">{{ $histories->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>
@endsection
