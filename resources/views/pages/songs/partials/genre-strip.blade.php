<div class="songs-genre-strip" role="tablist" aria-label="Lọc theo thể loại">
    <a href="{{ route('songs.index', array_merge(request()->except('page'), ['genre_id' => 0])) }}"
       class="genre-chip {{ $genreId === 0 ? 'is-active' : '' }}">
        Tất cả
    </a>

    @foreach($genres as $genre)
        <a href="{{ route('songs.index', array_merge(request()->except('page'), ['genre_id' => $genre->id])) }}"
           class="genre-chip {{ $genreId === (int) $genre->id ? 'is-active' : '' }}">
            {{ $genre->name }}
        </a>
    @endforeach
</div>
