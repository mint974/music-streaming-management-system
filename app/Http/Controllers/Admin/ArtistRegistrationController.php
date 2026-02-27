<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtistRegistration;
use App\Notifications\ArtistRegistrationReviewed;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ArtistRegistrationController extends Controller
{
    public function __construct(protected UserRepository $repo) {}

    /**
     * Danh sách đơn đăng ký nghệ sĩ.
     * GET /admin/artist-registrations
     */
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'pending_review');

        $query = ArtistRegistration::with(['user', 'package', 'reviewer'])
            ->where('status', $tab)
            ->latest();

        $registrations = $query->paginate(15)->withQueryString();

        $counts = [
            'pending_review' => ArtistRegistration::where('status', 'pending_review')->count(),
            'approved'       => ArtistRegistration::where('status', 'approved')->count(),
            'rejected'       => ArtistRegistration::where('status', 'rejected')->count(),
        ];

        return view('admin.artist-registrations.index', compact('registrations', 'counts', 'tab'));
    }

    /**
     * Phê duyệt đơn → nâng cấp role user lên 'artist'.
     * POST /admin/artist-registrations/{id}/approve
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $registration = ArtistRegistration::with('user', 'package')->findOrFail($id);

        if (!$registration->isPendingReview()) {
            return back()->with('error', 'Đơn này không ở trạng thái chờ xét duyệt.');
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $admin = Auth::guard('admin')->user();

        // Nâng cấp role → artist + ghi lịch sử
        $this->repo->adminChangeRole($registration->user, 'artist', $admin->id);

        // Cập nhật đơn đăng ký
        $registration->update([
            'status'      => 'approved',
            'admin_note'  => $request->input('admin_note'),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Ghi lịch sử tài khoản riêng cho hành động phê duyệt
        $this->repo->createHistory(
            $registration->user->id,
            $admin->id,
            '[Admin] Phê duyệt đăng ký Nghệ sĩ — ' . $registration->artist_name,
            $registration->user->status
        );

        // Thông báo đến user (db + mail)
        try {
            $registration->user->notify(new ArtistRegistrationReviewed($registration));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send artist approved notification: ' . $e->getMessage());
        }

        return back()->with('success', "Đã phê duyệt đơn đăng ký nghệ sĩ của <strong>{$registration->user->name}</strong> (nghệ danh: {$registration->artist_name}).");
    }

    /**
     * Từ chối đơn đăng ký (lý do bắt buộc).
     * POST /admin/artist-registrations/{id}/reject
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        $registration = ArtistRegistration::with('user', 'package')->findOrFail($id);

        if (!$registration->isPendingReview()) {
            return back()->with('error', 'Đơn này không ở trạng thái chờ xét duyệt.');
        }

        $request->validate([
            'admin_note' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối.',
            'admin_note.min'      => 'Lý do từ chối phải có ít nhất 10 ký tự.',
        ]);

        $admin = Auth::guard('admin')->user();

        $registration->update([
            'status'      => 'rejected',
            'admin_note'  => $request->input('admin_note'),
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        // Ghi lịch sử tài khoản
        $this->repo->createHistory(
            $registration->user->id,
            $admin->id,
            '[Admin] Từ chối đăng ký Nghệ sĩ — ' . $registration->artist_name,
            $registration->user->status
        );

        // Thông báo đến user (db + mail)
        try {
            $registration->user->notify(new ArtistRegistrationReviewed($registration));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send artist rejected notification: ' . $e->getMessage());
        }

        return back()->with('success', "Đã từ chối đơn đăng ký của <strong>{$registration->user->name}</strong>.");
    }
}
