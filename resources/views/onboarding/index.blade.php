@extends('layouts.auth')
@section('title', 'Định hình Gu âm nhạc của bạn')

@section('content')
<div class="onboard-wrapper">
    <div class="onboard-overlay"></div>
    
    <div class="container d-flex align-items-center justify-content-center min-vh-100 position-relative z-index-1">
        <div class="onboard-card text-center p-5 animate-fade-in-up">
            
            <h1 class="onboard-title mb-3">Khám phá vũ trụ của bạn</h1>
            <p class="onboard-subtitle mb-5">BlueWave AI cần ít nhất 3 thể loại để xây dựng một dải ngân hà âm nhạc hoàn hảo cho riêng bạn.</p>

            @if($errors->any())
                <div class="alert alert-danger custom-alert mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('onboarding.store') }}" method="POST" id="onboardingForm">
                @csrf
                <div class="tag-container mb-5 d-flex flex-wrap justify-content-center gap-3">
                    @foreach($tags as $tag)
                        <button type="button" class="tag-btn" data-tag="{{ $tag }}">
                            #{{ $tag }}
                        </button>
                        <input type="checkbox" name="tags[]" value="{{ $tag }}" class="d-none hidden-checkbox">
                    @endforeach
                </div>

                <div class="d-flex flex-column align-items-center">
                    <span class="text-muted small mb-3 tracking-wider"><span id="count" class="fw-bold text-white">0</span> đã chọn (tối thiểu 3, tối đa 15)</span>
                    <button type="submit" id="submitBtn" class="btn onboard-submit-btn" disabled>
                        Gợi ý nhạc cho tôi 🚀
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Reset & Fonts */
    .onboard-wrapper {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background-image: url('https://images.unsplash.com/photo-1614113489855-66422ad300a4?q=80&w=2000&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
        overflow-y: auto;
    }
    
    .onboard-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .z-index-1 {
        z-index: 1;
    }

    /* Card Layout */
    .onboard-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        max-width: 900px;
        width: 100%;
        color: #fff;
    }

    .onboard-title {
        font-weight: 800;
        font-size: 2.5rem;
        background: linear-gradient(135deg, #c084fc, #db2777);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -0.5px;
    }

    .onboard-subtitle {
        color: #d1d5db;
        font-size: 1.1rem;
    }

    /* Tag Container & Buttons */
    .tag-container {
        max-height: 350px;
        overflow-y: auto;
        padding: 5px;
    }

    .tag-container::-webkit-scrollbar { width: 6px; }
    .tag-container::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 10px; }
    .tag-container::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }

    .tag-btn {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
        padding: 10px 20px;
        border-radius: 50px;
        font-weight: 500;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .tag-btn:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
    }

    .tag-btn.active {
        background: linear-gradient(135deg, #9333ea, #db2777);
        border-color: transparent;
        box-shadow: 0 0 20px rgba(219, 39, 119, 0.5);
        transform: scale(1.05);
    }

    /* Submit Button */
    .onboard-submit-btn {
        background: linear-gradient(to right, #9333ea, #db2777);
        border: none;
        color: #fff;
        padding: 15px 40px;
        font-size: 1.25rem;
        font-weight: 700;
        border-radius: 50px;
        transition: all 0.3s ease;
        box-shadow: 0 0 20px rgba(147, 51, 234, 0.4);
    }

    .onboard-submit-btn:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 0 30px rgba(219, 39, 119, 0.6);
        color: #fff;
    }

    .onboard-submit-btn:disabled {
        background: #374151;
        box-shadow: none;
        color: #9ca3af;
        cursor: not-allowed;
    }

    .custom-alert {
        background: rgba(239, 68, 68, 0.2);
        border: 1px solid rgba(239, 68, 68, 0.4);
        color: #fca5a5;
        border-radius: 12px;
    }

    /* Animation */
    .animate-fade-in-up {
        animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
        transform: translateY(30px);
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
