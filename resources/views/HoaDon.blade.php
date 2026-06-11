@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/HoaDon.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=IM+Fell+English:ital@0;1&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
@endpush

@section('content')

<div class="hoa-don-container">
<div class="page-body" id="page-body">

    {{-- ── Ô 1: BỘ LỌC ── --}}
    <form method="GET" action="{{ route('hoa-don.index') }}" id="filter-form">
        <div class="filter-box">
            <div class="filter-grid">

                {{-- Khách hàng --}}
                <div class="filter-group">
                    <label>Khách Hàng</label>
                    <select name="ma_khach_hang">
                        <option value="">— Tất cả —</option>
                        @foreach ($khachHangs as $kh)
                            <option value="{{ $kh->ma_khach_hang }}"
                                {{ request('ma_khach_hang') == $kh->ma_khach_hang ? 'selected' : '' }}>
                                {{ $kh->ten_khach_hang }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Trạng thái đơn --}}
                <div class="filter-group">
                    <label>Trạng Thái Đơn</label>
                    <select name="trang_thai">
                        <option value="">— Tất cả —</option>
                        @foreach (['PENDING'=>'Chờ xử lý','CONFIRMED'=>'Đã xác nhận','SHIPPING'=>'Đang giao','DELIVERED'=>'Đã giao','CANCELLED'=>'Đã hủy'] as $val => $label)
                            <option value="{{ $val }}" {{ request('trang_thai') == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Thanh toán --}}
                <div class="filter-group">
                    <label>Thanh Toán</label>
                    <select name="trang_thai_thanh_toan">
                        <option value="">— Tất cả —</option>
                        <option value="CHUA_THANH_TOAN" {{ request('trang_thai_thanh_toan') == 'CHUA_THANH_TOAN' ? 'selected' : '' }}>Chưa thanh toán</option>
                        <option value="DA_THANH_TOAN"   {{ request('trang_thai_thanh_toan') == 'DA_THANH_TOAN'   ? 'selected' : '' }}>Đã thanh toán</option>
                    </select>
                </div>

                {{-- Từ ngày --}}
                <div class="filter-group">
                    <label>Từ Ngày</label>
                    <input type="date" name="tu_ngay" value="{{ request('tu_ngay') }}">
                </div>

                {{-- Đến ngày --}}
                <div class="filter-group">
                    <label>Đến Ngày</label>
                    <input type="date" name="den_ngay" value="{{ request('den_ngay') }}">
                </div>

                {{-- Nút --}}
                <div class="filter-group filter-actions">
                    <button type="submit" class="btn btn-primary">Tìm Kiếm</button>
                    <a href="{{ route('hoa-don.index') }}" class="btn btn-reset">Reset</a>
                </div>

            </div>
        </div>
    </form>

    {{-- ── Ô 2: BẢNG HÓA ĐƠN ── --}}
    <div class="table-box">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mã HD</th>
                        <th>Khách Hàng</th>
                        <th>Ngày Đặt</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Thanh Toán</th>
                        <th>PT Thanh Toán</th>
                        <th>Địa Chỉ Giao</th>
                        <th>SĐT</th>
                        <th>Ngày Giao</th>
                        <th>Nhân Viên Giao</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody id="invoice-table-body">
                @forelse ($hoaDons as $index => $hd)
                    <tr id="row-{{ $hd->ma_hoa_don }}">
                        <td class="id-col">{{ $hoaDons->firstItem() + $index }}</td>
                        <td class="id-col">#HD-{{ str_pad($hd->ma_hoa_don, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $hd->khachHang->ten_khach_hang ?? '—' }}</td>
                        <td>{{ optional($hd->ngay_dat)->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="money">{{ number_format($hd->tong_tien, 0, ',', '.') }}₫</td>
                        <td>
                            @php
                                $statusMap = ['PENDING'=>'badge-pending','CONFIRMED'=>'badge-confirmed','SHIPPING'=>'badge-shipping','DELIVERED'=>'badge-delivered','CANCELLED'=>'badge-cancelled'];
                                $statusLabel = ['PENDING'=>'Chờ xử lý','CONFIRMED'=>'Xác nhận','SHIPPING'=>'Đang giao','DELIVERED'=>'Đã giao','CANCELLED'=>'Đã hủy'];
                            @endphp
                            <span class="badge {{ $statusMap[$hd->trang_thai] ?? '' }}">
                                {{ $statusLabel[$hd->trang_thai] ?? $hd->trang_thai }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ in_array($hd->trang_thai_thanh_toan, ['DA_THANH_TOAN','DA_NOP']) ? 'badge-paid' : 'badge-unpaid' }}">
                                        {{ in_array($hd->trang_thai_thanh_toan, ['DA_THANH_TOAN','DA_NOP']) ? 'Đã TT' : 'Chưa TT' }}
                        </span>
                        </td>
                        <td>{{ $hd->phuong_thuc_thanh_toan ?? '—' }}</td>
                        <td class="wrap">{{ $hd->dia_chi_giao ?? '—' }}</td>
                        <td>{{ $hd->so_dien_thoai ?? '—' }}</td>
                        <td>{{ optional($hd->ngay_giao)->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ $hd->shipper->ten_nhan_vien ?? '—' }}</td>
                        <td>
                            <div style="display:flex;gap:6px;align-items:center;">
                                {{-- Nút Chi Tiết --}}
                                <button class="btn btn-detail"
                                        data-id="{{ $hd->ma_hoa_don }}"
                                        onclick="openDetail({{ $hd->ma_hoa_don }}, this)">
                                    Chi Tiết
                                </button>

                                {{-- Nút Xóa: chỉ enabled khi chưa thanh toán hoặc đã hủy --}}
                                @php
                                    $canDelete = ($hd->trang_thai_thanh_toan === 'CHUA_THANH_TOAN'
                                              || $hd->trang_thai === 'CANCELLED')
                                              && $hd->trang_thai !== 'DELIVERED';
                                @endphp
                                <button class="btn btn-delete"
                                        data-id="{{ $hd->ma_hoa_don }}"
                                        {{ !$canDelete ? 'disabled' : '' }}
                                        onclick="deleteInvoice({{ $hd->ma_hoa_don }}, this)">
                                    Xóa
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="13">Không tìm thấy hóa đơn nào.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($hoaDons->hasPages())
        <div class="pagination-wrap">
            <span>Hiển thị {{ $hoaDons->firstItem() }}–{{ $hoaDons->lastItem() }} / {{ $hoaDons->total() }} hóa đơn</span>
            <div class="pages">
                {{-- Prev --}}
                @if ($hoaDons->onFirstPage())
                    <span class="disabled">‹ Trước</span>
                @else
                    <a href="{{ $hoaDons->previousPageUrl() }}">‹ Trước</a>
                @endif

                {{-- Page numbers --}}
                @foreach ($hoaDons->getUrlRange(max(1, $hoaDons->currentPage()-2), min($hoaDons->lastPage(), $hoaDons->currentPage()+2)) as $page => $url)
                    @if ($page == $hoaDons->currentPage())
                        <span class="active-page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($hoaDons->hasMorePages())
                    <a href="{{ $hoaDons->nextPageUrl() }}">Sau ›</a>
                @else
                    <span class="disabled">Sau ›</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>{{-- /page-body --}}

{{-- ── DETAIL PANEL ── --}}
<div id="detail-panel">
    <div class="panel-header">
        <h2 id="panel-title">Chi Tiết Hóa Đơn</h2>
        <button class="panel-close" onclick="closeDetail()">✕ Đóng</button>
    </div>
    <div class="panel-body" id="panel-body">
    </div>
</div>

{{-- ── TOAST ── --}}
<div id="toast"></div>
</div>

@endsection

@push('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let activeDetailId = null;
    let activeBtn      = null;

    function showToast(msg, isError = false) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.className   = 'show' + (isError ? ' error' : '');
        clearTimeout(t._timer);
        t._timer = setTimeout(() => { t.className = ''; }, 3000);
    }

    function openDetail(id, btn) {
        const panel = document.getElementById('detail-panel');
        const body  = document.getElementById('panel-body');

        if (activeDetailId === id && panel.classList.contains('open')) {
            closeDetail();
            return;
        }

        document.querySelectorAll('tr.highlighted').forEach(r => r.classList.remove('highlighted'));
        document.querySelectorAll('.btn-detail.active').forEach(b => b.classList.remove('active'));
        const row = document.getElementById('row-' + id);
        if (row) row.classList.add('highlighted');
        btn.classList.add('active');

        activeDetailId = id;
        activeBtn      = btn;

        document.getElementById('panel-title').textContent = '#HD-' + String(id).padStart(4, '0');
        body.innerHTML = '<div class="panel-loading">Đang tải</div>';
        panel.classList.add('open');
        document.getElementById('page-body').classList.add('panel-open');

        fetch(`/hoa-don/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success) { body.innerHTML = '<p style="padding:20px;color:#888">Không tải được dữ liệu.</p>'; return; }
            renderPanel(res.data);
        })
        .catch(() => { body.innerHTML = '<p style="padding:20px;color:#888">Lỗi kết nối.</p>'; });
    }

    function closeDetail() {
        document.getElementById('detail-panel').classList.remove('open');
        document.getElementById('page-body').classList.remove('panel-open');
        document.querySelectorAll('tr.highlighted').forEach(r => r.classList.remove('highlighted'));
        document.querySelectorAll('.btn-detail.active').forEach(b => b.classList.remove('active'));
        activeDetailId = null;
        activeBtn      = null;
    }

    function renderPanel(d) {
        const statusLabel = {PENDING:'Chờ xử lý',CONFIRMED:'Đã xác nhận',SHIPPING:'Đang giao',DELIVERED:'Đã giao',CANCELLED:'Đã hủy'};
        const fmt = v => v ? Number(v).toLocaleString('vi-VN') + '₫' : '—';

        let productsHtml = '';
        let grandTotal = 0;
        if (d.chi_tiet_hoa_don && d.chi_tiet_hoa_don.length) {
            d.chi_tiet_hoa_don.forEach(ct => {
                grandTotal += Number(ct.thanh_tien);
                productsHtml += `
                <tr>
                    <td>${ct.san_pham?.ten_san_pham ?? '—'}</td>
                    <td style="text-align:center">${ct.so_luong}</td>
                    <td style="text-align:right">${fmt(ct.gia_ban_snapshot)}</td>
                    <td style="text-align:right;font-weight:700">${fmt(ct.thanh_tien)}</td>
                </tr>`;
            });
            productsHtml += `
                <tr class="total-row">
                    <td colspan="3">Tổng cộng</td>
                    <td style="text-align:right">${fmt(grandTotal)}</td>
                </tr>`;
        } else {
            productsHtml = '<tr><td colspan="4" style="text-align:center;color:#888;padding:12px">Không có sản phẩm</td></tr>';
        }

        document.getElementById('panel-body').innerHTML = `
        <div class="panel-section">
            <div class="panel-section-title">Thông Tin Hóa Đơn</div>
            <div class="info-row"><span class="info-label">Mã hóa đơn</span><span class="info-value">#HD-${String(d.ma_hoa_don).padStart(4,'0')}</span></div>
            <div class="info-row"><span class="info-label">Ngày đặt</span><span class="info-value">${d.ngay_dat ?? '—'}</span></div>
            <div class="info-row"><span class="info-label">Ngày giao</span><span class="info-value">${d.ngay_giao ?? '—'}</span></div>
            <div class="info-row"><span class="info-label">Trạng thái</span><span class="info-value">${statusLabel[d.trang_thai] ?? d.trang_thai}</span></div>
            <div class="info-row"><span class="info-label">Thanh toán</span><span class="info-value">${['DA_THANH_TOAN','DA_NOP'].includes(d.trang_thai_thanh_toan) ? 'Đã thanh toán' : 'Chưa thanh toán'}</span></div>
            <div class="info-row"><span class="info-label">Phương thức</span><span class="info-value">${d.phuong_thuc_thanh_toan ?? '—'}</span></div>
            <div class="info-row"><span class="info-label">Tổng tiền</span><span class="info-value">${fmt(d.tong_tien)}</span></div>
        </div>

        <div class="panel-section">
            <div class="panel-section-title">Thông Tin Khách Hàng</div>
            <div class="info-row"><span class="info-label">Tên KH</span><span class="info-value">${d.khach_hang?.ten_khach_hang ?? '—'}</span></div>
            <div class="info-row"><span class="info-label">SĐT KH</span><span class="info-value">${d.khach_hang?.so_dien_thoai ?? '—'}</span></div>
            <div class="info-row"><span class="info-label">Email</span><span class="info-value">${d.khach_hang?.email ?? '—'}</span></div>
        </div>

        <div class="panel-section">
            <div class="panel-section-title">Thông Tin Giao Hàng</div>
            <div class="info-row"><span class="info-label">Địa chỉ giao</span><span class="info-value" style="max-width:240px;text-align:right;white-space:normal">${d.dia_chi_giao ?? '—'}</span></div>
            <div class="info-row"><span class="info-label">SĐT nhận</span><span class="info-value">${d.so_dien_thoai ?? '—'}</span></div>
            <div class="info-row"><span class="info-label">NV giao</span><span class="info-value">${d.nhan_vien_giao?.ten_nhan_vien ?? '—'}</span></div>
        </div>

        <div class="panel-section">
            <div class="panel-section-title">Chi Tiết Sản Phẩm</div>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Sản Phẩm</th>
                        <th style="text-align:center">SL</th>
                        <th style="text-align:right">Đơn Giá</th>
                        <th style="text-align:right">Thành Tiền</th>
                    </tr>
                </thead>
                <tbody>${productsHtml}</tbody>
            </table>
        </div>
        `;
    }

    function deleteInvoice(id, btn) {
        if (!confirm(`Bạn chắc chắn muốn xóa hóa đơn #HD-${String(id).padStart(4,'0')}?\nChi tiết hóa đơn cũng sẽ bị xóa theo.`)) return;

        btn.disabled = true;
        btn.textContent = '...';

        fetch(`/hoa-don/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN':    CSRF,
                'X-Requested-With':'XMLHttpRequest',
                'Accept':          'application/json',
            }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (activeDetailId === id) closeDetail();

                const row = document.getElementById('row-' + id);
                if (row) {
                    row.style.transition = 'opacity .3s';
                    row.style.opacity    = '0';
                    setTimeout(() => row.remove(), 300);
                }
                showToast(res.message);
            } else {
                btn.disabled    = false;
                btn.textContent = 'Xóa';
                showToast(res.message, true);
            }
        })
        .catch(() => {
            btn.disabled    = false;
            btn.textContent = 'Xóa';
            showToast('Lỗi kết nối máy chủ.', true);
        });
    }
</script>
@endpush
