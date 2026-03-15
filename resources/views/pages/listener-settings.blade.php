@extends('layouts.main')

@section('title', 'Cài đặt thông báo')

@section('content')
<div class="container py-4" style="max-width:720px">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card" style="background:#111827;border:1px solid #1f2937">
        <div class="card-body">
            <h5 class="text-white mb-3">Cài đặt thông báo listener</h5>
            <form method="POST" action="{{ route('listener.settings.update') }}">
                @csrf
                @method('PATCH')

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="notify_new_song" name="notify_new_song" value="1" {{ $setting->notify_new_song ? 'checked' : '' }}>
                    <label class="form-check-label text-light" for="notify_new_song">Thông báo khi nghệ sĩ theo dõi ra bài mới</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="notify_new_album" name="notify_new_album" value="1" {{ $setting->notify_new_album ? 'checked' : '' }}>
                    <label class="form-check-label text-light" for="notify_new_album">Thông báo khi nghệ sĩ theo dõi ra album mới</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="notify_in_app" name="notify_in_app" value="1" {{ $setting->notify_in_app ? 'checked' : '' }}>
                    <label class="form-check-label text-light" for="notify_in_app">Nhận thông báo trong ứng dụng</label>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="notify_email" name="notify_email" value="1" {{ $setting->notify_email ? 'checked' : '' }}>
                    <label class="form-check-label text-light" for="notify_email">Nhận thông báo qua email</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                    <a href="{{ route('listener.index') }}" class="btn btn-outline-light">Quay lại thư viện listener</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
