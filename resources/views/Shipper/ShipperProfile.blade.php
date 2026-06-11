<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/Shipper/ShipperProfile.css') }}">
    <title>Hồ sơ cá nhân · Shipper</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    {{-- Dùng chung CSS ShipperDashboard nếu có, cộng thêm style inline bên dưới --}}
    <link rel="stylesheet" href="{{ asset('css/Shipper/ShipperDashboard.css') }}">
    
</head>
<body>

{{-- ── Topbar (dùng lại cùng topbar với ShipperDashboard) ── --}}
<div class="topbar">
    <span class="topbar-brand">🌸 FlowerStore · Shipper</span>
    <div style="display:flex; align-items:center; gap:20px;">
        <span class="topbar-status">
            <span class="dot-green"></span>
            Đang hoạt động
        </span>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" style="background:none;border:none;color:#6c757d;cursor:pointer;font-size:14px;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </button>
        </form>
    </div>
</div>

<div class="profile-page">

    <a href="{{ route('shipper.dashboard') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Quay lại Dashboard
    </a>

    <div class="page-title">
        <i class="fas fa-user-circle"></i>
        Hồ sơ cá nhân
    </div>

    {{-- ── Flash messages ── --}}
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

    {{-- ── Tab switcher ── --}}
    <div class="tab-bar">
        <button class="tab-btn {{ $errors->has('mat_khau_cu') || $errors->has('mat_khau_moi') || session('tab') === 'password' ? '' : 'active' }}"
                id="tab-info-btn" onclick="switchTab('info')">
            <i class="fas fa-id-card"></i> Thông tin cá nhân
        </button>
        <button class="tab-btn {{ $errors->has('mat_khau_cu') || $errors->has('mat_khau_moi') || session('tab') === 'password' ? 'active' : '' }}"
                id="tab-pwd-btn" onclick="switchTab('password')">
            <i class="fas fa-lock"></i> Đổi mật khẩu
        </button>
    </div>

    {{-- ══════════════════════════════════════════
         TAB 1: Thông tin cá nhân
    ══════════════════════════════════════════ --}}
    <div class="tab-pane {{ $errors->has('mat_khau_cu') || $errors->has('mat_khau_moi') || session('tab') === 'password' ? '' : 'active' }}"
         id="tab-info">
        <div class="pcard">
            <div class="pcard-header">
                <i class="fas fa-id-card"></i> Thông tin cá nhân
            </div>
            <div class="pcard-body">

                {{-- Lỗi validation form info --}}
                @if($errors->has('ten_nhan_vien') || $errors->has('so_dien_thoai') || $errors->has('email'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul style="margin:0;padding-left:1.2rem;">
                            @foreach(['ten_nhan_vien','so_dien_thoai','email'] as $field)
                                @if($errors->has($field))
                                    <li>{{ $errors->first($field) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('shipper.profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-grid">
                        {{-- Họ tên --}}
                        <div class="fgroup">
                            <label>Họ tên *</label>
                            <input type="text" name="ten_nhan_vien"
                                   value="{{ old('ten_nhan_vien', $shipper->ten_nhan_vien) }}"
                                   class="{{ $errors->has('ten_nhan_vien') ? 'is-invalid' : '' }}"
                                   required>
                            @error('ten_nhan_vien')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Số điện thoại --}}
                        <div class="fgroup">
                            <label>Số điện thoại *</label>
                            <input type="text" name="so_dien_thoai"
                                   value="{{ old('so_dien_thoai', $shipper->so_dien_thoai) }}"
                                   maxlength="10"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                   class="{{ $errors->has('so_dien_thoai') ? 'is-invalid' : '' }}"
                                   required>
                            @error('so_dien_thoai')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="fgroup">
                            <label>Email *</label>
                            <input type="email" name="email"
                                   value="{{ old('email', $shipper->email) }}"
                                   class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                                   required>
                            @error('email')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Chức vụ (readonly) --}}
                        <div class="fgroup">
                            <label>Chức vụ</label>
                            <input type="text" value="{{ $shipper->chuc_vu }}" class="readonly-field" readonly>
                        </div>

                        {{-- Lương (readonly) --}}
                        <div class="fgroup">
                            <label>Lương cơ bản</label>
                            <input type="text" value="{{ number_format($shipper->luong, 0, ',', '.') }} đ" class="readonly-field" readonly>
                        </div>

                        {{-- Công việc (readonly) --}}
                        <div class="fgroup">
                            <label>Mô tả công việc</label>
                            <input type="text" value="{{ $shipper->cong_viec ?? '—' }}" class="readonly-field" readonly>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Lưu thông tin
                    </button>
                </form>

            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         TAB 2: Đổi mật khẩu
    ══════════════════════════════════════════ --}}
    <div class="tab-pane {{ $errors->has('mat_khau_cu') || $errors->has('mat_khau_moi') || session('tab') === 'password' ? 'active' : '' }}"
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

                <form method="POST" action="{{ route('shipper.profile.password') }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-grid" style="max-width:420px;">

                        {{-- Mật khẩu hiện tại --}}
                        <div class="fgroup full">
                            <label>Mật khẩu hiện tại *</label>
                            <div class="pwd-wrap">
                                <input type="password" name="mat_khau_cu" id="mat_khau_cu"
                                       autocomplete="current-password"
                                       inputmode="numeric"
                                       class="{{ $errors->has('mat_khau_cu') ? 'is-invalid' : '' }}"
                                       required>
                                <button type="button" class="pwd-eye" onclick="togglePwd('mat_khau_cu', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('mat_khau_cu')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mật khẩu mới --}}
                        <div class="fgroup full">
                            <label>Mật khẩu mới *</label>
                            <div class="pwd-wrap">
                                <input type="password" name="mat_khau_moi" id="mat_khau_moi"
                                       autocomplete="new-password"
                                       inputmode="numeric"
                                       minlength="6"
                                       oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                       class="{{ $errors->has('mat_khau_moi') ? 'is-invalid' : '' }}"
                                       required>
                                <button type="button" class="pwd-eye" onclick="togglePwd('mat_khau_moi', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('mat_khau_moi')
                                <div class="invalid-msg">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Xác nhận mật khẩu mới --}}
                        <div class="fgroup full">
                            <label>Xác nhận mật khẩu mới *</label>
                            <div class="pwd-wrap">
                                <input type="password" name="mat_khau_moi_confirmation" id="mat_khau_moi_confirmation"
                                       autocomplete="new-password"
                                       inputmode="numeric"
                                       minlength="6"
                                       oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                       required>
                                <button type="button" class="pwd-eye" onclick="togglePwd('mat_khau_moi_confirmation', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-key"></i> Đổi mật khẩu
                    </button>
                </form>

            </div>
        </div>
    </div>

</div>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        document.getElementById('tab-' + tab + '-btn').classList.add('active');
    }

    function togglePwd(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
</script>
</body>
</html>