@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/BaoCao.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')

<div class="bao-cao-doanh-thu-container">
<div class="page-container">

    <div class="page-header-section">
        <div class="header-actions">
            @if(Route::has('bao-cao.doanh-thu.export'))
                <a href="{{ route('bao-cao.doanh-thu.export', request()->query()) }}" class="btn-excel">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    Xuất Excel
                </a>
            @endif
        </div>
    </div>



    {{-- FILTER --}}
   <form method="GET" action="{{ route('bao-cao.doanh-thu') }}">
    <div class="filter-card">
    <div class="filter-card-header">
        <i class="bi bi-funnel"></i>LỌC THEO THỜI GIAN
    </div>

    <div class="filter-content">
        <div>
            <label>Từ ngày</label>
            <input type="date" name="tu_ngay" value="{{ $tuNgay ?? now()->startOfMonth()->format('Y-m-d') }}">
        </div>

        <div>
            <label>Đến ngày</label>
            <input type="date" name="den_ngay" value="{{ $denNgay ?? now()->format('Y-m-d') }}">
        </div>



        <div>
            <label>Nhóm dữ liệu theo</label>
            <select name="kieu_hien_thi">
            <option value="ngay" {{ ($kieuHienThi ?? '') == 'ngay' ? 'selected' : '' }}>
                Theo ngày
            </option>

            <option value="thang" {{ ($kieuHienThi ?? 'thang') == 'thang' ? 'selected' : '' }}>
                Theo tháng
            </option>

            <option value="quy" {{ ($kieuHienThi ?? '') == 'quy' ? 'selected' : '' }}>
                Theo quý
            </option>

            <option value="nam" {{ ($kieuHienThi ?? '') == 'nam' ? 'selected' : '' }}>
                Theo năm
            </option>
        </select>
        </div>

        <button type="submit" class="btn-filter">
            <i class="bi bi-funnel"></i>
            Lọc dữ liệu
        </button>

    </div>
</div>
</form>
{{-- CARD --}}
<div class="stats-grid">

    <div class="stat-card">
        <div class="label">Tổng đơn hàng</div>
        <div class="value">{{ number_format($tongDonHang ?? 0) }}</div>
    </div>
    <div class="stat-card">
    <div class="label">
        Tổng sản phẩm bán
    </div>

    <div class="value">
        {{ number_format($tongSanPhamBan ?? 0) }}
    </div>
</div>

   <div class="stat-card">
        <div class="label">Tổng doanh thu</div>
        <div class="value">{{ number_format($tongDoanhThu ?? 0, 0, ',', '.') }}đ</div>

        @if(!empty($ssTuNgay) && !empty($ssDenNgay))
           
        @endif
    </div>

    <div class="stat-card">
        <div class="label">Tổng vốn</div>
        <div class="value">{{ number_format($tongVon ?? 0, 0, ',', '.') }}đ</div>
    </div>

    <div class="stat-card">
        <div class="label">Tổng lợi nhuận</div>
        <div class="value">{{ number_format($tongLoiNhuan ?? 0, 0, ',', '.') }}đ</div>
    </div>

    <div class="stat-card">
        <div class="label">Giá trị đơn TB</div>
        <div class="value">{{ number_format($giaTriDonTrungBinh ?? 0, 0, ',', '.') }}đ</div>
    </div>

</div>
<div class="table-card">
    <h3>Chi tiết doanh thu</h3>

    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Thời gian</th>
                <th>Số đơn hàng</th>
                <th>Sản phẩm bán</th>
                <th>Doanh thu</th>
                <th>Giá vốn</th>
                <th>Lợi nhuận</th>
                <th>Tỷ lệ lợi nhuận</th>
            </tr>
        </thead>

        <tbody>
            @forelse($chiTietDoanhThu as $index => $item)
                @php
                    $tyLeLoiNhuan = $item->doanh_thu > 0
                        ? ($item->loi_nhuan / $item->doanh_thu) * 100
                        : 0;
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->thoi_gian }}</td>
                    <td>{{ number_format($item->so_don_hang) }}</td>
                    <td>{{ number_format($item->tong_san_pham_ban) }}</td>
                    <td>{{ number_format($item->doanh_thu, 0, ',', '.') }}đ</td>
                    <td>{{ number_format($item->gia_von, 0, ',', '.') }}đ</td>
                    <td class="profit">{{ number_format($item->loi_nhuan, 0, ',', '.') }}đ</td>
                    <td>{{ number_format($tyLeLoiNhuan, 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Không có dữ liệu trong khoảng thời gian này.</td>
                </tr>
            @endforelse

            <tr class="total-row">
                <td colspan="2">Tổng cộng</td>
                <td>{{ number_format($tongDonHang ?? 0) }}</td>
                <td>{{ number_format($tongSanPhamBan ?? 0) }}</td>
                <td>{{ number_format($tongDoanhThu ?? 0, 0, ',', '.') }}đ</td>
                <td>{{ number_format($tongVon ?? 0, 0, ',', '.') }}đ</td>
                <td class="profit">{{ number_format($tongLoiNhuan ?? 0, 0, ',', '.') }}đ</td>
                <td>
                    {{ ($tongDoanhThu ?? 0) > 0 ? number_format((($tongLoiNhuan ?? 0) / $tongDoanhThu) * 100, 2, ',', '.') : '0,00' }}%
                </td>
            </tr>
        </tbody>
    </table>
</div>
   
    {{-- BẢNG --}}
   <div class="table-card">

    <h3>Top sản phẩm doanh thu cao</h3>

    <table>

        <thead>
            <tr>
                <th>STT</th>
                <th>Sản phẩm</th>
                <th>Số lượng bán</th>
                <th>Doanh thu</th>
            </tr>
        </thead>    

        <tbody>

            @forelse($topSanPham as $index => $sp)

                <tr>

                    <td>{{ $index + 1 }}</td>

                    <td>
                        {{ $sp->ten_san_pham }}
                    </td>
                    <td>
                        {{ number_format($sp->tong_san_pham_ban ?? 0) }}
                    </td>
                    <td>
                        {{ number_format($sp->doanh_thu, 0, ',', '.') }}đ
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="4">
                        Không có dữ liệu
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>
</div>
</div>

@endsection
