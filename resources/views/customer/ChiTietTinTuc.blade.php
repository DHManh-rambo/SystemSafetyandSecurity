@extends('customer.layouts.customer')

@section('title', 'Chi tiết tin tức')

@section('head-styles')
<link rel="stylesheet" href="{{ asset('css/Customer/ChiTietTinTuc.css') }}">
@endsection

@section('content')

@php
$posts = [
    'bao-quan-hoa-hong' => [
        'title' => 'Cách bảo quản hoa hồng lâu tàn',
        'subtitle' => 'Những mẹo nhỏ giúp hoa luôn tươi, đẹp và giữ được vẻ rực rỡ lâu hơn.',
        'banner' => asset('img/hongdo.jpg'),
        'img1' => asset('img/hoa lan.jpeg'),
        'img2' => asset('img/hoa cúc.jpg'),
    ],
    'y-nghia-mau-hoa' => [
        'title' => 'Ý nghĩa các màu hoa tươi',
        'subtitle' => 'Mỗi sắc hoa là một thông điệp yêu thương được gửi gắm thật tinh tế.',
        'banner' => asset('img/hoa tulip.jpeg'),
        'img1' => asset('img/hoa ly.jpeg'),
        'img2' => asset('img/hoa hướng dương.jpeg'),
    ],
    'hoa-tang-sinh-nhat' => [
        'title' => 'Gợi ý hoa tặng sinh nhật',
        'subtitle' => 'Chọn một bó hoa phù hợp để ngày sinh nhật trở nên đáng nhớ hơn.',
        'banner' => asset('img/hoa ly.jpeg'),
        'img1' => asset('img/hoa hong.jpeg'),
        'img2' => asset('img/hoa tulip.jpeg'),
    ],
];

$post = $posts[$slug] ?? $posts['bao-quan-hoa-hong'];
@endphp

<main class="article-page">

    <div class="article-breadcrumb">
        <a href="{{ route('customer.dashboard') }}">Trang chủ</a>
        <span>/</span>
        <a href="{{ route('customer.tin-tuc') }}">Tin tức</a>
        <span>/</span>
        <span>{{ $post['title'] }}</span>
    </div>

    <section class="article-hero">
        <div class="article-hero-text">
            <span class="article-tag">🌸 RoseShop Blog</span>
            <h1>{{ $post['title'] }}</h1>
            <p>{{ $post['subtitle'] }}</p>

            <div class="article-meta">
                <span>📅 {{ now()->format('d/m/Y') }}</span>
                <span>⏱️ 5 phút đọc</span>
                <span>💐 Chủ đề hoa tươi</span>
            </div>
        </div>

        <img src="{{ $post['banner'] }}" alt="{{ $post['title'] }}">
    </section>

    <section class="article-content-card">

        <p>
            Hoa không chỉ là một món quà đơn thuần mà còn là cách để con người gửi gắm những cảm xúc chân thành nhất
            đến những người thân yêu. Từ xa xưa, hoa đã xuất hiện trong đời sống như một biểu tượng của tình yêu,
            lòng biết ơn, sự kính trọng và những lời chúc tốt đẹp.
        </p>

        <p>
            Trong cuộc sống hiện đại, hoa được sử dụng trong rất nhiều dịp khác nhau như sinh nhật, lễ kỷ niệm,
            khai trương, chúc mừng thành công hay đơn giản là để trang trí không gian sống. Một bó hoa tươi không chỉ
            mang đến vẻ đẹp tự nhiên mà còn giúp cải thiện tâm trạng, tạo cảm giác thư giãn và lan tỏa nguồn năng lượng tích cực.
        </p>

        <div class="article-quote">
            “Một bó hoa đẹp không chỉ làm sáng không gian, mà còn làm dịu trái tim người nhận.”
        </div>

        <h2>🌷 Vì sao hoa tươi luôn là món quà đặc biệt?</h2>

        <p>
            Mỗi loài hoa đều có một vẻ đẹp riêng. Hoa hồng tượng trưng cho tình yêu, hoa tulip thể hiện sự thanh lịch,
            hoa hướng dương mang ý nghĩa về niềm tin và hy vọng, còn hoa ly lại gợi cảm giác tinh tế, sang trọng.
            Chính vì vậy, việc chọn đúng loài hoa sẽ khiến món quà trở nên sâu sắc và đáng nhớ hơn.
        </p>

        <div class="article-image-row">
            <img src="{{ $post['img1'] }}" alt="Hoa tươi RoseShop">
            <img src="{{ $post['img2'] }}" alt="Hoa đẹp RoseShop">
        </div>

        <h2>🌿 Cách giữ hoa luôn tươi lâu</h2>

        <p>
            Để hoa luôn giữ được vẻ đẹp rực rỡ, bạn nên cắt gốc hoa theo góc 45 độ trước khi cắm vào bình.
            Bình hoa cần được vệ sinh sạch sẽ và thay nước hằng ngày. Ngoài ra, nên đặt hoa ở nơi thoáng mát,
            tránh ánh nắng trực tiếp, tránh gần thiết bị tỏa nhiệt hoặc trái cây chín.
        </p>

        <div class="tips-box">
            <h3>💡 Mẹo nhỏ từ RoseShop</h3>
            <ul>
                <li>Cắt gốc hoa mỗi ngày để hoa hút nước tốt hơn.</li>
                <li>Thay nước sạch thường xuyên để hạn chế vi khuẩn.</li>
                <li>Đặt hoa ở nơi mát, tránh nắng gắt và gió mạnh.</li>
                <li>Có thể dùng dung dịch dưỡng hoa để kéo dài độ tươi.</li>
            </ul>
        </div>

        <h2>💝 Chọn hoa cũng là một nghệ thuật</h2>

        <p>
            Việc lựa chọn hoa phù hợp với từng dịp thể hiện sự tinh tế của người tặng. Một bó hoa sinh nhật nên mang
            màu sắc tươi sáng, một bó hoa kỷ niệm có thể nhẹ nhàng và lãng mạn, còn hoa chúc mừng nên rực rỡ,
            nổi bật và tràn đầy năng lượng.
        </p>

        <p>
            Tại RoseShop, mỗi sản phẩm hoa đều được lựa chọn kỹ lưỡng từ nguồn cung cấp uy tín. Chúng tôi mong muốn
            mỗi bó hoa khi đến tay khách hàng không chỉ đẹp về hình thức mà còn truyền tải trọn vẹn tình cảm,
            sự quan tâm và những lời chúc tốt lành.
        </p>

        <div class="article-final">
            <h3>🌸 RoseShop - Gửi trọn yêu thương qua từng đóa hoa</h3>
            <p>
                Dù là một bó hoa nhỏ dành tặng người thân hay một lẵng hoa sang trọng cho những sự kiện quan trọng,
                RoseShop luôn đồng hành để giúp bạn lưu giữ những khoảnh khắc đẹp nhất.
            </p>
        </div>

    </section>

    <div class="article-back">
        <a href="{{ route('customer.tin-tuc') }}">← Quay lại danh sách tin tức</a>
    </div>

</main>

@endsection