@extends('layouts.admin')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/BaoCaoSanPham.css') }}">
@endpush

@section('content')

<div class="bao-cao-san-pham-container">
{{-- ═══════════════════════ TOPBAR ═══════════════════════ --}}


<div class="page-container">

    {{-- ═══════════════════════ PAGE HEADER ═══════════════════════ --}}
    <div class="page-header-section mb-4">
       
        <div class="header-actions">
            <a href="{{ route('bao-cao.san-pham.export', array_filter([
                    'ngay_bat_dau' => $ngayBatDau->format('Y-m-d'),
                    'ngay_ket_thuc' => $ngayKetThuc->format('Y-m-d'),
                    'keyword' => $keyword
                ])) }}"
               class="btn btn-excel">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Xuất Excel
            </a>
        </div>
    </div>

    {{-- ═══════════════════════ BỘ LỌC THỜI GIAN ═══════════════════════ --}}
    <div class="filter-card mb-4">
        <div class="filter-card-header mb-3">
            <span class="filter-title">
                <i class="bi bi-funnel-fill me-1"></i> LỌC THEO THỜI GIAN
            </span>
        </div>

        <form method="GET" action="{{ route('bao-cao.san-pham') }}" id="formFilter">
            <div class="d-flex flex-wrap gap-3 align-items-end">
                <div>
                    <label class="filter-label">Ngày bắt đầu</label>
                    <div class="input-icon-wrap">
                        <input type="date"
                               name="ngay_bat_dau"
                               class="form-control filter-input"
                               value="{{ $ngayBatDau->format('Y-m-d') }}">
                        <i class="bi bi-calendar3 input-icon"></i>
                    </div>
                </div>

                <div>
                    <label class="filter-label">Ngày kết thúc</label>
                    <div class="input-icon-wrap">
                        <input type="date"
                               name="ngay_ket_thuc"
                               class="form-control filter-input"
                               value="{{ $ngayKetThuc->format('Y-m-d') }}">
                        <i class="bi bi-calendar3 input-icon"></i>
                    </div>
                </div>

                @if($keyword)
                    <input type="hidden" name="keyword" value="{{ $keyword }}">
                @endif

                <div>
                    <button type="submit" class="btn btn-filter">
                        <i class="bi bi-funnel me-1"></i> Lọc dữ liệu
                    </button>
                </div>

                <div>
                    <a href="{{ route('bao-cao.san-pham') }}" class="btn btn-reset">
                        <i class="bi bi-arrow-clockwise me-1"></i> Đặt lại
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ═══════════════════════ TÌM KIẾM SẢN PHẨM ═══════════════════════ --}}
    <div class="search-wrap mb-3">
        <form method="GET" action="{{ route('bao-cao.san-pham') }}" id="formSearch">
            <input type="hidden" name="ngay_bat_dau" value="{{ $ngayBatDau->format('Y-m-d') }}">
            <input type="hidden" name="ngay_ket_thuc" value="{{ $ngayKetThuc->format('Y-m-d') }}">

            <div class="search-input-wrap">
                <i class="bi bi-search search-icon"></i>
                <input type="text"
                       name="keyword"
                       class="search-input"
                       placeholder="Tìm kiếm sản phẩm..."
                       value="{{ $keyword }}"
                       oninput="this.form.submit()">
            </div>
        </form>
    </div>

    {{-- ═══════════════════════ BẢNG BÁO CÁO SẢN PHẨM ═══════════════════════ --}}
    <div class="table-card mb-3">
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th class="col-stt">STT</th>
                        <th class="col-sp">Sản phẩm</th>
                        <th class="col-loai">Loại sản phẩm</th>
                        <th class="col-num">Nhập thêm<br><small>(Số lượng)</small></th>
                        <th class="col-num">Đã bán<br><small>(Số lượng)</small></th>
                        <th class="col-num">Hàng hỏng<br><small>(Số lượng)</small></th>
                        <th class="col-num">Tồn kho hiện tại<br><small>(Số lượng)</small></th>
                        <th class="col-num">Giá nhập TB<br><small>(VNĐ)</small></th>
                        <th class="col-num">Giá bán hiện tại<br><small>(VNĐ)</small></th>
                        <th class="col-num">Doanh thu<br><small>(VNĐ)</small></th>
                        <th class="col-num">Tỷ lệ hỏng<br><small>(%)</small></th>
                        <th class="col-tt">Trạng thái</th>
                        <th class="col-action">Chi tiết</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($baoCaoSanPham as $i => $row)
                        <tr>
                            <td class="text-center ">{{ $i + 1 }}</td>

                            {{-- Sản phẩm --}}
                            <td >
    {{ $row['ten_san_pham'] }}
</td>

                            {{-- Loại --}}
                            <td>{{ \App\Helpers\LabelHelper::loaiSanPham($row['loai_san_pham']) }}</td>

                            {{-- Nhập thêm --}}
                            <td class="num-green">{{ number_format($row['nhap_them']) }}</td>

                            {{-- Đã bán --}}
                            <td class="num-purple">{{ number_format($row['da_ban']) }}</td>

                            {{-- Hàng hỏng --}}
                            <td class="num-red">
                                {{ number_format($row['hang_hong']) }}

                                @if($row['hang_hong'] > 0)
                                    <br>
                                    <a href="#"
                                       class="link-chitiet"
                                       data-id="{{ $row['ma_san_pham'] }}"
                                       data-ten="{{ $row['ten_san_pham'] }}"
                                       data-batdau="{{ $ngayBatDau->format('Y-m-d') }}"
                                       data-ketthuc="{{ $ngayKetThuc->format('Y-m-d') }}">
                                        &lt;&lt; Chi tiết &gt;&gt;
                                    </a>
                                @endif
                            </td>

                            {{-- Tồn kho hiện tại --}}
                            <td class="num-orange">{{ number_format($row['ton_kho_hien_tai']) }}</td>

                            {{-- Giá nhập TB --}}
                            <td class="text-end">
                                {{ $row['gia_nhap_tb'] !== null ? number_format($row['gia_nhap_tb'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Giá bán hiện tại --}}
                            <td class="text-end">
                                {{ $row['gia_ban_hien_tai'] > 0 ? number_format($row['gia_ban_hien_tai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Doanh thu --}}
                            <td class="text-end fw-semibold">
                                {{ number_format($row['doanh_thu'] ?? 0, 0, ',', '.') }}
                            </td>

                            {{-- Tỷ lệ hỏng --}}
                            <td class="text-end {{ $row['ty_le_hong'] > 10 ? 'num-red' : '' }}">
                                {{ number_format($row['ty_le_hong'], 2, ',', '.') }}%
                            </td>

                            {{-- Trạng thái --}}
                            <td>
                                <span class="badge-tt {{ $row['trang_thai'] === 'DANG_BAN' ? 'badge-dang-ban' : 'badge-ngung-ban' }}">
                                    {{ $row['trang_thai'] === 'DANG_BAN' ? 'Đang bán' : 'Ngừng bán' }}
                                </span>
                            </td>

                            {{-- Chi tiết sản phẩm tổng hợp --}}
                            <td class="text-center">
                                <button type="button"
                                        class="btn-chi-tiet-san-pham"
                                        data-id="{{ $row['ma_san_pham'] }}"
                                        data-batdau="{{ $ngayBatDau->format('Y-m-d') }}"
                                        data-ketthuc="{{ $ngayKetThuc->format('Y-m-d') }}"
                                        title="Xem chi tiết sản phẩm">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Không có dữ liệu sản phẩm trong khoảng thời gian này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- ── DÒNG TỔNG CỘNG ── --}}
                @if($baoCaoSanPham->count() > 0)
                    <tfoot>
                        <tr class="tong-cong-row">
                            <td colspan="3" class="fw-bold">TỔNG CỘNG</td>
                            <td class="num-green fw-bold">{{ number_format($tongNhapThem) }}</td>
                            <td class="num-purple fw-bold">{{ number_format($tongDaBan) }}</td>
                            <td class="num-red fw-bold">{{ number_format($tongHangHong) }}</td>
                            <td class="num-orange fw-bold">{{ number_format($tongTonKhoHienTai) }}</td>
                            <td class="text-end fw-bold">
                                {{ $giaNhapTrungBinhChung !== null ? number_format($giaNhapTrungBinhChung, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end">—</td>
                            <td class="text-end fw-bold">{{ number_format($tongDoanhThuSanPham ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end num-red fw-bold">{{ number_format($tyLeHongChung, 2, ',', '.') }}%</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ═══════════════════════ GHI CHÚ ═══════════════════════ --}}
    <div class="info-note mb-3">
        <i class="bi bi-info-circle-fill me-2 flex-shrink-0"></i>
        <div>
            <div>• <strong>Nhập thêm:</strong> Tổng số lượng nhập trong khoảng thời gian đã chọn.</div>
            <div>• <strong>Đã bán:</strong> Tổng số lượng bán từ các hóa đơn đã hoàn thành.</div>
            <div>• <strong>Hàng hỏng:</strong> Tổng số lượng hỏng được ghi nhận trong khoảng thời gian đã chọn.</div>
            <div>• <strong>Tồn kho hiện tại:</strong> Số lượng sản phẩm hiện đang còn trong kho.</div>
            <div>• <strong>Doanh thu:</strong> Tổng tiền bán của từng sản phẩm trong khoảng thời gian đã chọn.</div>
        </div>
    </div>

    {{-- ═══════════════════════ ĐIỀU HƯỚNG PHIẾU NHẬP ═══════════════════════ --}}
    <div class="nav-phieu-nhap mb-4">
        <div class="nav-pn-left">
            <i class="bi bi-box-seam-fill me-2 flex-shrink-0" style="color:#28a745;font-size:1.3rem;"></i>
            <div>
                <div class="fw-semibold">Muốn xem chi tiết phiếu nhập, giá nhập và các thông tin nhập hàng?</div>
                <div class="text-muted small mt-1">Vào Quản lý phiếu nhập để xem đầy đủ thông tin.</div>
            </div>
        </div>

        <a href="{{ route('phieu-nhap.index') }}" class="btn btn-nav-pn" target="_blank">
            <i class="bi bi-box-arrow-up-right me-1"></i> Đến Quản lý phiếu nhập
        </a>
    </div>

    {{-- ═══════════════════════ BIỂU ĐỒ TOP 5 SẢN PHẨM BÁN CHẠY ═══════════════════════ --}}
    <div class="chart-card mb-4">
        <div class="chart-title">
            <i class="bi bi-trophy-fill me-2" style="color:#e91e8c;"></i>
            BIỂU ĐỒ TOP 5 SẢN PHẨM BÁN CHẠY
        </div>

        @if($topSanPhamBanChay->count() > 0)
            <div class="chart-label-y">Số lượng đã bán</div>
            <canvas id="chartTop5" style="max-height:320px;"></canvas>
            <div class="chart-legend mt-3">
                <span class="legend-dot"></span> Số lượng đã bán
            </div>
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-bar-chart-line fs-2 d-block mb-2"></i>
                Chưa có dữ liệu bán hàng trong khoảng thời gian này.
            </div>
        @endif
    </div>

</div>{{-- end page-container --}}

{{-- ═══════════════════════ MODAL CHI TIẾT HÀNG HỎNG ═══════════════════════ --}}
<div class="modal fade" id="modalHangHong" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header modal-header-custom">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    CHI TIẾT HÀNG HỎNG
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="modalHangHongInfo" class="mb-3" style="display:none;">
                    <div class="modal-sp-info">
                        <span>Sản phẩm: <strong id="modalTenSP"></strong></span>
                        <span class="ms-3">
                            Tổng số lượng hỏng:
                            <strong class="text-danger" id="modalTongSoLuong"></strong>
                        </span>
                    </div>
                </div>

                <div id="modalHangHongBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-danger" role="status"></div>
                        <div class="mt-2 text-muted">Đang tải dữ liệu...</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Đóng
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════ MODAL CHI TIẾT SẢN PHẨM TỔNG HỢP ═══════════════════════ --}}
<!-- <div class="modal fade" id="modalChiTietSanPham" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header modal-header-custom">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam-fill me-2"></i>
                    CHI TIẾT SẢN PHẨM
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="chiTietSanPhamBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-danger" role="status"></div>
                        <div class="mt-2 text-muted">Đang tải dữ liệu...</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div> -->


<div id="drawerChiTietSanPham" class="drawer-chi-tiet">

    <div class="drawer-overlay"></div>

    <div class="drawer-content">

        <div class="drawer-header">

            <h5 class="mb-0">
                <i class="bi bi-box-seam-fill me-2"></i>
                Chi tiết sản phẩm
            </h5>

            <button type="button" class="drawer-close">
                <i class="bi bi-x-lg"></i>
            </button>

        </div>

        <div class="drawer-body" id="chiTietSanPhamBody">

            <div class="text-center py-5">
                <div class="spinner-border text-danger"></div>
                <div class="mt-2 text-muted">
                    Đang tải dữ liệu...
                </div>
            </div>

        </div>

    </div>

</div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── 1. Biểu đồ Top 5 sản phẩm bán chạy ───────────────────────────────────
@if($topSanPhamBanChay->count() > 0)
(function () {
    const labels = @json($topSanPhamBanChay->pluck('ten_san_pham'));
    const data   = @json($topSanPhamBanChay->pluck('tong_da_ban'));

    new Chart(document.getElementById('chartTop5'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Số lượng đã bán',
                data,
                backgroundColor: '#e91e8c',
                borderRadius: 6,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.y.toLocaleString('vi-VN') + ' sản phẩm'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' },
                    ticks: { callback: v => v.toLocaleString('vi-VN') }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
})();
@endif

// ── 2. Helpers ───────────────────────────────────────────────────────────
function formatMoney(value) {
    value = Number(value || 0);
    return value.toLocaleString('vi-VN') + ' đ';
}

function formatNumber(value) {
    value = Number(value || 0);
    return value.toLocaleString('vi-VN');
}

function formatDateVN(value) {
    if (!value) return '-';

    const d = new Date(value);

    if (isNaN(d.getTime())) {
        return value;
    }

    return d.toLocaleDateString('vi-VN');
}

// ── 3. Modal hàng hỏng ───────────────────────────────────────────────────
function moModalHangHong(id, ten, batDau, ketThuc) {
    document.getElementById('modalTenSP').textContent = ten;
    document.getElementById('modalTongSoLuong').textContent = '...';
    document.getElementById('modalHangHongInfo').style.display = 'none';
    document.getElementById('modalHangHongBody').innerHTML =
        '<div class="text-center py-4">' +
        '<div class="spinner-border text-danger" role="status"></div>' +
        '<div class="mt-2 text-muted">Đang tải dữ liệu...</div></div>';

    const modal = new bootstrap.Modal(document.getElementById('modalHangHong'));
    modal.show();

    const url = `/bao-cao/san-pham/${id}/hang-hong?ngay_bat_dau=${batDau}&ngay_ket_thuc=${ketThuc}`;

    fetch(url, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Không tải được dữ liệu hàng hỏng');
            }

            return response.json();
        })
        .then(function (res) {
            document.getElementById('modalTenSP').textContent = res.ten_san_pham;
            document.getElementById('modalTongSoLuong').textContent = res.tong_so_luong_hong;
            document.getElementById('modalHangHongInfo').style.display = 'block';

            if (!res.data || res.data.length === 0) {
                document.getElementById('modalHangHongBody').innerHTML =
                    '<p class="text-muted text-center py-3"><i class="bi bi-inbox me-1"></i>Không có dữ liệu hàng hỏng trong kỳ này.</p>';
                return;
            }

            let rows = '';

            res.data.forEach(function (item) {
                rows += `
                    <tr>
                        <td>${item.ngay_ghi_nhan}</td>
                        <td class="text-end text-danger fw-semibold">${formatNumber(item.so_luong_hong)}</td>
                        <td>${item.ly_do_hong}</td>
                        <td>${item.nhan_vien_ghi_nhan}</td>
                    </tr>
                `;
            });

            document.getElementById('modalHangHongBody').innerHTML = `
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày ghi nhận</th>
                                <th class="text-end">Số lượng hỏng</th>
                                <th>Lý do hỏng</th>
                                <th>Nhân viên ghi nhận</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            `;
        })
        .catch(function () {
            document.getElementById('modalHangHongBody').innerHTML =
                '<p class="text-danger text-center py-3"><i class="bi bi-exclamation-circle me-1"></i>Lỗi khi tải dữ liệu. Vui lòng thử lại.</p>';
        });
}

// ── 4. Modal chi tiết sản phẩm tổng hợp ──────────────────────────────────
// function moModalChiTietSanPham(id, batDau, ketThuc) {
//     const modalEl = document.getElementById('modalChiTietSanPham');
//     const bodyEl = document.getElementById('chiTietSanPhamBody');

//     bodyEl.innerHTML = `
//         <div class="text-center py-4">
//             <div class="spinner-border text-danger" role="status"></div>
//             <div class="mt-2 text-muted">Đang tải dữ liệu...</div>
//         </div>
//     `;

//     //const modal = new bootstrap.Modal(modalEl);
//     //modal.show();
//     const drawer = document.getElementById('drawerChiTietSanPham');

// drawer.classList.add('active');

//     const url = `/bao-cao/san-pham/${id}/chi-tiet?ngay_bat_dau=${batDau}&ngay_ket_thuc=${ketThuc}`;

//     fetch(url, {
//         headers: {
//             'Accept': 'application/json'
//         }
//     })
//         .then(function (response) {
//             if (!response.ok) {
//                 throw new Error('Không tải được chi tiết sản phẩm');
//             }

//             return response.json();
//         })
//         .then(function (res) {
//             renderChiTietSanPham(res);
//         })
//         .catch(function () {
//             bodyEl.innerHTML = `
//                 <div class="text-center py-4 text-danger">
//                     <i class="bi bi-exclamation-circle fs-3"></i>
//                     <div class="mt-2">Không thể tải chi tiết sản phẩm.</div>
//                 </div>
//             `;
//         });
// }
function moModalChiTietSanPham(id, batDau, ketThuc) {
    const drawer = document.getElementById('drawerChiTietSanPham');
    const bodyEl = document.getElementById('chiTietSanPhamBody');

    drawer.classList.add('active');

    bodyEl.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-danger"></div>
            <div class="mt-2 text-muted">Đang tải dữ liệu...</div>
        </div>
    `;

    const url = `/bao-cao/san-pham/${id}/chi-tiet?ngay_bat_dau=${batDau}&ngay_ket_thuc=${ketThuc}`;

    console.log('Đang gọi API:', url);

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(async function (response) {
        console.log('Status API:', response.status);

        if (!response.ok) {
            const text = await response.text();
            console.error('API lỗi:', text);
            throw new Error('API lỗi ' + response.status);
        }

        return response.json();
    })
    .then(function (res) {
        console.log('Dữ liệu nhận được:', res);

        const sp = res.san_pham;
        const tq = res.tong_quan;

        bodyEl.innerHTML = `
            <div class="mb-4">
                <h4 class="fw-bold text-danger">${sp.ten_san_pham}</h4>
                <p class="text-muted mb-1">Mã sản phẩm: ${sp.ma_san_pham}</p>
                <p class="text-muted">Loại: ${sp.loai_san_pham}</p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="border rounded-4 p-3 bg-light">
                        <div class="text-muted small">Tồn kho</div>
                        <div class="fs-4 fw-bold">${sp.so_luong}</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded-4 p-3 bg-light">
                        <div class="text-muted small">Đã bán</div>
                        <div class="fs-4 fw-bold">${tq.tong_da_ban}</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded-4 p-3 bg-light">
                        <div class="text-muted small">Hàng hỏng</div>
                        <div class="fs-4 fw-bold text-danger">${tq.tong_hang_hong}</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="border rounded-4 p-3 bg-light">
                        <div class="text-muted small">Doanh thu</div>
                        <div class="fs-4 fw-bold">
                            ${Number(tq.tong_doanh_thu).toLocaleString('vi-VN')}đ
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold mt-4">Lịch sử giá bán</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Ngày bán</th>
                            <th>Số lượng</th>
                            <th>Giá bán</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${
                            res.lich_su_gia_ban.length
                            ? res.lich_su_gia_ban.map(item => `
                                <tr>
                                    <td>${item.ngay_dat}</td>
                                    <td>${item.so_luong}</td>
                                    <td>${Number(item.gia_ban_snapshot).toLocaleString('vi-VN')}đ</td>
                                </tr>
                            `).join('')
                            : `<tr><td colspan="3" class="text-center text-muted">Chưa có dữ liệu bán hàng</td></tr>`
                        }
                    </tbody>
                </table>
            </div>

            <h5 class="fw-bold mt-4">Lịch sử nhập</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Ngày nhập</th>
                            <th>Số lượng</th>
                            <th>Giá nhập</th>
                            <th>Giá bán</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${
                            res.lich_su_nhap.length
                            ? res.lich_su_nhap.map(item => `
                                <tr>
                                    <td>${item.ngay_nhap}</td>
                                    <td>${item.so_luong}</td>
                                    <td>${Number(item.gia_nhap).toLocaleString('vi-VN')}đ</td>
                                    <td>${Number(item.gia_ban).toLocaleString('vi-VN')}đ</td>
                                </tr>
                            `).join('')
                            : `<tr><td colspan="4" class="text-center text-muted">Chưa có dữ liệu nhập hàng</td></tr>`
                        }
                    </tbody>
                </table>
            </div>
        `;
    })
    .catch(function (error) {
        console.error('Lỗi chi tiết sản phẩm:', error);

        bodyEl.innerHTML = `
            <div class="alert alert-danger">
                Không thể tải dữ liệu chi tiết sản phẩm.
                <br>
                Mở Console để xem lỗi chi tiết.
            </div>
        `;
    });
}
function renderChiTietSanPham(res) {
    const bodyEl = document.getElementById('chiTietSanPhamBody');

    const sp = res.san_pham || {};
    const tq = res.tong_quan || {};

    let rowsGiaBan = '';

    if (res.lich_su_gia_ban && res.lich_su_gia_ban.length > 0) {
        res.lich_su_gia_ban.forEach(function (item) {
            rowsGiaBan += `
                <tr>
                    <td>${formatDateVN(item.ngay_dat)}</td>
                    <td class="text-end">${formatNumber(item.so_luong)}</td>
                    <td class="text-end">${formatMoney(item.gia_ban_snapshot)}</td>
                </tr>
            `;
        });
    } else {
        rowsGiaBan = `
            <tr>
                <td colspan="3" class="text-center text-muted py-3">
                    Chưa có dữ liệu bán hàng trong khoảng này.
                </td>
            </tr>
        `;
    }

    let rowsNhap = '';

    if (res.lich_su_nhap && res.lich_su_nhap.length > 0) {
        res.lich_su_nhap.forEach(function (item) {
            rowsNhap += `
                <tr>
                    <td>${formatDateVN(item.ngay_nhap)}</td>
                    <td class="text-end">${formatNumber(item.so_luong)}</td>
                    <td class="text-end">${formatMoney(item.gia_nhap)}</td>
                    <td class="text-end">${formatMoney(item.gia_ban)}</td>
                </tr>
            `;
        });
    } else {
        rowsNhap = `
            <tr>
                <td colspan="4" class="text-center text-muted py-3">
                    Chưa có dữ liệu nhập hàng trong khoảng này.
                </td>
            </tr>
        `;
    }

    //const imageUrl = sp.hinh_anh ? '/' + sp.hinh_anh : '/img/default.jpg';

    bodyEl.innerHTML = `
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="border rounded-4 p-3 h-100 text-center bg-light">

    <div class="fw-bold fs-5">
        ${sp.ten_san_pham || '-'}
    </div>

    <div class="text-muted small mt-2">
        ${sp.loai_san_pham || '-'}
    </div>

    <div class="mt-3">
        <span class="badge bg-secondary">
            Chưa cập nhật hình ảnh
        </span>
    </div>

</div>
            </div>

            <div class="col-md-9">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Tồn kho hiện tại</div>
                            <div class="fs-5 fw-bold">${formatNumber(sp.so_luong)}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Đã bán</div>
                            <div class="fs-5 fw-bold">${formatNumber(tq.tong_da_ban)}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Hàng hỏng</div>
                            <div class="fs-5 fw-bold text-danger">${formatNumber(tq.tong_hang_hong)}</div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="text-muted small">Doanh thu</div>
                            <div class="fs-5 fw-bold">${formatMoney(tq.tong_doanh_thu)}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active"
                        data-bs-toggle="tab"
                        data-bs-target="#tabGiaBan"
                        type="button">
                    Lịch sử giá bán
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#tabNhapHang"
                        type="button">
                    Lịch sử nhập
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tabGiaBan">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày bán</th>
                                <th class="text-end">Số lượng</th>
                                <th class="text-end">Giá bán</th>
                            </tr>
                        </thead>
                        <tbody>${rowsGiaBan}</tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="tabNhapHang">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày nhập</th>
                                <th class="text-end">Số lượng</th>
                                <th class="text-end">Giá nhập</th>
                            </tr>
                        </thead>
                        <tbody>${rowsNhap}</tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

// ── 5. Gắn sự kiện ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.link-chitiet').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            moModalHangHong(
                this.dataset.id,
                this.dataset.ten,
                this.dataset.batdau,
                this.dataset.ketthuc
            );
        });
    });

    document.querySelectorAll('.btn-chi-tiet-san-pham').forEach(function (btn) {
        btn.addEventListener('click', function () {
            moModalChiTietSanPham(
                this.dataset.id,
                this.dataset.batdau,
                this.dataset.ketthuc
            );
        });
    });
});
document.querySelector('.drawer-close')
    .addEventListener('click', closeDrawer);

document.querySelector('.drawer-overlay')
    .addEventListener('click', closeDrawer);

function closeDrawer() {
    document
        .getElementById('drawerChiTietSanPham')
        .classList.remove('active');
}
</script>
@endpush
