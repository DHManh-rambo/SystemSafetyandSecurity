@extends('customer.layouts.customer')

@section('title', 'Giới thiệu - RoseShop')

@section('head-styles')
<link rel="stylesheet" href="{{ asset('css/Customer/GioiThieu.css') }}">
@endsection

@section('content')
<!-- <section class="about-hero">
    <div class="about-hero-text">
        <span>Giới thiệu về</span>
        <h1>RoseShop</h1>
        <p>
            RoseShop là cửa hàng chuyên cung cấp hoa tươi chất lượng cao,
            mang đến vẻ đẹp và niềm vui trong từng khoảnh khắc của bạn.
        </p>
    </div>
</section> -->
<section class="about-hero"
    style="background-image: url('{{ asset('img/back.jpg') }}');">

    <div class="about-hero-text">
        <span>Giới thiệu về</span>

        <h1>RoseShop</h1>

        <p>
            RoseShop là cửa hàng chuyên cung cấp hoa tươi chất lượng cao,
            mang đến vẻ đẹp và niềm vui trong từng khoảnh khắc của bạn.
        </p>
    </div>

</section>
<section class="about-section">
    <div class="about-text">
        <h2>Về chúng tôi</h2>
        <p>
            RoseShop được thành lập với mong muốn mang đến cho khách hàng
            những bó hoa tươi đẹp nhất, được tuyển chọn kỹ lưỡng từ các
            vườn hoa uy tín tại Đà Lạt và các vùng trồng hoa chất lượng.
        </p>
        <p>
            Chúng tôi không chỉ bán hoa, mà còn trao gửi yêu thương,
            giúp bạn kết nối cảm xúc trong những dịp đặc biệt và cả những ngày bình thường.
        </p>
    </div>

    <div class="about-image">
        <img src="{{ asset('img/rs.png') }}" alt="RoseShop">
    </div>
</section>

<section class="about-features">
    <div class="about-card">
        <div>🌹</div>
        <h3>Hoa tươi mỗi ngày</h3>
        <p>Hoa được nhập mới mỗi ngày, đảm bảo tươi lâu và đẹp tự nhiên.</p>
    </div>

    <div class="about-card">
        <div>🏅</div>
        <h3>Chất lượng đảm bảo</h3>
        <p>Tuyển chọn kỹ lưỡng từ các nguồn hoa uy tín, chất lượng cao.</p>
    </div>

    <div class="about-card">
        <div>🚚</div>
        <h3>Giao hàng nhanh</h3>
        <p>Hỗ trợ giao hoa nhanh trong nội thành, đúng thời gian khách chọn.</p>
    </div>

    <div class="about-card">
        <div>🎧</div>
        <h3>Hỗ trợ tận tâm</h3>
        <p>Tư vấn nhiệt tình, giúp khách hàng chọn mẫu hoa phù hợp.</p>
    </div>
</section>

<section class="about-commit">
    <div>
        <h2>Cam kết của chúng tôi</h2>

        <ul>
            <li>100% hoa tươi, không hoa héo, hoa dập nát.</li>
            <li>Đổi trả miễn phí nếu hoa không đúng chất lượng.</li>
            <li>Giá cả hợp lý, cạnh tranh nhất thị trường.</li>
            <li>Tư vấn tận tình, giúp bạn chọn hoa phù hợp.</li>
        </ul>
    </div>

    <img src="{{ asset('img/ban1.png') }}" alt="Cam kết RoseShop">
</section>
@endsection