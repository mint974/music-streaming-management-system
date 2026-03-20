@extends('layouts.main')
@section('title', 'Playlist của tôi - Blue Wave Music')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white mb-0">Playlist của tôi</h2>
        <button class="btn btn-primary rounded-pill py-2 px-4 shadow" data-bs-toggle="modal" data-bs-target="#createPlaylistModal">
            <i class="fa-solid fa-plus me-2"></i>Tạo Playlist
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4 mt-2">
        @forelse($playlists as $pl)
        <div class="col">
            <div class="card h-100 border-0 bg-transparent" style="transition: transform 0.2s;">
                <div class="position-relative overflow-hidden rounded-3 shadow-sm mb-2" style="aspect-ratio: 1;">
                    <img src="{{ $pl->getCoverUrl() }}" class="w-100 h-100 object-fit-cover" alt="{{ $pl->name }}">
                    <div class="position-absolute w-100 h-100 top-0 start-0 d-flex justify-content-center align-items-center" style="background: rgba(0,0,0,0.4); opacity: 0; transition: opacity 0.2s; cursor: pointer;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0" onclick="window.location.href='{{ route('listener.playlists.show', $pl) }}'">
                        <a href="{{ route('listener.playlists.show', $pl) }}" class="btn btn-primary rounded-circle shadow-lg" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-play"></i></a>
                    </div>
                </div>
                <div class="card-body p-0 mt-2">
                    <h6 class="text-white fw-bold mb-1 text-truncate"><a href="{{ route('listener.playlists.show', $pl) }}" class="text-decoration-none text-white hover-primary">{{ $pl->name }}</a></h6>
                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">{{ $pl->songs_count }} Bài hát</p>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 w-100">
            <div class="text-center py-5 text-muted" style="background-color: var(--black-soft); border-radius: 12px;">
                <i class="fa-solid fa-compact-disc fs-1 mb-3 opacity-50"></i>
                <p>Bạn chưa có Playlist nào. Hãy tạo một playlist mới để lưu các bản nhạc yêu thích.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

{{-- Modal Create --}}
<div class="modal fade" id="createPlaylistModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0" style="background-color: var(--black-soft);">
      <form action="{{ route('listener.playlists.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header border-bottom border-dark">
          <h5 class="modal-title text-white fw-bold">Tạo Playlist Mới</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-white">
          <div class="mb-3">
            <label class="form-label text-muted small text-uppercase">Tên Playlist <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control bg-dark text-white border-secondary" required placeholder="Nhập tên playlist...">
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small text-uppercase">Mô tả (Tuỳ chọn)</label>
            <textarea name="description" class="form-control bg-dark text-white border-secondary" rows="2" placeholder="Ghi chú thêm về playlist này..."></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small text-uppercase">Ảnh đại diện (Tuỳ chọn)</label>
            <input type="file" name="cover_image" class="form-control bg-dark text-white border-secondary" accept="image/*">
          </div>
        </div>
        <div class="modal-footer border-top border-dark">
          <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary rounded-pill">Tạo mới</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
