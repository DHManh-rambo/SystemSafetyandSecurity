<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/Customer/ThongBao.css') }}">
    <title>Thông báo & Đơn hàng · RoseShop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body>

<header class="topbar">
    <span class="topbar-brand">🌸 RoseShop</span>
    <a href="{{ route('customer.dashboard') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Quay lại cửa hàng
    </a>
</header>

<div class="page-wrap">

    <div class="page-title">
        <i class="fas fa-bell" style="color:var(--brand)"></i>
        Thông báo & Đơn hàng
    </div>

    {{-- TAB BAR --}}
    <div class="tab-bar">
        <button class="tab-btn {{ $activeTab === 'thong-bao' ? 'active' : '' }}"
                id="tab-thong-bao-btn" onclick="switchTab('thong-bao')">
            <i class="fas fa-bell"></i> Thông báo
            @if($soThongBao > 0)
                <span class="tab-badge">{{ $soThongBao }}</span>
            @endif
        </button>
        <button class="tab-btn {{ $activeTab === 'don-hien-tai' ? 'active' : '' }}"
                id="tab-don-hien-tai-btn" onclick="switchTab('don-hien-tai')">
            <i class="fas fa-truck"></i> Đơn hiện tại
            @if($soDonHienTai > 0)
                <span class="tab-badge">{{ $soDonHienTai }}</span>
            @endif
        </button>
        <button class="tab-btn {{ $activeTab === 'lich-su' ? 'active' : '' }}"
                id="tab-lich-su-btn" onclick="switchTab('lich-su')">
            <i class="fas fa-history"></i> Lịch sử
        </button>
    </div>

    {{-- ════════════════════════════════════
         TAB 1: THÔNG BÁO
    ════════════════════════════════════ --}}
    <div class="tab-pane {{ $activeTab === 'thong-bao' ? 'active' : '' }}" id="tab-thong-bao">
        <p style="font-size:.82rem; color:var(--muted); margin-bottom:16px;">
            Ấn <strong>×</strong> để xóa thông báo sau khi đã nhận hàng.
        </p>

        <div id="notif-list">
            @forelse($thongBaos as $tb)
                @php
                    $loai = $tb['loai'] ?? '';
                    $notifConfig = match($loai) {
                        'shipping' => [
                            'label'      => 'Đơn hàng bắt đầu giao',
                            'icon'       => 'fa-motorcycle',
                            'border'     => '#3b82f6',
                            'icon_bg'    => '#eff6ff',
                            'icon_color' => '#3b82f6',
                        ],
                        'confirmed' => [
                            'label'      => 'Đơn hàng đã được xác nhận',
                            'icon'       => 'fa-check-circle',
                            'border'     => '#22c55e',
                            'icon_bg'    => '#f0fdf4',
                            'icon_color' => '#16a34a',
                        ],
                        'cancelled' => [
                            'label'      => 'Đơn hàng bị từ chối',
                            'icon'       => 'fa-times-circle',
                            'border'     => '#ef4444',
                            'icon_bg'    => '#fef2f2',
                            'icon_color' => '#dc2626',
                        ],
                        'apology' => [
                            'label'      => 'Xin lỗi về sự chậm trễ',
                            'icon'       => 'fa-heart',
                            'border'     => '#f59e0b',
                            'icon_bg'    => '#fffbeb',
                            'icon_color' => '#f59e0b',
                        ],
                        default => [
                            'label'      => 'Shipper đã đến nơi',
                            'icon'       => 'fa-map-marker-alt',
                            'border'     => '#e75480',
                            'icon_bg'    => '#fce8ef',
                            'icon_color' => '#e75480',
                        ],
                    };
                @endphp
                <div class="notif-card" id="notif-{{ $tb['id'] }}"
                     style="border-left: 3px solid {{ $notifConfig['border'] }};">
                    <div class="notif-icon"
                         style="background:{{ $notifConfig['icon_bg'] }}; color:{{ $notifConfig['icon_color'] }};">
                        <i class="fas {{ $notifConfig['icon'] }}"></i>
                    </div>
                    <div class="notif-body">
                        <div class="notif-title" style="color:{{ $notifConfig['icon_color'] }};">
                            <i class="fas {{ $notifConfig['icon'] }}"></i> {{ $notifConfig['label'] }}
                        </div>
                        <div class="notif-content">{{ $tb['noi_dung'] }}</div>
                        <div class="notif-time">
                            <i class="fas fa-clock"></i> {{ $tb['thoi_gian'] }}
                        </div>
                    </div>
                    <button class="btn-close-notif"
                            onclick="xoaThongBao('{{ $tb['id'] }}')"
                            title="Đóng thông báo">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @empty
                <div class="empty-state" id="empty-notif">
                    <i class="fas fa-bell-slash"></i>
                    <p>Không có thông báo nào</p>
                    <small>Khi shipper đến nơi giao, bạn sẽ thấy thông báo ở đây.</small>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ════════════════════════════════════
         TAB 2: ĐƠN HÀNG HIỆN TẠI
    ════════════════════════════════════ --}}
    <div class="tab-pane {{ $activeTab === 'don-hien-tai' ? 'active' : '' }}" id="tab-don-hien-tai">

        {{-- Filter chips --}}
        <div class="filter-bar">
            <button class="filter-chip active" data-filter="all" onclick="filterDon(this, 'all')">
                Tất cả
            </button>
            <button class="filter-chip" data-filter="PENDING" onclick="filterDon(this, 'PENDING')">
                <i class="fas fa-clock"></i> Chờ xử lý
            </button>
            <button class="filter-chip" data-filter="CONFIRMED" onclick="filterDon(this, 'CONFIRMED')">
                <i class="fas fa-check"></i> Đã xác nhận
            </button>
            <button class="filter-chip" data-filter="SHIPPING" onclick="filterDon(this, 'SHIPPING')">
                <i class="fas fa-motorcycle"></i> Đang giao
            </button>
        </div>

        <div id="don-hien-tai-list">
            @forelse($donHienTai as $hd)
                @php
                    $stMap = [
                        'PENDING'   => ['Chờ xử lý',    'pill-pending',   'fa-clock'],
                        'CONFIRMED' => ['Đã xác nhận',   'pill-confirmed', 'fa-check-circle'],
                        'SHIPPING'  => ['Đang giao',     'pill-shipping',  'fa-motorcycle'],
                    ];
                    $st = $stMap[$hd->trang_thai] ?? [$hd->trang_thai, 'pill-pending', 'fa-circle'];
                @endphp
                <div class="order-card" id="don-{{ $hd->ma_hoa_don }}" data-status="{{ $hd->trang_thai }}">
                    <div class="order-card-header" onclick="toggleOrder('don-{{ $hd->ma_hoa_don }}')">
                        <div>
                            <div class="order-id">
                                <i class="fas fa-receipt" style="font-size:.8rem"></i>
                                #HD-{{ str_pad($hd->ma_hoa_don, 4, '0', STR_PAD_LEFT) }}
                                <span class="pill {{ $st[1] }}">
                                    <i class="fas {{ $st[2] }}"></i> {{ $st[0] }}
                                </span>
                            </div>
                            <div class="order-date">
                                <i class="fas fa-calendar-alt"></i>
                                {{ optional($hd->ngay_dat)->format('d/m/Y H:i') ?? '—' }}
                            </div>
                        </div>
                        <div class="order-header-right">
                            <span class="order-total" style="color:var(--brand)">
                                {{ number_format($hd->tong_tien, 0, ',', '.') }} đ
                            </span>
                            <i class="fas fa-chevron-down chevron"></i>
                        </div>
                    </div>

                    <div class="order-detail">
                        <div class="order-detail-inner">

                            {{-- Thanh tiến trình --}}
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="fas fa-route"></i> Tiến trình đơn hàng
                                </div>
                                <div class="progress-steps">
                                    {{-- Bước 1: Đặt hàng --}}
                                    <div class="step">
                                        <div class="step-dot done"><i class="fas fa-check"></i></div>
                                        <div class="step-lbl done">Đặt<br>hàng</div>
                                    </div>
                                    <div class="step-line {{ in_array($hd->trang_thai, ['CONFIRMED','SHIPPING','DELIVERED']) ? 'done' : '' }}"></div>

                                    {{-- Bước 2: Xác nhận --}}
                                    <div class="step">
                                        @php $confirmed = in_array($hd->trang_thai, ['CONFIRMED','SHIPPING','DELIVERED']); @endphp
                                        <div class="step-dot {{ $confirmed ? 'done' : ($hd->trang_thai === 'PENDING' ? 'active' : '') }}">
                                            @if($confirmed)
                                                <i class="fas fa-check"></i>
                                            @else
                                                <i class="fas fa-clipboard-check"></i>
                                            @endif
                                        </div>
                                        <div class="step-lbl {{ $confirmed ? 'done' : ($hd->trang_thai === 'PENDING' ? 'active' : '') }}">
                                            Xác<br>nhận
                                        </div>
                                    </div>
                                    <div class="step-line {{ in_array($hd->trang_thai, ['SHIPPING','DELIVERED']) ? 'done' : '' }}"></div>

                                    {{-- Bước 3: Đang giao --}}
                                    <div class="step">
                                        @php $shipping = in_array($hd->trang_thai, ['SHIPPING','DELIVERED']); @endphp
                                        <div class="step-dot {{ $shipping ? ($hd->trang_thai === 'SHIPPING' ? 'active' : 'done') : '' }}">
                                            @if($hd->trang_thai === 'DELIVERED')
                                                <i class="fas fa-check"></i>
                                            @else
                                                <i class="fas fa-motorcycle"></i>
                                            @endif
                                        </div>
                                        <div class="step-lbl {{ $shipping ? ($hd->trang_thai === 'SHIPPING' ? 'active' : 'done') : '' }}">
                                            Đang<br>giao
                                        </div>
                                    </div>
                                    <div class="step-line {{ $hd->trang_thai === 'DELIVERED' ? 'done' : '' }}"></div>

                                    {{-- Bước 4: Hoàn thành --}}
                                    <div class="step">
                                        <div class="step-dot {{ $hd->trang_thai === 'DELIVERED' ? 'done' : '' }}">
                                            @if($hd->trang_thai === 'DELIVERED')
                                                <i class="fas fa-check"></i>
                                            @else
                                                <i class="fas fa-flag-checkered"></i>
                                            @endif
                                        </div>
                                        <div class="step-lbl {{ $hd->trang_thai === 'DELIVERED' ? 'done' : '' }}">
                                            Hoàn<br>thành
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Thông tin giao hàng --}}
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="fas fa-map-marker-alt"></i> Thông tin giao hàng
                                </div>
                                <div class="info-row">
                                    <span class="info-lbl">Địa chỉ</span>
                                    <span class="info-val">{{ $hd->dia_chi_giao ?: '—' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-lbl">Số điện thoại</span>
                                    <span class="info-val">{{ $hd->so_dien_thoai ?: '—' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-lbl">Ngày đặt</span>
                                    <span class="info-val">{{ optional($hd->ngay_dat)->format('d/m/Y H:i') ?? '—' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-lbl">Phương thức TT</span>
                                    <span class="info-val">
                                        @if($hd->phuong_thuc_thanh_toan === 'COD')
                                            <i class="fas fa-money-bill-wave" style="color:var(--green)"></i> Tiền mặt (COD)
                                        @else
                                            <i class="fas fa-university" style="color:var(--blue)"></i> Chuyển khoản
                                        @endif
                                    </span>
                                </div>
                                @if($hd->shipper)
                                <div class="info-row">
                                    <span class="info-lbl">Shipper</span>
                                    <span class="info-val">
                                        <i class="fas fa-user-tie" style="color:var(--purple)"></i>
                                        {{ $hd->shipper->ten_nhan_vien }}
                                    </span>
                                </div>
                                @endif
                            </div>

                            {{-- Sản phẩm --}}
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="fas fa-box-open"></i> Sản phẩm đặt
                                </div>
                                <table class="product-list">
                                    <thead>
                                        <tr>
                                            <th>Tên sản phẩm</th>
                                            <th class="text-center">SL</th>
                                            <th class="text-right">Đơn giá</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($hd->chiTietHoaDon as $ct)
                                        <tr>
                                            <td>{{ $ct->sanPham->ten_san_pham ?? '#' . $ct->ma_san_pham }}</td>
                                            <td class="text-center">×{{ $ct->so_luong }}</td>
                                            <td class="text-right">{{ number_format($ct->gia_ban_snapshot, 0, ',', '.') }} đ</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div style="margin-top:10px">
                                    <div class="total-row">
                                        <span style="color:var(--gray)">Phí vận chuyển</span>
                                        <span style="color:var(--green)">Miễn phí</span>
                                    </div>
                                    <div class="total-row main">
                                        <span>Tổng cộng</span>
                                        <span>{{ number_format($hd->tong_tien, 0, ',', '.') }} đ</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-truck" style="color:var(--muted)"></i>
                    <p>Không có đơn hàng nào đang xử lý</p>
                    <small>Các đơn hàng chưa giao sẽ hiển thị tại đây.</small>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ════════════════════════════════════
         TAB 3: LỊCH SỬ ĐƠN HÀNG
    ════════════════════════════════════ --}}
    <div class="tab-pane {{ $activeTab === 'lich-su' ? 'active' : '' }}" id="tab-lich-su">

        <div class="filter-bar">
            <button class="filter-chip active" data-filter="all" onclick="filterLichSu(this, 'all')">
                Tất cả
            </button>
            <button class="filter-chip" data-filter="DELIVERED" onclick="filterLichSu(this, 'DELIVERED')">
                <i class="fas fa-check-circle"></i> Đã giao
            </button>
            <button class="filter-chip" data-filter="CANCELLED" onclick="filterLichSu(this, 'CANCELLED')">
                <i class="fas fa-times-circle"></i> Đã hủy
            </button>
        </div>

        <div id="lich-su-list">
            @forelse($lichSuDonHang as $hd)
                @php
                    $stMap2 = [
                        'DELIVERED' => ['Đã giao',  'pill-delivered', 'fa-check-circle'],
                        'CANCELLED' => ['Đã hủy',   'pill-cancelled', 'fa-times-circle'],
                    ];
                    $st2 = $stMap2[$hd->trang_thai] ?? [$hd->trang_thai, 'pill-pending', 'fa-circle'];
                @endphp
                <div class="order-card" id="ls-{{ $hd->ma_hoa_don }}" data-status="{{ $hd->trang_thai }}">
                    <div class="order-card-header" onclick="toggleOrder('ls-{{ $hd->ma_hoa_don }}')">
                        <div>
                            <div class="order-id">
                                <i class="fas fa-receipt" style="font-size:.8rem"></i>
                                #HD-{{ str_pad($hd->ma_hoa_don, 4, '0', STR_PAD_LEFT) }}
                                <span class="pill {{ $st2[1] }}">
                                    <i class="fas {{ $st2[2] }}"></i> {{ $st2[0] }}
                                </span>
                            </div>
                            <div class="order-date">
                                @if($hd->trang_thai === 'DELIVERED')
                                    <i class="fas fa-shipping-fast" style="color:var(--green)"></i>
                                    Giao lúc {{ optional($hd->ngay_giao)->format('d/m/Y H:i') ?? '—' }}
                                @else
                                    <i class="fas fa-calendar-alt"></i>
                                    Đặt lúc {{ optional($hd->ngay_dat)->format('d/m/Y H:i') ?? '—' }}
                                @endif
                            </div>
                        </div>
                        <div class="order-header-right">
                            <span class="order-total" style="{{ $hd->trang_thai === 'CANCELLED' ? 'color:var(--muted);text-decoration:line-through' : 'color:var(--brand)' }}">
                                {{ number_format($hd->tong_tien, 0, ',', '.') }} đ
                            </span>
                            <i class="fas fa-chevron-down chevron"></i>
                        </div>
                    </div>

                    <div class="order-detail">
                        <div class="order-detail-inner">

                            {{-- Thông tin --}}
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="fas fa-info-circle"></i> Thông tin đơn hàng
                                </div>
                                <div class="info-row">
                                    <span class="info-lbl">Ngày đặt</span>
                                    <span class="info-val">{{ optional($hd->ngay_dat)->format('d/m/Y H:i') ?? '—' }}</span>
                                </div>
                                @if($hd->trang_thai === 'DELIVERED')
                                <div class="info-row">
                                    <span class="info-lbl">Ngày giao</span>
                                    <span class="info-val" style="color:var(--green)">
                                        {{ optional($hd->ngay_giao)->format('d/m/Y H:i') ?? '—' }}
                                    </span>
                                </div>
                                @endif
                                <div class="info-row">
                                    <span class="info-lbl">Địa chỉ giao</span>
                                    <span class="info-val">{{ $hd->dia_chi_giao ?: '—' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-lbl">Phương thức TT</span>
                                    <span class="info-val">
                                        @if($hd->phuong_thuc_thanh_toan === 'COD')
                                            <i class="fas fa-money-bill-wave" style="color:var(--green)"></i> Tiền mặt (COD)
                                        @else
                                            <i class="fas fa-university" style="color:var(--blue)"></i> Chuyển khoản
                                        @endif
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-lbl">Trạng thái TT</span>
                                    <span class="info-val">
                                        @if($hd->trang_thai_thanh_toan === 'DA_THANH_TOAN')
                                            <span style="color:var(--green)"><i class="fas fa-check-circle"></i> Đã thanh toán</span>
                                        @else
                                            <span style="color:var(--yellow)"><i class="fas fa-exclamation-circle"></i> Chưa thanh toán</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            {{-- Sản phẩm --}}
                            <div class="detail-section">
                                <div class="detail-section-title">
                                    <i class="fas fa-box-open"></i> Chi tiết sản phẩm
                                </div>
                                <table class="product-list">
                                    <thead>
                                        <tr>
                                            <th>Tên sản phẩm</th>
                                            <th class="text-center">SL</th>
                                            <th class="text-right">Đơn giá</th>
                                            <th class="text-right">T.tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($hd->chiTietHoaDon as $ct)
                                        <tr>
                                            <td>{{ $ct->sanPham->ten_san_pham ?? '#' . $ct->ma_san_pham }}</td>
                                            <td class="text-center">×{{ $ct->so_luong }}</td>
                                            <td class="text-right">{{ number_format($ct->gia_ban_snapshot, 0, ',', '.') }} đ</td>
                                            <td class="text-right">{{ number_format($ct->gia_ban_snapshot * $ct->so_luong, 0, ',', '.') }} đ</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div style="margin-top:10px">
                                    <div class="total-row main">
                                        <span>Tổng cộng</span>
                                        <span>{{ number_format($hd->tong_tien, 0, ',', '.') }} đ</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-history" style="color:var(--muted)"></i>
                    <p>Chưa có đơn hàng nào trong lịch sử</p>
                    <small>Các đơn đã giao hoặc đã hủy sẽ hiển thị tại đây.</small>
                </div>
            @endforelse
        </div>
    </div>

</div>

<div id="toast"></div>

<script>
const _csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function showToast(msg, type = '') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'show ' + type;
    setTimeout(() => { el.className = ''; }, 3000);
}

function switchTab(tab) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('tab-' + tab + '-btn').classList.add('active');
    history.replaceState(null, '', '?tab=' + tab);
}

function toggleOrder(id) {
    const card = document.getElementById(id);
    if (!card) return;
    card.classList.toggle('open');
}

function filterDon(btn, status) {
    document.querySelectorAll('#tab-don-hien-tai .filter-chip').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#don-hien-tai-list .order-card').forEach(card => {
        if (status === 'all' || card.dataset.status === status) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

/* ── FILTER LỊCH SỬ ── */
function filterLichSu(btn, status) {
    document.querySelectorAll('#tab-lich-su .filter-chip').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#lich-su-list .order-card').forEach(card => {
        if (status === 'all' || card.dataset.status === status) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

/* ── XÓA THÔNG BÁO ── */
function xoaThongBao(id) {
    const card = document.getElementById('notif-' + id);
    if (!card) return;

    fetch(`/customer/thong-bao/${id}/xoa`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            card.classList.add('removing');
            setTimeout(() => {
                card.remove();
                const list = document.getElementById('notif-list');
                if (list && list.querySelectorAll('.notif-card').length === 0) {
                    list.innerHTML = `
                        <div class="empty-state" id="empty-notif">
                            <i class="fas fa-bell-slash"></i>
                            <p>Không có thông báo nào</p>
                            <small>Khi shipper đến nơi giao, bạn sẽ thấy thông báo ở đây.</small>
                        </div>`;
                }
            }, 300);
        } else {
            showToast('Không thể xóa thông báo.', 'error');
        }
    })
    .catch(() => showToast('Có lỗi xảy ra.', 'error'));
}
</script>
</body>
</html>