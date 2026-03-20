<div class="song-list">
    <table class="table">
        <thead>
            <tr>
                <th class="col-number">#</th>
                <th class="col-title">Title</th>
                <th class="col-plays"><i class="fa-solid fa-play"></i></th>
                <th class="col-duration"><i class="fa-regular fa-clock"></i></th>
            </tr>
        </thead>
        <tbody>
            @forelse($songs ?? [] as $index => $song)
                <tr class="song-row" data-song-id="{{ $song->id }}">
                    <td class="col-number">
                        <span class="song-number">{{ $index + 1 }}</span>
                        <button class="btn btn-play-sm js-play-song"
                                data-song-id="{{ $song->id }}"
                                data-song-title="{{ e($song->title) }}"
                                data-song-artist="{{ e($song->artist?->getDisplayArtistName() ?? 'Artist') }}"
                                data-song-cover="{{ $song->getCoverUrl() }}"
                                data-stream-url="{{ route('songs.stream', $song->id) }}">
                            <i class="fa-solid fa-play"></i>
                        </button>
                    </td>
                    <td class="col-title">
                        <div class="song-info">
                            <img src="{{ $song->getCoverUrl() }}"
                                 alt="{{ $song->title }}"
                                 class="song-thumb"
                                 loading="lazy"
                                 decoding="async">
                            <div>
                                <a href="{{ route('songs.show', $song->id) }}" class="song-title-link">
                                    <h6 class="song-title">{{ $song->title }}</h6>
                                </a>
                                <p class="song-artist">{{ $song->artist?->getDisplayArtistName() ?? 'Artist' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="col-plays">{{ number_format((int) $song->listens) }}</td>
                    <td class="col-duration">
                        @auth
                        <form method="POST" action="{{ route('listener.song.toggleFavorite', $song->id) }}" style="display: inline;">
                            @csrf
                            <button class="btn btn-icon-xs btn-favorite-song" type="submit">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                        </form>
                        @endauth
                        <span class="song-duration">{{ $song->durationFormatted() }}</span>
                        <button class="btn btn-icon-xs btn-options">
                            <i class="fa-solid fa-ellipsis"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
                        <p>No songs available</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
