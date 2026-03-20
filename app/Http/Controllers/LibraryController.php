<?php

namespace App\Http\Controllers;

use App\Models\SavedAlbum;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Nghệ sĩ đang theo dõi
        $followedArtists = $user->followedArtists()
            ->withCount(['artistSongs as songs_count' => function ($query) {
                $query->published();
            }])
            ->get();
            
        // Playlists
        $playlists = $user->playlists()->withCount('songs')->latest()->get();
        
        // Albums đã lưu
        $savedAlbums = SavedAlbum::with(['album.artist'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->pluck('album');

        return view('pages.library.index', compact('followedArtists', 'playlists', 'savedAlbums'));
    }
}
