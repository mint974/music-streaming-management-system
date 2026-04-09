<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\ArtistPackage;
use App\Models\ArtistRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $activeRegistration = $user->artistRegistrations()
            ->with(['package.features'])
            ->where('status', 'approved')
            ->where('expires_at', '>=', now())
            ->latest('expires_at')
            ->first();

        $pendingRegistration = $user->artistRegistrations()
            ->with('package')
            ->whereIn('status', ['pending_payment', 'pending_review'])
            ->latest()
            ->first();

        $upgradePackages = ArtistPackage::query()
            ->active()
            ->with('features')
            ->orderBy('price')
            ->get()
            ->filter(function (ArtistPackage $package) use ($activeRegistration) {
                if (! $activeRegistration || ! $activeRegistration->package) {
                    return true;
                }

                if ((int) $package->id === (int) $activeRegistration->package_id) {
                    return false;
                }

                return (int) $package->price > (int) $activeRegistration->package->price;
            })
            ->values();

        $registrationHistory = $user->artistRegistrations()
            ->with(['package', 'reviewer'])
            ->latest()
            ->take(20)
            ->get();

        return view('artist.account.index', [
            'activeRegistration' => $activeRegistration,
            'pendingRegistration' => $pendingRegistration,
            'upgradePackages' => $upgradePackages,
            'registrationHistory' => $registrationHistory,
        ]);
    }

    public function cancelPackage(Request $request, ArtistRegistration $registration): RedirectResponse
    {
        $user = $request->user();

        if ((int) $registration->user_id !== (int) $user->id) {
            abort(403);
        }

        if (! $registration->isApproved()) {
            return redirect()->route('artist.account.index')
                ->with('error', 'Chi co the huy goi da duoc phe duyet.');
        }

        if ($registration->expires_at && $registration->expires_at->isPast()) {
            return redirect()->route('artist.account.index')
                ->with('warning', 'Goi nay da het han, khong can huy them.');
        }

        DB::transaction(function () use ($registration) {
            $note = trim((string) $registration->admin_note);
            $suffix = '[Artist] Huy goi theo yeu cau tu Artist Studio luc ' . now()->format('d/m/Y H:i');
            $registration->status = 'expired';
            $registration->expires_at = now();
            $registration->admin_note = $note !== '' ? ($note . PHP_EOL . $suffix) : $suffix;
            $registration->save();
        });

        return redirect()->route('artist.account.index')
            ->with('success', 'Da huy goi nghe si thanh cong. Ban co the dang ky/nang cap goi moi ngay.');
    }
}
