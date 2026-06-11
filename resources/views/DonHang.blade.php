@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/DonHang.css') }}">
<link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=IM+Fell+English:ital@0;1&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
@endpush

@section('content')

<div class="don-hang-container">
{{-- resources/views/DonHang.blade.php --}}




{{-- ════════ HEADER ════════ --}}


<div class="page-body">

    {{-- ════════ Ô 1: BỘ LỌC & TÌM KIẾM ════════ --}}
    <div>
        <!-- <p class="section-label">[ 01 ] Bộ lọc &amp; Tìm kiếm</p> -->
        <div class="filter-box">
            <form method="GET" action="{{ route('don-hang.index') }}">
                <div class="filter-grid">

                    {{-- Trạng thái thanh toán --}}
                    <div class="filter-group">
                        <label>Thanh Toán</label>
                        <select name="trang_thai_thanh_toan">
                            <option value="">— Tất cả —</option>
                            <option value="CHUA_THANH_TOAN" {{ request('trang_thai_thanh_toan') === 'CHUA_THANH_TOAN' ? 'selected' : '' }}>
                                Chưa thanh toán
                            </option>
                            <option value="DA_THANH_TOAN" {{ request('trang_thai_thanh_toan') === 'DA_THANH_TOAN' ? 'selected' : '' }}>
                                Đã thanh toán
                            </option>
                        </select>
                    </div>

                    {{-- Tổng tiền từ --}}
                    <div class="filter-group">
                        <label>Tổng tiền từ (₫)</label>
                        <input type="number" name="tu_tien"
                               placeholder="VD: 100000"
                               value="{{ request('tu_tien') }}"
                               min="0" step="1000">
                    </div>

                    {{-- Tổng tiền đến --}}
                    <div class="filter-group">
                        <label>Tổng tiền đến (₫)</label>
                        <input type="number" name="den_tien"
                               placeholder="VD: 500000"
                               value="{{ request('den_tien') }}"
                               min="0" step="1000">
                    </div>

                    {{-- Từ ngày --}}
                    <div class="filter-group">
                        <label>Từ ngày</label>
                        <input type="date" name="tu_ngay" value="{{ request('tu_ngay') }}">
                    </div>

                    {{-- Đến ngày --}}
                    <div class="filter-group">
                        <label>Đến ngày</label>
                        <input type="date" name="den_ngay" value="{{ request('den_ngay') }}">
                    </div>

                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">⊞ Lọc kết quả</button>
                    <a href="{{ route('don-hang.index') }}" class="btn btn-ghost">↺ Đặt lại</a>
                    @if(request()->hasAny(['trang_thai_thanh_toan','tu_tien','den_tien','tu_ngay','den_ngay']))
                        <span class="filter-result-hint">
                            Đang lọc — <strong>{{ $donHangs->total() }}</strong> kết quả
                        </span>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ════════ Ô 2: BẢNG ĐƠN HÀNG ĐANG CHỜ ════════ --}}
    <div>
        <!-- <p class="section-label">[ 02 ] Đơn hàng chờ xử lý — chỉ hiển thị trạng thái PENDING</p> -->
        <div class="table-box">

            <div class="table-meta">
                <span>Tổng: <strong>{{ $donHangs->total() }}</strong> đơn đang chờ duyệt</span>
                <span>Trang {{ $donHangs->currentPage() }} / {{ $donHangs->lastPage() }}</span>
            </div>

            <div class="tbl-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mã ĐH</th>
                            <th>Khách Hàng</th>
                            <th>Ngày Đặt</th>
                            <th>Tổng Tiền</th>
                            <th>Thanh Toán</th>
                            <th>Địa Chỉ Giao</th>
                            <th>SĐT</th>
                            <th>Chọn Shipper</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($donHangs as $index => $dh)
                        <tr id="row-{{ $dh->ma_hoa_don }}">
                            {{-- STT --}}
                            <td class="td-mono" style="color:var(--ink-muted)">
                                {{ $donHangs->firstItem() + $index }}
                            </td>

                            {{-- Mã đơn hàng --}}
                            <td class="td-mono">
                                HD-{{ str_pad($dh->ma_hoa_don, 4, '0', STR_PAD_LEFT) }}
                            </td>

                            {{-- Tên khách hàng --}}
                            <td>{{ $dh->khachHang?->ten_khach_hang ?? '—' }}</td>

                            {{-- Ngày đặt --}}
                            <td class="td-mono">
                                {{ $dh->ngay_dat?->format('d/m/Y H:i') ?? '—' }}
                            </td>

                            {{-- Tổng tiền --}}
                            <td class="td-mono" style="font-weight:700">
                                {{ number_format($dh->tong_tien, 0, ',', '.') }}₫
                            </td>

                            {{-- Trạng thái thanh toán --}}
                            <td>
                                <span class="badge {{ $dh->trang_thai_thanh_toan === 'DA_THANH_TOAN' ? 'badge-paid' : 'badge-unpaid' }}">
                                    {{ $dh->trang_thai_thanh_toan === 'DA_THANH_TOAN' ? 'Đã TT' : 'Chưa TT' }}
                                </span>
                            </td>

                            {{-- Địa chỉ giao (rút gọn, hover xem đầy đủ) --}}
                            <td title="{{ $dh->dia_chi_giao }}" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                {{ $dh->dia_chi_giao ?? '—' }}
                            </td>

                            {{-- Số điện thoại --}}
                            <td class="td-mono">{{ $dh->so_dien_thoai ?? '—' }}</td>

                            {{-- Dropdown chọn shipper ──────────────────────────────
                                 Bắt buộc phải chọn trước khi ấn "Xác nhận".
                                 ngay_giao sẽ null cho đến khi shipper hoàn tất giao.
                            --}}
                            <td>
                                <select class="shipper-select"
                                        id="shipper-{{ $dh->ma_hoa_don }}"
                                        data-id="{{ $dh->ma_hoa_don }}">
                                    <option value="">— Chọn shipper —</option>
                                    @foreach($shippers as $sp)
                                        <option value="{{ $sp->ma_nhan_vien }}">
                                            {{ $sp->ten_nhan_vien }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Hành động --}}
                            <td>
                                <div class="td-actions">
                                    <button class="btn btn-success"
                                            onclick="confirmOrder({{ $dh->ma_hoa_don }})"
                                            title="Phải chọn shipper trước. Xác nhận → trạng thái chuyển CONFIRMED.">
                                        ✔ Xác nhận
                                    </button>
                                    <button class="btn btn-danger"
                                            onclick="openCancelDialog({{ $dh->ma_hoa_don }}, 'HD-{{ str_pad($dh->ma_hoa_don, 4, '0', STR_PAD_LEFT) }}')"
                                            title="Từ chối → trạng thái chuyển CANCELLED.">
                                        ✕ Từ chối
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <div class="empty-icon">◻</div>
                                    <p>Không có đơn hàng nào đang chờ xử lý.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Phân trang --}}
            @if($donHangs->hasPages())
            <div class="pagination-wrap">
                @if($donHangs->onFirstPage())
                    <span class="disabled">‹ Trước</span>
                @else
                    <a href="{{ $donHangs->previousPageUrl() }}">‹ Trước</a>
                @endif

                @foreach($donHangs->getUrlRange(1, $donHangs->lastPage()) as $page => $url)
                    @if($page == $donHangs->currentPage())
                        <span class="current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($donHangs->hasMorePages())
                    <a href="{{ $donHangs->nextPageUrl() }}">Sau ›</a>
                @else
                    <span class="disabled">Sau ›</span>
                @endif
            </div>
            @endif

        </div>
    </div>

</div>{{-- /page-body --}}


{{-- ════════ DIALOG XÁC NHẬN TỪ CHỐI ════════ --}}
<div class="overlay" id="cancelOverlay">
    <div class="dialog">
        <h3>Xác nhận từ chối đơn hàng</h3>
        <p id="cancelMsg">Bạn có chắc muốn từ chối đơn hàng này?<br>
            Trạng thái sẽ chuyển sang <strong>CANCELLED</strong>.<br>
            Hành động này không thể hoàn tác.
        </p>
        <div class="dialog-actions">
            <button class="btn btn-ghost" onclick="closeCancelDialog()">Hủy bỏ</button>
            <button class="btn btn-danger" id="cancelOkBtn">✕ Xác nhận từ chối</button>
        </div>
    </div>
</div>

{{-- ════════ TOAST NOTIFICATION ════════ --}}
<div class="toast" id="toast"></div>
</div>

@endsection

@push('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let currentCancelId = null;

   
    function showToast(msg, type = 'success') {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.className   = 'toast ' + type + ' show';
        clearTimeout(t._t);
        t._t = setTimeout(() => t.classList.remove('show'), 3500);
    }

    
    function removeRow(id) {
        const row = document.getElementById('row-' + id);
        if (!row) return;
        row.style.transition = 'opacity .35s, transform .35s';
        row.style.opacity    = '0';
        row.style.transform  = 'translateX(16px)';
        setTimeout(() => row.remove(), 370);
    }

    
    function confirmOrder(id) {
        const sel = document.getElementById('shipper-' + id);

        if (!sel || !sel.value) {
            sel && sel.classList.add('error');
            showToast('Vui lòng chọn shipper trước khi xác nhận!', 'error');
            sel && sel.focus();
            return;
        }
        sel.classList.remove('error');

        const btn = document.querySelector('#row-' + id + ' .btn-success');
        if (btn) { btn.disabled = true; btn.textContent = '…'; }

        fetch(`/don-hang/${id}/confirm`, {
            method:  'POST',
            headers: {
                'Content-Type':    'application/json',
                'X-CSRF-TOKEN':    CSRF,
                'Accept':          'application/json',
                'X-Requested-With':'XMLHttpRequest',
            },
            body: JSON.stringify({ ma_nhan_vien_giao: sel.value }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                removeRow(id);
                showToast(res.message, 'success');
            } else {
                showToast(res.message || 'Có lỗi xảy ra.', 'error');
                if (btn) { btn.disabled = false; btn.textContent = '✔ Xác nhận'; }
            }
        })
        .catch(() => {
            showToast('Lỗi kết nối, vui lòng thử lại.', 'error');
            if (btn) { btn.disabled = false; btn.textContent = '✔ Xác nhận'; }
        });
    }

   
    function openCancelDialog(id, label) {
        currentCancelId = id;
        document.getElementById('cancelMsg').innerHTML =
            `Bạn có chắc muốn từ chối <strong>${label}</strong>?<br>
             Trạng thái sẽ chuyển sang <strong>CANCELLED</strong>.<br>
             Hành động này không thể hoàn tác.`;
        document.getElementById('cancelOverlay').classList.add('show');
    }

    function closeCancelDialog() {
        document.getElementById('cancelOverlay').classList.remove('show');
        currentCancelId = null;
    }

    
    document.getElementById('cancelOkBtn').addEventListener('click', function () {
        if (!currentCancelId) return;
        const id  = currentCancelId;
        this.disabled    = true;
        this.textContent = '…';

        fetch(`/don-hang/${id}/cancel`, {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN':    CSRF,
                'Accept':          'application/json',
                'X-Requested-With':'XMLHttpRequest',
            },
        })
        .then(r => r.json())
        .then(res => {
            closeCancelDialog();
            this.disabled    = false;
            this.textContent = '✕ Xác nhận từ chối';

            if (res.success) {
                removeRow(id);
                showToast(res.message, 'success');
            } else {
                showToast(res.message || 'Có lỗi xảy ra.', 'error');
            }
        })
        .catch(() => {
            closeCancelDialog();
            this.disabled    = false;
            this.textContent = '✕ Xác nhận từ chối';
            showToast('Lỗi kết nối, vui lòng thử lại.', 'error');
        });
    });

    document.getElementById('cancelOverlay').addEventListener('click', function (e) {
        if (e.target === this) closeCancelDialog();
    });

    document.querySelectorAll('.shipper-select').forEach(sel => {
        sel.addEventListener('change', () => sel.classList.remove('error'));
    });
</script>
@endpush
