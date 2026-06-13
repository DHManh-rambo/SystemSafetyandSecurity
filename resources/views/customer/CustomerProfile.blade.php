<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/Customer/Profile.css') }}">
    <title>Hồ sơ cá nhân · Cửa Hàng Hoa</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Be+Vietnam+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body>

{{-- TOPBAR --}}
<div class="topbar">
    <span class="topbar-brand">🌸 RoseShop</span>
    <div class="topbar-right">
        <span><i class="fas fa-user"></i> {{ $user->ten_dang_nhap }}</span>
        <form class="logout-form" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"><i class="fas fa-sign-out-alt"></i> Đăng xuất</button>
        </form>
    </div>
</div>

<div class="profile-page">

    <a href="{{ route('customer.dashboard') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Quay lại cửa hàng
    </a>

    <div class="page-title">
        <i class="fas fa-user-circle"></i> Hồ sơ cá nhân
    </div>

    {{-- Flash --}}
    @if(session('success_info'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success_info') }}
        </div>
    @endif
    @if(session('success_password'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success_password') }}
        </div>
    @endif

    {{-- TABS --}}
    <div class="tab-bar">
        <button class="tab-btn {{ ($errors->has('mat_khau_cu') || $errors->has('mat_khau_moi') || session('tab') === 'password') ? '' : 'active' }}"
                id="tab-info-btn" onclick="switchTab('info')">
            <i class="fas fa-id-card"></i> Thông tin cá nhân
        </button>
        <button class="tab-btn {{ ($errors->has('mat_khau_cu') || $errors->has('mat_khau_moi') || session('tab') === 'password') ? 'active' : '' }}"
                id="tab-password-btn" onclick="switchTab('password')">
            <i class="fas fa-lock"></i> Đổi mật khẩu
        </button>
    </div>

    {{-- ════════════════════════════════════
         TAB 1: Thông tin cá nhân
    ════════════════════════════════════ --}}
    <div class="tab-pane {{ ($errors->has('mat_khau_cu') || $errors->has('mat_khau_moi') || session('tab') === 'password') ? '' : 'active' }}"
         id="tab-info">
        <div class="pcard">
            <div class="pcard-header">
                <i class="fas fa-id-card"></i> Thông tin cá nhân
            </div>
            <div class="pcard-body">

                {{-- Lỗi validation thông tin --}}
                @if($errors->has('ten_khach_hang') || $errors->has('so_dien_thoai') || $errors->has('email') || $errors->has('quan_huyen') || $errors->has('xa_phuong') || $errors->has('dia_chi_chi_tiet'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul style="margin:0;padding-left:1.2rem;">
                            @foreach(['ten_khach_hang','so_dien_thoai','email','quan_huyen','xa_phuong','dia_chi_chi_tiet'] as $field)
                                @if($errors->has($field))
                                    <li>{{ $errors->first($field) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    {{--
                        Phân tách địa chỉ hiện tại từ DB để điền lại vào các ô select.
                        Định dạng lưu: "Số nhà, Xã/Phường, Quận/Huyện, Hà Nội"
                        → tách theo dấu phẩy từ cuối lên.
                    --}}
                    @php
                        $diaChiHienTai   = $khachHang->dia_chi ?? '';
                        $parts           = array_map('trim', explode(',', $diaChiHienTai));
                        // Bỏ "Hà Nội" ở cuối nếu có
                        if (count($parts) >= 1 && strtolower(end($parts)) === 'hà nội') array_pop($parts);
                        $savedQuan       = old('quan_huyen',      count($parts) >= 2 ? $parts[count($parts)-1] : '');
                        $savedPhuong     = old('xa_phuong',       count($parts) >= 3 ? $parts[count($parts)-2] : '');
                        $savedChiTiet    = old('dia_chi_chi_tiet', count($parts) >= 1 ? implode(', ', array_slice($parts, 0, count($parts)-2)) : $diaChiHienTai);
                    @endphp

                    <div class="form-grid">
                        <!-- Avatar Section -->
                        <div class="fgroup span2" style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px; padding: 15px; background: rgba(232, 67, 106, 0.05); border-radius: 12px; border: 1px dashed rgba(232, 67, 106, 0.2);">
                            <div class="avatar-preview-container" style="position: relative; width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background: #f3f4f6; border: 2px solid var(--primary); display: flex; align-items: center; justify-content: center;">
                                @if($khachHang->anh_dai_dien && file_exists(public_path($khachHang->anh_dai_dien)))
                                    <img src="{{ asset($khachHang->anh_dai_dien) }}" id="avatar-preview" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div id="avatar-placeholder" style="font-size: 2rem; color: var(--gray);">👤</div>
                                    <img id="avatar-preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                @endif
                            </div>
                            <div class="avatar-upload-inputs" style="flex: 1;">
                                <label style="font-weight: 600; display: block; margin-bottom: 8px; font-size: 0.82rem; color: var(--primary-dark);"><i class="fas fa-camera"></i> Ảnh đại diện (Avatar)</label>
                                <input type="file" name="anh_dai_dien" id="anh_dai_dien_input" accept="image/*" style="font-size: 0.85rem;" onchange="previewImage(this)">
                                <span style="display: block; font-size: 0.75rem; color: var(--gray); margin-top: 4px;">Chấp nhận file ảnh: JPG, JPEG, PNG, GIF, SVG (Tối đa 2MB).</span>
                            </div>
                        </div>

                        {{-- Tên đăng nhập (readonly) --}}
                        <div class="fgroup">
                            <label><i class="fas fa-at" style="font-size:.7rem;"></i> Tên đăng nhập</label>
                            <input type="text" value="{{ $user->ten_dang_nhap }}" class="readonly-field" readonly>
                        </div>

                        {{-- Họ tên --}}
                        <div class="fgroup">
                            <label><i class="fas fa-user" style="font-size:.7rem;"></i> Họ tên *</label>
                            <input type="text" name="ten_khach_hang"
                                   value="{{ old('ten_khach_hang', $khachHang->ten_khach_hang) }}"
                                   class="{{ $errors->has('ten_khach_hang') ? 'is-invalid' : '' }}"
                                   placeholder="Nhập họ và tên"
                                   required>
                            @error('ten_khach_hang')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Số điện thoại --}}
                        <div class="fgroup">
                            <label><i class="fas fa-phone" style="font-size:.7rem;"></i> Số điện thoại *</label>
                            <input type="text" name="so_dien_thoai"
                                   value="{{ old('so_dien_thoai', $khachHang->so_dien_thoai) }}"
                                   maxlength="10"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                   placeholder="0xxxxxxxxx"
                                   class="{{ $errors->has('so_dien_thoai') ? 'is-invalid' : '' }}"
                                   required>
                            @error('so_dien_thoai')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="fgroup">
                            <label><i class="fas fa-envelope" style="font-size:.7rem;"></i> Email *</label>
                            <input type="email" name="email"
                                   value="{{ old('email', $khachHang->email) }}"
                                   placeholder="example@email.com"
                                   class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                                   required>
                            @error('email')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ── KHỐI ĐỊA CHỈ ── --}}
                        <div class="address-box">
                            <div class="address-box-title">
                                <i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng
                            </div>

                            {{-- Tỉnh / Thành phố cố định --}}
                            <div class="fgroup">
                                <label>Tỉnh / Thành phố *</label>
                                <input type="text" value="Hà Nội" class="readonly-field" readonly>
                            </div>

                            {{-- Quận / Huyện + Xã / Phường --}}
                            <div class="address-row">
                                <div class="fgroup">
                                    <label>Quận / Huyện *</label>
                                    <select id="sel_quan_huyen" name="quan_huyen"
                                            class="{{ $errors->has('quan_huyen') ? 'is-invalid' : '' }}"
                                            required>
                                        <option value="">Chọn quận/huyện</option>
                                    </select>
                                    @error('quan_huyen')
                                        <div class="invalid-msg">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="fgroup">
                                    <label>Xã / Phường *</label>
                                    <select id="sel_xa_phuong" name="xa_phuong"
                                            class="{{ $errors->has('xa_phuong') ? 'is-invalid' : '' }}"
                                            required>
                                        <option value="">Chọn xã/phường</option>
                                    </select>
                                    @error('xa_phuong')
                                        <div class="invalid-msg">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Địa chỉ chi tiết --}}
                            <div class="fgroup">
                                <label>Địa chỉ chi tiết *</label>
                                <input type="text" name="dia_chi_chi_tiet"
                                       value="{{ $savedChiTiet }}"
                                       placeholder="Ví dụ: Số 20, ngõ 90"
                                       class="{{ $errors->has('dia_chi_chi_tiet') ? 'is-invalid' : '' }}"
                                       required>
                                @error('dia_chi_chi_tiet')
                                    <div class="invalid-msg">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        {{-- ── KẾT THÚC KHỐI ĐỊA CHỈ ── --}}

                        {{-- Điểm tích lũy (readonly, badge) --}}
                        <div class="fgroup">
                            <label><i class="fas fa-star" style="font-size:.7rem;"></i> Điểm tích lũy</label>
                            <div class="diem-badge">
                                <i class="fas fa-coins"></i>
                                <span class="diem-val">{{ number_format($khachHang->diem_tich_luy) }}</span>
                                <span style="font-weight:400;font-size:.78rem;">điểm</span>
                            </div>
                        </div>

                        {{-- Vai trò (readonly) --}}
                        <div class="fgroup">
                            <label><i class="fas fa-shield-alt" style="font-size:.7rem;"></i> Loại tài khoản</label>
                            <input type="text" value="Khách hàng" class="readonly-field" readonly>
                        </div>
                    </div>

                    <div class="btn-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Lưu thông tin
                        </button>
                        <button type="button" class="btn-cancel" id="btn-cancel-info"
                                style="display:none;" onclick="resetInfoForm()">
                            <i class="fas fa-undo"></i> Hủy thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════
         TAB 2: Đổi mật khẩu
    ════════════════════════════════════ --}}
    <div class="tab-pane {{ ($errors->has('mat_khau_cu') || $errors->has('mat_khau_moi') || session('tab') === 'password') ? 'active' : '' }}"
         id="tab-password">
        <div class="pcard">
            <div class="pcard-header">
                <i class="fas fa-lock"></i> Đổi mật khẩu
            </div>
            <div class="pcard-body">

                @if($errors->has('mat_khau_cu') || $errors->has('mat_khau_moi'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul style="margin:0;padding-left:1.2rem;">
                            @foreach(['mat_khau_cu','mat_khau_moi'] as $field)
                                @if($errors->has($field))
                                    <li>{{ $errors->first($field) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.profile.password') }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-grid" style="max-width:440px; grid-template-columns:1fr;">

                        {{-- Mật khẩu hiện tại --}}
                        <div class="fgroup">
                            <label>Mật khẩu hiện tại *</label>
                            <div class="pwd-wrap">
                                <input type="password" name="mat_khau_cu" id="mat_khau_cu"
                                       autocomplete="current-password"
                                       placeholder="Nhập mật khẩu hiện tại"
                                       class="{{ $errors->has('mat_khau_cu') ? 'is-invalid' : '' }}"
                                       required>
                                <button type="button" class="pwd-eye" onclick="togglePwd('mat_khau_cu',this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('mat_khau_cu')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mật khẩu mới --}}
                        <div class="fgroup">
                            <label>Mật khẩu mới * <span style="color:var(--gray);font-weight:400;">(ít nhất 6 ký tự)</span></label>
                            <div class="pwd-wrap">
                                <input type="password" name="mat_khau_moi" id="mat_khau_moi"
                                       autocomplete="new-password"
                                       placeholder="Tối thiểu 6 ký tự"
                                       minlength="6"
                                       oninput="checkStrength(this.value)"
                                       class="{{ $errors->has('mat_khau_moi') ? 'is-invalid' : '' }}"
                                       required>
                                <button type="button" class="pwd-eye" onclick="togglePwd('mat_khau_moi',this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            {{-- Strength meter --}}
                            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                            <div class="strength-label" id="strengthLabel"></div>
                            @error('mat_khau_moi')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Xác nhận mật khẩu mới --}}
                        <div class="fgroup">
                            <label>Xác nhận mật khẩu mới *</label>
                            <div class="pwd-wrap">
                                <input type="password" name="mat_khau_moi_confirmation" id="mat_khau_moi_confirmation"
                                       autocomplete="new-password"
                                       placeholder="Nhập lại mật khẩu mới"
                                       minlength="6"
                                       oninput="checkMatch()"
                                       required>
                                <button type="button" class="pwd-eye" onclick="togglePwd('mat_khau_moi_confirmation',this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="strength-label" id="matchLabel"></div>
                        </div>
                    </div>

                    <div class="btn-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-key"></i> Đổi mật khẩu
                        </button>
                        <button type="button" class="btn-cancel" id="btn-cancel-pwd"
                                style="display:none;" onclick="resetPwdForm()">
                            <i class="fas fa-undo"></i> Hủy thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
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

const selQuan   = document.getElementById('sel_quan_huyen');
const selPhuong = document.getElementById('sel_xa_phuong');

Object.keys(hanoiData).sort().forEach(q => {
    const opt = document.createElement('option');
    opt.value = q; opt.textContent = q;
    selQuan.appendChild(opt);
});

function updatePhuong(selectedPhuong) {
    selPhuong.innerHTML = '<option value="">Chọn xã/phường</option>';
    const wards = hanoiData[selQuan.value] || [];
    wards.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p; opt.textContent = p;
        if (p === selectedPhuong) opt.selected = true;
        selPhuong.appendChild(opt);
    });
}

selQuan.addEventListener('change', () => updatePhuong(''));

const savedQuan   = "{{ $savedQuan }}";
const savedPhuong = "{{ $savedPhuong }}";
if (savedQuan) {
    selQuan.value = savedQuan;
    updatePhuong(savedPhuong);
}

const originalInfo = {
    ten_khach_hang:   "{{ addslashes($khachHang->ten_khach_hang) }}",
    so_dien_thoai:    "{{ addslashes($khachHang->so_dien_thoai) }}",
    email:            "{{ addslashes($khachHang->email) }}",
    quan_huyen:       savedQuan,
    xa_phuong:        savedPhuong,
    dia_chi_chi_tiet: "{{ addslashes($savedChiTiet) }}",
};

function checkInfoDirty() {
    const form   = document.querySelector('#tab-info form');
    const fields = ['ten_khach_hang','so_dien_thoai','email','dia_chi_chi_tiet'];
    const dirty  = fields.some(f => form[f] && form[f].value !== originalInfo[f])
                || selQuan.value   !== originalInfo.quan_huyen
                || selPhuong.value !== originalInfo.xa_phuong;
    document.getElementById('btn-cancel-info').style.display = dirty ? 'inline-flex' : 'none';
}

function resetInfoForm() {
    const form = document.querySelector('#tab-info form');
    form.ten_khach_hang.value   = originalInfo.ten_khach_hang;
    form.so_dien_thoai.value    = originalInfo.so_dien_thoai;
    form.email.value            = originalInfo.email;
    form.dia_chi_chi_tiet.value = originalInfo.dia_chi_chi_tiet;
    selQuan.value = originalInfo.quan_huyen;
    updatePhuong(originalInfo.xa_phuong);
    document.getElementById('btn-cancel-info').style.display = 'none';
}

document.querySelectorAll('#tab-info input:not([readonly]), #tab-info select').forEach(el => {
    el.addEventListener('input',  checkInfoDirty);
    el.addEventListener('change', checkInfoDirty);
});

function checkPwdDirty() {
    const hasCu   = document.getElementById('mat_khau_cu').value.length > 0;
    const hasMoi  = document.getElementById('mat_khau_moi').value.length > 0;
    const hasConf = document.getElementById('mat_khau_moi_confirmation').value.length > 0;
    document.getElementById('btn-cancel-pwd').style.display =
        (hasCu || hasMoi || hasConf) ? 'inline-flex' : 'none';
}

function resetPwdForm() {
    document.getElementById('mat_khau_cu').value                    = '';
    document.getElementById('mat_khau_moi').value                   = '';
    document.getElementById('mat_khau_moi_confirmation').value      = '';
    document.getElementById('strengthFill').style.width      = '0%';
    document.getElementById('strengthFill').style.background = '';
    document.getElementById('strengthLabel').textContent     = '';
    document.getElementById('matchLabel').textContent        = '';
    document.getElementById('btn-cancel-pwd').style.display  = 'none';
}

document.querySelectorAll('#tab-password input[type=password]').forEach(el => {
    el.addEventListener('input', checkPwdDirty);
});
function switchTab(tab) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('tab-' + tab + '-btn').classList.add('active');
}

// ── Password helpers ──────────────────────────────────────────────────────────
function togglePwd(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function checkStrength(val) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { w: '0%',   c: '',         t: '' },
        { w: '25%',  c: '#ef4444',  t: 'Rất yếu' },
        { w: '50%',  c: '#f97316',  t: 'Yếu' },
        { w: '75%',  c: '#f59e0b',  t: 'Trung bình' },
        { w: '90%',  c: '#22c55e',  t: 'Mạnh' },
        { w: '100%', c: '#16a34a',  t: 'Rất mạnh 🔒' },
    ];

    const lvl = val.length === 0 ? levels[0] : levels[Math.min(score, 5)];
    fill.style.width      = lvl.w;
    fill.style.background = lvl.c;
    label.textContent     = lvl.t;
    label.style.color     = lvl.c;

    checkMatch();
}

function checkMatch() {
    const moi   = document.getElementById('mat_khau_moi').value;
    const conf  = document.getElementById('mat_khau_moi_confirmation').value;
    const label = document.getElementById('matchLabel');
    if (!conf) { label.textContent = ''; return; }
    if (moi === conf) {
        label.textContent = '✓ Mật khẩu khớp';
        label.style.color = 'var(--green)';
    } else {
        label.textContent = '✗ Mật khẩu chưa khớp';
        label.style.color = 'var(--red)';
    }
}

function previewImage(input) {
    const file = input.files[0];
    const preview = document.getElementById('avatar-preview');
    const placeholder = document.getElementById('avatar-placeholder');
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
}
</script>
</body>
</html>