<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shipper') · RoseShop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- CSS riêng của từng trang (link tags) --}}
    @yield('head-styles')

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        /* ── Biến CSS dùng chung cho layout (NhanDon, Profile…) ── */
        :root {
            --brand:       #e75480;
            --brand-light: #fce8ef;
            --brand-dark:  #c73566;
            --navy:        #1a1f36;
            --surface:     #f5f7fb;
            --card:        #ffffff;
            --border:      #e8ecf0;   /* chỉ là màu, dùng với border: 1.5px solid var(--border) */
            --text-main:   #1a1f36;
            --text-muted:  #6c757d;
            --green:       #22c55e;
            --orange:      #f59e0b;
            --blue:        #3b82f6;
            --radius-lg:   18px;
            --radius-md:   12px;
            --radius-sm:   8px;
            --shadow-sm:   0 2px 8px rgba(0,0,0,.06);
            --shadow-md:   0 6px 24px rgba(0,0,0,.09);
            --topbar-h:    58px;
        }

        html, body { height: 100%; margin: 0; }
        body {
            font-family: 'IBM Plex Sans', sans-serif;
            background: var(--surface);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* Topbar cho các trang con (NhanDon, Profile) — không áp dụng cho Dashboard */
        .topbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: var(--topbar-h);
            background: var(--card);
            border-bottom: 1.5px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px;
        }
        .topbar-brand {
            font-family: 'IBM Plex Mono', monospace;
            font-weight: 700; font-size: 15px;
            color: var(--brand);
            letter-spacing: -.3px;
        }
        .topbar-right {
            display: flex; align-items: center; gap: 18px;
        }
        .topbar-status {
            display: flex; align-items: center; gap: 7px;
            font-size: 13px; font-weight: 500; color: var(--text-muted);
        }
        .dot-green {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 0 3px rgba(34,197,94,.18);
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0%,100% { box-shadow: 0 0 0 3px rgba(34,197,94,.18); }
            50%      { box-shadow: 0 0 0 6px rgba(34,197,94,.08); }
        }
        .btn-logout {
            background: none; border: none;
            color: var(--text-muted); cursor: pointer;
            font-size: 13px; font-weight: 500;
            display: flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: var(--radius-sm);
            transition: background .15s, color .15s;
        }
        .btn-logout:hover { background: var(--surface); color: var(--text-main); }

        .page-wrap {
            padding-top: var(--topbar-h);
            min-height: 100vh;
        }

        #toast {
            position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%);
            padding: 12px 24px; border-radius: 40px;
            font-size: 14px; font-weight: 500;
            background: var(--navy); color: #fff;
            opacity: 0; pointer-events: none;
            transition: opacity .3s; z-index: 9999;
            max-width: 90vw; text-align: center;
        }
        #toast.show { opacity: 1; }
        #toast.success { background: #16a34a; }
        #toast.error   { background: #dc2626; }
    </style>

    {{-- CSS inline tùy chỉnh của từng trang (style blocks) --}}
    @yield('extra-styles')

    @yield('head')
</head>
<body>

{{-- ── Topbar ──────────────────────────────────────────────── --}}
<header class="topbar">
    <span class="topbar-brand">🌸 FlowerStore · Shipper</span>
    <div class="topbar-right">
        <span class="topbar-status">
            <span class="dot-green"></span> Đang hoạt động
        </span>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </button>
        </form>
    </div>
</header>

{{-- ── Main content ─────────────────────────────────────────── --}}
<div class="page-wrap">
    @yield('content')
</div>

<div id="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

{{-- Shared toast helper --}}
<script>
function showToast(msg, type = '') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'show ' + type;
    setTimeout(() => { el.className = ''; }, 3200);
}
const _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
</script>

@yield('scripts')
</body>
</html>