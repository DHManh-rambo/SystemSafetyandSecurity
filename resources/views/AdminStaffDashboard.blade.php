@extends('layouts.admin')

@section('content')

<div class="admin-home">

    <div class="home-header">
        <div>
            <!-- <h1>Chào mừng trở lại, {{ Auth::user()->ten_dang_nhap }} 👋</h1>
            <p>Dưới đây là tổng quan hoạt động của cửa hàng hôm nay.</p> -->
        </div>

        <div class="home-date">
            📅 Hôm nay: {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <div class="home-stats">
        <div class="home-card">
            <div class="home-icon pink">🛍️</div>
            <div>
                <p>Tổng sản phẩm</p>
                <h2>{{ number_format($tongSanPham ?? 0) }}</h2>
            </div>
        </div>

        <div class="home-card">
            <div class="home-icon blue">🛒</div>
            <div>
                <p>Tổng đơn hàng</p>
                <h2>{{ number_format($tongDonHang ?? 0) }}</h2>
            </div>
        </div>

        <div class="home-card">
            <div class="home-icon green">💵</div>
            <div>
                <p>Doanh thu hôm nay</p>
                <h2>{{ number_format($doanhThuHomNay ?? 0, 0, ',', '.') }}đ</h2>
            </div>
        </div>

        <div class="home-card">
            <div class="home-icon purple">👥</div>
            <div>
                <p>Tổng khách hàng</p>
                <h2>{{ number_format($tongKhachHang ?? 0) }}</h2>
            </div>
        </div>
    </div>

    <div class="home-main">

        <div class="home-panel chart-panel">
            <div class="panel-title">
                <h3>Doanh thu</h3>

                <select id="revenueRange" class="dashboard-select">
                    <option value="today" {{ ($range ?? '7days') == 'today' ? 'selected' : '' }}>Hôm nay</option>
                    <option value="7days" {{ ($range ?? '7days') == '7days' ? 'selected' : '' }}>7 ngày qua</option>
                    <option value="30days" {{ ($range ?? '7days') == '30days' ? 'selected' : '' }}>30 ngày qua</option>
                    <option value="month" {{ ($range ?? '7days') == 'month' ? 'selected' : '' }}>Tháng này</option>
                    <option value="year" {{ ($range ?? '7days') == 'year' ? 'selected' : '' }}>Năm nay</option>
                </select>
            </div>

            @php
                $chartData = $doanhThuTheoNgay ?? collect();
                $maxRevenue = max($chartData->max('doanh_thu') ?? 1, 1);
            @endphp

            <div class="dashboard-chart">
                @forelse($chartData as $item)
                    <div class="chart-bar-item">
                        <div
                            class="bar"
                            style="height: {{ max(25, ($item->doanh_thu / $maxRevenue) * 180) }}px;">
                        </div>

                        <strong>{{ number_format($item->doanh_thu, 0, ',', '.') }}</strong>

                        <span>{{ \Carbon\Carbon::parse($item->ngay)->format('d/m') }}</span>
                    </div>
                @empty
                    <div class="empty-chart">
                        Chưa có dữ liệu doanh thu.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="home-panel">
            <div class="panel-title">
                <h3>Đơn hàng chờ xử lý</h3>
                <a href="{{ route('don-hang.index') }}" class="mini-link">Xem tất cả</a>
            </div>

            <div class="order-list">
                @forelse($donChoXuLy as $order)
                    <div class="order-item">
                        <div class="order-icon">🧾</div>

                        <div>
                            <strong>HD{{ $order->ma_hoa_don }}</strong>
                            <p>{{ $order->ten_khach_hang ?? 'Khách hàng' }}</p>
                        </div>

                        <span>
                            {{ $order->ngay_dat ? \Carbon\Carbon::parse($order->ngay_dat)->format('d/m/Y') : 'Chưa có ngày' }}
                        </span>

                        <em>{{ $order->trang_thai }}</em>
                    </div>
                @empty
                    <p>Không có đơn hàng chờ xử lý.</p>
                @endforelse
            </div>
        </div>

    </div>

    <div class="home-bottom">

        <div class="home-panel">
            <div class="panel-title">
                <h3>Top 5 sản phẩm bán chạy</h3>
                <a href="{{ route('bao-cao.san-pham') }}" class="mini-link">Xem tất cả</a>
            </div>

            <table class="home-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Sản phẩm</th>
                        <th>Đã bán</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($topSanPham as $index => $sp)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $sp->ten_san_pham }}</td>
                            <td>{{ number_format($sp->tong_ban ?? 0) }}</td>
                            <td>{{ number_format($sp->doanh_thu ?? 0, 0, ',', '.') }}đ</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Chưa có dữ liệu bán hàng</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="home-panel">
            <div class="panel-title">
                <h3>Sản phẩm sắp hết hàng</h3>
                <a href="{{ route('san-pham.index') }}" class="mini-link">Xem tất cả</a>
            </div>

            <table class="home-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($sanPhamSapHet as $sp)
                        <tr>
                            <td>{{ $sp->ten_san_pham }}</td>
                            <td>{{ number_format($sp->so_luong ?? 0) }}</td>
                            <td><span class="low-stock">Sắp hết</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">Không có sản phẩm sắp hết hàng</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

<script>
document.getElementById('revenueRange')?.addEventListener('change', function () {
    window.location.href = "{{ route('admin.dashboard') }}?range=" + this.value;
});
</script>

@endsection