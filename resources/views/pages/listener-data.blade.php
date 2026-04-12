@extends('layouts.main')

@section('title', 'Dữ liệu Listener')

@section('content')
<div class="container py-4 listener-v2">
    <div class="listener-v2-hero mb-4">
        <div class="listener-v2-hero-content">
            <p class="listener-v2-kicker mb-2">Không gian Listener</p>
            <h3 class="listener-v2-title mb-2">Thư viện cá nhân của bạn</h3>
            <p class="listener-v2-subtitle mb-0">Theo dõi nghệ sĩ, lưu album, quản lý bài hát yêu thích và quay lại những bài bạn đã nghe gần đây trong một giao diện tập trung.</p>
        </div>
        <div class="listener-v2-hero-actions">
            <a href="{{ route('listener.history') }}" class="btn mm-btn mm-btn-primary">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Lịch sử nghe
            </a>
            <a href="{{ route('listener.settings') }}" class="btn mm-btn mm-btn-outline">
                <i class="fa-solid fa-gear"></i>
                Cài đặt thông báo
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="listener-v2-stat">
                <div class="listener-v2-stat-label">Nghệ sĩ theo dõi</div>
                <div class="listener-v2-stat-value">{{ number_format((int) $followedArtistsTotal) }}</div>
                <div class="listener-v2-stat-note">Trong thư viện của bạn</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="listener-v2-stat">
                <div class="listener-v2-stat-label">Album đã lưu</div>
                <div class="listener-v2-stat-value">{{ number_format((int) $savedAlbumsTotal) }}</div>
                <div class="listener-v2-stat-note">Sẵn sàng phát lại</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="listener-v2-stat listener-v2-stat-highlight">
                <div class="listener-v2-stat-label">Bài hát yêu thích</div>
                <div class="listener-v2-stat-value">{{ number_format((int) $favoriteSongsTotal) }}</div>
                <div class="listener-v2-stat-note">Danh sách nổi bật</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="listener-v2-stat">
                <div class="listener-v2-stat-label">Lịch sử gần đây</div>
                <div class="listener-v2-stat-value">{{ number_format($recentHistory->count()) }}</div>
                <div class="listener-v2-stat-note">Bản ghi mới nhất</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-4">
            <section class="listener-v2-section h-100">
                <header class="listener-v2-section-header">
                    <div>
                        <h5 class="listener-v2-section-title mb-1">Nghệ sĩ đang theo dõi</h5>
                        <p class="listener-v2-section-sub mb-0">{{ number_format((int) $followedArtistsTotal) }} nghệ sĩ</p>
                    </div>
                    <a href="{{ route('library.index') }}" class="btn mm-btn mm-btn-ghost btn-sm">Thư viện</a>
                </header>

                <div class="listener-v2-stack">
                    @php $artistShown = 0; @endphp
                    @forelse($followedArtists as $follow)
                        @php $artist = $follow->artist; @endphp
                        @if($artist)
                            @if($artistShown >= 4)
                                @break
                            @endif
                            @php $artistShown++; @endphp
                            <article class="listener-v2-item">
                                <a href="{{ route('search.artist.show', $artist->id) }}" class="flex-shrink-0">
                                    <img src="{{ $artist->getAvatarUrl() }}" alt="{{ $artist->getDisplayArtistName() }}" class="listener-v2-item-avatar">
                                </a>
                                <div class="listener-v2-item-content">
                                    <a href="{{ route('search.artist.show', $artist->id) }}" class="listener-v2-item-title">{{ $artist->getDisplayArtistName() }}</a>
                                    <div class="listener-v2-item-meta">Theo dõi từ {{ $follow->created_at?->format('d/m/Y') }}</div>
                                    @if($artist->bio)
                                        <div class="listener-v2-item-desc">{{ $artist->bio }}</div>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('listener.artist.toggleFollow', $artist->id) }}" class="m-0" data-confirm-message="Hủy theo dõi nghệ sĩ {{ $artist->getDisplayArtistName() }}?" data-confirm-title="Hủy theo dõi">
                                    @csrf
                                    <button type="submit" class="btn mm-btn mm-btn-danger btn-sm">Hủy</button>
                                </form>
                            </article>
                        @endif
                    @empty
                        <div class="listener-v2-empty">
                            <i class="fa-solid fa-user-group"></i>
                            <span>Bạn chưa theo dõi nghệ sĩ nào.</span>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-4">
            <section class="listener-v2-section h-100">
                <header class="listener-v2-section-header">
                    <div>
                        <h5 class="listener-v2-section-title mb-1">Album đã lưu</h5>
                        <p class="listener-v2-section-sub mb-0">{{ number_format((int) $savedAlbumsTotal) }} album</p>
                    </div>
                    <a href="{{ route('listener.albums') }}" class="btn mm-btn mm-btn-ghost btn-sm">Xem đầy đủ</a>
                </header>

                <div class="listener-v2-stack">
                    @php $albumShown = 0; @endphp
                    @forelse($savedAlbums as $saved)
                        @php $album = $saved->album; @endphp
                        @if($album)
                            @if($albumShown >= 4)
                                @break
                            @endif
                            @php $albumShown++; @endphp
                            <article class="listener-v2-item listener-v2-item-album">
                                <a href="{{ route('albums.show', $album->id) }}" class="flex-shrink-0">
                                    <img src="{{ $album->getCoverUrl() }}" alt="{{ $album->title }}" class="listener-v2-item-cover">
                                </a>
                                <div class="listener-v2-item-content">
                                    <a href="{{ route('albums.show', $album->id) }}" class="listener-v2-item-title">{{ $album->title }}</a>
                                    <div class="listener-v2-item-meta">{{ $album->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}</div>
                                    <div class="listener-v2-item-meta">Đã lưu {{ $saved->created_at?->format('d/m/Y') }}</div>
                                    <div class="listener-v2-item-actions">
                                        <a href="{{ route('albums.show', $album->id) }}" class="btn mm-btn mm-btn-outline btn-sm">Chi tiết</a>
                                        <form method="POST" action="{{ route('listener.album.toggleSave', $album->id) }}" class="m-0" data-confirm-message="Bỏ lưu album {{ $album->title }}?" data-confirm-title="Bỏ lưu album">
                                            @csrf
                                            <button type="submit" class="btn mm-btn mm-btn-danger btn-sm">Bỏ lưu</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endif
                    @empty
                        <div class="listener-v2-empty">
                            <i class="fa-solid fa-compact-disc"></i>
                            <span>Bạn chưa lưu album nào.</span>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-4">
            <section class="listener-v2-section h-100">
                <header class="listener-v2-section-header">
                    <div>
                        <h5 class="listener-v2-section-title mb-1">Bài hát yêu thích</h5>
                        <p class="listener-v2-section-sub mb-0">{{ number_format((int) $favoriteSongsTotal) }} bài hát</p>
                    </div>
                    <a href="{{ route('listener.favorites') }}" class="btn mm-btn mm-btn-ghost btn-sm">Xem đầy đủ</a>
                </header>

                <div class="listener-v2-stack">
                    @php $favoriteShown = 0; @endphp
                    @forelse($favoriteSongs as $item)
                        @php $song = $item->song; @endphp
                        @if($song)
                            @if($favoriteShown >= 4)
                                @break
                            @endif
                            @php $favoriteShown++; @endphp
                            <article class="listener-v2-item listener-v2-item-song">
                                <a href="{{ route('songs.show', $song->id) }}" class="flex-shrink-0">
                                    <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}" class="listener-v2-item-cover-sm">
                                </a>
                                <div class="listener-v2-item-content">
                                    <a href="{{ route('songs.show', $song->id) }}" class="listener-v2-item-title">{{ $song->title }}</a>
                                    <div class="listener-v2-item-meta">
                                        {{ $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}
                                        @if($song->album)
                                            <span class="mx-1">•</span>{{ $song->album->title }}
                                        @endif
                                    </div>
                                    <div class="listener-v2-item-actions">
                                        <button
                                            type="button"
                                            class="btn mm-btn mm-btn-primary btn-sm js-play-song"
                                            data-song-id="{{ $song->id }}"
                                            data-song-title="{{ e($song->title) }}"
                                            data-song-artist="{{ e($song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                                            data-song-cover="{{ $song->getCoverUrl() }}"
                                            data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                                            data-stream-url="{{ route('songs.stream', $song->id) }}">
                                            Phát
                                        </button>
                                        <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}" class="m-0" data-confirm-message="Bỏ thích bài hát {{ $song->title }}?" data-confirm-title="Bỏ thích bài hát">
                                            @csrf
                                            <button type="submit" class="btn mm-btn mm-btn-danger btn-sm">Bỏ thích</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @endif
                    @empty
                        <div class="listener-v2-empty">
                            <i class="fa-solid fa-heart"></i>
                            <span>Bạn chưa có bài hát yêu thích.</span>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <div class="listener-v2-history mb-4">
        <div class="listener-v2-history-head">
            <div>
                <h5 class="listener-v2-section-title mb-1">Lịch sử nghe gần đây</h5>
                <p class="listener-v2-section-sub mb-0">{{ number_format($recentHistory->count()) }} bản ghi gần nhất</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('listener.history') }}" class="btn mm-btn mm-btn-outline btn-sm">Xem đầy đủ</a>
                <form method="POST" action="{{ route('listener.history.clear') }}" class="m-0" data-confirm-message="Xóa toàn bộ lịch sử nghe?" data-confirm-title="Xóa lịch sử nghe">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn mm-btn mm-btn-danger btn-sm">Xóa toàn bộ</button>
                </form>
            </div>
        </div>

        <div class="listener-v2-history-grid">
            @php $historyShown = 0; @endphp
            @forelse($recentHistory as $item)
                @php $song = $item->song; @endphp
                @if($song)
                    @if($historyShown >= 6)
                        @break
                    @endif
                    @php $historyShown++; @endphp
                    <article class="listener-v2-history-card">
                        <a href="{{ route('songs.show', $song->id) }}" class="listener-v2-history-thumb-wrap">
                            <img src="{{ $song->getCoverUrl() }}" alt="{{ $song->title }}" class="listener-v2-history-thumb">
                        </a>
                        <div class="listener-v2-history-content">
                            <a href="{{ route('songs.show', $song->id) }}" class="listener-v2-item-title">{{ $song->title }}</a>
                            <div class="listener-v2-item-meta">
                                {{ $song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ' }}
                                @if($song->album)
                                    <span class="mx-1">•</span>{{ $song->album->title }}
                                @endif
                            </div>
                            <div class="listener-v2-item-meta">{{ $item->listened_at?->format('d/m/Y H:i') }}</div>
                        </div>
                        <button
                            type="button"
                            class="btn mm-btn mm-btn-primary btn-sm js-play-song"
                            data-song-id="{{ $song->id }}"
                            data-song-title="{{ e($song->title) }}"
                            data-song-artist="{{ e($song->artist?->getDisplayArtistName() ?? 'Nghệ sĩ') }}"
                            data-song-cover="{{ $song->getCoverUrl() }}"
                            data-song-premium="{{ $song->is_vip ? '1' : '0' }}"
                            data-stream-url="{{ route('songs.stream', $song->id) }}">
                            Phát lại
                        </button>
                    </article>
                @endif
            @empty
                <div class="listener-v2-empty w-100">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Chưa có dữ liệu lịch sử nghe.</span>
                </div>
            @endforelse
        </div>
    </div>

    <div class="listener-v2-notify">
        <h5 class="listener-v2-section-title mb-3">Thiết lập thông báo nhanh</h5>
        <div class="listener-v2-notify-list">
            <span>Mở thông báo bài mới: <strong>{{ $notificationSetting->notify_new_song ? 'Bật' : 'Tắt' }}</strong></span>
            <span>Mở thông báo album mới: <strong>{{ $notificationSetting->notify_new_album ? 'Bật' : 'Tắt' }}</strong></span>
            <span>In-app: <strong>{{ $notificationSetting->notify_in_app ? 'Bật' : 'Tắt' }}</strong></span>
            <span>Email: <strong>{{ $notificationSetting->notify_email ? 'Bật' : 'Tắt' }}</strong></span>
        </div>
    </div>
    </div>
</div>
@endsection
