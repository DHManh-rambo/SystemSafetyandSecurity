@extends('layouts.admin')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/SanPham.css') }}">
@endpush

@section('content')

<div class="san-pham-container">
    <div class="container-fluid p-0">
    {{-- ===== THÔNG BÁO ===== --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
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


    {{-- ===== Ô TRÊN: THÊM / SỬA SẢN PHẨM ===== --}}
    <div class="card mb-4" id="cardForm">

        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center" id="cardFormHeader">
            <span id="tieuDeForm">
                <i class="bi bi-plus-circle me-2"></i>Thêm Sản Phẩm Mới
            </span>
            <button type="button" id="btnHuy" class="btn btn-light btn-sm"
                    onclick="chuyenVeCheDoDaThem()" style="display:none;">
                <i class="bi bi-x-lg me-1"></i>Hủy sửa
            </button>
        </div>

        <div class="card-body">
            <form id="formChung" action="{{ route('san-pham.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="methodField"></div>

                <div class="row g-3">

                    {{-- Tên sản phẩm --}}
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">
                            Tên sản phẩm <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="ten_san_pham" id="inp_ten" class="form-control"
                               value="{{ old('ten_san_pham') }}"
                               placeholder="Ví dụ: Hoa hồng đỏ" required>
                    </div>

                    {{-- Loại sản phẩm --}}
                    <div class="col-md-4" id="khuVucLoai">
                        <label class="form-label fw-semibold">
                            Loại sản phẩm <span class="text-danger" id="loaiRequired">*</span>
                        </label>
                        <select name="loai_san_pham" id="inp_loai" class="form-select" required>
                            <option value="">-- Chọn loại sản phẩm --</option>
                            @foreach($danhSachLoai as $value => $label)
                                <option value="{{ $value }}" {{ old('loai_san_pham') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div id="loaiHienThi" style="display:none;">
                            <input type="text" id="inp_loai_text" class="form-control bg-light" readonly>
                            <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Không thể thay đổi loại sản phẩm</small>
                        </div>
                    </div>

                    {{-- Số lượng (chỉ đọc khi SỬA) --}}
                    <div class="col-md-3" id="khuVucSoLuong" style="display:none;">
                        <label class="form-label fw-semibold">Số lượng trong kho</label>
                        <input type="text" id="inp_so_luong" class="form-control bg-light" readonly>
                        <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Chỉ thay đổi khi nhập hàng</small>
                    </div>
                    {{-- Giá bán hiện tại --}}
                  
    <div class="col-md-3">
    <label class="form-label fw-semibold">Giá bán hiện tại</label>
    <input
        type="number"
        name="gia_ban_hien_tai"
        id="inp_gia_ban"
        class="form-control"
        min="0"
        step="1000"
        placeholder="Ví dụ: 25000"
    >
    <small class="text-muted">Giá khách hàng đang nhìn thấy</small>
</div>
                    {{-- Mô tả --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="mo_ta" id="inp_mo_ta" class="form-control" rows="2"
                                  placeholder="Nhập mô tả sản phẩm...">{{ old('mo_ta') }}</textarea>
                    </div>
                    

                    {{-- Hình ảnh --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" id="labelAnh">Hình ảnh</label>
                        <input type="file" name="hinh_anh" id="inp_anh" class="form-control"
                               accept="image/jpg,image/jpeg,image/png,image/gif,image/webp"
                               onchange="xemTruocAnh(this)">
                        <small class="text-muted">
                            Chọn ảnh từ máy tính. JPG, PNG, GIF, WEBP — tối đa 2MB.
                        </small>
                        <div id="khuVucAnhHienTai" style="display:none; margin-top:6px;">
                            <small class="text-muted d-block mb-1">Ảnh hiện tại:</small>
                            <img id="anhHienTai" src="" alt="Ảnh hiện tại"
                                 style="max-height:80px; border-radius:8px;">
                        </div>
                        <img id="xemTruocAnh" class="preview-img" alt="Xem trước ảnh mới">
                    </div>

                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" id="btnSubmit" class="btn btn-success">
                        <i class="bi bi-plus-lg me-1" id="iconSubmit"></i>
                        <span id="textSubmit">Thêm sản phẩm</span>
                    </button>
                </div>

            </form>
        </div>
    </div>


    {{-- ===== Ô DƯỚI: DANH SÁCH SẢN PHẨM ===== --}}
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2"></i>Danh Sách Sản Phẩm (Admin)</span>
            <span class="badge bg-white text-primary">{{ $danhSachSanPham->count() }} sản phẩm</span>
        </div>
        <div class="card-body">

            {{-- Bộ lọc theo loại sản phẩm --}}
            <form method="GET" action="{{ route('san-pham.index') }}"
                  class="mb-3 d-flex gap-2 flex-wrap align-items-center">
                <label class="fw-semibold me-1 mb-0">Lọc theo loại:</label>
                <select name="loai_san_pham" class="form-select w-auto"
                        onchange="this.form.submit()">
                    <option value="">-- Tất cả --</option>
                    @foreach($danhSachLoai as $value => $label)
                        <option value="{{ $value }}"
                            {{ request('loai_san_pham') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @if(request('loai_san_pham'))
                    <a href="{{ route('san-pham.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Xóa lọc
                    </a>
                @endif
            </form>

            {{-- Bảng danh sách --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Loại</th>
                            <th>Giá bán (từ phiếu nhập)</th>
                            <th>Giá hiện tại</th>
                            <th>Số lượng</th>
                            <th>Mô tả</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($danhSachSanPham as $sp)
                        <tr>
                            {{-- Ảnh --}}
                            <td>
                                @if($sp->hinh_anh)
                                    <img src="{{ asset($sp->hinh_anh) }}"
                                         alt="{{ $sp->ten_san_pham }}"
                                         width="60" height="60">
                                @else
                                    <img src="{{ asset('img/default.jpg') }}"
                                         alt="Chưa có ảnh"
                                         width="60" height="60">
                                @endif
                            </td>

                            {{-- Tên --}}
                            <td class="fw-semibold">{{ $sp->ten_san_pham }}</td>

                            {{-- Loại --}}

                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $danhSachLoai[$sp->loai_san_pham] ?? $sp->loai_san_pham }}
                                    </span>
                                </td>

                               

                            {{-- Giá bán theo lô --}}
                            <td>
                                @php
                                    $loCoHang = $sp->chiTietNhaps;
                                    $tongConLai = $loCoHang->sum('so_luong_con_lai');
                                @endphp
                                @if($loCoHang->isNotEmpty())
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($loCoHang as $ct)
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="badge bg-success" style="min-width:90px; text-align:right;">
                                                    {{ number_format($ct->gia_ban, 0, ',', '.') }} đ
                                                </span>
                                                <span class="badge bg-secondary" title="Số lượng còn lại của lô này">
                                                    <i class="bi bi-box-seam me-1"></i>{{ $ct->so_luong_con_lai }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">Chưa nhập hàng</span>
                                @endif
                            </td>
                             {{-- Giá hiện tại --}}
                                <td class="fw-bold text-success">
                                    {{ $sp->gia_ban_hien_tai
                                        ? number_format($sp->gia_ban_hien_tai, 0, ',', '.') . 'đ'
                                        : '-' }}
                                </td>
                    
                            {{-- Giá bán hiện tại --}}  
                            {{-- Tổng số lượng trong kho --}}
                            <td>
                                <span class="{{ $tongConLai == 0 ? 'text-danger fw-bold' : ($tongConLai < 5 ? 'text-warning fw-bold' : '') }}"
                                      title="Tổng các lô CONFIRMED còn hàng">
                                    {{ $tongConLai }}
                                </span>
                                @if($loCoHang->count() > 1)
                                    <div class="text-muted" style="font-size:11px;">
                                        ({{ $loCoHang->count() }} mức giá)
                                    </div>
                                @endif
                            </td>

                            {{-- Mô tả --}}
                            <td>
                                <span title="{{ $sp->mo_ta }}">
                                    {{ \Illuminate\Support\Str::limit($sp->mo_ta, 40) }}
                                </span>
                            </td>

                            {{-- Trạng thái --}}
                            <td>
                                @if($sp->trang_thai === 'DANG_BAN')
                                    <span class="badge badge-dang-ban px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i>Đang bán
                                    </span>
                                @else
                                    <span class="badge badge-ngung-ban px-2 py-1">
                                        <i class="bi bi-eye-slash me-1"></i>Ngừng bán
                                    </span>
                                @endif
                            </td>

                            {{-- Hành động --}}
                            <td>
                                <div class="d-flex gap-1 flex-wrap">

                                    {{-- Nút SỬA --}}
                                    <button class="btn btn-warning btn-sm"
                                            onclick="chuyenCheDeSua({{ $sp->ma_san_pham }})"
                                            title="Sửa sản phẩm">
                                        <i class="bi bi-pencil-fill"></i> Sửa
                                    </button>

                                    {{-- Nút ẨN / HIỆN --}}
                                    <form action="{{ route('san-pham.toggle', $sp->ma_san_pham) }}"
                                          method="POST"
                                          onsubmit="return confirm('{{ $sp->trang_thai === 'DANG_BAN' ? 'Ẩn sản phẩm này? Khách hàng sẽ không thể mua.' : 'Hiện lại sản phẩm này để khách hàng mua được?' }}')">
                                        @csrf
                                        @method('PATCH')
                                        @if($sp->trang_thai === 'DANG_BAN')
                                            <button type="submit" class="btn btn-secondary btn-sm" title="Ẩn sản phẩm">
                                                <i class="bi bi-eye-slash-fill"></i> Ẩn
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-success btn-sm" title="Hiện lại">
                                                <i class="bi bi-eye-fill"></i> Hiện
                                            </button>
                                        @endif
                                    </form>

                                    {{-- ===== NÚT BÁO CÁO HỎNG ===== --}}
                                    <button class="btn btn-danger btn-sm"
                                            onclick="moModalHangHong(
                                                {{ $sp->ma_san_pham }},
                                                '{{ addslashes($sp->ten_san_pham) }}',
                                                {{ $tongConLai }}
                                            )"
                                            title="Báo cáo hàng hỏng"
                                            {{ $tongConLai == 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-exclamation-triangle-fill"></i> Hỏng
                                    </button>

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Không có sản phẩm nào.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


{{-- ===== MODAL: BÁO CÁO HÀNG HỎNG ===== --}}
<div class="modal fade modal-hong" id="modalHangHong" tabindex="-1" aria-labelledby="labelModalHangHong" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="labelModalHangHong">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Báo Cáo Hàng Hỏng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- ĐỔI route từ bao-cao.bao-hang-hong → san-pham.bao-hang-hong --}}
            <form action="{{ route('san-pham.bao-hang-hong') }}" method="POST">
                @csrf
                <input type="hidden" name="ma_san_pham" id="modal_ma_sp">

                <div class="modal-body">

                    {{-- Thông tin sản phẩm --}}
                    <div class="info-sp-hong mb-3">
                        <div class="text-muted mb-1" style="font-size:12px;">Sản phẩm được báo cáo</div>
                        <strong id="modal_ten_sp">—</strong>
                        <div class="mt-1">
                            Tồn kho lô đang chọn:
                            <span class="badge bg-secondary" id="modal_so_luong_badge">0</span>
                        </div>
                    </div>

                    {{-- CHỌN LÔ NHẬP — MỚI --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Chọn lô nhập <span class="text-danger">*</span>
                        </label>
                        <select name="ma_chi_tiet_nhap" id="modal_lo_nhap" class="form-select" required>
                            <option value="">-- Đang tải lô... --</option>
                        </select>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Chọn đúng lô bị hỏng. Mỗi lô hiển thị: Phiếu nhập — Ngày nhập — Số lượng còn lại.
                        </small>
                    </div>

                    {{-- Số lượng hỏng --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Số lượng hỏng <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="so_luong_hong" id="modal_so_luong_hong"
                               class="form-control" min="1" required
                               placeholder="Nhập số lượng hỏng">
                        <div class="form-text text-danger" id="modal_sl_error" style="display:none;">
                            Số lượng hỏng không được vượt tồn kho của lô này!
                        </div>
                    </div>

                    {{-- Lý do --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lý do hỏng</label>
                        <input type="text" name="ly_do" class="form-control"
                               maxlength="255"
                               placeholder="VD: Hoa héo, Vỡ bình, Hết hạn...">
                    </div>

                    {{-- Ghi chú --}}
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Ghi chú thêm</label>
                        <textarea name="ghi_chu" class="form-control" rows="2"
                                  placeholder="Ghi chú thêm nếu cần..."></textarea>
                    </div>

                    <div class="text-muted" style="font-size:12px;">
                        <i class="bi bi-person-fill me-1"></i>
                        Nhân viên báo cáo: <strong>Nhân Viên 2</strong>
                        &nbsp;|&nbsp;
                        <i class="bi bi-clock me-1"></i>Thời gian: lúc gửi form
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-danger" id="btnGuiBaoCao" disabled>
                        <i class="bi bi-send-fill me-1"></i>Xác nhận báo cáo
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const danhSachLoai = @json($danhSachLoai);

    // ── Xem trước ảnh ──────────────────────────────────────────
    function xemTruocAnh(input) {
        const imgEl = document.getElementById('xemTruocAnh');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgEl.src = e.target.result;
                imgEl.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ── Chuyển chế độ sửa ──────────────────────────────────────
    function chuyenCheDeSua(id) {
        fetch('/san-pham/' + id + '/edit-data')
            .then(function(res) { return res.json(); })
            .then(function(sp) {
                document.getElementById('inp_ten').value   = sp.ten_san_pham;
                document.getElementById('inp_mo_ta').value = sp.mo_ta ?? '';
                document.getElementById('inp_gia_ban').value = sp.gia_ban_hien_tai ?? '';

                document.getElementById('khuVucSoLuong').style.display = 'block';
                document.getElementById('inp_so_luong').value = sp.ton_kho_thuc_te ?? sp.so_luong;

                document.getElementById('inp_loai').style.display = 'none';
                document.getElementById('inp_loai').removeAttribute('required');
                document.getElementById('loaiHienThi').style.display = 'block';
                document.getElementById('inp_loai_text').value = danhSachLoai[sp.loai_san_pham] ?? sp.loai_san_pham;
                document.getElementById('loaiRequired').style.display = 'none';

                if (sp.hinh_anh) {
                    document.getElementById('anhHienTai').src = '/' + sp.hinh_anh;
                    document.getElementById('khuVucAnhHienTai').style.display = 'block';
                } else {
                    document.getElementById('khuVucAnhHienTai').style.display = 'none';
                }

                document.getElementById('xemTruocAnh').style.display = 'none';
                document.getElementById('inp_anh').value = '';

                document.getElementById('formChung').action = '/san-pham/' + id;
                document.getElementById('methodField').innerHTML =
                    '<input type="hidden" name="_method" value="PUT">';

                const header = document.getElementById('cardFormHeader');
                header.className = 'card-header bg-warning text-dark d-flex justify-content-between align-items-center';

                document.getElementById('tieuDeForm').innerHTML =
                    '<i class="bi bi-pencil-fill me-2"></i>Sửa Sản Phẩm: <strong>' + sp.ten_san_pham + '</strong>';

                document.getElementById('btnHuy').style.display = 'inline-block';
                document.getElementById('btnSubmit').className = 'btn btn-warning';
                document.getElementById('iconSubmit').className = 'bi bi-save me-1';
                document.getElementById('textSubmit').textContent = 'Lưu thay đổi';
                document.getElementById('labelAnh').textContent = 'Hình ảnh mới (để trống nếu không đổi)';
                document.getElementById('cardForm').scrollIntoView({ behavior: 'smooth' });
            })
            .catch(function() {
                alert('Không thể tải dữ liệu sản phẩm! Vui lòng thử lại.');
            });
    }

    // ── Quay về chế độ thêm ────────────────────────────────────
    function chuyenVeCheDoDaThem() {
        document.getElementById('formChung').reset();
        document.getElementById('inp_gia_ban').value = '';
        document.getElementById('formChung').action = '{{ route('san-pham.store') }}';
        document.getElementById('methodField').innerHTML = '';

        document.getElementById('inp_loai').style.display = 'block';
        document.getElementById('inp_loai').setAttribute('required', 'required');
        document.getElementById('loaiHienThi').style.display = 'none';
        document.getElementById('loaiRequired').style.display = 'inline';
        document.getElementById('khuVucSoLuong').style.display = 'none';
        document.getElementById('khuVucAnhHienTai').style.display = 'none';
        document.getElementById('xemTruocAnh').style.display = 'none';

        const header = document.getElementById('cardFormHeader');
        header.className = 'card-header bg-success text-white d-flex justify-content-between align-items-center';
        document.getElementById('tieuDeForm').innerHTML =
            '<i class="bi bi-plus-circle me-2"></i>Thêm Sản Phẩm Mới';
        document.getElementById('btnHuy').style.display = 'none';
        document.getElementById('btnSubmit').className = 'btn btn-success';
        document.getElementById('iconSubmit').className = 'bi bi-plus-lg me-1';
        document.getElementById('textSubmit').textContent = 'Thêm sản phẩm';
        document.getElementById('labelAnh').textContent = 'Hình ảnh';
    }

    // ── Modal báo cáo hàng hỏng ────────────────────────────────
    let _soLuongKho = 0;

    function moModalHangHong(maSP, tenSP, soLuong) {
        _soLuongKho = soLuong;

        document.getElementById('modal_ma_sp').value         = maSP;
        document.getElementById('modal_ten_sp').textContent  = tenSP;
        document.getElementById('modal_so_luong_badge').textContent = soLuong;
        document.getElementById('modal_so_luong_hong').value = '';
        document.getElementById('modal_so_luong_hong').max   = soLuong;
        document.getElementById('modal_sl_error').style.display = 'none';
        document.getElementById('btnGuiBaoCao').disabled = true;

        // Reset dropdown lô
        const selectLo = document.getElementById('modal_lo_nhap');
        selectLo.innerHTML = '<option value="">-- Đang tải lô nhập... --</option>';

        // Gọi API lấy danh sách lô còn hàng
        fetch('/san-pham/' + maSP + '/lo-nhap')
            .then(res => res.json())
            .then(data => {
                selectLo.innerHTML = '<option value="">-- Chọn lô nhập --</option>';

                if (data.lo_nhaps.length === 0) {
                    selectLo.innerHTML = '<option value="">Không có lô nào còn hàng</option>';
                    return;
                }

                data.lo_nhaps.forEach(lo => {
                    const opt = document.createElement('option');
                    opt.value = lo.ma_chi_tiet_nhap;
                    opt.textContent = lo.label;
                    opt.dataset.soLuongConLai = lo.so_luong_con_lai;
                    selectLo.appendChild(opt);
                });
            })
            .catch(() => {
                selectLo.innerHTML = '<option value="">Lỗi tải lô, vui lòng thử lại</option>';
            });

        // Khi đổi lô → cập nhật max số lượng hỏng
        selectLo.onchange = function () {
            const opt = this.options[this.selectedIndex];
            const slConLai = parseInt(opt.dataset.soLuongConLai) || 0;
            _soLuongKho = slConLai;
            document.getElementById('modal_so_luong_hong').max = slConLai;
            document.getElementById('modal_so_luong_badge').textContent = slConLai;
            document.getElementById('modal_so_luong_hong').value = '';
            document.getElementById('modal_sl_error').style.display = 'none';
            // Chỉ enable nút Xác nhận khi đã chọn lô hợp lệ
            document.getElementById('btnGuiBaoCao').disabled = (slConLai === 0 || !this.value);
        };

        new bootstrap.Modal(document.getElementById('modalHangHong')).show();
    }

    // Validate số lượng hỏng không vượt tồn kho lô
    document.getElementById('modal_so_luong_hong').addEventListener('input', function () {
        const val    = parseInt(this.value) || 0;
        const errEl  = document.getElementById('modal_sl_error');
        const btnGui = document.getElementById('btnGuiBaoCao');
        const loVal  = document.getElementById('modal_lo_nhap').value;

        if (!loVal) {
            // Chưa chọn lô thì không cho submit
            btnGui.disabled = true;
            return;
        }

        if (val < 1 || val > _soLuongKho) {
            errEl.style.display = 'block';
            btnGui.disabled = true;
        } else {
            errEl.style.display = 'none';
            btnGui.disabled = false;
        }
    });
</script>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush

@endsection