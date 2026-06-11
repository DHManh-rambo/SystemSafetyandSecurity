<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoseShop - Đăng ký tài khoản</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-white to-rose-50 min-h-screen flex items-center justify-center py-10 px-4">

<div class="w-full max-w-xl bg-white rounded-2xl shadow-lg p-8">
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-rose-600">Đăng ký tài khoản</h1>
        </div>

    {{-- Lỗi chung --}}
    @if ($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-5 text-sm text-red-700 space-y-1">
        @foreach ($errors->all() as $error)
            <p>• {{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        {{-- Tên đăng nhập --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tên đăng nhập <span class="text-red-500">*</span>
            </label>
            <input type="text" name="ten_dang_nhap" value="{{ old('ten_dang_nhap') }}"
                required autocomplete="off"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300
                       @error('ten_dang_nhap') border-red-400 @enderror"
                placeholder="Ví dụ: nguyenvana">
        </div>

        {{-- Họ tên --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Họ và tên <span class="text-red-500">*</span>
            </label>
            <input type="text" name="ten_khach_hang" value="{{ old('ten_khach_hang') }}"
                required
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300
                       @error('ten_khach_hang') border-red-400 @enderror"
                placeholder="Ví dụ: Nguyễn Văn A">
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" value="{{ old('email') }}"
                required
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300
                       @error('email') border-red-400 @enderror"
                placeholder="example@email.com">
        </div>

        {{-- Số điện thoại --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Số điện thoại <span class="text-red-500">*</span>
            </label>
            <input type="text" name="so_dien_thoai" value="{{ old('so_dien_thoai') }}"
                required maxlength="10" inputmode="numeric"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300
                       @error('so_dien_thoai') border-red-400 @enderror"
                placeholder="0xxxxxxxxx">
        </div>

        {{-- ── ĐỊA CHỈ ── --}}
        <div class="border border-gray-200 rounded-xl p-4 space-y-3 bg-gray-50">
            <p class="text-sm font-semibold text-gray-700">Địa chỉ giao hàng</p>

            {{-- Tỉnh / Thành phố (cố định Hà Nội) --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tỉnh/Thành phố <span class="text-red-500">*</span></label>
                <input type="text" value="Hà Nội" readonly
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-500 cursor-not-allowed">
            </div>

            {{-- Quận / Huyện + Xã / Phường --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Quận/Huyện <span class="text-red-500">*</span></label>
                    <select id="quan_huyen" name="quan_huyen" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300
                               @error('quan_huyen') border-red-400 @enderror">
                        <option value="">Chọn quận huyện</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Xã/Phường <span class="text-red-500">*</span></label>
                    <select id="xa_phuong" name="xa_phuong" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300
                               @error('xa_phuong') border-red-400 @enderror">
                        <option value="">Chọn xã/phường</option>
                    </select>
                </div>
            </div>

            {{-- Địa chỉ chi tiết --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Địa chỉ chi tiết <span class="text-red-500">*</span></label>
                <input type="text" name="dia_chi_chi_tiet" value="{{ old('dia_chi_chi_tiet') }}"
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300"
                    placeholder="Ví dụ: Số 20, ngõ 90">
            </div>
        </div>

        {{-- Mật khẩu --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Mật khẩu <span class="text-red-500">*</span>
            </label>
            <input type="password" name="mat_khau" required minlength="6"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300
                       @error('mat_khau') border-red-400 @enderror"
                placeholder="Ít nhất 6 ký tự">
        </div>

        {{-- Xác nhận mật khẩu --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Xác nhận mật khẩu <span class="text-red-500">*</span>
            </label>
            <input type="password" name="mat_khau_confirmation" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                placeholder="Nhập lại mật khẩu">
        </div>

        <button type="submit"
            class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
            Đăng ký
        </button>

        <p class="text-center text-sm text-gray-500">
            Đã có tài khoản?
            <a href="{{ route('login') }}" class="text-rose-600 hover:underline font-medium">Đăng nhập</a>
        </p>
    </form>
</div>

<script>
// ── Dữ liệu quận/huyện và phường/xã của Hà Nội ──────────────────────────────
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

const selQuan  = document.getElementById('quan_huyen');
const selPhuong = document.getElementById('xa_phuong');

// Điền danh sách quận/huyện
Object.keys(hanoiData).sort().forEach(q => {
    const opt = document.createElement('option');
    opt.value = q; opt.textContent = q;
    selQuan.appendChild(opt);
});

// Khi chọn quận → điền phường
selQuan.addEventListener('change', () => {
    selPhuong.innerHTML = '<option value="">Chọn xã/phường</option>';
    const wards = hanoiData[selQuan.value] || [];
    wards.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p; opt.textContent = p;
        selPhuong.appendChild(opt);
    });
});

// Khôi phục old value nếu có
const oldQuan  = "{{ old('quan_huyen') }}";
const oldPhuong = "{{ old('xa_phuong') }}";
if (oldQuan) {
    selQuan.value = oldQuan;
    selQuan.dispatchEvent(new Event('change'));
    setTimeout(() => { selPhuong.value = oldPhuong; }, 0);
}
</script>
</body>
</html>