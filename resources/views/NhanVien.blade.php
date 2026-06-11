@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/NhanVien.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endpush

@section('content')

<div class="nhan-vien-container">
<div class="container mt-4">
    <h2>Quản lý nhân viên</h2>

    {{-- Thông báo --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ================================================= --}}
    {{-- 1. FORM SỬA (ẨN, HIỆN KHI NHẤN NÚT SỬA TRÊN DÒNG) --}}
    {{-- ================================================= --}}
    <div id="editFormContainer" class="card mb-4 d-none">
        <div class="card-header bg-warning text-dark">
            <strong><i class="fas fa-edit"></i> Chỉnh sửa thông tin nhân viên</strong>
            <button type="button" class="btn-close float-end" id="closeEditForm"></button>
        </div>
        <div class="card-body">
            <form id="editForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mã nhân viên</label>
                        <input type="text" class="form-control" id="edit_ma_nhan_vien" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tên nhân viên</label>
                        <input type="text" name="ten_nhan_vien" id="edit_ten_nhan_vien" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="so_dien_thoai" id="edit_so_dien_thoai" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Chức vụ</label>
                        <select name="chuc_vu" id="edit_chuc_vu" class="form-select" required>
                            <option value="CSKH">CSKH</option>
                            <option value="VAN_HANH">Vận hành</option>
                            <option value="THIET_KE">Thiết kế</option>
                            <option value="ONLINE">Online</option>
                            <option value="SHIPPER">Shipper</option>
                            <option value="KHAC">Khác</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Lương (VNĐ)</label>
                        <input type="number" name="luong" id="edit_luong" class="form-control" step="1000"
                               min="0" max="999999999999999"
                               oninput="checkLuong(this)">
                        <small class="text-muted">Tối đa: 999.999.999.999.999 đ</small>
                        <small id="luong-feedback" style="display:none; color:#e53e3e;"></small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Công việc</label>
                        <input type="text" name="cong_viec" id="edit_cong_viec" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu thay đổi</button>
                <button type="button" class="btn btn-secondary" id="cancelEdit">Hủy</button>
            </form>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- 2. TOOLBAR: LỌC + SẮP XẾP --}}
    {{-- ================================================= --}}
    <div class="row mb-3 align-items-end">
        <div class="col-md-3">
            <form method="GET" action="{{ route('nhan-vien.index') }}" id="filterForm">
                <label class="form-label">Lọc theo chức vụ</label>
                <select name="chuc_vu" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Tất cả --</option>
                    @foreach($chucVus as $value => $label)
                        <option value="{{ $value }}" {{ request('chuc_vu') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="col-md-4">
            <label class="form-label">Sắp xếp theo lương</label>
            <div>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'luong_desc']) }}" class="btn btn-sm btn-outline-primary {{ request('sort') == 'luong_desc' ? 'active' : '' }}">
                    <i class="fas fa-arrow-down"></i> Cao nhất
                </a>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'luong_asc']) }}" class="btn btn-sm btn-outline-primary {{ request('sort') == 'luong_asc' ? 'active' : '' }}">
                    <i class="fas fa-arrow-up"></i> Thấp nhất
                </a>
                <a href="{{ route('nhan-vien.index') }}" class="btn btn-sm btn-secondary">Reset</a>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- 3. DANH SÁCH NHÂN VIÊN (BẢNG) --}}
    {{-- ================================================= --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
            <tr>
                <th>Mã</th>
                <th>Tên nhân viên</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Chức vụ</th>
                <th>Lương (VNĐ)</th>
                <th>Công việc</th>
                <th>Tiền cần nộp</th>
                <th width="160">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse($nhanViens as $nv)
                <tr id="nv-row-{{ $nv->ma_nhan_vien }}">
                    <td>{{ $nv->ma_nhan_vien }}</td>
                    <td>{{ $nv->ten_nhan_vien }}</td>
                    <td>{{ $nv->email }}</td>
                    <td>{{ $nv->so_dien_thoai }}</td>
                    <td>{{ $chucVus[$nv->chuc_vu] ?? $nv->chuc_vu }}</td>
                    <td class="text-end">{{ number_format($nv->luong, 0, ',', '.') }}</td>
                    <td>{{ $nv->cong_viec }}</td>

                    {{-- ── CỘT TIỀN CẦN NỘP (chỉ hiện với SHIPPER) ── --}}
                    <td class="text-center">
                        @if($nv->chuc_vu === 'SHIPPER')
                            @php $no = $shipperDebts[$nv->ma_nhan_vien] ?? 0; @endphp
                            <span class="debt-badge {{ $no == 0 ? 'zero' : '' }}" id="debt-{{ $nv->ma_nhan_vien }}">
                                {{ $no == 0 ? '✓ 0 đ' : number_format($no, 0, ',', '.') . ' đ' }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        {{-- Nút Sửa --}}
                        <button type="button" class="btn btn-sm btn-warning btn-edit"
                                data-id="{{ $nv->ma_nhan_vien }}"
                                data-ten="{{ $nv->ten_nhan_vien }}"
                                data-email="{{ $nv->email }}"
                                data-sdt="{{ $nv->so_dien_thoai }}"
                                data-chucvu="{{ $nv->chuc_vu }}"
                                data-congviec="{{ $nv->cong_viec }}"
                                data-luong="{{ $nv->luong }}">
                            <i class="fas fa-edit"></i>
                        </button>

                        {{-- Nút Xóa --}}
                        <form action="{{ route('nhan-vien.destroy', $nv->ma_nhan_vien) }}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger btn-delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>

                        {{-- Nút Trả tiền (chỉ shipper) --}}
                        @if($nv->chuc_vu === 'SHIPPER')
                            @php $no = $shipperDebts[$nv->ma_nhan_vien] ?? 0; @endphp
                            <button type="button"
                                    class="btn btn-sm btn-success btn-payback"
                                    id="payback-btn-{{ $nv->ma_nhan_vien }}"
                                    data-id="{{ $nv->ma_nhan_vien }}"
                                    data-ten="{{ $nv->ten_nhan_vien }}"
                                    title="Xác nhận shipper đã nộp tiền COD"
                                    {{ $no == 0 ? 'disabled' : '' }}>
                                <i class="fas fa-hand-holding-usd"></i> Trả tiền
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Không có nhân viên nào.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phân trang --}}
    <div class="d-flex justify-content-center">
        {{ $nhanViens->links() }}
    </div>
</div>

{{-- Modal xác nhận trả tiền --}}
<div class="modal fade" id="paybackModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-hand-holding-usd"></i> Xác nhận nhận tiền từ Shipper</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paybackModalBody">
                Shipper <strong id="payback-name"></strong> đã nộp tiền COD về công ty?
                <br><br>
                Thao tác này sẽ đánh dấu tất cả đơn COD đã giao của shipper này là <strong>Đã thanh toán</strong>.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" id="confirmPayback">
                    <i class="fas fa-check"></i> Xác nhận đã nhận tiền
                </button>
            </div>
        </div>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
   
    const editFormContainer = document.getElementById('editFormContainer');
    const editForm          = document.getElementById('editForm');

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_ma_nhan_vien').value  = this.dataset.id;
            document.getElementById('edit_ten_nhan_vien').value = this.dataset.ten;
            document.getElementById('edit_email').value         = this.dataset.email  || '';
            document.getElementById('edit_so_dien_thoai').value = this.dataset.sdt    || '';
            document.getElementById('edit_chuc_vu').value       = this.dataset.chucvu;
            document.getElementById('edit_cong_viec').value     = this.dataset.congviec || '';
            document.getElementById('edit_luong').value         = this.dataset.luong  || '';

            editForm.action = `/nhan-vien/${this.dataset.id}`;
            editFormContainer.classList.remove('d-none');
            editFormContainer.scrollIntoView({ behavior: 'smooth' });
        });
    });

    function closeEditForm() {
        editFormContainer.classList.add('d-none');
        editForm.reset();
    }
    document.getElementById('closeEditForm').addEventListener('click', closeEditForm);
    document.getElementById('cancelEdit').addEventListener('click', closeEditForm);

    
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            if (confirm('Bạn có chắc chắn muốn xóa nhân viên này? Hành động không thể hoàn tác.')) {
                this.closest('form.delete-form').submit();
            }
        });
    });

  
    const paybackModal = new bootstrap.Modal(document.getElementById('paybackModal'));
    let   pendingPaybackId = null;

    document.querySelectorAll('.btn-payback').forEach(btn => {
        btn.addEventListener('click', function () {
            pendingPaybackId = this.dataset.id;
            document.getElementById('payback-name').textContent = this.dataset.ten;
            paybackModal.show();
        });
    });

    document.getElementById('confirmPayback').addEventListener('click', function () {
        if (!pendingPaybackId) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';

        fetch(`/nhan-vien/${pendingPaybackId}/tra-tien`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            paybackModal.hide();
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Xác nhận đã nhận tiền';

            if (data.success) {
               
                const debtBadge = document.getElementById('debt-' + pendingPaybackId);
                if (debtBadge) {
                    debtBadge.className = 'debt-badge zero';
                    debtBadge.textContent = '✓ 0 đ';
                }
                
                const payBtn = document.getElementById('payback-btn-' + pendingPaybackId);
                if (payBtn) payBtn.disabled = true;

                
                showAlert(data.message, 'success');
            } else {
                showAlert(data.message, 'danger');
            }
        })
        .catch(() => {
            paybackModal.hide();
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Xác nhận đã nhận tiền';
            showAlert('Có lỗi xảy ra, vui lòng thử lại.', 'danger');
        });
    });

    function showAlert(msg, type) {
        const div = document.createElement('div');
        div.className = `alert alert-${type} alert-dismissible fade show`;
        div.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        document.querySelector('.container').prepend(div);
        setTimeout(() => div.remove(), 4000);
    }

    function checkLuong(input) {
        const MAX = 999999999999999;
        const fb  = document.getElementById('luong-feedback');
        if (input.value !== '' && Number(input.value) > MAX) {
            fb.textContent = '✗ Vượt giới hạn tối đa';
            fb.style.display = 'block';
            input.classList.add('is-invalid');
        } else {
            fb.style.display = 'none';
            input.classList.remove('is-invalid');
        }
    }
</script>
@endpush
