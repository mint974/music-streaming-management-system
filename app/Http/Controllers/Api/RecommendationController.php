<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    /**
     * Lấy danh sách gợi ý cho một user
     */
    public function userRecommendations($user_id)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("http://127.0.0.1:5000/api/recommend/{$user_id}");

            if ($response->successful() && $response->json('success')) {
                $recommendationsRaw = $response->json('recommendations');
                
                $songIds = collect($recommendationsRaw)->pluck('song_id')->toArray();
                
                if (empty($songIds)) {
                    $songs = \App\Models\Song::with('artistProfile:id,stage_name')
                        ->where('status', 'published')
                        ->inRandomOrder()->limit(10)->get();
                } else {
                    // Sanitize: đảm bảo chỉ có integer thuần
                    $songIds = array_values(array_filter(array_map('intval', $songIds), fn($id) => $id > 0));
                    $idsOrdered = implode(',', $songIds);
                    $songs = \App\Models\Song::with('artistProfile:id,stage_name')
                        ->where('status', 'published')
                        ->whereIn('id', $songIds)
                        ->orderByRaw("FIELD(id, $idsOrdered)")
                        ->get();
                }

                $recommended_songs = $songs->map(function ($song) use ($recommendationsRaw) {
                    $matchingRec = collect($recommendationsRaw)->firstWhere('song_id', $song->id);
                    return [
                        'id' => $song->id,
                        'title' => $song->title,
                        'artist' => $song->artistProfile->stage_name ?? null,
                        'cover_image' => $song->cover_image ? asset('storage/' . $song->cover_image) : asset('images/default-cover.png'),
                        'file_path' => $song->file_path ? asset('storage/' . $song->file_path) : null,
                        'score' => $matchingRec['score'] ?? null,
                        'reason' => 'AI Real-time Recommend'
                    ];
                });

                return response()->json([
                    'success' => true,
                    'user_id' => $user_id,
                    'recommended_songs' => $recommended_songs
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Lỗi logic từ AI Server hoặc user chưa làm bài test Cold Start',
                'recommended_songs' => []
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service Unavailable: AI Server Offline.',
                'recommended_songs' => []
            ]);
        }
    }

    /**
     * Lấy các bài hát tương tự nhau (Item-based KNN)
     */
    public function similarSongs($song_id)
    {
        $baseSong = \App\Models\Song::with(['artistProfile:id,stage_name', 'genre:id,name'])->find($song_id);

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("http://127.0.0.1:5000/api/songs/{$song_id}/similar");

            if ($response->successful() && $response->json('success')) {
                $similarRaw = $response->json('similar_songs');
                $songIds = collect($similarRaw)->pluck('song_id')->toArray();
                
                $neighbors = collect();
                if (!empty($songIds)) {
                    // Sanitize: đảm bảo chỉ có integer thuần
                    $songIds = array_values(array_filter(array_map('intval', $songIds), fn($id) => $id > 0));
                    $idsOrdered = implode(',', $songIds);
                    $songs = \App\Models\Song::with('artistProfile:id,stage_name')
                        ->where('status', 'published')
                        ->whereIn('id', $songIds)
                        ->orderByRaw("FIELD(id, $idsOrdered)")
                        ->get();

                    $neighbors = $songs->map(function ($song) use ($similarRaw) {
                        $matchingRec = collect($similarRaw)->firstWhere('song_id', $song->id);
                        return [
                            'id' => $song->id,
                            'title' => $song->title,
                            'artist' => $song->artistProfile->stage_name ?? null,
                            'cover_image' => $song->cover_image ? asset('storage/' . $song->cover_image) : asset('images/default-cover.png'),
                            'file_path' => $song->file_path ? asset('storage/' . $song->file_path) : null,
                            'similarity' => $matchingRec['similarity'] ?? null
                        ];
                    });
                }

                return response()->json([
                    'success' => true,
                    'target_song' => $baseSong ? [
                        'id' => $baseSong->id,
                        'title' => $baseSong->title,
                        'artist' => $baseSong->artistProfile->stage_name ?? null,
                        'genre' => $baseSong->genre->name ?? null,
                        'cover_image' => $baseSong->cover_image ? asset('storage/' . $baseSong->cover_image) : asset('images/default-cover.png')
                    ] : null,
                    'similar_songs' => $neighbors
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Lỗi logic lấy gợi ý tương tự từ AI Server',
                'similar_songs' => []
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service Unavailable: AI Server Offline.',
                'similar_songs' => []
            ]);
        }
    }
}
