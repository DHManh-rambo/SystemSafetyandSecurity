@extends('customer.layouts.customer')

@section('title', '🌸 Cửa Hàng Hoa Tươi')
@section('page-id', 'dashboard')

@section('content')
<div class="main-wrap">

    @if(request('q'))

        {{-- KẾT QUẢ TÌM KIẾM --}}
        <section class="home-block">
            <div class="home-block-title">
                <h2>🔍 Kết quả tìm kiếm: "{{ request('q') }}"</h2>
                <a href="{{ route('customer.dashboard') }}">Quay lại trang chủ</a>
            </div>

            @if($sanPhams->isEmpty())
                <p style="padding:20px;color:#ef4444;font-size:16px;background:#fff;border-radius:14px;">
                    Không tìm thấy sản phẩm phù hợp với từ khóa
                    <strong>"{{ request('q') }}"</strong>.
                </p>
            @else
                <div class="home-product-grid">
                    @foreach($sanPhams as $sp)
                        @php
                            $loConHang = $sp->chiTietNhaps->where('so_luong_con_lai', '>', 0);
                            $gia = $sp->gia_ban_hien_tai;
                            $tonKho = $loConHang->sum('so_luong_con_lai');
                        @endphp

                        <div class="home-product-card">
                            <div class="home-product-img">
                                <img src="{{ $sp->anh }}" alt="{{ $sp->ten_san_pham }}">
                            </div>

                            <div class="home-product-info">
                                <h3>{{ $sp->ten_san_pham }}</h3>

                                <p class="home-product-desc">
                                    <strong>Mô tả:</strong>
                                    {{ $sp->mo_ta ?? 'Chưa có mô tả' }}
                                </p>

                                <div class="price">
                                    {{ $gia ? number_format($gia, 0, ',', '.') . 'đ' : 'Liên hệ' }}
                                </div>

                                <div class="home-card-bottom">
                                    <span>Sản phẩm: {{ $tonKho }}</span>

                                    <a href="{{ route('customer.san-pham.chi-tiet', $sp->ma_san_pham) }}" class="detail-btn">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

    @else

        {{-- SLIDER --}}
        <section class="home-slider">
            <div class="slider-track" id="sliderTrack">
                <div class="slide-item">
                    <img src="{{ asset('img/ban1.png') }}" alt="Banner 1">
                </div>

                <div class="slide-item">
                    <img src="{{ asset('img/ban2.png') }}" alt="Banner 2">
                </div>

                <div class="slide-item">
                    <img src="{{ asset('img/ban3.png') }}" alt="Banner 3">
                </div>
            </div>

            <button class="slider-btn slider-prev" type="button" onclick="prevSlide()">‹</button>
            <button class="slider-btn slider-next" type="button" onclick="nextSlide()">›</button>

            <div class="slider-dots">
                <span class="slider-dot active" onclick="goSlide(0)"></span>
                <span class="slider-dot" onclick="goSlide(1)"></span>
                <span class="slider-dot" onclick="goSlide(2)"></span>
            </div>
        </section>

        {{-- POLICY --}}
        <section class="home-policy">
            <div class="policy-item">
                <div class="policy-icon">🚚</div>
                <div>
                    <h4>Giao hàng nhanh</h4>
                    <p>Giao 2h-4h trong nội thành</p>
                </div>
            </div>

            <div class="policy-item">
                <div class="policy-icon">💐</div>
                <div>
                    <h4>Hoa tươi mỗi ngày</h4>
                    <p>Cam kết hoa tươi 100%</p>
                </div>
            </div>

            <div class="policy-item">
                <div class="policy-icon">💳</div>
                <div>
                    <h4>Thanh toán dễ dàng</h4>
                    <p>Nhiều hình thức thanh toán</p>
                </div>
            </div>

            <div class="policy-item">
                <div class="policy-icon">☎️</div>
                <div>
                    <h4>Hỗ trợ 24/7</h4>
                    <p>Hotline: 0357 634 696</p>
                </div>
            </div>
        </section>

        {{-- HOA TƯƠI BÁN CHẠY --}}
        <section class="home-block">
            <div class="home-block-title">
                <h2>🌸 Hoa tươi bán chạy</h2>
                <a href="{{ route('customer.hoa-tuoi') }}">Xem tất cả</a>
            </div>

            <div class="home-product-grid">
                @foreach($hoaTuoiBanChay as $sp)
                    @php
                        $loConHang = $sp->chiTietNhaps->where('so_luong_con_lai', '>', 0);
                        /////$gia = $loConHang->max('gia_ban');
                         $gia = $sp->gia_ban_hien_tai;
                        $tonKho = $loConHang->sum('so_luong_con_lai');
                    @endphp

                    <div class="home-product-card">
                        <div class="home-product-img">
                            <img src="{{ $sp->anh }}" alt="{{ $sp->ten_san_pham }}">
                        </div>

                        <div class="home-product-info">
                            <h3>{{ $sp->ten_san_pham }}</h3>

                            <p class="home-product-desc">
                                <strong>Mô tả:</strong>
                                {{ $sp->mo_ta ?? 'Chưa có mô tả' }}
                            </p>

                            <div class="price">
                                {{ $gia ? number_format($gia, 0, ',', '.') . 'đ' : 'Liên hệ' }}
                                <span>/ {{ $sp->loai_san_pham === 'PHU_KIEN' ? 'cái' : 'cành' }}</span>
                            </div>

                            <div class="home-card-bottom">
                                <span>Sản phẩm: {{ $tonKho }}</span>

                                <a href="{{ route('customer.san-pham.chi-tiet', $sp->ma_san_pham) }}" class="detail-btn">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- PHỤ KIỆN NỔI BẬT --}}
        <section class="home-block">
            <div class="home-block-title">
                <h2>🎀 Phụ kiện nổi bật</h2>
                <a href="{{ route('customer.phu-kien') }}">Xem tất cả</a>
            </div>

            @if($phuKienNoiBat->isEmpty())
                <p style="color:#6b7280;font-size:15px;">Chưa có phụ kiện nổi bật.</p>
            @else
                <div class="home-product-grid">
                    @foreach($phuKienNoiBat as $sp)
                        @php
                            $loConHang = $sp->chiTietNhaps->where('so_luong_con_lai', '>', 0);
                            $gia = $sp->gia_ban_hien_tai;
                            $tonKho = $loConHang->sum('so_luong_con_lai');
                        @endphp

                        <div class="home-product-card">
                            <div class="home-product-img">
                                <img src="{{ $sp->anh }}" alt="{{ $sp->ten_san_pham }}">
                            </div>

                            <div class="home-product-info">
                                <h3>{{ $sp->ten_san_pham }}</h3>

                                <p class="home-product-desc">
                                    <strong>Mô tả:</strong>
                                    {{ $sp->mo_ta ?? 'Chưa có mô tả' }}
                                </p>

                                <div class="price">
                                    {{ $gia ? number_format($gia, 0, ',', '.') . 'đ' : 'Liên hệ' }}
                                    <span>/ cái</span>
                                </div>

                                <div class="home-card-bottom">
                                    <span>Sản phẩm: {{ $tonKho }}</span>

                                    <a href="{{ route('customer.san-pham.chi-tiet', $sp->ma_san_pham) }}" class="detail-btn">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- QUÀ TẶNG NỔI BẬT --}}
        <section class="home-block">
            <div class="home-block-title">
                <h2>🎁 Quà tặng nổi bật</h2>
                <a href="{{ route('customer.qua-tang') }}">Xem tất cả</a>
            </div>

            @if($quaTangNoiBat->isEmpty())
                <p style="color:#6b7280;font-size:15px;">Chưa có quà tặng nổi bật.</p>
            @else
                <div class="home-product-grid">
                    @foreach($quaTangNoiBat as $sp)
                        @php
                            $loConHang = $sp->chiTietNhaps->where('so_luong_con_lai', '>', 0);
                            $gia = $sp->gia_ban_hien_tai;
                            $tonKho = $loConHang->sum('so_luong_con_lai');
                        @endphp

                        <div class="home-product-card">
                            <div class="home-product-img">
                                <img src="{{ $sp->anh }}" alt="{{ $sp->ten_san_pham }}">
                            </div>

                            <div class="home-product-info">
                                <h3>{{ $sp->ten_san_pham }}</h3>

                                <p class="home-product-desc">
                                    <strong>Mô tả:</strong>
                                    {{ $sp->mo_ta ?? 'Chưa có mô tả' }}
                                </p>

                                <div class="price">
                                    {{ $gia ? number_format($gia, 0, ',', '.') . 'đ' : 'Liên hệ' }}
                                    <span>/ sản phẩm</span>
                                </div>

                                <div class="home-card-bottom">
                                    <span>Sản phẩm: {{ $tonKho }}</span>

                                    <a href="{{ route('customer.san-pham.chi-tiet', $sp->ma_san_pham) }}" class="detail-btn">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- TIN TỨC NỔI BẬT --}}
        <section class="home-block news-home-block">
            <div class="home-block-title">
                <h2>📰 Tin tức nổi bật</h2>
                <a href="{{ route('customer.tin-tuc') }}">Xem tất cả</a>
            </div>

            <div class="news-grid">
                <div class="news-card">
                    <img src="{{ asset('img/TULIP.jpg') }}" alt="Cách bảo quản hoa">
                    <div>
                        <h3>Cách bảo quản hoa hồng lâu tàn</h3>
                        <p>Mẹo nhỏ giúp hoa hồng tươi lâu hơn và giữ màu đẹp.</p>
                        <a href="{{ route('customer.tin-tuc.chi-tiet', 'bao-quan-hoa-hong') }}">
                            Xem chi tiết →
                        </a>
                    </div>
                </div>

                <div class="news-card">
                    <img src="{{ asset('img/hoa tulip.jpeg') }}" alt="Ý nghĩa hoa">
                    <div>
                        <h3>Ý nghĩa các màu hoa tươi</h3>
                        <p>Mỗi loài hoa và màu sắc đều mang một thông điệp riêng.</p>
                        <a href="{{ route('customer.tin-tuc.chi-tiet', 'y-nghia-mau-hoa') }}">
                            Xem chi tiết →
                        </a>
                    </div>
                </div>

                <div class="news-card">
                    <img src="{{ asset('img/hoa ly.jpeg') }}" alt="Hoa sinh nhật">
                    <div>
                        <h3>Gợi ý hoa tặng sinh nhật</h3>
                        <p>Những mẫu hoa đẹp, dễ tặng và phù hợp nhiều đối tượng.</p>
                        <a href="{{ route('customer.tin-tuc.chi-tiet', 'goi-y-hoa-tang-sinh-nhat') }}">
                            Xem chi tiết →
                        </a>
                    </div>
                </div>
            </div>
        </section>

    @endif

</div>
@endsection

@section('scripts')
<script>
let currentSlide = 0;
const totalSlides = 3;

function updateSlider() {
    const track = document.getElementById('sliderTrack');
    const dots = document.querySelectorAll('.slider-dot');

    if (!track) return;

    track.style.transform = `translateX(-${currentSlide * 100}%)`;

    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
    });
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateSlider();
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateSlider();
}

function goSlide(index) {
    currentSlide = index;
    updateSlider();
}

document.addEventListener('DOMContentLoaded', function () {
    updateSlider();

    setInterval(function () {
        nextSlide();
    }, 3000);
});
</script>
@endsection