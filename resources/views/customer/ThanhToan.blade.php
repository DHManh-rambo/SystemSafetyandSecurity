@extends('customer.layouts.customer')

@section('title', '💳 Thanh Toán – Hoa Tươi Shop')
@section('page-id', 'thanh-toan')

@section('head-styles')
<link rel="stylesheet" href="{{ asset('css/Customer/ThanhToan.css') }}">
@endsection

@section('content')
<div class="checkout-page">

    <div class="checkout-heading">💳 Thanh Toán</div>

    <div class="checkout-steps">
        <span class="step-done">Giỏ hàng</span>
        <span class="sep">›</span>
        <span class="step-active">💳 Thông tin thanh toán</span>
        <span class="sep">›</span>
        <span>📦 Hoàn tất</span>
    </div>

    @if(session('error'))
        <div class="alert-checkout error">❌ {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('customer.thanh-toan.store') }}" id="checkoutForm">
        @csrf
        <div class="checkout-layout">

            {{-- LEFT: Thông tin --}}
            <div class="checkout-panel">
                <h3>📋 Thông Tin Giao Hàng</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Tên người nhận <span>*</span></label>
                        <input type="text" name="ten_nguoi_nhan"
                               value="{{ old('ten_nguoi_nhan', optional($user->khachHang)->ten_khach_hang) }}"
                               placeholder="Họ và tên"
                               class="{{ $errors->has('ten_nguoi_nhan') ? 'input-error' : '' }}">
                        @error('ten_nguoi_nhan')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại <span>*</span></label>
                        <input type="text" name="so_dien_thoai"
                               value="{{ old('so_dien_thoai', optional($user->khachHang)->so_dien_thoai) }}"
                               placeholder="0xxxxxxxxx"
                               class="{{ $errors->has('so_dien_thoai') ? 'input-error' : '' }}">
                        @error('so_dien_thoai')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Địa chỉ giao hàng dạng select --}}
                @php
                    $diaChiKH   = optional($user->khachHang)->dia_chi ?? '';
                    $parts      = array_map('trim', explode(',', $diaChiKH));
                    if (count($parts) >= 1 && strtolower(end($parts)) === 'hà nội') array_pop($parts);
                    $ttQuan     = old('quan_huyen',       count($parts) >= 2 ? $parts[count($parts)-1] : '');
                    $ttPhuong   = old('xa_phuong',        count($parts) >= 3 ? $parts[count($parts)-2] : '');
                    $ttChiTiet  = old('dia_chi_chi_tiet', count($parts) >= 1 ? implode(', ', array_slice($parts, 0, count($parts)-2)) : $diaChiKH);
                @endphp

                <div class="form-row full">
                    <div class="form-group">
                        <label>Tỉnh/Thành phố</label>
                        <input type="text" value="Hà Nội" readonly
                               style="background:#f9fafb;color:#9ca3af;cursor:not-allowed;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Quận/Huyện <span>*</span></label>
                        <select id="tt_quan_huyen" name="quan_huyen"
                                class="{{ $errors->has('quan_huyen') ? 'input-error' : '' }}" required>
                            <option value="">Chọn quận/huyện</option>
                        </select>
                        @error('quan_huyen')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Xã/Phường <span>*</span></label>
                        <select id="tt_xa_phuong" name="xa_phuong"
                                class="{{ $errors->has('xa_phuong') ? 'input-error' : '' }}" required>
                            <option value="">Chọn xã/phường</option>
                        </select>
                        @error('xa_phuong')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label>Địa chỉ chi tiết <span>*</span></label>
                        <input type="text" name="dia_chi_chi_tiet"
                               value="{{ $ttChiTiet }}"
                               placeholder="Số nhà, tên đường, ngõ..."
                               class="{{ $errors->has('dia_chi_chi_tiet') ? 'input-error' : '' }}" required>
                        @error('dia_chi_chi_tiet')<div class="error-msg">{{ $message }}</div>@enderror
                        <div class="field-note">📍 Giao hàng nhanh trong 60 phút nội thành Hà Nội</div>
                    </div>
                </div>

                {{-- Phương thức thanh toán --}}
                <div style="margin-top: 8px;">
                    <h3 style="margin-top: 0;">💳 Phương Thức Thanh Toán</h3>
                    @error('phuong_thuc_thanh_toan')<div class="error-msg" style="margin-bottom:10px;">{{ $message }}</div>@enderror

                    <div class="payment-option {{ old('phuong_thuc_thanh_toan', 'NGAN_HANG') === 'NGAN_HANG' ? 'selected' : '' }}"
                         onclick="selectPayment('NGAN_HANG', this)">
                        <input type="radio" name="phuong_thuc_thanh_toan" value="NGAN_HANG"
                               id="pay_ck" {{ old('phuong_thuc_thanh_toan', 'NGAN_HANG') === 'NGAN_HANG' ? 'checked' : '' }}>
                        <div class="payment-option-info">
                            <div class="payment-option-title">🏦 Chuyển khoản ngân hàng</div>
                            <div class="payment-option-desc">
                                Đơn hàng sẽ được đánh dấu <strong>đã thanh toán</strong> ngay sau khi đặt.
                                Vui lòng chuyển khoản theo thông tin bên dưới.
                            </div>
                        </div>
                    </div>

                    <div class="bank-info {{ old('phuong_thuc_thanh_toan', 'NGAN_HANG') === 'NGAN_HANG' ? 'show' : '' }}"
                         id="bankInfoBox">
                        <p>🏦 <strong>Ngân hàng:</strong> MB Bank (Ngân hàng Quân đội)</p>
                        <p>💳 <strong>Số tài khoản:</strong> 0357634696</p>
                        <p>👤 <strong>Chủ tài khoản:</strong> DUONG HUNG MANH</p>
                        <p>📝 <strong>Nội dung CK:</strong> Họ và Tên + Số điện thoại của bạn</p>
                    </div>

                    <div class="payment-option {{ old('phuong_thuc_thanh_toan') === 'COD' ? 'selected' : '' }}"
                         onclick="selectPayment('COD', this)">
                        <input type="radio" name="phuong_thuc_thanh_toan" value="COD"
                               id="pay_cod" {{ old('phuong_thuc_thanh_toan') === 'COD' ? 'checked' : '' }}>
                        <div class="payment-option-info">
                            <div class="payment-option-title">💵 Thanh toán khi nhận hàng (COD)</div>
                            <div class="payment-option-desc">Thanh toán trực tiếp cho shipper khi nhận được hoa.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Đơn hàng --}}
            <div class="order-summary-panel">
                <h3>🛒 Đơn Hàng Của Bạn</h3>

                <div class="order-items">
                    @foreach($gioHang as $key => $item)
                    <div class="order-item">
                        <div class="order-item-left">
                            @if($item['hinh_anh'])
                                <img src="{{ asset($item['hinh_anh']) }}" alt="{{ $item['ten_san_pham'] }}"
                                     class="order-item-img"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div class="order-item-placeholder" style="display:none;">🌸</div>
                            @else
                                <div class="order-item-placeholder">🌸</div>
                            @endif
                            <div>
                                <div class="order-item-name">{{ $item['ten_san_pham'] }}</div>
                                <div class="order-item-qty">× {{ $item['so_luong'] }}</div>
                            </div>
                        </div>
                        <div class="order-item-price">
                            {{ number_format($item['gia_ban'] * $item['so_luong'], 0, ',', '.') }}₫
                        </div>
                    </div>
                    @endforeach
                </div>

                @php
                    $tongTienGoc = collect($gioHang)->sum(fn($i) => $i['gia_ban'] * $i['so_luong']);
                    $giamGia     = $diemSuDung * 1000;
                    $tongCuoi    = max(0, $tongTienGoc - $giamGia);
                @endphp

                {{-- Áp điểm tích lũy --}}
                @auth
                @php $diemHienCo = optional($user->khachHang)->diem_tich_luy ?? 0; @endphp
                @if($diemHienCo > 0)
                <div class="points-box" id="pointsBox">
                    <div class="points-box-header">
                        <span>⭐ Điểm tích lũy của bạn: <strong>{{ number_format($diemHienCo) }} điểm</strong></span>
                        <span class="points-equiv">(≡ {{ number_format($diemHienCo * 1000, 0, ',', '.') }}₫)</span>
                    </div>
                    <div class="points-input-row">
                        <input type="number" id="diemNhap" min="0" max="{{ $diemHienCo }}"
                               value="{{ $diemSuDung > 0 ? $diemSuDung : '' }}"
                               placeholder="Nhập số điểm muốn dùng">
                        <button type="button" onclick="apDiem()">Áp dụng</button>
                    </div>
                    <div id="pointsMsg" class="points-msg"></div>
                </div>
                @endif
                @endauth

                <div class="order-totals">
                    <div class="total-row">
                        <span>Tạm tính</span>
                        <span class="val">{{ number_format($tongTienGoc, 0, ',', '.') }}₫</span>
                    </div>
                    <div class="total-row">
                        <span>Vận chuyển</span>
                        <span class="ship-free">Miễn phí</span>
                    </div>
                    <div class="total-row" id="rowGiamGia" style="{{ $diemSuDung > 0 ? '' : 'display:none;' }}">
                        <span id="lblGiamGia">Giảm giá ({{ number_format($diemSuDung) }} điểm)</span>
                        <span class="discount-val" id="valGiamGia">−{{ number_format($diemSuDung * 1000, 0, ',', '.') }}₫</span>
                    </div>
                    <div class="total-row grand">
                        <span><strong>Tổng</strong></span>
                        <span class="val" id="valTongCuoi">{{ number_format($tongCuoi, 0, ',', '.') }}₫</span>
                    </div>
                </div>

                <button type="submit" class="btn-place-order" id="btnOrder">
                    🛍️ Đặt Hàng
                </button>

                <a href="{{ route('customer.gio-hang') }}" class="btn-back-cart">
                    ← Quay lại giỏ hàng
                </a>

                <!-- <div class="privacy-note">
                    Thông tin của bạn được bảo mật an toàn. Bằng việc đặt hàng, bạn đồng ý với điều khoản sử dụng của chúng tôi.
                </div> -->
            </div>

        </div>
    </form>

</div>
@endsection

@section('scripts')
<script>
// ── Quận/Huyện & Xã/Phường ──────────────────────────────────────────────────
const hanoiData = {
    "Ba Đình": ["Phúc Xá","Trúc Bạch","Vĩnh Phúc","Cống Vị","Liễu Giai","Nguyễn Trung Trực","Quán Thánh","Ngọc Hà","Điện Biên","Đội Cấn","Ngọc Khánh","Kim Mã","Giảng Võ","Thành Công"],
    "Hoàn Kiếm": ["Phúc Tân","Đồng Xuân","Hàng Mã","Hàng Buồm","Hàng Đào","Hàng Bồ","Cửa Đông","Lý Thái Tổ","Hàng Bạc","Hàng Gai","Chương Dương","Hàng Trống","Cửa Nam","Hàng Bông","Tràng Tiền","Trần Hưng Đạo","Phan Chu Trinh","Hàng Bài"],
    "Đống Đa": ["Cát Linh","Văn Miếu","Quốc Tử Giám","Láng Thượng","Ô Chợ Dừa","Văn Chương","Hàng Bột","Nam Đồng","Trung Phụng","Trung Liệt","Khâm Thiên","Thịnh Quang","Láng Hạ","Ngã Tư Sở","Phương Liên","Phương Mai","Kim Liên","Trung Tự","Khương Thượng"],
    "Hai Bà Trưng": ["Nguyễn Du","Bùi Thị Xuân","Ngô Thì Nhậm","Lê Đại Hành","Đồng Nhân","Phố Huế","Đống Mác","Thanh Lương","Thanh Nhàn","Cầu Dền","Bách Khoa","Quỳnh Lôi","Bạch Mai","Quỳnh Mai","Vĩnh Tuy","Minh Khai","Trương Định"],
    "Cầu Giấy": ["Nghĩa Đô","Nghĩa Tân","Mai Dịch","Dịch Vọng","Dịch Vọng Hậu","Quan Hoa","Yên Hòa","Trung Hòa"],
    "Thanh Xuân": ["Nhân Chính","Thượng Đình","Khương Trung","Khương Mai","Thanh Xuân Trung","Phương Liệt","Hạ Đình","Thanh Xuân Bắc","Thanh Xuân Nam","Khương Đình","Kim Giang"],
    "Hoàng Mai": ["Thanh Trì","Vĩnh Hưng","Định Công","Mai Động","Tương Mai","Đại Kim","Tân Mai","Hoàng Văn Thụ","Giáp Bát","Lĩnh Nam","Thịnh Liệt","Trần Phú","Hoàng Liệt","Yên Sở"],
    "Long Biên": ["Thượng Thanh","Ngọc Thụy","Giang Biên","Đức Giang","Việt Hưng","Gia Thụy","Ngọc Lâm","Phúc Lợi","Bồ Đề","Sài Đồng","Long Biên","Thạch Bàn","Phúc Đồng","Cự Khối"],
    "Nam Từ Liêm": ["Cầu Diễn","Xuân Phương","Phương Canh","Mỹ Đình 1","Mỹ Đình 2","Tây Mỗ","Mễ Trì","Phú Đô","Đại Mỗ","Trung Văn"],
    "Bắc Từ Liêm": ["Thượng Cát","Liên Mạc","Đông Ngạc","Đức Thắng","Thụy Phương","Tây Tựu","Xuân Đỉnh","Xuân Tảo","Minh Khai","Cổ Nhuế 1","Cổ Nhuế 2","Phú Diễn","Phúc Diễn"],
    "Tây Hồ": ["Phú Thượng","Nhật Tân","Tứ Liên","Quảng An","Xuân La","Yên Phụ","Bưởi","Thụy Khuê"],
    "Hà Đông": ["Nguyễn Trãi","Mỗ Lao","Văn Quán","Vạn Phúc","Yết Kiêu","Quang Trung","La Khê","Phú La","Phúc La","Hà Cầu","Yên Nghĩa","Kiến Hưng","Phú Lương","Dương Nội","Đồng Mai","Biên Giang"],
    "Gia Lâm": ["Yên Viên","Cổ Bi","Đặng Xá","Trâu Quỳ","Dương Xá","Kiêu Kỵ","Ninh Hiệp","Phù Đổng","Lệ Chi","Đông Dư","Bát Tràng","Kim Lan","Văn Đức","Dương Quang","Gia Lâm","Kim Sơn","Phú Thị","Trung Màu","Yên Thường"],
    "Đông Anh": ["Vân Nội","Liên Hà","Việt Hùng","Uy Nỗ","Xuân Nộn","Thụy Lâm","Bắc Hồng","Nguyên Khê","Nam Hồng","Tiên Dương","Vân Hà","Cổ Loa","Hải Bối","Xuân Canh","Võng La","Tầm Xá","Mai Lâm","Đông Hội","Kim Nỗ","Kim Chung","Dục Tú"],
    "Sóc Sơn": ["Sóc Sơn","Bắc Phú","Bắc Sơn","Đông Xuân","Đức Hòa","Hiền Ninh","Hồng Kỳ","Kim Lũ","Minh Phú","Minh Trí","Nam Sơn","Phù Lỗ","Phú Cường","Phú Minh","Quang Tiến","Tân Dân","Tân Hưng","Tiên Dược","Trung Giã","Việt Long","Xuân Giang","Xuân Thu"],
    "Thạch Thất": ["Thạch Thất","Bình Phú","Bình Yên","Cẩm Yên","Canh Nậu","Chàng Sơn","Dị Nậu","Đại Đồng","Hương Ngải","Hữu Bằng","Kim Quan","Lại Thượng","Liên Quan","Phú Kim","Phùng Xá","Tân Xã","Thạch Hòa","Tiến Xuân","Yên Bình","Yên Trung"],
    "Quốc Oai": ["Quốc Oai","Cấn Hữu","Cộng Hòa","Đông Cứu","Đông Yên","Đồng Quang","Hòa Thạch","Liệp Tuyết","Ngọc Liệp","Ngọc Mỹ","Phú Cát","Phú Mãn","Sài Sơn","Tân Hòa","Tân Phú","Thạch Thán","Tuyết Nghĩa","Yên Sơn"],
    "Mê Linh": ["Chi Đông","Chu Phan","Đại Thịnh","Hoàng Kim","Kim Hoa","Liên Mạc","Mê Linh","Tam Đồng","Thạch Đà","Tráng Việt","Tiền Phong","Tự Lập","Văn Khê","Vạn Yên"],
    "Chương Mỹ": ["Chúc Sơn","Đại Yên","Đồng Phú","Đồng Lạc","Hòa Chính","Hoàng Diệu","Hoàng Văn Thụ","Hữu Văn","Lam Điền","Long Sơn","Mỹ Lương","Nam Phương Tiến","Ngọc Hòa","Phú Nam An","Phụng Châu","Quảng Bị","Tốt Động","Thủy Xuân Tiên","Thanh Bình","Trung Hòa","Trường Yên","Xuân Mai"],
};

const selQuan   = document.getElementById('tt_quan_huyen');
const selPhuong = document.getElementById('tt_xa_phuong');

if (selQuan) {
    Object.keys(hanoiData).sort().forEach(q => {
        const opt = document.createElement('option');
        opt.value = q; opt.textContent = q;
        selQuan.appendChild(opt);
    });

    function updateTTPhuong(selectedPhuong) {
        selPhuong.innerHTML = '<option value="">Chọn xã/phường</option>';
        (hanoiData[selQuan.value] || []).forEach(p => {
            const opt = document.createElement('option');
            opt.value = p; opt.textContent = p;
            if (p === selectedPhuong) opt.selected = true;
            selPhuong.appendChild(opt);
        });
    }

    selQuan.addEventListener('change', () => updateTTPhuong(''));

    const initQuan   = "{{ $ttQuan }}";
    const initPhuong = "{{ $ttPhuong }}";
    if (initQuan) {
        selQuan.value = initQuan;
        updateTTPhuong(initPhuong);
    }
}

// ── Phương thức thanh toán ───────────────────────────────────────────────────
function selectPayment(value, el) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type=radio]').checked = true;
    const bankBox = document.getElementById('bankInfoBox');
    if (value === 'NGAN_HANG') bankBox.classList.add('show');
    else bankBox.classList.remove('show');
}

// ── Áp điểm tích lũy ────────────────────────────────────────────────────────
const DIEM_QUY_DOI = 1000;
const tongTienGoc  = {{ collect($gioHang)->sum(fn($i) => $i['gia_ban'] * $i['so_luong']) }};
let   diemDangDung = {{ $diemSuDung }};

function renderTotals(diem) {
    const giamGia  = diem * DIEM_QUY_DOI;
    const tongCuoi = Math.max(0, tongTienGoc - giamGia);

    document.getElementById('rowGiamGia').style.display = diem > 0 ? 'flex' : 'none';
    document.getElementById('valGiamGia').textContent   = '−' + formatVND(giamGia) + '₫';
    document.getElementById('lblGiamGia').textContent   = 'Giảm giá (' + diem.toLocaleString('vi') + ' điểm)';
    document.getElementById('valTongCuoi').textContent  = formatVND(tongCuoi) + '₫';
}

function formatVND(n) {
    return n.toLocaleString('vi-VN');
}

function apDiem() {
    const input    = document.getElementById('diemNhap');
    const msg      = document.getElementById('pointsMsg');
    const diemMax  = parseInt(input.max) || 0;
    let   diem     = parseInt(input.value) || 0;

    if (diem < 0) diem = 0;
    if (diem > diemMax) {
        msg.textContent = `Bạn chỉ có ${diemMax.toLocaleString('vi')} điểm.`;
        msg.className   = 'points-msg err';
        return;
    }

    fetch("{{ route('customer.gio-hang.apply-points') }}", {
        method:  'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content
                         || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ diem })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            diemDangDung = data.diem_su_dung;
            renderTotals(diemDangDung);
            input.value     = diemDangDung > 0 ? diemDangDung : '';
            msg.textContent = diemDangDung > 0
                ? `✓ Đã áp dụng ${diemDangDung.toLocaleString('vi')} điểm – giảm ${formatVND(data.giam_gia)}₫`
                : '✓ Đã bỏ áp dụng điểm.';
            msg.className   = 'points-msg ok';
        } else {
            msg.textContent = data.message || 'Có lỗi xảy ra.';
            msg.className   = 'points-msg err';
        }
    })
    .catch(() => {
        msg.textContent = 'Không thể kết nối. Thử lại sau.';
        msg.className   = 'points-msg err';
    });
}

// Khởi tạo tổng khi tải trang (nếu đã có điểm từ session)
renderTotals(diemDangDung);

// ── Prevent double submit ────────────────────────────────────────────────────
document.getElementById('checkoutForm').addEventListener('submit', function() {
    const btn = document.getElementById('btnOrder');
    btn.disabled    = true;
    btn.textContent = '⏳ Đang xử lý...';
});
</script>
@endsection