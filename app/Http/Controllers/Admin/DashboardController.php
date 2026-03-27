<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Playlist;
use App\Models\Song;
use App\Models\SongDailyStat;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with general metrics, revenue, and charts.
     */
    public function index(): View
    {
        // 1. Content counts
        $totalSongs = Song::count();
        $totalAlbums = Album::count();
        $totalPlaylists = Playlist::count(); // Giả sử model Playlist tồn tại và đúng

        // 2. User & Artist counts
        $totalArtists = User::where('role', 'artist')->count();
        $totalFreeUsers = User::where('role', 'free')->count();
        $totalPremiumUsers = User::where('role', 'premium')->count();
        $totalUsers = $totalFreeUsers + $totalPremiumUsers + $totalArtists;

        // 3. Listens (Today, Week, Month)
        $today = Carbon::today()->toDateString();
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();

        $listensToday = SongDailyStat::where('stat_date', $today)->sum('play_count');
        $listensWeek = SongDailyStat::where('stat_date', '>=', $startOfWeek)
                                    ->where('stat_date', '<=', $today)->sum('play_count');
        $listensMonth = SongDailyStat::where('stat_date', '>=', $startOfMonth)
                                     ->where('stat_date', '<=', $today)->sum('play_count');

        // Lượt nghe mọi thời đại
        $listensTotal = Song::sum('listens'); 

        // 4. Premium Revenue
        $revenueTotal = Subscription::whereIn('status', ['active', 'expired', 'cancelled'])
                                      ->sum('amount_paid');
        $revenueMonth = Subscription::whereIn('status', ['active', 'expired', 'cancelled'])
                                      ->where('created_at', '>=', Carbon::now()->startOfMonth())
                                      ->sum('amount_paid');

        // 5. Growth Chart Data (Last 7 days listens and new users)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $label = Carbon::today()->subDays($i)->format('d/m');
            
            $plays = SongDailyStat::where('stat_date', $date)->sum('play_count');
            $revenue = Subscription::whereIn('status', ['active', 'expired', 'cancelled'])
                                   ->whereDate('created_at', $date)
                                   ->sum('amount_paid');
            
            $chartData['labels'][] = $label;
            $chartData['plays'][] = (int) $plays;
            $chartData['revenue'][] = (int) $revenue;
        }

        return view('admin.dashboard', compact(
            'totalSongs', 'totalAlbums', 'totalPlaylists',
            'totalArtists', 'totalFreeUsers', 'totalPremiumUsers', 'totalUsers',
            'listensToday', 'listensWeek', 'listensMonth', 'listensTotal',
            'revenueTotal', 'revenueMonth',
            'chartData'
        ));
    }
}
