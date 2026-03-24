@extends('layouts.main')
@section('title', $playlist->name . ' - Playlist')
@section('content')
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
                <span class="fw-bold text-white me-2">{{ auth()->user()->name }}</span> 
                <i class="fa-solid fa-circle" style="font-size: 0.3rem;"></i>&nbsp;&nbsp; 
                {{ $playlist->songs->count() }} bài hát
            </div>
            
            <div class="d-flex gap-3 align-items-center">
                <button class="btn btn-primary rounded-circle shadow-lg hover-scale d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; font-size: 1.2rem;" onclick="playAllPlaylist()">
                    <i class="fa-solid fa-play"></i>
                </button>
                <button class="btn btn-outline-light rounded-pill" data-bs-toggle="modal" data-bs-target="#editPlaylistModal"><i class="fa-solid fa-pen"></i> Chỉnh sửa</button>
                <form action="{{ route('listener.playlists.destroy', $playlist) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger rounded-pill" onclick="return confirm('Bạn có chắc xoá toàn bộ playlist này không?');"><i class="fa-solid fa-trash-can"></i></button>
                </form>

                @if(auth()->user()->isPremium())
                {{-- Offline Feature Check --}}
                <div class="ms-auto" id="offlineSyncBlock">
                    <button class="btn rounded-pill border-0 text-success bg-success bg-opacity-10 fw-bold" onclick="syncPlaylistOffline()" id="btnSyncOffline">
                        <i class="fa-solid fa-download me-2"></i>Tải Playlist Mạng (Premium)
                    </button>
                    <div id="storageStatus" class="small mt-1 text-muted text-end"></div>
                </div>
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
            <div class="text-muted fw-bold text-center" style="width: 60px;">XÓA</div>
        </div>
        <div class="list-group list-group-flush" id="sortableList">
            @forelse($playlist->songs as $idx => $song)
            <div class="list-group-item bg-transparent border-0 px-2 py-3 row-hover align-items-center d-flex sortable-item js-play-song" 
                 draggable="true" 
                 data-song-id="{{ $song->id }}" 
                 data-song-title="{{ $song->title }}" 
                 data-song-artist="{{ $song->artist?->getDisplayArtistName() }}" 
                 data-song-cover="{{ $song->getCoverUrl() }}" 
                 data-stream-url="{{ $song->streamUrl ?? $song->getAudioUrl() }}" 
                 data-song-premium="{{ $song->is_vip ? '1' : '0' }}" 
                 data-id="{{ $song->id }}"
                 style="cursor: grab; transition: background 0.2s;">
                 
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
                
                <div class="text-center" style="width: 60px;">
                    <form action="{{ route('listener.playlists.removeSong', $playlist) }}" method="POST">
                        @csrf @method('DELETE')
                        <input type="hidden" name="song_id" value="{{ $song->id }}">
                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="event.stopPropagation();"><i class="fa-regular fa-trash-can"></i></button>
                    </form>
                </div>
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
            <input type="text" name="name" class="form-control bg-dark text-white border-secondary" value="{{ $playlist->name }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small text-uppercase">Mô tả (Tuỳ chọn)</label>
            <textarea name="description" class="form-control bg-dark text-white border-secondary" rows="2">{{ $playlist->description }}</textarea>
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small text-uppercase">Ảnh đại diện (Tuỳ chọn - Sẽ ghi đè)</label>
            <input type="file" name="cover_image" class="form-control bg-dark text-white border-secondary" accept="image/*">
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

<style>
.row-hover:hover { background-color: rgba(255,255,255,0.05) !important; border-radius: 8px; }
.sortable-item.dragging { opacity: 0.5; background-color: rgba(255,255,255,0.1) !important; }
</style>

@push('scripts')
<script>
// --- HTML5 Drag/Drop Sắp xếp Playlist ---
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('sortableList');
    if (!list) return;
    
    let dragElement = null;

    list.addEventListener('dragstart', (e) => {
        if (!e.target.classList.contains('sortable-item')) return;
        dragElement = e.target;
        e.dataTransfer.effectAllowed = 'move';
        setTimeout(() => dragElement.classList.add('dragging'), 0);
    });

    list.addEventListener('dragend', (e) => {
        if (dragElement) dragElement.classList.remove('dragging');
        dragElement = null;
        recalculateIndexes();
        saveNewOrder(); // Sync server Backend
    });

    list.addEventListener('dragover', (e) => {
        e.preventDefault();
        const afterElement = getDragAfterElement(list, e.clientY);
        if (afterElement == null) {
            list.appendChild(dragElement);
        } else {
            list.insertBefore(dragElement, afterElement);
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
        const orderData = {};
        list.querySelectorAll('.sortable-item').forEach((item, index) => {
            const sid = item.dataset.id;
            orderData[sid] = index;
        });
        
        fetch('{{ route('listener.playlists.reorder', $playlist) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order: orderData })
        });
    }
});

// --- PWA Offline Cache Download Sync -- Premium Feature ---
async function syncPlaylistOffline() {
    const btn = document.getElementById('btnSyncOffline');
    const status = document.getElementById('storageStatus');
    const songs = Array.from(document.querySelectorAll('.js-play-song')).map(s => s.dataset.streamUrl).filter(url => url);
    
    if(!('caches' in window)) {
        alert("Trình duyệt không hỗ trợ Cache Storage API tải Offline!");
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Đang tải xuống...';

    try {
        const cache = await caches.open('bwm-offline-tunes-v1');
        let downloaded = 0;
        for (const url of songs) {
            const response = await caches.match(url);
            if (!response) {
                await cache.add(url);
            }
            downloaded++;
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-2"></i>Đang tải... (${downloaded}/${songs.length})`;
        }
        btn.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i>Đã lưu Offline';
        btn.classList.add('bg-success', 'text-white');
        updateStorageEstimate();
    } catch (e) {
        console.error(e);
        btn.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-2"></i>Lỗi tải xuống';
    } finally {
        btn.disabled = false;
    }
}

async function updateStorageEstimate() {
    try {
        if(navigator.storage && navigator.storage.estimate) {
            const est = await navigator.storage.estimate();
            let usageMB = (est.usage / (1024*1024)).toFixed(2);
            document.getElementById('storageStatus').innerHTML = `Đã dùng dữ liệu Cache: <b>${usageMB} MB</b><br><a href="#" onclick="clearCacheApp()" class="text-danger">Xóa dữ liệu Offline</a>`;
        }
    } catch(e){}
}

window.clearCacheApp = async () => {
    if(confirm('Bạn có chắc muốn xóa tất cả bộ nhớ bài hát Offline đã tải về máy không?')) {
         await caches.delete('bwm-offline-tunes-v1');
         alert('Đã dọn dẹp dung lượng tải nhạc về!');
         location.reload();
    }
};

window.playAllPlaylist = () => {
    document.querySelector('.js-play-song')?.click(); 
};

// Auto fetch estimate if premium elements exist
setTimeout(updateStorageEstimate, 500);

</script>
@endpush
@endsection
