<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Tag;

class OnboardingController extends Controller
{
    public function index()
    {
        if (Auth::user()->is_onboarded) {
            return redirect()->route('home');
        }

        // Lấy danh sách tag ngẫu nhiên hoặc phổ biến
        $tags = Tag::inRandomOrder()->limit(40)->pluck('label');

        return view('onboarding.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tags' => 'required|array|min:3|max:15',
        ], [
            'tags.min' => 'Bạn phải chọn ít nhất 3 thể loại/tags.',
            'tags.max' => 'Bạn chỉ được chọn tối đa 15 thể loại/tags.',
        ]);

        $user = Auth::user();

        // Gửi sang Python API để tạo Cold Start Vector
        try {
            $response = Http::timeout(10)->post('http://127.0.0.1:5000/api/users/init-cold-start', [
                'user_id' => $user->id,
                'tags' => $request->tags
            ]);

            if ($response->successful()) {
                $user->is_onboarded = true;
                $user->save();

                return redirect()->route('home')->with('success', 'Gu âm nhạc của bạn đã được thiết lập bởi AI Recommender!');
            } else {
                return back()->withErrors(['api' => 'Phản hồi bất thường từ AI Server: ' . $response->body()]);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['api' => 'Trái tim hệ thống AI hiện chưa được khởi động (Port 5000 offline). Vui lòng thử lại sau.']);
        }
    }
}
