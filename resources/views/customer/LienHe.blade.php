@extends('customer.layouts.customer')

@section('title', 'Liên hệ - RoseShop')

@section('head-styles')
<link rel="stylesheet" href="{{ asset('css/Customer/LienHe.css') }}">
<link rel="stylesheet" href="{{ asset('css/Customer/GioiThieu.css') }}">
@endsection

@section('content')
<!-- <section class="about-hero">
    style="background-image: url('{{ asset('img/back.jpg') }}');">
    <div class="about-hero-text">
        <h1>Liên hệ với chúng tôi</h1>
        <p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn!</p>

    </div>
</section> -->
<section class="about-hero"
    style="background-image: url('{{ asset('img/back.jpg') }}');">

    <div class="about-hero-text">
        <!-- <span>Giới thiệu về</span> -->

        <h1>Liên hệ với chúng tôi</h1>

        <p>
            Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn!
        </p>
    </div>

</section>
<section class="contact-wrap">
    <div class="contact-form-box">
        <h2>✉️ Gửi tin nhắn cho chúng tôi</h2>

        <form onsubmit="handleContactSubmit(event)">
            <label>Họ và tên <span>*</span></label>
            <input type="text" placeholder="Nhập họ và tên của bạn">

            <div class="form-row">
                <div>
                    <label>Số điện thoại <span>*</span></label>
                    <input type="text" placeholder="Nhập số điện thoại">
                </div>

                <div>
                    <label>Email</label>
                    <input type="email" placeholder="Nhập email của bạn">
                </div>
            </div>

            <label>Nội dung tin nhắn <span>*</span></label>
            <textarea placeholder="Nhập nội dung tin nhắn..."></textarea>

            <button type="submit">Gửi tin nhắn</button>
        </form>
    </div>

    <div class="contact-info-box">
        <h2>📬 Thông tin liên hệ</h2>

        <div class="contact-item">
            <div>📍</div>
            <p><strong>Địa chỉ</strong><br>123 Nguyễn Trãi, Thanh Xuân, Hà Nội</p>
        </div>

        <div class="contact-item">
            <div>📞</div>
            <p><strong>Hotline</strong><br>0357 634 696</p>
        </div>

        <div class="contact-item">
            <div>✉️</div>
            <p><strong>Email</strong><br>roseshop@gmail.com</p>
        </div>

        <div class="contact-item">
            <div>🕘</div>
            <p><strong>Giờ làm việc</strong><br>7:00 - 22:00 (Tất cả các ngày)</p>
        </div>

        <h3>Kết nối với chúng tôi</h3>
        <div class="contact-social">
            <a href="#">f</a>
            <a href="#">Zalo</a>
            <a href="#">◎</a>
            <a href="#">▶</a>
        </div>
    </div>
</section>

<section class="map-section">
    <h2>📍 Vị trí của chúng tôi</h2>

    <div class="map-frame">
        <iframe
            src="https://www.google.com/maps?q=Phenikaa%20University,%20Hanoi&z=15&output=embed"
            width="100%"
            height="450"
            style="border:0;"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</section>
@endsection
@section('scripts')
<script>
function handleContactSubmit(e) {
    e.preventDefault();

    showSuccessToast('Cảm ơn bạn! RoseShop sẽ liên hệ lại sớm nhất.');

    e.target.reset();
}
</script>
@endsection