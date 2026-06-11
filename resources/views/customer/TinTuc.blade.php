@extends('customer.layouts.customer')

@section('title', 'Tin tức')

@section('head-styles')
<link rel="stylesheet" href="{{ asset('css/Customer/TinTuc.css') }}">
@endsection

@section('content')
<main class="news-page">
    <div class="news-page-head">
        <h1>Tin tức hoa tươi</h1>
        <p>Cập nhật mẹo chăm hoa, ý nghĩa các loài hoa và gợi ý quà tặng.</p>
    </div>

    <div class="news-list">
        <article class="news-item">
            <img src="{{ asset('img/TULIP.jpg') }}" alt="Cách bảo quản hoa hồng">
            <div>
                <h2>Cách bảo quản hoa hồng lâu tàn</h2>
                <p>Mẹo nhỏ giúp hoa hồng tươi lâu hơn và giữ màu đẹp.</p>
                <a href="{{ route('customer.tin-tuc.chi-tiet', 'bao-quan-hoa-hong') }}">
                    Xem chi tiết →
                </a>
            </div>
        </article>

        <article class="news-item">
            <img src="{{ asset('img/hoa tulip.jpeg') }}" alt="Ý nghĩa các màu hoa">
            <div>
                <h2>Ý nghĩa các màu hoa tươi</h2>
                <p>Mỗi loài hoa và màu sắc đều mang một thông điệp riêng.</p>
                <a href="{{ route('customer.tin-tuc.chi-tiet', 'y-nghia-mau-hoa') }}">
                    Xem chi tiết →
                </a>
            </div>
        </article>

        <article class="news-item">
            <img src="{{ asset('img/hoa ly.jpeg') }}" alt="Hoa tặng sinh nhật">
            <div>
                <h2>Gợi ý hoa tặng sinh nhật</h2>
                <p>Những mẫu hoa đẹp, dễ tặng và phù hợp nhiều đối tượng.</p>
                <a href="{{ route('customer.tin-tuc.chi-tiet', 'hoa-tang-sinh-nhat') }}">
                    Xem chi tiết →
                </a>
            </div>
        </article>
    </div>
</main>
@endsection