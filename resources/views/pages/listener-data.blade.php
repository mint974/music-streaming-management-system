@extends('layouts.main')

@section('title', 'Dữ liệu Listener')

@section('content')
<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->has('listener'))
        <div class="alert alert-danger">{{ $errors->first('listener') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0 text-white">Dữ liệu Listener</h4>
        <a href="{{ route('listener.settings') }}" class="btn btn-outline-light btn-sm">
            <i class="fa-solid fa-gear me-1"></i>Cài đặt thông báo
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100" style="background:#111827;border:1px solid #1f2937">
                <div class="card-body">
                    <h6 class="text-info mb-3"><i class="fa-solid fa-user-plus me-2"></i>Nghệ sĩ đang theo dõi ({{ $followedArtists->count() }})</h6>
                    @forelse($followedArtists as $follow)
                        @php $artist = $follow->artist; @endphp
                        @if($artist)
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $artist->getAvatarUrl() }}" alt="{{ $artist->getDisplayArtistName() }}" style="width:32px;height:32px;border-radius:50%">
                                <div>
                                    <div class="text-white small fw-semibold">{{ $artist->getDisplayArtistName() }}</div>
                                    <div class="text-muted" style="font-size:.75rem">Theo dõi từ {{ $follow->created_at?->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('listener.artist.toggleFollow', $artist->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">Hủy theo dõi</button>
                            </form>
                        </div>
                        @endif
                    @empty
                        <p class="text-muted mb-0">Bạn chưa theo dõi nghệ sĩ nào.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100" style="background:#111827;border:1px solid #1f2937">
                <div class="card-body">
                    <h6 class="text-warning mb-3"><i class="fa-solid fa-bookmark me-2"></i>Album đã lưu ({{ $savedAlbums->count() }})</h6>
                    @forelse($savedAlbums as $saved)
                        @php $album = $saved->album; @endphp
                        @if($album)
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $album->getCoverUrl() }}" alt="{{ $album->title }}" style="width:32px;height:32px;border-radius:6px;object-fit:cover">
                                <div>
                                    <div class="text-white small fw-semibold">{{ $album->title }}</div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $album->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}</div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('listener.album.toggleSave', $album->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-warning">Bỏ lưu</button>
                            </form>
                        </div>
                        @endif
                    @empty
                        <p class="text-muted mb-0">Bạn chưa lưu album nào.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card" style="background:#111827;border:1px solid #1f2937">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="text-success mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử nghe gần đây ({{ $recentHistory->count() }})</h6>
                        <div class="d-flex gap-2">
                            <a href="{{ route('listener.history') }}" class="btn btn-sm btn-outline-light">Xem đầy đủ</a>
                            <form method="POST" action="{{ route('listener.history.clear') }}" onsubmit="return confirm('Xóa toàn bộ lịch sử nghe?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Xóa toàn bộ</button>
                            </form>
                        </div>
                    </div>

                    @forelse($recentHistory as $item)
                        @php $song = $item->song; @endphp
                        @if($song)
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                            <div>
                                <div class="text-white small fw-semibold">{{ $song->title }}</div>
                                <div class="text-muted" style="font-size:.75rem">
                                    {{ $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }} • {{ $item->listened_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary js-play-song"
                                data-song-id="{{ $song->id }}"
                                data-song-title="{{ e($song->title) }}"
                                data-song-artist="{{ e($song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                                data-song-cover="{{ $song->getCoverUrl() }}"
                                data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                                data-stream-url="{{ route('songs.stream', $song->id) }}">
                                Phát lại
                            </button>
                        </div>
                        @endif
                    @empty
                        <p class="text-muted mb-0">Chưa có dữ liệu lịch sử nghe.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card" style="background:#111827;border:1px solid #1f2937">
                <div class="card-body">
                    <h6 class="text-secondary mb-3"><i class="fa-solid fa-bell me-2"></i>Thiết lập thông báo nhanh</h6>
                    <div class="d-flex flex-wrap gap-3 text-light small">
                        <span>Mở thông báo bài mới: <strong>{{ $notificationSetting->notify_new_song ? 'Bật' : 'Tắt' }}</strong></span>
                        <span>Mở thông báo album mới: <strong>{{ $notificationSetting->notify_new_album ? 'Bật' : 'Tắt' }}</strong></span>
                        <span>In-app: <strong>{{ $notificationSetting->notify_in_app ? 'Bật' : 'Tắt' }}</strong></span>
                        <span>Email: <strong>{{ $notificationSetting->notify_email ? 'Bật' : 'Tắt' }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
