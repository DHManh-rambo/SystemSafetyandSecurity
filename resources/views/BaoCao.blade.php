<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo & Thống Kê</title>
    <link rel="stylesheet" href="{{ asset('css/BaoCao.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="wrapper">

    <div class="page-header">
        <div>
            <h1> Báo Cáo & Thống Kê</h1>
            <p>Báo cáo sản phẩm và doanh thu theo kỳ</p>
        </div>
    </div>

    <div class="tabs">
        <a href="{{ route('bao-cao.index', ['tab' => 'san-pham']) }}" class="tab-btn {{ $tab === 'san-pham' ? 'active' : '' }}"> Báo Cáo Sản Phẩm</a>
        <a href="{{ route('bao-cao.index', ['tab' => 'doanh-thu']) }}" class="tab-btn {{ $tab === 'doanh-thu' ? 'active' : '' }}"> Báo Cáo Doanh Thu</a>
    </div>

    {{-- ==================== DOANH THU ==================== --}}
    <div id="tab-doanh-thu" class="tab-content {{ $tab === 'doanh-thu' ? 'active' : '' }}">

        <form method="GET" action="{{ route('bao-cao.index') }}">
            <input type="hidden" name="tab" value="doanh-thu">
            <div class="filter-bar">
                <div>
                    <label>Từ Ngày</label>
                    <input type="date" name="tu_ngay"
                        value="{{ isset($tuNgay) ? \Carbon\Carbon::parse($tuNgay)->format('Y-m-d') : \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                <div>
                    <label>Đến Ngày</label>
                    <input type="date" name="den_ngay"
                        value="{{ isset($denNgay) ? \Carbon\Carbon::parse($denNgay)->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d') }}">
                </div>
                <div>
                    <label>Nhóm theo</label>
                    <select name="kieu_hien_thi">
                        <option value="ngay"  {{ ($kieuHienThi ?? '') === 'ngay'  ? 'selected' : '' }}>Theo ngày</option>
                        <option value="thang" {{ ($kieuHienThi ?? 'thang') === 'thang' ? 'selected' : '' }}>Theo tháng</option>
                        <option value="quy"   {{ ($kieuHienThi ?? '') === 'quy'   ? 'selected' : '' }}>Theo quý</option>
                        <option value="nam"   {{ ($kieuHienThi ?? '') === 'nam'   ? 'selected' : '' }}>Theo năm</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-dark">Xem Báo Cáo</button>
                <a href="{{ route('bao-cao.index', ['tab' => 'doanh-thu']) }}" class="btn btn-light">Đặt Lại</a>
            </div>
        </form>

        @isset($chiTietDoanhThu)
        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Tổng đơn hàng</div>
                <div class="value">{{ number_format($tongDonHang ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="label">Tổng sản phẩm bán</div>
                <div class="value">{{ number_format($tongSanPhamBan ?? 0) }}</div>
            </div>
            <div class="stat-card highlight">
                <div class="label">Tổng doanh thu</div>
                <div class="value">{{ number_format($tongDoanhThu ?? 0, 0, ',', '.') }}đ</div>
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

        <div class="card">
            <div class="card-title">Chi tiết doanh thu</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Thời gian</th>
                            <th class="text-right">Số đơn hàng</th>
                            <th class="text-right">Sản phẩm bán</th>
                            <th class="text-right">Doanh thu</th>
                            <th class="text-right">Giá vốn</th>
                            <th class="text-right">Lợi nhuận</th>
                            <th class="text-right">Tỷ lệ LN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($chiTietDoanhThu as $index => $item)
                            @php
                                $tyLeLoiNhuan = ($item->doanh_thu ?? 0) > 0
                                    ? (($item->loi_nhuan ?? 0) / $item->doanh_thu) * 100
                                    : 0;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->thoi_gian }}</td>
                                <td class="text-right">{{ number_format($item->so_don_hang ?? 0) }}</td>
                                <td class="text-right">{{ number_format($item->tong_san_pham_ban ?? 0) }}</td>
                                <td class="text-right">{{ number_format($item->doanh_thu ?? 0, 0, ',', '.') }}đ</td>
                                <td class="text-right">{{ number_format($item->gia_von ?? 0, 0, ',', '.') }}đ</td>
                                <td class="text-right">{{ number_format($item->loi_nhuan ?? 0, 0, ',', '.') }}đ</td>
                                <td class="text-right">{{ number_format($tyLeLoiNhuan, 2, ',', '.') }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-row">Không có dữ liệu trong khoảng thời gian này.</td>
                            </tr>
                        @endforelse
                        <tr class="total-row">
                            <td colspan="2"><strong>Tổng cộng</strong></td>
                            <td class="text-right"><strong>{{ number_format($tongDonHang ?? 0) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($tongSanPhamBan ?? 0) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($tongDoanhThu ?? 0, 0, ',', '.') }}đ</strong></td>
                            <td class="text-right"><strong>{{ number_format($tongVon ?? 0, 0, ',', '.') }}đ</strong></td>
                            <td class="text-right"><strong>{{ number_format($tongLoiNhuan ?? 0, 0, ',', '.') }}đ</strong></td>
                            <td class="text-right"><strong>{{ ($tongDoanhThu ?? 0) > 0 ? number_format((($tongLoiNhuan ?? 0) / $tongDoanhThu) * 100, 2, ',', '.') : '0,00' }}%</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if(isset($topSanPham) && $topSanPham->count() > 0)
        <div class="card">
            <div class="card-title">Top sản phẩm doanh thu cao</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Sản phẩm</th>
                            <th class="text-right">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topSanPham as $index => $sp)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $sp->ten_san_pham }}</td>
                                <td class="text-right">{{ number_format($sp->doanh_thu, 0, ',', '.') }}đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @endisset
    </div>

    {{-- ==================== BÁO CÁO SẢN PHẨM ==================== --}}
    <div id="tab-san-pham" class="tab-content {{ $tab === 'san-pham' ? 'active' : '' }}">
        <iframe
            src="{{ route('bao-cao.san-pham') }}"
            style="width:100%; min-height:1100px; border:none; display:block;"
            id="iframeSanPham"
            onload="autoResizeIframe(this)">
        </iframe>
    </div>

</div>

<script>
function autoResizeIframe(iframe) {
    try {
        iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 40 + 'px';
    } catch(e) {}
}
</script>

<script>
const mauChinh = '#111111';
const mauXam   = ['#111','#333','#555','#777','#999','#bbb','#ccc','#ddd','#eee','#f5f5f5'];

const ctx2 = document.getElementById('chartTrangThai');
if (ctx2) {
    const nhanTrangThai = {
        'PENDING': 'Chờ Xử Lý', 'CONFIRMED': 'Đã Xác Nhận',
        'SHIPPING': 'Đang Giao', 'DELIVERED': 'Hoàn Thành', 'CANCELLED': 'Đã Hủy',
    };
    const rawTrangThai = @json($trangThaiDonHang);
    new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: Object.keys(rawTrangThai).map(k => nhanTrangThai[k] || k),
            datasets: [{
                data: Object.values(rawTrangThai),
                backgroundColor: ['#111','#444','#777','#aaa','#ddd'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
        }
    });
}

// Chart: Sản phẩm bán chạy (tab sản phẩm)
@isset($topSanPhamBanChay)
@if($topSanPhamBanChay->count() > 0)
const ctx3 = document.getElementById('chartSanPham');
if (ctx3) {
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: @json($topSanPhamBanChay->pluck('ten_san_pham')),
            datasets: [{
                label: 'Số lượng bán',
                data: @json($topSanPhamBanChay->pluck('tong_da_ban')),
                backgroundColor: mauXam.slice(0, @json($topSanPhamBanChay->count())),
                borderWidth: 0,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#eee' }, beginAtZero: true },
                x: { grid: { display: false } }
            }
        }
    });
}
@endif
@endisset

// Chart: Doanh thu theo khoảng ngày (tab doanh-thu)
@isset($dtTheoNgay)
@if(count($dtTheoNgay['labels']) > 0)
const ctx4 = document.getElementById('chartDoanhThuRange');
if (ctx4) {
    new Chart(ctx4, {
        type: 'line',
        data: {
            labels: @json($dtTheoNgay['labels']),
            datasets: [{
                label: 'Doanh Thu (đ)',
                data: @json($dtTheoNgay['data']),
                borderColor: mauChinh,
                backgroundColor: 'rgba(17,17,17,0.07)',
                borderWidth: 2,
                pointRadius: 3,
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    ticks: { callback: val => new Intl.NumberFormat('vi-VN').format(val) + 'đ' },
                    grid: { color: '#eee' }
                },
                x: { grid: { display: false } }
            }
        }
    });
}
@endif
@endisset

// Chart: Sản phẩm bán chạy (tab sản phẩm)
@isset($spDanhSach)
@if($spDanhSach->count() > 0)
const ctx6 = document.getElementById('chartSanPham');
if (ctx6) {
    new Chart(ctx6, {
        type: 'bar',
        data: {
            labels: @json($spDanhSach->pluck('ten_san_pham')),
            datasets: [{
                label: 'Số lượng bán',
                data: @json($spDanhSach->pluck('tong_ban')),
                backgroundColor: mauXam.slice(0, {{ $spDanhSach->count() }}),
                borderWidth: 0,
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: '#eee' }, beginAtZero: true },
                y: { grid: { display: false } }
            }
        }
    });
}
@endif
@endisset

@isset($hhTopHong)
@if($hhTopHong->count() > 0)
const ctx8 = document.getElementById('chartTopHangHong');
if (ctx8) {
    new Chart(ctx8, {
        type: 'bar',
        data: {
            labels: @json($hhTopHong->pluck('ten_san_pham')),
            datasets: [{
                label: 'Tổng hỏng',
                data: @json($hhTopHong->pluck('tong_hong')),
                backgroundColor: ['#dc3545','#e74c3c','#c0392b','#922b21','#7b241c'],
                borderWidth: 0,
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: '#eee' }, beginAtZero: true },
                y: { grid: { display: false } }
            }
        }
    });
}
@endif
@endisset
</script>
</body>
</html>