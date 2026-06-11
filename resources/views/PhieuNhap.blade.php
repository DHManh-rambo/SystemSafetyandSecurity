@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="{{ asset('css/PhieuNhap.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@endpush

@section('content')

<div class="nhap-hang-container">
<div class="container py-4">

    {{-- ===== THÔNG BÁO ===== --}}
    @if(session('success'))
        <div class="alert alert-dark alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


    {{-- =====================================================================
         Ô TRÊN: THÊM / SỬA PHIẾU NHẬP
    ===================================================================== --}}
    <div class="card mb-4" id="cardForm">

        <div class="card-header header-them d-flex justify-content-between align-items-center" id="cardFormHeader">
            <span id="tieuDeForm">
                <i class="bi bi-plus-circle me-2"></i>Thêm Phiếu Nhập Mới
            </span>
            <button type="button" id="btnHuy" class="btn btn-light btn-sm"
                    onclick="chuyenVeCheDoDaThem()" style="display:none;">
                <i class="bi bi-x-lg me-1"></i>Hủy sửa
            </button>
        </div>

        <div class="card-body">
            <form id="formChung" action="{{ route('phieu-nhap.store') }}" method="POST">
                @csrf
                <div id="methodField"></div>

                {{-- ===== THÔNG TIN PHIẾU NHẬP ===== --}}
                <p class="tieu-de-chi-tiet"><i class="bi bi-file-earmark-text me-1"></i>Thông tin phiếu nhập</p>
                <div class="row g-3 mb-3">

                    {{-- Ngày nhập --}}
                    <div class="col-md-3">
                        <label class="form-label">Ngày nhập <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="ngay_nhap" id="inp_ngay_nhap"
                               class="form-control" value="{{ old('ngay_nhap') }}" required>
                    </div>

                    {{-- Nhân viên vận hành --}}
                    <div class="col-md-3">
                        <label class="form-label">Nhân viên vận hành <span class="text-danger">*</span></label>
                        <select name="ma_nhan_vien" id="inp_nhan_vien" class="form-select" required>
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach($danhSachNhanVien as $nv)
                                <option value="{{ $nv->ma_nhan_vien }}"
                                    {{ old('ma_nhan_vien') == $nv->ma_nhan_vien ? 'selected' : '' }}>
                                    {{ $nv->ten_nhan_vien }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tên nhà cung cấp --}}
                    <div class="col-md-3">
                        <label class="form-label">Tên nhà cung cấp</label>
                        <input type="text" name="ten_nha_cung_cap" id="inp_ncc"
                               class="form-control" value="{{ old('ten_nha_cung_cap') }}"
                               placeholder="VD: NCC Hoa Đà Lạt">
                    </div>

                    {{-- Số điện thoại NCC --}}
                    <div class="col-md-3">
                        <label class="form-label">Số điện thoại NCC</label>
                        <input type="text" name="so_dien_thoai_ncc" id="inp_sdt_ncc"
                               class="form-control" value="{{ old('so_dien_thoai_ncc') }}"
                               placeholder="10 chữ số" maxlength="10">
                    </div>

                    {{-- Email NCC --}}
                    <div class="col-md-4">
                        <label class="form-label">Email NCC</label>
                        <input type="email" name="email_ncc" id="inp_email_ncc"
                               class="form-control" value="{{ old('email_ncc') }}"
                               placeholder="ncc@example.com">
                    </div>

                    {{-- Địa chỉ NCC --}}
                    <div class="col-md-8">
                        <label class="form-label">Địa chỉ NCC</label>
                        <input type="text" name="dia_chi_ncc" id="inp_dia_chi_ncc"
                               class="form-control" value="{{ old('dia_chi_ncc') }}"
                               placeholder="VD: Đà Lạt, Lâm Đồng">
                    </div>

                </div>

                {{-- ===== DANH SÁCH SẢN PHẨM NHẬP ===== --}}
                <p class="tieu-de-chi-tiet mt-2">
                    <i class="bi bi-box-seam me-1"></i>Chi tiết nhập hàng
                    <small class="text-muted fw-normal ms-2" style="font-size:0.8rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Giá bán: để trống → hệ thống tự tính = Giá nhập + 50%
                    </small>
                </p>

                <div id="danhSachDongChiTiet">
                    {{-- Dòng sản phẩm đầu tiên (không xóa được) --}}
                    <div class="dong-chi-tiet" id="dong_0">
                        <div class="row g-2 align-items-end">

                            {{-- Chọn sản phẩm --}}
                            <div class="col-md-4">
                                <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                                <select name="san_pham[]" class="form-select" required>
                                    <option value="">-- Chọn sản phẩm --</option>
                                    @foreach($danhSachSanPham as $sp)
                                        <option value="{{ $sp->ma_san_pham }}">
                                            {{ $sp->ten_san_pham }} (Tồn: {{ $sp->ton_kho_lo }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Số lượng --}}
                            <div class="col-md-2">
                                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                <input type="number" name="so_luong[]" class="form-control"
                                       min="1" placeholder="0" required>
                            </div>

                            {{-- Giá nhập --}}
                            <div class="col-md-2">
                                <label class="form-label">Giá nhập (₫) <span class="text-danger">*</span></label>
                                <input type="number" name="gia_nhap[]" class="form-control inp-gia-nhap"
                                       min="0" placeholder="0" required>
                            </div>

                            {{-- Giá bán (không bắt buộc, tự tính nếu trống) --}}
                            <div class="col-md-3">
                                <label class="form-label">
                                    Giá bán (₫)
                                    <span class="text-muted" style="font-size:0.75rem; font-weight:normal;">
                                        — để trống = +50%
                                    </span>
                                </label>
                                <input type="number" name="gia_ban[]" class="form-control inp-gia-ban"
                                       min="0" placeholder="Tự tính nếu để trống">
                            </div>

                            {{-- Cột trống để canh nút X của các dòng sau --}}
                            <div class="col-md-1"></div>

                        </div>
                    </div>
                </div>

                {{-- Nút thêm dòng sản phẩm --}}
                <button type="button" class="btn btn-outline-secondary btn-sm mt-1"
                        onclick="themDongSanPham()">
                    <i class="bi bi-plus-lg me-1"></i>Thêm sản phẩm
                </button>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" id="btnSubmit" class="btn btn-dark">
                        <i class="bi bi-plus-lg me-1" id="iconSubmit"></i>
                        <span id="textSubmit">Tạo phiếu nhập</span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- =====================================================================
         Ô DƯỚI: BỘ LỌC + DANH SÁCH PHIẾU NHẬP
    ===================================================================== --}}
    <div class="card">
        <div class="card-header header-danh-sach d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2"></i>Danh Sách Phiếu Nhập</span>
            <span class="badge bg-light text-dark">{{ $danhSachPhieuNhap->count() }} phiếu</span>
        </div>

        <div class="card-body">

            {{-- ===== BỘ LỌC / TÌM KIẾM ===== --}}
            <div class="card mb-3" style="border-color:#aaa;">
                <div class="card-header header-filter py-2">
                    <i class="bi bi-funnel me-1"></i>Lọc & Tìm kiếm
                </div>
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('phieu-nhap.index') }}">
                        <div class="row g-2 align-items-end">

                            {{-- Lọc trạng thái --}}
                            <div class="col-md-2">
                                <label class="form-label">Trạng thái</label>
                                <select name="trang_thai" class="form-select form-select-sm">
                                    <option value="">-- Tất cả --</option>
                                    <option value="DRAFT"     {{ request('trang_thai') == 'DRAFT'     ? 'selected' : '' }}>
                                        Đang tạo (Draft)
                                    </option>
                                    <option value="CONFIRMED" {{ request('trang_thai') == 'CONFIRMED' ? 'selected' : '' }}>
                                        Đã xác nhận
                                    </option>
                                </select>
                            </div>

                            {{-- Từ ngày --}}
                            <div class="col-md-2">
                                <label class="form-label">Từ ngày</label>
                                <input type="date" name="tu_ngay" class="form-control form-control-sm"
                                       value="{{ request('tu_ngay') }}">
                            </div>

                            {{-- Đến ngày --}}
                            <div class="col-md-2">
                                <label class="form-label">Đến ngày</label>
                                <input type="date" name="den_ngay" class="form-control form-control-sm"
                                       value="{{ request('den_ngay') }}">
                            </div>

                            {{-- Tìm nhà cung cấp (tìm mờ: gõ 1 phần cũng ra) --}}
                            <div class="col-md-4">
                                <label class="form-label">Tên nhà cung cấp</label>
                                <input type="text" name="tim_ncc" class="form-control form-control-sm"
                                       value="{{ request('tim_ncc') }}"
                                       placeholder="Nhập tên hoặc 1 phần tên NCC...">
                            </div>

                            {{-- Nút lọc --}}
                            <div class="col-md-2 d-flex gap-1">
                                <button type="submit" class="btn btn-dark btn-sm">
                                    <i class="bi bi-search me-1"></i>Lọc
                                </button>
                                <a href="{{ route('phieu-nhap.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            {{-- ===== BẢNG DANH SÁCH ===== --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:60px">STT</th>
                            <th>Ngày nhập</th>
                            <th>Nhân viên</th>
                            <th>Nhà cung cấp</th>
                            <th>SĐT NCC</th>
                            <th>Trạng thái</th>
                            <th style="width:220px">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($danhSachPhieuNhap as $pn)
                        <tr>
                            <td>{{ $pn->ma_phieu_nhap }}</td>

                            <td>
                                {{ $pn->ngay_nhap ? \Carbon\Carbon::parse($pn->ngay_nhap)->format('d/m/Y H:i') : '—' }}
                            </td>

                            <td>{{ $pn->nhanVien ? $pn->nhanVien->ten_nhan_vien : '—' }}</td>

                            <td>{{ $pn->ten_nha_cung_cap ?? '—' }}</td>

                            <td>{{ $pn->so_dien_thoai_ncc ?? '—' }}</td>

                            {{-- Trạng thái --}}
                            <td>
                                @if($pn->trang_thai === 'DRAFT')
                                    <span class="badge badge-draft">
                                        <i class="bi bi-pencil me-1"></i>Đang tạo
                                    </span>
                                @else
                                    <span class="badge badge-confirmed">
                                        <i class="bi bi-check-lg me-1"></i>Đã xác nhận
                                    </span>
                                @endif
                            </td>

                            {{-- Hành động --}}
                            <td>
                                <div class="d-flex gap-1 flex-wrap">

                                    {{-- NÚT XEM CHI TIẾT (luôn hiện) --}}
                                    <button class="btn btn-chitiet btn-sm"
                                            onclick="xemChiTiet({{ $pn->ma_phieu_nhap }})"
                                            title="Xem chi tiết nhập">
                                        <i class="bi bi-eye me-1"></i>Chi tiết
                                    </button>

                                    @if($pn->trang_thai === 'DRAFT')

                                        {{-- NÚT SỬA (chỉ hiện khi DRAFT) --}}
                                        <button class="btn btn-sua btn-sm"
                                                onclick="chuyenCheDeSua({{ $pn->ma_phieu_nhap }})"
                                                title="Sửa phiếu nhập">
                                            <i class="bi bi-pencil-fill me-1"></i>Sửa
                                        </button>

                                        {{-- NÚT XÁC NHẬN (chỉ hiện khi DRAFT) --}}
                                        <form action="{{ route('phieu-nhap.confirm', $pn->ma_phieu_nhap) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Xác nhận phiếu nhập STT{{ $pn->ma_phieu_nhap }}?\nSố lượng kho sẽ được cập nhật và không thể hoàn tác!')">
                                            @csrf
                                            <button type="submit" class="btn btn-xacnhan btn-sm" title="Xác nhận nhập kho">
                                                <i class="bi bi-check2-circle me-1"></i>Xác nhận
                                            </button>
                                        </form>

                                        {{-- NÚT XÓA (chỉ hiện khi DRAFT) --}}
                                        <form action="{{ route('phieu-nhap.destroy', $pn->ma_phieu_nhap) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Bạn chắc chắn muốn xóa phiếu nhập STT{{ $pn->ma_phieu_nhap }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xoa btn-sm" title="Xóa phiếu nhập">
                                                <i class="bi bi-trash me-1"></i>Xóa
                                            </button>
                                        </form>

                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Không tìm thấy phiếu nhập nào.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


{{-- =====================================================================
     MODAL XEM CHI TIẾT NHẬP
===================================================================== --}}
<div class="modal fade" id="modalChiTiet" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:0; border:2px solid #333;">

            <div class="modal-header" style="background:#1a1a1a; color:#fff; border-radius:0;">
                <h5 class="modal-title" id="tieuDeModal">
                    <i class="bi bi-file-earmark-text me-2"></i>Chi tiết phiếu nhập
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="noiDungModal">
                <div class="text-center py-3 text-muted">Đang tải...</div>
            </div>

            <div class="modal-footer" style="border-top:1px solid #ccc;">
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

  
    const danhSachSanPham = @json($danhSachSanPham);

  
    let demDong = 1;


    
    function themDongSanPham() {
        const id = 'dong_' + demDong++;
        const container = document.getElementById('danhSachDongChiTiet');

        let optionsSanPham = '<option value="">-- Chọn sản phẩm --</option>';
        danhSachSanPham.forEach(sp => {
            optionsSanPham += `<option value="${sp.ma_san_pham}">${sp.ten_san_pham} (Tồn: ${sp.ton_kho_lo})</option>`;
        });

        const dongHtml = `
            <div class="dong-chi-tiet" id="${id}">
                <button type="button" class="btn-xoa-dong" onclick="xoaDong('${id}')" title="Xóa dòng này">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                        <select name="san_pham[]" class="form-select" required>
                            ${optionsSanPham}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                        <input type="number" name="so_luong[]" class="form-control" min="1" placeholder="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Giá nhập (₫) <span class="text-danger">*</span></label>
                        <input type="number" name="gia_nhap[]" class="form-control inp-gia-nhap" min="0" placeholder="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            Giá bán (₫)
                            <span class="text-muted" style="font-size:0.75rem; font-weight:normal;">— để trống = +50%</span>
                        </label>
                        <input type="number" name="gia_ban[]" class="form-control inp-gia-ban" min="0" placeholder="Tự tính nếu để trống">
                    </div>
                    <div class="col-md-1"></div>
                </div>
            </div>`;

        container.insertAdjacentHTML('beforeend', dongHtml);
    }
    function xoaDong(id) {
        const dong = document.getElementById(id);
        if (dong) dong.remove();
    }

    function chuyenCheDeSua(id) {
        fetch(`/phieu-nhap/${id}/edit-data`)
            .then(res => res.json())
            .then(pn => {

                document.getElementById('inp_ngay_nhap').value   = pn.ngay_nhap
                    ? pn.ngay_nhap.replace(' ', 'T').substring(0, 16) : '';
                document.getElementById('inp_nhan_vien').value   = pn.ma_nhan_vien ?? '';
                document.getElementById('inp_ncc').value         = pn.ten_nha_cung_cap ?? '';
                document.getElementById('inp_sdt_ncc').value     = pn.so_dien_thoai_ncc ?? '';
                document.getElementById('inp_email_ncc').value   = pn.email_ncc ?? '';
                document.getElementById('inp_dia_chi_ncc').value = pn.dia_chi_ncc ?? '';

                const container = document.getElementById('danhSachDongChiTiet');
                container.innerHTML = '';
                demDong = 0;

                if (pn.chi_tiet_nhaps && pn.chi_tiet_nhaps.length > 0) {
                    pn.chi_tiet_nhaps.forEach((ct, index) => {
                        const dongId = 'dong_' + demDong++;

                        let opts = '<option value="">-- Chọn sản phẩm --</option>';
                        danhSachSanPham.forEach(sp => {
                            const sel = sp.ma_san_pham == ct.ma_san_pham ? 'selected' : '';
                            opts += `<option value="${sp.ma_san_pham}" ${sel}>${sp.ten_san_pham} (Tồn: ${sp.ton_kho_lo})</option>`;
                        });

                        const nutXoa = index > 0
                            ? `<button type="button" class="btn-xoa-dong" onclick="xoaDong('${dongId}')"><i class="bi bi-x-circle-fill"></i></button>`
                            : '';

                        const dongHtml = `
                            <div class="dong-chi-tiet" id="${dongId}">
                                ${nutXoa}
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                                        <select name="san_pham[]" class="form-select" required>${opts}</select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                        <input type="number" name="so_luong[]" class="form-control"
                                               min="1" value="${ct.so_luong}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Giá nhập (₫) <span class="text-danger">*</span></label>
                                        <input type="number" name="gia_nhap[]" class="form-control inp-gia-nhap"
                                               min="0" value="${ct.gia_nhap}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">
                                            Giá bán (₫)
                                            <span class="text-muted" style="font-size:0.75rem; font-weight:normal;">— để trống = +50%</span>
                                        </label>
                                        <input type="number" name="gia_ban[]" class="form-control inp-gia-ban"
                                               min="0" value="${ct.gia_ban ?? ''}" placeholder="Tự tính nếu để trống">
                                    </div>
                                    <div class="col-md-1"></div>
                                </div>
                            </div>`;
                        container.insertAdjacentHTML('beforeend', dongHtml);
                    });
                }

                document.getElementById('formChung').action = `/phieu-nhap/${id}`;
                document.getElementById('methodField').innerHTML =
                    '<input type="hidden" name="_method" value="PUT">';

                const header = document.getElementById('cardFormHeader');
                header.className = 'card-header header-sua d-flex justify-content-between align-items-center';
                document.getElementById('tieuDeForm').innerHTML =
                    `<i class="bi bi-pencil-fill me-2"></i>Sửa Phiếu Nhập #${id}`;
                document.getElementById('btnHuy').style.display = 'inline-block';

                document.getElementById('btnSubmit').className = 'btn btn-secondary';
                document.getElementById('iconSubmit').className = 'bi bi-save me-1';
                document.getElementById('textSubmit').textContent = 'Lưu thay đổi';

                document.getElementById('cardForm').scrollIntoView({ behavior: 'smooth' });
            })
            .catch(() => alert('Không thể tải dữ liệu phiếu nhập!'));
    }


    
    function chuyenVeCheDoDaThem() {
        const container = document.getElementById('danhSachDongChiTiet');
        demDong = 0;

        let optionsSanPham = '<option value="">-- Chọn sản phẩm --</option>';
        danhSachSanPham.forEach(sp => {
            optionsSanPham += `<option value="${sp.ma_san_pham}">${sp.ten_san_pham} (Tồn: ${sp.ton_kho_lo})</option>`;
        });

        
        container.innerHTML = `
            <div class="dong-chi-tiet" id="dong_0">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                        <select name="san_pham[]" class="form-select" required>${optionsSanPham}</select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                        <input type="number" name="so_luong[]" class="form-control" min="1" placeholder="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Giá nhập (₫) <span class="text-danger">*</span></label>
                        <input type="number" name="gia_nhap[]" class="form-control inp-gia-nhap" min="0" placeholder="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            Giá bán (₫)
                            <span class="text-muted" style="font-size:0.75rem; font-weight:normal;">— để trống = +50%</span>
                        </label>
                        <input type="number" name="gia_ban[]" class="form-control inp-gia-ban" min="0" placeholder="Tự tính nếu để trống">
                    </div>
                    <div class="col-md-1"></div>
                </div>
            </div>`;
        demDong = 1;

        document.getElementById('formChung').reset();
        document.getElementById('formChung').action = '{{ route('phieu-nhap.store') }}';
        document.getElementById('methodField').innerHTML = '';

        const header = document.getElementById('cardFormHeader');
        header.className = 'card-header header-them d-flex justify-content-between align-items-center';
        document.getElementById('tieuDeForm').innerHTML =
            '<i class="bi bi-plus-circle me-2"></i>Thêm Phiếu Nhập Mới';
        document.getElementById('btnHuy').style.display = 'none';

        document.getElementById('btnSubmit').className = 'btn btn-dark';
        document.getElementById('iconSubmit').className = 'bi bi-plus-lg me-1';
        document.getElementById('textSubmit').textContent = 'Tạo phiếu nhập';
    }


   
    function xemChiTiet(id) {
        document.getElementById('tieuDeModal').innerHTML =
            `<i class="bi bi-file-earmark-text me-2"></i>Chi tiết phiếu nhập #${id}`;
        document.getElementById('noiDungModal').innerHTML =
            '<div class="text-center py-3 text-muted"><i class="bi bi-hourglass-split me-2"></i>Đang tải...</div>';

        const modal = new bootstrap.Modal(document.getElementById('modalChiTiet'));
        modal.show();

        fetch(`/phieu-nhap/${id}/edit-data`)
            .then(res => res.json())
            .then(pn => {
                let tongTienNhap = 0;
                let rowsSanPham = '';

                if (pn.chi_tiet_nhaps && pn.chi_tiet_nhaps.length > 0) {
                    pn.chi_tiet_nhaps.forEach(ct => {
                        const thanhTien = ct.so_luong * ct.gia_nhap;
                        tongTienNhap += thanhTien;
                        const tenSP = ct.san_pham ? ct.san_pham.ten_san_pham : `SP #${ct.ma_san_pham}`;

                        
                        const giaBanTuDong = ct.gia_ban == null || ct.gia_ban == (ct.gia_nhap * 1.5);
                        const giaBanHtml = giaBanTuDong
                            ? `${Number(ct.gia_ban).toLocaleString('vi-VN')} ₫ <small class="text-muted">(tự tính)</small>`
                            : `${Number(ct.gia_ban).toLocaleString('vi-VN')} ₫`;

                       rowsSanPham += `
                    <tr>
                        <td>${tenSP}</td>
                        <td class="text-center">${ct.so_luong}</td>
                        <td class="text-end">${Number(ct.gia_nhap).toLocaleString('vi-VN')} ₫</td>
                        <td class="text-end">${giaBanHtml}</td>
                        <td class="text-end fw-semibold">${thanhTien.toLocaleString('vi-VN')} ₫</td>
                    </tr>`;
                                    });
                } else {
                    rowsSanPham = '<tr><td colspan="5" class="text-center text-muted">Không có sản phẩm nào</td></tr>';
                }

                const trangThaiHtml = pn.trang_thai === 'DRAFT'
                    ? '<span class="badge" style="background:#e8e8e8;color:#333;border:1px solid #aaa;">Đang tạo (Draft)</span>'
                    : '<span class="badge bg-dark">Đã xác nhận</span>';

                const ngayNhap = pn.ngay_nhap
                    ? new Date(pn.ngay_nhap).toLocaleString('vi-VN') : '—';

                document.getElementById('noiDungModal').innerHTML = `
                    <div class="row g-2 mb-3" style="font-size:0.88rem;">
                        <div class="col-md-6">
                            <strong>Ngày nhập:</strong> ${ngayNhap}<br>
                            <strong>Trạng thái:</strong> ${trangThaiHtml}
                        </div>
                        <div class="col-md-6">
                            <strong>Nhà cung cấp:</strong> ${pn.ten_nha_cung_cap ?? '—'}<br>
                            <strong>SĐT NCC:</strong> ${pn.so_dien_thoai_ncc ?? '—'}<br>
                            <strong>Email NCC:</strong> ${pn.email_ncc ?? '—'}<br>
                            <strong>Địa chỉ NCC:</strong> ${pn.dia_chi_ncc ?? '—'}
                        </div>
                    </div>

                    <p style="font-weight:600; border-bottom:2px solid #333; padding-bottom:4px;">
                        <i class="bi bi-box-seam me-1"></i>Danh sách sản phẩm nhập
                    </p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" style="font-size:0.87rem;">
                            <thead style="background:#333; color:#fff;">
                                <tr>
                                    <th>Tên sản phẩm</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Giá nhập</th>
                                    <th class="text-end">Giá bán phiếu nhập</th>
                                    <th class="text-end">Thành tiền (nhập)</th>
                                </tr>
                            </thead>
                            <tbody>${rowsSanPham}</tbody>
                            <tfoot>
                                <tr style="background:#f0f0f0; font-weight:600;">
                                    <td colspan="4" class="text-end">Tổng tiền nhập:</td>
                                    <td class="text-end">${tongTienNhap.toLocaleString('vi-VN')} ₫</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>`;
            })
            .catch(() => {
                document.getElementById('noiDungModal').innerHTML =
                    '<div class="text-danger text-center py-3"><i class="bi bi-exclamation-triangle me-2"></i>Không thể tải dữ liệu!</div>';
            });
    }

</script>
@endpush
