@extends('layouts.main')
@section('title', $playlist->name . ' - Playlist')
@section('content')
@php
    $isOwner = (int) $playlist->user_id === (int) auth()->id();
    $canManagePlaylist = $isOwner && auth()->user()->canAccessPremium();
    $ownerName = $playlist->user?->name ?? 'Người dùng';
@endphp
<div class="container py-4" style="color: var(--text-primary);">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mb-5 align-items-end">
        <!-- Cover Art -->
        <div class="col-12 col-md-3 text-center text-md-start mb-4 mb-md-0">
            <div class="shadow-lg rounded-3 overflow-hidden d-inline-block" style="width: 230px; height: 230px;">
                <img src="{{ $playlist->getCoverUrl() }}" alt="Cover" class="w-100 h-100 object-fit-cover">
            </div>
        </div>
        
        <!-- Info & Actions -->
        <div class="col-12 col-md-9 pt-3">
            <span class="text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 0.8rem;">PLAYLIST CÁ NHÂN</span>
            <h1 class="display-3 fw-bold text-white mb-2" style="word-wrap: break-word;">{{ $playlist->name }}</h1>
            <p class="text-muted mb-3 fs-6">{{ $playlist->description ?? 'Chưa có mô tả' }}</p>
            <div class="d-flex align-items-center text-muted mb-4 small">
                <span class="fw-bold text-white me-2">{{ $ownerName }}</span>
                <i class="fa-solid fa-circle" style="font-size: 0.3rem;"></i>&nbsp;&nbsp; 
                {{ $playlist->songs->count() }} bài hát
            </div>
            
            <div class="d-flex gap-3 align-items-center">
                <button class="btn btn-primary rounded-circle shadow-lg hover-scale d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; font-size: 1.2rem;" onclick="playAllPlaylist()">
                    <i class="fa-solid fa-play"></i>
                </button>
                @if($canManagePlaylist)
                <button class="btn btn-outline-info rounded-pill" data-bs-toggle="modal" data-bs-target="#addSongModal"><i class="fa-solid fa-plus"></i> Thêm bài</button>
                <button class="btn btn-outline-light rounded-pill" data-bs-toggle="modal" data-bs-target="#editPlaylistModal"><i class="fa-solid fa-pen"></i> Chỉnh sửa</button>
                <form action="{{ route('listener.playlists.destroy', $playlist) }}" method="POST" class="d-inline" data-confirm-message="Bạn có chắc xoá toàn bộ playlist này không?" data-confirm-title="Xóa playlist">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger rounded-pill" data-confirm-message="Bạn có chắc xoá toàn bộ playlist này không?" data-confirm-title="Xóa playlist"><i class="fa-solid fa-trash-can"></i></button>
                </form>
                <div class="ms-auto" id="offlineSyncBlock">
                    <button class="btn rounded-pill border-0 text-success bg-success bg-opacity-10 fw-bold" onclick="downloadPlaylistAudio()" id="btnSyncOffline">
                        <i class="fa-solid fa-download me-2"></i>Tải nhạc về máy
                    </button>
                    <div id="storageStatus" class="small mt-1 text-muted text-end">Chỉ tải các bài không phải Premium.</div>
                </div>
                @elseif($isOwner)
                <a href="{{ route('subscription.index') }}" class="btn btn-outline-warning rounded-pill ms-auto">
                    <i class="fa-solid fa-crown me-1"></i>Nâng cấp Premium để chỉnh sửa playlist
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Tracks Order list (Sortable API) -->
    <div class="card bg-transparent border-0">
        <div class="card-header bg-transparent border-bottom border-dark px-2 pb-3 mb-2 d-flex">
            <div class="text-muted fw-bold" style="width: 50px;">#</div>
            <div class="text-muted fw-bold flex-grow-1">TIÊU ĐỀ</div>
            <div class="text-muted fw-bold text-end" style="width: 100px;"><i class="fa-regular fa-clock"></i></div>
            @if($canManagePlaylist)
            <div class="text-muted fw-bold text-center" style="width: 60px;">XÓA</div>
            @endif
        </div>
        <div class="list-group list-group-flush" id="sortableList">
            @forelse($playlist->songs as $idx => $song)
            <div class="list-group-item bg-transparent border-0 px-2 py-3 row-hover align-items-center d-flex sortable-item js-play-song" 
                 draggable="{{ $canManagePlaylist ? 'true' : 'false' }}" 
                 data-song-id="{{ $song->id }}" 
                 data-song-title="{{ $song->title }}" 
                 data-song-artist="{{ $song->artist?->getDisplayArtistName() }}" 
                 data-song-cover="{{ $song->getCoverUrl() }}" 
                 data-stream-url="{{ route('songs.stream', $song->id) }}" 
                 data-song-premium="{{ $song->is_vip ? '1' : '0' }}" 
                 data-id="{{ $song->id }}"
                 style="cursor: {{ $canManagePlaylist ? 'grab' : 'pointer' }}; transition: background 0.2s;">
                 
                <div class="d-flex align-items-center" style="width: 50px;">
                    <span class="text-muted item-idx">{{ $idx + 1 }}</span>
                    <i class="fa-solid fa-grip-vertical ms-2 text-secondary opacity-50 drag-handle"></i>
                </div>
                
                <div class="flex-grow-1 d-flex align-items-center">
                    <img src="{{ $song->getCoverUrl() }}" class="rounded me-3 object-fit-cover" style="width: 44px; height: 44px;">
                    <div>
                        <div class="text-white fw-bold">
                            {{ $song->title }}
                            @if($song->is_vip)
                                <i class="fa-solid fa-crown text-warning ms-1" style="font-size: 0.8rem;" title="Premium"></i>
                            @endif
                        </div>
                        <div class="text-muted small">{{ $song->artist?->getDisplayArtistName() }}</div>
                    </div>
                </div>

                <div class="text-muted text-end" style="width: 100px;">{{ $song->durationFormatted() }}</div>
                
                @if($canManagePlaylist)
                <div class="text-center" style="width: 60px;">
                    <form action="{{ route('listener.playlists.removeSong', $playlist) }}" method="POST">
                        @csrf @method('DELETE')
                        <input type="hidden" name="song_id" value="{{ $song->id }}">
                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="event.stopPropagation();" data-confirm-message="Xóa bài hát này khỏi playlist?" data-confirm-title="Xóa bài khỏi playlist"><i class="fa-regular fa-trash-can"></i></button>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="fa-solid fa-music fs-3 opacity-50 mb-2"></i>
                <p>Playlist này chưa có bài nhạc nào.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@if($canManagePlaylist)
{{-- Modal Edit --}}
<div class="modal fade" id="editPlaylistModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0" style="background-color: var(--black-soft);">
      <form action="{{ route('listener.playlists.update', $playlist) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="modal-header border-bottom border-dark">
          <h5 class="modal-title text-white fw-bold">Sửa Playlist</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-white">
          <div class="mb-3">
            <label class="form-label text-muted small text-uppercase">Tên Playlist <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control bg-dark text-white border-secondary @error('name') is-invalid @enderror" value="{{ old('name', $playlist->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small text-uppercase">Mô tả (Tuỳ chọn)</label>
            <textarea name="description" class="form-control bg-dark text-white border-secondary @error('description') is-invalid @enderror" rows="2">{{ old('description', $playlist->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small text-uppercase">Ảnh đại diện (Tuỳ chọn - Sẽ ghi đè)</label>
            <input type="file" name="cover_image" class="form-control bg-dark text-white border-secondary @error('cover_image') is-invalid @enderror" accept="image/*">
            @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text text-muted small">Khuyên dùng tỷ lệ 1:1, tối đa 2MB.</div>
          </div>
        </div>
        <div class="modal-footer border-top border-dark">
          <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary rounded-pill">Lưu thay đổi</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal Add Song --}}
<div class="modal fade" id="addSongModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0" style="background-color: var(--black-soft);">
      <div class="modal-header border-bottom border-dark">
        <h5 class="modal-title text-white fw-bold">Tìm kiếm & Thêm bài hát</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-white p-0">
         <div class="p-3 border-bottom border-dark position-sticky top-0 bg-dark" style="z-index: 10;">
             <div class="input-group">
                 <span class="input-group-text bg-transparent border-secondary text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                 <input type="text" id="songSearchInput" class="form-control bg-transparent text-white border-secondary" placeholder="Tên bài hát, ca sĩ..." autocomplete="off">
             </div>
         </div>
         <div id="songSearchResults" class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
             <div class="text-center p-4 text-muted small">
                 Gõ tên bài hát để tìm kiếm...
             </div>
         </div>
      </div>
    </div>
  </div>
</div>
@endif

<style>
.row-hover:hover { background-color: rgba(255,255,255,0.05) !important; border-radius: 8px; }
.sortable-item.dragging { opacity: 0.5; background-color: rgba(255,255,255,0.1) !important; }
</style>

@push('scripts')
<script>
// --- HTML5 Drag/Drop Sắp xếp Playlist ---
document.addEventListener('DOMContentLoaded', () => {
    const canManagePlaylist = @json($canManagePlaylist);
    const list = document.getElementById('sortableList');
    if (!list) return;
    
    let dragElement = null;

    list.addEventListener('dragstart', (e) => {
        if (!canManagePlaylist) return;
        const item = e.target.closest('.sortable-item');
        if (!item || !list.contains(item)) return;
        dragElement = item;
        e.dataTransfer.effectAllowed = 'move';
        setTimeout(() => dragElement.classList.add('dragging'), 0);
    });

    list.addEventListener('dragend', (e) => {
        if (!canManagePlaylist) return;
        if (dragElement) dragElement.classList.remove('dragging');
        dragElement = null;
        recalculateIndexes();
        saveNewOrder(); // Sync server Backend
    });

    list.addEventListener('dragover', (e) => {
        if (!canManagePlaylist) return;
        if (!dragElement) return;
        e.preventDefault();
        const afterElement = getDragAfterElement(list, e.clientY);
        if (afterElement == null) {
            list.appendChild(dragElement);
        } else if (afterElement !== dragElement) {
            list.insertBefore(dragElement, afterElement);
        } else {
            // no-op
        }
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.sortable-item:not(.dragging)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function recalculateIndexes() {
        list.querySelectorAll('.sortable-item').forEach((item, index) => {
            item.querySelector('.item-idx').textContent = index + 1;
        });
    }

    function saveNewOrder() {
        if (!canManagePlaylist) return;
        const orderedSongIds = [];
        list.querySelectorAll('.sortable-item').forEach((item, index) => {
            const sid = parseInt(item.dataset.id, 10);
            if (!Number.isNaN(sid)) {
                orderedSongIds.push(sid);
            }
        });
        
        fetch('{{ route('listener.playlists.reorder', $playlist) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order: orderedSongIds })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                showToast('Đã lưu vị trí bài hát trong playlist.', 'success');
            }
        });
    }

    // --- Search & Add Songs Logic ---
    const songSearchInput = document.getElementById('songSearchInput');
    const songSearchResults = document.getElementById('songSearchResults');
    const addSongModal = document.getElementById('addSongModal');
    let searchTimeout = null;

    if (addSongModal) {
        addSongModal.addEventListener('shown.bs.modal', function () {
            songSearchInput?.focus();
        });

        // Avoid aria-hidden warning when the modal starts hiding while an inner element is focused.
        addSongModal.addEventListener('hide.bs.modal', function () {
            if (addSongModal.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        });
    }

    if (songSearchInput) {
        songSearchInput.addEventListener('input', function() {
            if (!canManagePlaylist) return;
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            
            if (q.length < 2) {
                songSearchResults.innerHTML = '<div class="text-center p-4 text-muted small">Nhập ít nhất 2 ký tự để tìm kiếm...</div>';
                return;
            }

            songSearchResults.innerHTML = '<div class="text-center p-4 text-muted"><i class="fa-solid fa-spinner fa-spin fs-4"></i></div>';

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('listener.playlists.searchSongs', $playlist) }}?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(songs => {
                        if (songs.length === 0) {
                            songSearchResults.innerHTML = '<div class="text-center p-4 text-muted small">Không tìm thấy bài hát nào khớp.</div>';
                            return;
                        }
                        
                        let html = '';
                        songs.forEach(song => {
                           const btnHtml = song.is_added 
                               ? `<button class="btn btn-sm btn-secondary rounded-pill disabled" style="font-size: 0.75rem;"><i class="fa-solid fa-check me-1"></i>Đã thêm</button>` 
                               : `<button class="btn btn-sm btn-outline-primary rounded-pill btn-add-song" data-id="${song.id}" style="font-size: 0.75rem;"><i class="fa-solid fa-plus me-1"></i>Thêm vào</button>`;
                               
                           const vipIcon = song.is_vip ? '<i class="fa-solid fa-crown text-warning ms-1" style="font-size: 0.7rem;"></i>' : '';
                           
                           html += `
                               <div class="list-group-item bg-transparent text-white border-dark d-flex align-items-center py-2 px-3">
                                   <img src="${song.cover}" class="rounded me-3 object-fit-cover" style="width: 40px; height: 40px;">
                                   <div class="flex-grow-1">
                                       <div class="fw-bold fs-6 lh-sm">${song.title} ${vipIcon}</div>
                                       <div class="text-muted small">${song.artist} • ${song.duration}</div>
                                   </div>
                                   <div>
                                       ${btnHtml}
                                   </div>
                               </div>
                           `;
                        });
                        songSearchResults.innerHTML = html;
                    });
            }, 400);
        });
    }

    if (songSearchResults) {
        songSearchResults.addEventListener('click', function(e) {
            if (!canManagePlaylist) return;
            const btn = e.target.closest('.btn-add-song');
            if (!btn) return;
            
            const songId = btn.dataset.id;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch('{{ route('listener.playlists.addSong', $playlist) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ song_id: songId })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-secondary');
                    btn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Đã thêm';
                    btn.classList.remove('btn-add-song');
                    
                    showToast('Đã thêm bài hát vào Playlist. Vui lòng tải lại trang (F5) để thấy bài báo hiển thị ngoài màn hình.', 'success');
                } else {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    showToast(data.message || 'Lỗi thêm bài hát', 'danger');
                }
            }).catch(err => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                showToast('Lỗi mạng', 'danger');
            });
        });
    }

// --- Validation Fallback HTMX ---
    @if($errors->any())
        if (typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editPlaylistModal')).show();
        }
    @endif
});

function downloadPlaylistAudio() {
    window.location.href = '{{ route('listener.playlists.download', $playlist) }}';
}

window.playAllPlaylist = () => {
    document.querySelector('.js-play-song')?.click(); 
};

</script>
@endpush
@endsection
