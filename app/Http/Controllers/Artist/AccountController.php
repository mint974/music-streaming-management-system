<?php

namespace App\Http\Controllers\Artist;

use App\Http\Controllers\Controller;
use App\Models\ArtistPackage;
use App\Models\ArtistRegistration;
use App\Services\ArtistRegistrationStateService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(private readonly ArtistRegistrationStateService $stateService) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $activeRegistration = $user->artistRegistrations()
            ->with(['package.features'])
            ->where('status', 'approved')
            ->where('expires_at', '>=', now())
            ->latest('expires_at')
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

        // ── Validate filter params ───────────────────────────────────────────
        $filter = $request->validate([
            'filter_status'     => ['nullable', 'in:pending_payment,pending_review,approved,rejected,expired'],
            'filter_package_id' => ['nullable', 'integer', 'exists:artist_packages,id'],
            'filter_start_date' => ['nullable', 'date', 'before_or_equal:today'],
            'filter_end_date'   => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:filter_start_date'],
        ]);

        // ── Build history query ──────────────────────────────────────────────
        $historyQuery = $user->artistRegistrations()
            ->with(['package', 'reviewer', 'payment']);

        if (!empty($filter['filter_status'])) {
            $historyQuery->where('status', $filter['filter_status']);
        }
        if (!empty($filter['filter_package_id'])) {
            $historyQuery->where('package_id', $filter['filter_package_id']);
        }
        if (!empty($filter['filter_start_date'])) {
            $historyQuery->whereDate('created_at', '>=', $filter['filter_start_date']);
        }
        if (!empty($filter['filter_end_date'])) {
            $historyQuery->whereDate('created_at', '<=', $filter['filter_end_date']);
        }

        $registrationHistory = $historyQuery->latest()->paginate(8)->withQueryString();

        // Tổng đã chi (approved + expired)
        $totalSpent = $user->artistRegistrations()
            ->whereIn('status', ['approved', 'expired'])
            ->with('package')
            ->get()
            ->sum(fn ($r) => (int) ($r->package?->price ?? 0));

        // Danh sách packages cho dropdown filter
        $allPackages = ArtistPackage::orderBy('price')->get();

        return view('artist.account.index', [
            'activeRegistration'  => $activeRegistration,
            'upgradePackages'     => $upgradePackages,
            'registrationHistory' => $registrationHistory,
            'totalSpent'          => $totalSpent,
            'allPackages'         => $allPackages,
            'filter'              => $filter,
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

            $noteForSave = $note !== '' ? ($note . PHP_EOL . $suffix) : $suffix;
            $this->stateService->expire($registration, $noteForSave);
        });

        return redirect()->route('artist.account.index')
            ->with('success', 'Da huy goi nghe si thanh cong. Ban co the dang ky/nang cap goi moi ngay.');
    }
}
