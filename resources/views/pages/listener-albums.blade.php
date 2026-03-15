@extends('layouts.main')

@section('title', 'Album đã lưu')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="text-white mb-0">Album đã lưu</h4>
        <a href="{{ route('albums.index') }}" class="btn btn-sm btn-outline-light">Khám phá album</a>
    </div>

    <div class="songs-card-grid">
        @forelse($savedAlbums as $saved)
            @php $album = $saved->album; @endphp
            @if($album)
                @include('pages.albums.partials.album-card', [
                    'album' => $album,
                    'savedAlbumIds' => [(int) $album->id],
                ])
            @endif
        @empty
            <div class="songs-empty">
                <i class="fa-solid fa-compact-disc"></i>
                <p class="mb-0">Bạn chưa lưu album nào.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $savedAlbums->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
