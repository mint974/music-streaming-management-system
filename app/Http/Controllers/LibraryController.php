<?php

namespace App\Http\Controllers;

use App\Models\SavedAlbum;
use App\Models\Song;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Nghệ sĩ đang theo dõi
        $follows = $user->artistFollows()
            ->with('followedArtistProfile.user')
            ->get();

        $profileIds = $follows
            ->pluck('followed_artist_profile_id')
            ->filter()
            ->unique()
            ->values();

        $songCounts = Song::query()
            ->published()
            ->whereIn('artist_profile_id', $profileIds)
            ->selectRaw('artist_profile_id, COUNT(*) as aggregate')
            ->groupBy('artist_profile_id')
            ->pluck('aggregate', 'artist_profile_id');

        $followedArtists = $follows
            ->map(function ($follow) use ($songCounts) {
                $artist = $follow->followedArtistProfile?->user;

                if (!$artist) {
                    return null;
                }

                $artist->songs_count = (int) ($songCounts[$follow->followed_artist_profile_id] ?? 0);

                return $artist;
            })
            ->filter()
            ->values();
            
        // Playlists
        $playlists = $user->playlists()->withCount('songs')->latest()->get();
        
        // Albums đã lưu
        $savedAlbums = SavedAlbum::with(['album.artistProfile', 'album.artistProfile.user'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->pluck('album');

        return view('pages.library.index', compact('followedArtists', 'playlists', 'savedAlbums'));
    }
}
