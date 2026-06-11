<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RoseShop Admin</title>

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
])

@stack('styles')

@vite([
    'resources/css/admin-dashboard.css'
])
</head>

<body>
<div class="admin-layout">

    <aside class="sidebar">
        <div>
            <div class="brand">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" class="brand-logo">
                <div>
                    <h2>RoseShop</h2>
                </div>
            </div>

            <nav class="menu">
                <a href="{{ route('admin.dashboard') }}" 
                 class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    🏠 Tổng quan
                </a>

                @if(Auth::user()->vai_tro === 'ADMIN')
                    <a href="{{ route('nguoi-dung.index') }}" 
                     class="menu-item {{ request()->routeIs('nguoi-dung.*') ? 'active' : '' }}">
                        👥 Quản lý tài khoản
                    </a>

                    <a href="{{ route('khach-hang.index') }}"
                     class="menu-item {{ request()->routeIs('khach-hang.*') ? 'active' : '' }}">
                        👤 Quản lý khách hàng
                    </a>
                @endif

                <a href="{{ route('san-pham.index') }}" 
                 class="menu-item {{ request()->routeIs('san-pham.*') ? 'active' : '' }}">
                    🌷 Quản lý sản phẩm
                </a>

                <a href="{{ route('phieu-nhap.index') }}" 
                 class="menu-item {{ request()->routeIs('phieu-nhap.*') ? 'active' : '' }}">
                    🧾 Quản lý nhập hàng
                </a>

                <a href="{{ route('don-hang.index') }}"
                class="menu-item {{ request()->routeIs('don-hang.*') ? 'active' : '' }}">
                    🛒 Quản lý đơn hàng
                </a>

                <a href="{{ route('hoa-don.index') }}" 
                class="menu-item {{ request()->routeIs('hoa-don.*') ? 'active' : '' }}">
                    📄 Quản lý hóa đơn
                </a>

                @if(Auth::user()->vai_tro === 'ADMIN')
                    <a href="{{ route('nhan-vien.index') }}" 
                    class="menu-item {{ request()->routeIs('nhan-vien.*') ? 'active' : '' }}">
                        🧑‍💼 Quản lý nhân viên
                    </a>

                    <div class="menu-item report-title">
                        📊 Báo cáo thống kê
                    </div>

                    <a href="{{ route('bao-cao.doanh-thu') }}" class="submenu-item {{ request()->routeIs('bao-cao.doanh-thu') ? 'active' : '' }}">
                        📈 Báo cáo doanh thu
                    </a>

                    <a href="{{ route('bao-cao.san-pham') }}" class="submenu-item {{ request()->routeIs('bao-cao.san-pham') ? 'active' : '' }}">
                        📦 Báo cáo sản phẩm
                    </a>
                @endif
            </nav>
        </div>

        <div class="admin-footer">
            <div>
                <p class="admin-name">{{ Auth::user()->ten_dang_nhap }}</p>
                <p class="admin-role">{{ Auth::user()->vai_tro }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Đăng xuất</button>
            </form>
        </div>
    </aside>

    <main class="content admin-page-content">
        @yield('content')
    </main>

    @stack('scripts')
</div>
</body>
</html>