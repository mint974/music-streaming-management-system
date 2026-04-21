<?php

namespace App\Providers;

use App\Models\ArtistRegistration;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register UserRepository
        $this->app->singleton(UserRepository::class, function ($app) {
            return new UserRepository();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Set default password rules
        // Note: uncompromised() removed — it calls an external API (HaveIBeenPwned)
        // which makes validation network-dependent and unreliable in local/offline envs.
        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();
        });

        // Share pending artist registration count with admin sidebar
        View::composer('partials.admin-sidebar', function ($view) {
            $view->with('pendingArtist', ArtistRegistration::where('status', 'pending_review')->count());
        });

        // Tự động đồng bộ lên AI Recommender khi người dùng thay đổi lịch sử nghe
        \App\Models\ListeningHistory::observe(\App\Observers\ListeningHistoryObserver::class);
    }
}
