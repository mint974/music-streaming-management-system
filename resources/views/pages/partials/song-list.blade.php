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
            @php
                $songs = [
                    ['id' => 1, 'title' => 'Midnight Dreams', 'artist' => 'Dave', 'plays' => '2,451,234', 'duration' => '3:24', 'image' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%23e11d48'%3E1%3C/text%3E%3C/svg%3E"],
                    ['id' => 2, 'title' => 'Neon Lights', 'artist' => 'Dave', 'plays' => '1,892,456', 'duration' => '4:12', 'image' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%238b5cf6'%3E2%3C/text%3E%3C/svg%3E"],
                    ['id' => 3, 'title' => 'Ocean Breeze', 'artist' => 'Dave', 'plays' => '3,123,789', 'duration' => '2:58', 'image' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%233b82f6'%3E3%3C/text%3E%3C/svg%3E"],
                    ['id' => 4, 'title' => 'Sunset Boulevard', 'artist' => 'Dave', 'plays' => '987,234', 'duration' => '3:45', 'image' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%23f59e0b'%3E4%3C/text%3E%3C/svg%3E"],
                    ['id' => 5, 'title' => 'Electric Soul', 'artist' => 'Dave', 'plays' => '2,567,890', 'duration' => '4:01', 'image' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%2310b981'%3E5%3C/text%3E%3C/svg%3E"],
                    ['id' => 6, 'title' => 'Velvet Sky', 'artist' => 'Dave', 'plays' => '1,456,123', 'duration' => '3:33', 'image' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%23ec4899'%3E6%3C/text%3E%3C/svg%3E"],
                    ['id' => 7, 'title' => 'Crystal Rain', 'artist' => 'Dave', 'plays' => '2,234,567', 'duration' => '2:47', 'image' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%2306b6d4'%3E7%3C/text%3E%3C/svg%3E"],
                    ['id' => 8, 'title' => 'Golden Hour', 'artist' => 'Dave', 'plays' => '3,789,012', 'duration' => '4:22', 'image' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%23eab308'%3E8%3C/text%3E%3C/svg%3E"],
                    ['id' => 9, 'title' => 'Urban Jungle', 'artist' => 'Dave', 'plays' => '1,678,345', 'duration' => '3:56', 'image' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%2322c55e'%3E9%3C/text%3E%3C/svg%3E"],
                    ['id' => 10, 'title' => 'Cosmic Dance', 'artist' => 'Dave', 'plays' => '2,901,234', 'duration' => '3:18', 'image' => "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='40' height='40' fill='%231e293b'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='18' fill='%23a855f7'%3E10%3C/text%3E%3C/svg%3E"],
                ];
            @endphp

            @foreach($songs as $index => $song)
                <tr class="song-row" data-song-id="{{ $song['id'] }}">
                    <td class="col-number">
                        <span class="song-number">{{ $song['id'] }}</span>
                        <button class="btn btn-play-sm">
                            <i class="fa-solid fa-play"></i>
                        </button>
                    </td>
                    <td class="col-title">
                        <div class="song-info">
                            <img src="{{ $song['image'] }}" 
                                 alt="{{ $song['title'] }}" 
                                 class="song-thumb"
                                 loading="lazy"
                                 decoding="async">
                            <div>
                                <h6 class="song-title">{{ $song['title'] }}</h6>
                                <p class="song-artist">{{ $song['artist'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="col-plays">{{ $song['plays'] }}</td>
                    <td class="col-duration">
                        <button class="btn btn-icon-xs btn-favorite-song">
                            <i class="fa-regular fa-heart"></i>
                        </button>
                        <span class="song-duration">{{ $song['duration'] }}</span>
                        <button class="btn btn-icon-xs btn-options">
                            <i class="fa-solid fa-ellipsis"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
