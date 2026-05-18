@extends('layouts.auth')
@section('title', 'Định hình Gu âm nhạc của bạn')

@section('content')
    <div class="auth-container">
        {{-- Random sparkles --}}
        <x-sparkles :count="40" />

        <div class="container d-flex align-items-center justify-content-center min-vh-100 position-relative z-index-10">
            <div class="onboard-card text-center p-4 p-md-5 animate-fade-in-up">

                <div class="mb-4">
                    <img src="{{ asset('storage/logo.png') }}" alt="Blue Wave Music Logo"
                        class="auth-logo mx-auto d-block mb-3" style="width: 80px; height: 80px;">
                    <h1 class="onboard-title mb-2">Khám phá vũ trụ âm nhạc của bạn</h1>
                    <p class="onboard-subtitle mb-0">BlueWave AI cần ít nhất 3 thể loại để xây dựng một dải ngân hà âm nhạc
                        hoàn hảo cho riêng bạn.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger custom-alert mb-4 py-3">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('onboarding.store') }}" method="POST" id="onboardingForm">
                    @csrf
                    <div class="tag-container mb-4 d-flex flex-wrap justify-content-center gap-2 gap-md-3">
                        @foreach ($tags as $tag)
                            <button type="button" class="tag-btn" data-tag="{{ $tag }}">
                                #{{ $tag }}
                            </button>
                            <input type="checkbox" name="tags[]" value="{{ $tag }}"
                                class="d-none hidden-checkbox">
                        @endforeach
                    </div>

                    <div class="d-flex flex-column align-items-center pt-3 border-top border-secondary border-opacity-10">
                        <span class="text-muted small mb-3 tracking-wider" style="color: rgba(255,255,255,0.6) !important;">
                            <span id="count" class="fw-bold text-white">0</span> đã chọn (tối thiểu 3, tối đa 15)
                        </span>
                        <button type="submit" id="submitBtn" class="btn onboard-submit-btn" disabled>
                            Gợi ý nhạc cho tôi 🚀
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .z-index-10 {
            z-index: 10;
        }

        /* Card Layout - Align with auth-card but keep unique onboarding identity */
        .onboard-card {
            background: rgba(30, 39, 54, 0.85);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 32px;
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.6),
                0 0 100px rgba(147, 51, 234, 0.1);
            max-width: 1000px;
            width: 100%;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .onboard-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        }

        .onboard-title {
            font-weight: 800;
            font-size: clamp(1.75rem, 5vw, 2.75rem);
            background: linear-gradient(135deg, #fff 30%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.02em;
        }

        .onboard-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.05rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Tag Container & Buttons */
        .tag-container {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        .tag-container::-webkit-scrollbar {
            width: 5px;
        }

        .tag-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .tag-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .tag-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(5px);
        }

        .tag-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.3);
            color: #fff;
            transform: translateY(-2px);
        }

        .tag-btn.active {
            background: linear-gradient(135deg, #9333ea, #db2777);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 8px 20px rgba(219, 39, 119, 0.4);
            transform: scale(1.05);
        }

        /* Submit Button */
        .onboard-submit-btn {
            background: #fff;
            border: none;
            color: #000;
            padding: 14px 48px;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 50px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .onboard-submit-btn:hover:not(:disabled) {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 15px 35px rgba(255, 255, 255, 0.3);
            background: #f8fafc;
            color: #000;
        }

        .onboard-submit-btn:disabled {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: none;
            color: rgba(255, 255, 255, 0.3);
            cursor: not-allowed;
        }

        .custom-alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
            border-radius: 16px;
            font-size: 0.9rem;
        }

        /* Animation */
        .animate-fade-in-up {
            animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(40px);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('.tag-btn');
            const submitBtn = document.getElementById('submitBtn');
            const counter = document.getElementById('count');

            let selectedCount = 0;

            buttons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = this.nextElementSibling;

                    if (this.classList.contains('active')) {
                        this.classList.remove('active');
                        input.checked = false;
                        selectedCount--;
                    } else {
                        if (selectedCount >= 15) {
                            alert('Tuyệt lắm nhưng bạn chỉ có thể chọn tối đa 15 thể loại!');
                            return;
                        }
                        this.classList.add('active');
                        input.checked = true;
                        selectedCount++;
                    }

                    counter.innerText = selectedCount;
                    if (selectedCount >= 3) {
                        submitBtn.disabled = false;
                    } else {
                        submitBtn.disabled = true;
                    }
                });
            });
        });
    </script>
@endsection
