@extends('customer.layouts.customer')

@section('head-styles')
<link rel="stylesheet" href="{{ asset('css/Customer/HoaTuoi.css') }}">
@endsection

@section('title', 'Phụ Kiện')

@section('content')
<main class="flower-page">
   <aside class="flower-sidebar">
    <form method="GET">
        <h3>KHOẢNG GIÁ</h3>

        <label>
            <input type="checkbox" name="price[]" value="duoi_20000"
                {{ in_array('duoi_20000', request('price', [])) ? 'checked' : '' }}>
            Dưới 20.000đ
        </label>

        <label>
            <input type="checkbox" name="price[]" value="20000_50000"
                {{ in_array('20000_50000', request('price', [])) ? 'checked' : '' }}>
            20.000đ - 50.000đ
        </label>

        <label>
            <input type="checkbox" name="price[]" value="50000_100000"
                {{ in_array('50000_100000', request('price', [])) ? 'checked' : '' }}>
            50.000đ - 100.000đ
        </label>

        <label>
            <input type="checkbox" name="price[]" value="tren_100000"
                {{ in_array('tren_100000', request('price', [])) ? 'checked' : '' }}>
            Trên 100.000đ
        </label>

        <button type="submit" class="filter-btn">
            Lọc sản phẩm
        </button>
    </form>
</aside>

    <section class="flower-content">
        <div class="flower-grid">
            @foreach($phuKiens as $sp)
                @php
                    $loConHang = $sp->chiTietNhaps
                        ->where('so_luong_con_lai', '>', 0);
                    $gia = $sp->gia_ban_hien_tai;
                    $tonKho = $loConHang->sum('so_luong_con_lai');
                @endphp

                <div class="flower-card">
                    <img src="{{ $sp->anh }}" alt="{{ $sp->ten_san_pham }}">

                    <div class="flower-card-body">
                        <h3>{{ $sp->ten_san_pham }}</h3>

                        <p class="flower-desc">
                            <strong>Mô tả:</strong>
                            {{ $sp->mo_ta ?? 'Chưa có mô tả' }}
                        </p>

                        <p class="flower-price">
                            {{ $gia ? number_format($gia, 0, ',', '.') . 'đ' : 'Liên hệ' }}
                            <span>/ cái</span>
                        </p>

                        <div class="home-card-bottom">
                            <span>Sản Phẩm: {{ $tonKho }}</span>

                            <a href="{{ route('customer.san-pham.chi-tiet', $sp->ma_san_pham) }}" class="detail-btn">
                                Chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAll');
    const categories = document.querySelectorAll('.category-checkbox');

    if (!checkAll) return;

    checkAll.addEventListener('change', function () {
        categories.forEach(item => {
            item.checked = this.checked;
        });
    });

    categories.forEach(item => {
        item.addEventListener('change', function () {
            const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
            checkAll.checked = checkedCount === categories.length;
        });
    });
});
</script>
@endsection