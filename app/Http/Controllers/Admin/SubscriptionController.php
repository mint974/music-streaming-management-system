<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Vip;
use App\Notifications\AccountUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * Danh sách tất cả lượt đăng ký, hỗ trợ lọc.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'vip_id', 'status']);

        $query = Subscription::with(['user', 'vip', 'payment'])->latest();

        if (! empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->whereHas('user', fn ($q) =>
                $q->where('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
            );
        }

        if (! empty($filters['vip_id'])) {
            $query->where('vip_id', $filters['vip_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        $subscriptions = $query->paginate(20)->withQueryString();
        $vips          = Vip::orderBy('price')->get();

        // Stats for top bar
        $stats = [
            'total'     => Subscription::count(),
            'active'    => Subscription::where('status', 'active')->count(),
            'expired'   => Subscription::where('status', 'expired')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
            'revenue'   => (int) Subscription::whereIn('status', ['active', 'expired'])->sum('amount_paid'),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'vips', 'filters', 'stats'));
    }

    /**
     * Admin cấp đăng ký thủ công cho người dùng.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id'    => ['required', 'exists:users,id'],
            'vip_id'     => ['required', 'exists:vips,id'],
            'start_date' => ['required', 'date'],
            'amount_paid'=> ['required', 'integer', 'min:0'],
        ]);

        $vip      = Vip::findOrFail($data['vip_id']);
        $start    = \Carbon\Carbon::parse($data['start_date']);
        $end      = $start->copy()->addDays($vip->duration_days);
        $user     = \App\Models\User::findOrFail($data['user_id']);

        // Hủy tất cả các gói active cũ trước khi cấp gói mới để tránh duplicate
        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $subscription = Subscription::create([
            'user_id'     => $user->id,
            'vip_id'      => $data['vip_id'],
            'start_date'  => $start->toDateString(),
            'end_date'    => $end->toDateString(),
            'status'      => 'active',
            'amount_paid' => $data['amount_paid'],
        ]);

        $subscription->payment()->create([
            'user_id'          => $user->id,
            'provider'         => 'ADMIN',
            'method'           => 'ADMIN',
            'amount'           => 0,
            'status'           => 'paid',
            'transaction_code'  => 'ADMIN_SUB_' . $subscription->id . '_' . time(),
            'paid_at'          => now(),
            'raw_response'     => null,
        ]);

        if (! $user->hasRole('admin')) {
            $user->assignRole('premium');
            if (! $user->hasRole('artist')) {
                $user->removeRole('free');
            }
        }

        $user->notify(new AccountUpdated('role_premium'));

        return redirect()->route('admin.subscriptions.index')
                         ->with('success', "Đã cấp đăng ký <strong>{$vip->title}</strong> cho tài khoản <strong>{$user?->name}</strong>.");
    }

    /**
     * Hủy đăng ký (cancelled).
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ], [
            'reason.required' => 'Vui lòng nhập lý do hủy đăng ký.',
        ]);

        $sub = Subscription::with('user', 'vip')->findOrFail($id);

        if (! $sub->isActive()) {
            return back()->with('error', 'Chỉ có thể hủy đăng ký đang hiệu lực.');
        }

        $sub->update(['status' => 'cancelled']);

        $user = $sub->user;
        if ($user) {
            $user->notify(new AccountUpdated('subscription_cancelled', $request->input('reason')));

            if (! $user->subscriptions()->where('status', 'active')->exists()) {
                $user->removeRole('premium');
                if (! $user->hasRole('admin') && ! $user->hasRole('artist')) {
                    $user->assignRole('free');
                }
            }
        }

        return back()->with('success', "Đã hủy đăng ký <strong>{$sub->vip?->title}</strong> của <strong>{$sub->user?->name}</strong>.");
    }

    /**
     * Đánh dấu hết hạn thủ công (cho test/admin).
     */
    public function expire(int $id): RedirectResponse
    {
        $sub = Subscription::with('user', 'vip')->findOrFail($id);

        if (! $sub->isActive()) {
            return back()->with('error', 'Chỉ có thể đặt hết hạn cho đăng ký đang hiệu lực.');
        }

        $sub->update(['status' => 'expired', 'end_date' => now()->toDateString()]);

        $user = $sub->user;
        if ($user) {
            $user->notify(new AccountUpdated('subscription_expired'));

            if (! $user->subscriptions()->where('status', 'active')->exists()) {
                $user->removeRole('premium');
                if (! $user->hasRole('admin') && ! $user->hasRole('artist')) {
                    $user->assignRole('free');
                }
            }
        }

        return back()->with('success', "Đã đánh dấu hết hạn đăng ký của <strong>{$sub->user?->name}</strong>.");
    }
}
