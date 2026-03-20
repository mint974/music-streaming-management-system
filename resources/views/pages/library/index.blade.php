@extends('layouts.main')
@section('title', 'Thư viện - Danh sách theo dõi')
@section('content')
<div class="container py-4">
    <h2 class="fw-bold text-white mb-4">Thư viện của bạn</h2>
    
    <ul class="nav nav-pills mb-4" id="libraryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active bg-transparent text-white fw-bold rounded-pill" id="artists-tab" data-bs-toggle="pill" data-bs-target="#artists" type="button" role="tab" style="border: 1px solid rgba(255,255,255,0.2);">Nghệ sĩ Đang theo dõi</button>
        </li>
        <li class="nav-item ms-2" role="presentation">
            <button class="nav-link bg-transparent text-white fw-bold rounded-pill" id="playlists-tab" data-bs-toggle="pill" data-bs-target="#playlists" type="button" role="tab" style="border: 1px solid rgba(255,255,255,0.2);">Playlist của tôi</button>
        </li>
    </ul>

    <div class="tab-content" id="libraryTabsContent">
        <!-- Tab: Artists -->
        <div class="tab-pane fade show active" id="artists" role="tabpanel">
            <div class="row row-cols-2 row-cols-md-4 row-cols-lg-5 g-4 mt-2">
                @forelse($followedArtists as $artist)
                <div class="col">
                    <a href="{{ route('search.artist.show', $artist->id) }}" class="text-decoration-none">
                        <div class="card h-100 border-0 bg-transparent text-center hover-scale">
                            <div class="position-relative overflow-hidden rounded-circle shadow mb-3 mx-auto" style="width: 140px; height: 140px; border: 3px solid rgba(255,255,255,0.1);">
                                <img src="{{ $artist->getAvatarUrl() }}" class="w-100 h-100 object-fit-cover" alt="{{ $artist->name }}">
                            </div>
                            <h6 class="text-white fw-bold mb-1 text-truncate hover-primary">{{ $artist->artist_name ?: $artist->name }}</h6>
                            <p class="text-muted small mb-0">{{ $artist->songs_count }} Bài hát</p>
                        </div>
                    </a>
                </div>
                @empty
                <div class="col-12 w-100 text-center py-5 text-muted" style="background-color: var(--black-soft); border-radius: 12px;">
                    <i class="fa-solid fa-user-group fs-1 mb-3 opacity-50"></i>
                    <p>Bạn chưa theo dõi bất kỳ nghệ sĩ nào. Hãy theo dõi Nghệ sĩ bạn thích để nhận thông báo có bài mới!</p>
                    <a href="{{ route('home') }}" class="btn btn-outline-light rounded-pill mt-3 px-4">Khám phá Âm nhạc</a>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Tab: Playlists -->
        <div class="tab-pane fade" id="playlists" role="tabpanel">
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4 mt-2">
                @foreach($playlists as $pl)
                <div class="col">
                    <div class="card h-100 border-0 bg-transparent hover-scale">
                        <div class="position-relative overflow-hidden rounded-3 shadow-sm mb-2" style="aspect-ratio: 1;">
                            <img src="{{ $pl->getCoverUrl() }}" class="w-100 h-100 object-fit-cover" alt="{{ $pl->name }}">
                            <div class="position-absolute w-100 h-100 top-0 start-0 d-flex justify-content-center align-items-center" style="background: rgba(0,0,0,0.4); opacity: 0; transition: opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                                <a href="{{ route('listener.playlists.show', $pl) }}" class="btn btn-primary rounded-circle shadow-lg" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-play"></i></a>
                            </div>
                        </div>
                        <div class="card-body p-0 mt-2">
                            <h6 class="text-white fw-bold mb-1 text-truncate"><a href="{{ route('listener.playlists.show', $pl) }}" class="text-decoration-none text-white hover-primary">{{ $pl->name }}</a></h6>
                            <p class="text-muted small mb-0">{{ $pl->songs_count }} Bài hát</p>
                        </div>
                    </div>
                </div>
                @endforeach
                <div class="col">
                    <div class="card h-100 border-0 text-center hover-scale shadow" style="background-color: var(--black-soft); border-radius: 12px; cursor: pointer; aspect-ratio: 1;" onclick="window.location.href='{{ route('listener.playlists.index') }}'">
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                            <i class="fa-solid fa-plus fs-1 mb-2"></i>
                            <span class="fw-bold">Tạo Playlist Của Bạn</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.nav-pills .nav-link.active { background-color: var(--white-main) !important; color: var(--black-main) !important; border-color: transparent !important; }
.hover-scale { transition: transform 0.2s; }
.hover-scale:hover { transform: translateY(-5px); }
</style>
@endsection
