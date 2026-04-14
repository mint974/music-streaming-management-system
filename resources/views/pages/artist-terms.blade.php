@extends('layouts.main')

@section('title', 'Điều khoản và dịch vụ dành cho Nghệ sĩ')

@section('content')
<div class="container py-5" style="max-width: 960px;">
    <div class="mb-4 p-4 rounded-4" style="background:linear-gradient(135deg,rgba(168,85,247,.12),rgba(59,130,246,.06));border:1px solid rgba(168,85,247,.18);">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="text-uppercase small fw-bold text-info mb-2" style="letter-spacing:.08em">Blue Wave Music</div>
                <h1 class="h3 text-white fw-bold mb-2">Điều khoản và dịch vụ dành cho Nghệ sĩ</h1>
                <p class="text-muted mb-0">Vui lòng đọc kỹ trước khi thanh toán để hiểu quyền lợi, trách nhiệm và quy trình xét duyệt.</p>
            </div>
            <a href="{{ route('artist-register.index') }}" class="btn btn-outline-light btn-sm">
                <i class="fa-solid fa-arrow-left me-2"></i>Quay lại đăng ký
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card bg-transparent text-light border-0 p-4" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08) !important;border-radius:18px;">
                <h5 class="fw-bold mb-3">1. Trước khi thanh toán</h5>
                <p class="text-muted">Bạn cần cung cấp nghệ danh và thông tin hồ sơ chính xác. Sau khi thanh toán thành công, hồ sơ sẽ chuyển sang trạng thái chờ xét duyệt và bạn sẽ được chuyển tới phần hồ sơ nghệ sĩ để hoàn thiện thông tin.</p>

                <h5 class="fw-bold mb-3 mt-4">2. Hồ sơ cần hoàn thiện</h5>
                <ul class="text-muted">
                    <li>Nghệ danh hiển thị công khai.</li>
                    <li>Tiểu sử nghệ sĩ và mô tả phong cách âm nhạc.</li>
                    <li>Ảnh đại diện, ảnh bìa và liên kết mạng xã hội nếu có.</li>
                </ul>

                <h5 class="fw-bold mb-3 mt-4">3. Quy trình xét duyệt</h5>
                <p class="text-muted">Sau khi hồ sơ được hoàn thiện, admin sẽ xem xét và đưa ra quyết định phê duyệt hoặc từ chối dựa trên thông tin bạn cung cấp, tính đầy đủ của hồ sơ và mức độ phù hợp với chính sách nền tảng.</p>

                <h5 class="fw-bold mb-3 mt-4">4. Quyền lợi và trách nhiệm</h5>
                <ul class="text-muted">
                    <li>Quyền lợi gói nghệ sĩ chỉ có hiệu lực sau khi thanh toán hoàn tất.</li>
                    <li>Bạn chịu trách nhiệm về tính chính xác của thông tin đăng ký và nội dung hồ sơ.</li>
                    <li>Nền tảng có thể từ chối nếu hồ sơ không đầy đủ hoặc không phù hợp chính sách.</li>
                </ul>

                <h5 class="fw-bold mb-3 mt-4">5. Hỗ trợ</h5>
                <p class="text-muted mb-0">Nếu cần hỗ trợ, vui lòng liên hệ bộ phận CSKH của Blue Wave Music trước khi tiến hành thanh toán.</p>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card bg-transparent text-light border-0 p-4" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08) !important;border-radius:18px;position:sticky;top:24px;">
                <h5 class="fw-bold mb-3">Tóm tắt nhanh</h5>
                <div class="mb-3 small text-muted">1. Đọc kỹ điều khoản</div>
                <div class="mb-3 small text-muted">2. Thanh toán gói nghệ sĩ</div>
                <div class="mb-3 small text-muted">3. Hoàn thiện hồ sơ nghệ sĩ</div>
                <div class="mb-3 small text-muted">4. Chờ admin phê duyệt hoặc từ chối</div>
                <div class="alert alert-info border-0 mt-4 mb-0" style="background:rgba(59,130,246,.12);color:#bfdbfe;">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    Hệ thống sẽ tự chuyển bạn tới trang hồ sơ nghệ sĩ sau khi thanh toán thành công.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
