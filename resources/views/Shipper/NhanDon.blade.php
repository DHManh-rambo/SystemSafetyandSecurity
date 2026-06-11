@extends('Shipper.layouts.shipper')

@section('title', 'Chi tiết đơn #HD-' . str_pad($hoaDon->ma_hoa_don, 4, '0', STR_PAD_LEFT))

@section('head-styles')
<link rel="stylesheet" href="{{ asset('css/Shipper/ShipperDashboard.css') }}">
@endsection

@section('extra-styles')
<style>
    :root {
        --brand:      #e75480 !important;
        --border:     #e8ecf0 !important;
        --text-main:  #1a1f36 !important;
        --text-muted: #6c757d !important;
        --surface:    #f5f7fb !important;
        --card:       #ffffff !important;
        --green:      #22c55e !important;
        --radius-md:  12px    !important;
        --radius-lg:  18px    !important;
        --radius-sm:  8px     !important;
        --shadow-sm:  0 2px 8px rgba(0,0,0,.06) !important;
    }

    .countdown-box {
        background: linear-gradient(135deg, #fff7ed, #fef3c7);
        border: 2px solid #f59e0b;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .countdown-box.danger-zone {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border-color: #ef4444;
        animation: pulse-danger 1s infinite;
    }
    @keyframes pulse-danger {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,.2); }
        50%       { box-shadow: 0 0 0 8px rgba(239,68,68,.0); }
    }
    .countdown-icon {
        font-size: 28px;
        flex-shrink: 0;
    }
    .countdown-label {
        font-size: 12px;
        font-weight: 600;
        color: #92400e;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 4px;
    }
    .countdown-box.danger-zone .countdown-label { color: #991b1b; }
    .countdown-timer {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 26px;
        font-weight: 700;
        color: #b45309;
        letter-spacing: 1px;
        line-height: 1;
    }
    .countdown-box.danger-zone .countdown-timer { color: #dc2626; }
    .countdown-sub {
        font-size: 12px;
        color: #a16207;
        margin-top: 4px;
    }
    .countdown-box.danger-zone .countdown-sub { color: #991b1b; }

    .timeout-applied {
        background: #fef2f2;
        border: 2px solid #ef4444;
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 16px;
        font-size: 14px;
        color: #991b1b;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .timeout-applied i { margin-top: 2px; flex-shrink: 0; }
</style>
@endsection

@section('content')
<div class="detail-wrap">

    {{-- ── Back bar ──────────────────────────────────────────── --}}
    <div class="back-bar">
        <a href="{{ route('shipper.dashboard') }}" class="back-btn">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <span class="order-id-title">
            #HD-{{ str_pad($hoaDon->ma_hoa_don, 4, '0', STR_PAD_LEFT) }}
        </span>
        @php
            $st = strtolower($hoaDon->trang_thai);
            $stLabel = match($hoaDon->trang_thai) {
                'CONFIRMED' => 'Chờ lấy hàng',
                'SHIPPING'  => 'Đang giao',
                'DELIVERED' => 'Đã giao',
                default     => $hoaDon->trang_thai,
            };

            // Phát hiện hoa tươi
            $coHoaTuoi = $hoaDon->chiTietHoaDon->contains(function ($item) {
                $ten = strtolower($item->sanPham->ten_san_pham ?? '');
                return str_contains($ten, 'hoa') || str_contains($ten, 'flower') || str_contains($ten, 'bó');
            });

            // Lấy thời gian bắt đầu giao từ cache
            $shippingStartAt = null;
            $remainingSeconds = null;
            $timeoutApplied = false;

            if ($hoaDon->trang_thai === 'SHIPPING') {
                $startCacheKey  = "shipping_start_{$hoaDon->ma_hoa_don}";
                $penaltyCacheKey = "shipping_penalty_done_{$hoaDon->ma_hoa_don}";
                $startTimeRaw   = \Illuminate\Support\Facades\Cache::get($startCacheKey);
                $timeoutApplied = \Illuminate\Support\Facades\Cache::has($penaltyCacheKey);

                if ($startTimeRaw) {
                    $shippingStartAt  = \Carbon\Carbon::parse($startTimeRaw);
                    $elapsed          = now()->diffInSeconds($shippingStartAt);
                    $remainingSeconds = (int) max(0, 10800 - $elapsed); // 3h = 10800s
                }
            }
        @endphp
        <span class="status-pill {{ $st === 'confirmed' ? 'confirmed' : ($st === 'shipping' ? 'shipping' : 'delivered') }}"
              id="status-pill">
            <i class="fas {{ $st === 'confirmed' ? 'fa-clock' : ($st === 'shipping' ? 'fa-motorcycle' : 'fa-check-circle') }}"></i>
            {{ $stLabel }}
        </span>
    </div>

    {{-- ── Countdown 3h (chỉ hiện khi đang giao và là hoa tươi) ── --}}
    @if($hoaDon->trang_thai === 'SHIPPING' && $coHoaTuoi && $remainingSeconds !== null)
        @if($timeoutApplied)
            <div class="timeout-applied">
                <i class="fas fa-exclamation-circle"></i>
                <span>
                    <strong>Đã quá 3 tiếng.</strong> Hệ thống đã tự động cộng 15 điểm tích lũy cho khách hàng
                    và trừ thêm 15.000 đ vào khoản bạn cần nộp do giao hoa trễ.
                </span>
            </div>
        @else
            <div class="countdown-box {{ $remainingSeconds < 1800 ? 'danger-zone' : '' }}" id="countdown-wrap">
                <div class="countdown-icon">
                    {{ $remainingSeconds < 1800 ? '🚨' : '⏰' }}
                </div>
                <div>
                    <div class="countdown-label">Thời gian giao hoa tươi còn lại</div>
                    <div class="countdown-timer" id="countdown-display">--:--:--</div>
                    <div class="countdown-sub">
                        Hoa tươi cần được giao trong 3 tiếng để đảm bảo chất lượng.
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ── Action / Progress ─────────────────────────────────── --}}
    <div class="action-section">
        <div class="action-label"><i class="fas fa-route" style="color:var(--brand)"></i>&nbsp; Tiến trình giao hàng</div>

        @php
            $daDenNoi = session('da_den_noi_' . $hoaDon->ma_hoa_don, false);
        @endphp
        <div class="progress-steps">
            <div class="step">
                <div class="step-dot done"><i class="fas fa-check"></i></div>
                <div class="step-lbl done">Xác nhận<br>đơn hàng</div>
            </div>

            <div class="step-line {{ in_array($hoaDon->trang_thai, ['SHIPPING','DELIVERED']) ? 'done' : '' }}"></div>

            <div class="step">
                <div class="step-dot {{ $hoaDon->trang_thai === 'SHIPPING' && !$daDenNoi ? 'active' : ($hoaDon->trang_thai === 'DELIVERED' || ($hoaDon->trang_thai === 'SHIPPING' && $daDenNoi) ? 'done' : '') }}">
                    @if($hoaDon->trang_thai === 'DELIVERED' || ($hoaDon->trang_thai === 'SHIPPING' && $daDenNoi))
                        <i class="fas fa-check"></i>
                    @else
                        <i class="fas fa-motorcycle"></i>
                    @endif
                </div>
                <div class="step-lbl {{ $hoaDon->trang_thai === 'SHIPPING' && !$daDenNoi ? 'active' : ($hoaDon->trang_thai === 'DELIVERED' || ($hoaDon->trang_thai === 'SHIPPING' && $daDenNoi) ? 'done' : '') }}">
                    Đang<br>vận chuyển
                </div>
            </div>

            <div class="step-line {{ $daDenNoi || $hoaDon->trang_thai === 'DELIVERED' ? 'done' : '' }}"></div>

            <div class="step">
                <div class="step-dot {{ $hoaDon->trang_thai === 'DELIVERED' ? 'done' : ($daDenNoi ? 'active' : '') }}" id="step-arrived-dot">
                    @if($hoaDon->trang_thai === 'DELIVERED')
                        <i class="fas fa-check"></i>
                    @else
                        <i class="fas fa-map-marker-alt"></i>
                    @endif
                </div>
                <div class="step-lbl {{ $hoaDon->trang_thai === 'DELIVERED' ? 'done' : ($daDenNoi ? 'active' : '') }}" id="step-arrived-lbl">
                    Đã đến<br>điểm giao
                </div>
            </div>

            <div class="step-line {{ $hoaDon->trang_thai === 'DELIVERED' ? 'done' : '' }}" id="step-line-final"></div>

            <div class="step">
                <div class="step-dot {{ $hoaDon->trang_thai === 'DELIVERED' ? 'done' : '' }}">
                    @if($hoaDon->trang_thai === 'DELIVERED')
                        <i class="fas fa-check"></i>
                    @else
                        <i class="fas fa-flag-checkered"></i>
                    @endif
                </div>
                <div class="step-lbl {{ $hoaDon->trang_thai === 'DELIVERED' ? 'done' : '' }}">
                    Hoàn thành<br>giao hàng
                </div>
            </div>
        </div>

        {{-- Action button --}}
        @if($hoaDon->trang_thai === 'CONFIRMED')
            <button class="btn-action-main btn-confirm" id="action-btn"
                    onclick="handleUpdateStatus({{ $hoaDon->ma_hoa_don }}, 'CONFIRMED')">
                <span class="spinner" id="btn-spinner"></span>
                <i class="fas fa-truck-pickup" id="btn-icon"></i>
                <span id="btn-text">Nhận hàng &amp; bắt đầu giao</span>
            </button>
            <p class="action-hint">
                <i class="fas fa-info-circle"></i>
                Nhấn để xác nhận bạn đã lấy hàng tại kho và bắt đầu giao đến khách.
                @if($coHoaTuoi)
                    <strong style="color:#b45309"> Đơn có hoa tươi — đồng hồ 3 tiếng sẽ bắt đầu ngay khi bạn nhận hàng.</strong>
                @endif
            </p>

        @elseif($hoaDon->trang_thai === 'SHIPPING')
            <button class="btn-action-main btn-shipping" id="btn-arrived"
                    onclick="handleDaDenNoi({{ $hoaDon->ma_hoa_don }})"
                    style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); box-shadow: 0 4px 16px rgba(139,92,246,.35);">
                <span class="spinner" id="spinner-arrived"></span>
                <i class="fas fa-map-marker-alt" id="icon-arrived"></i>
                <span id="text-arrived">Đã đến điểm giao — Thông báo khách</span>
            </button>
            <p class="action-hint" id="hint-arrived">
                <i class="fas fa-info-circle"></i>
                Nhấn khi đã đến địa chỉ giao. Hệ thống sẽ gửi thông báo để khách hàng xuống nhận.
            </p>

            <div id="finish-wrap" style="
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                transition: max-height .45s ease, opacity .35s ease, margin .35s ease;
                margin-top: 0;
            ">
                <button class="btn-action-main btn-done" id="action-btn"
                        onclick="handleUpdateStatus({{ $hoaDon->ma_hoa_don }}, 'SHIPPING')"
                        style="margin-top: 14px;">
                    <span class="spinner" id="btn-spinner"></span>
                    <i class="fas fa-check-circle" id="btn-icon"></i>
                    <span id="btn-text">Hoàn thành đơn hàng</span>
                </button>
                <p class="action-hint" style="margin-top:8px;">
                    <i class="fas fa-info-circle"></i>
                    Nhấn khi đã giao hàng thành công cho khách hàng.
                    @if($hoaDon->phuong_thuc_thanh_toan === 'COD' && $hoaDon->trang_thai_thanh_toan === 'CHUA_THANH_TOAN')
                        Nhớ thu <strong>{{ number_format($hoaDon->tong_tien, 0, ',', '.') }} đ</strong> tiền mặt.
                    @endif
                </p>
            </div>

        @else
            <button class="btn-action-main btn-done" disabled>
                <i class="fas fa-check-circle"></i>
                <span>Đơn hàng đã giao thành công</span>
            </button>
            <p class="action-hint">
                Giao lúc {{ optional($hoaDon->ngay_giao)->format('H:i, d/m/Y') ?? '—' }}
            </p>
        @endif
    </div>

    {{-- ── Thông tin khách hàng ──────────────────────────────── --}}
    <div class="card-section">
        <div class="card-header">
            <i class="fas fa-user-circle"></i> Thông tin khách hàng
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-lbl">Họ tên</div>
                    <div class="info-val">{{ $hoaDon->khachHang->ten_khach_hang ?? 'Khách vãng lai' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-lbl">Số điện thoại</div>
                    <div class="info-val">
                        <a href="tel:{{ $hoaDon->so_dien_thoai }}">
                            <i class="fas fa-phone" style="font-size:12px"></i>
                            {{ $hoaDon->so_dien_thoai ?: '—' }}
                        </a>
                    </div>
                </div>
                <div class="info-item full">
                    <div class="info-lbl">Địa chỉ giao hàng</div>
                    <div class="info-val">
                        <i class="fas fa-map-marker-alt" style="color:var(--brand); font-size:12px; margin-right:5px"></i>
                        {{ $hoaDon->dia_chi_giao ?: '—' }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-lbl">Ngày đặt</div>
                    <div class="info-val">{{ optional($hoaDon->ngay_dat)->format('d/m/Y H:i') ?? '—' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-lbl">Mã hóa đơn</div>
                    <div class="info-val" style="font-family:'IBM Plex Mono',monospace">
                        #HD-{{ str_pad($hoaDon->ma_hoa_don, 4, '0', STR_PAD_LEFT) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Chi tiết sản phẩm ─────────────────────────────────── --}}
    <div class="card-section">
        <div class="card-header">
            <i class="fas fa-box-open"></i> Chi tiết sản phẩm
        </div>
        <div class="card-body" style="padding-bottom:8px">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th style="text-align:center">SL</th>
                        <th style="text-align:right">Đơn giá</th>
                        <th style="text-align:right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hoaDon->chiTietHoaDon as $item)
                        <tr>
                            <td>
                                <div class="product-name">
                                    {{ $item->sanPham->ten_san_pham ?? 'Sản phẩm #' . $item->ma_san_pham }}
                                    @php
                                        $tenSP = strtolower($item->sanPham->ten_san_pham ?? '');
                                        $isHoa = str_contains($tenSP, 'hoa') || str_contains($tenSP, 'flower') || str_contains($tenSP, 'bó');
                                    @endphp
                                    @if($isHoa)
                                        <span style="font-size:10px; background:#fef3c7; color:#92400e; border-radius:4px; padding:1px 6px; margin-left:4px; font-weight:600;">🌸 Hoa tươi</span>
                                    @endif
                                </div>
                            </td>
                            <td style="text-align:center">
                                <span class="product-qty">× {{ $item->so_luong }}</span>
                            </td>
                            <td style="text-align:right">
                                <span class="product-price">
                                    {{ number_format($item->gia_ban_snapshot, 0, ',', '.') }} đ
                                </span>
                            </td>
                            <td>
                                {{ number_format($item->gia_ban_snapshot * $item->so_luong, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="color:var(--text-muted); text-align:center; padding:20px">
                                Không có thông tin chi tiết sản phẩm.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="totals-block">
                <div class="totals-row">
                    <span class="totals-lbl">Tạm tính</span>
                    <span class="totals-val">{{ number_format($hoaDon->tong_tien, 0, ',', '.') }} đ</span>
                </div>
                <div class="totals-row">
                    <span class="totals-lbl">Phí vận chuyển</span>
                    <span class="totals-val" style="color:var(--green)">Miễn phí</span>
                </div>
                <div class="totals-row main">
                    <span>Tổng cộng</span>
                    <span style="color:var(--brand)">{{ number_format($hoaDon->tong_tien, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Thanh toán ────────────────────────────────────────── --}}
    <div class="card-section">
        <div class="card-header">
            <i class="fas fa-wallet"></i> Thanh toán
        </div>
        <div class="card-body">
            <div class="payment-row">
                <div>
                    <div class="info-lbl" style="margin-bottom:8px">Phương thức</div>
                    @if($hoaDon->phuong_thuc_thanh_toan === 'COD')
                        <span class="payment-method cod">
                            <i class="fas fa-money-bill-wave"></i> Tiền mặt (COD)
                        </span>
                    @else
                        <span class="payment-method bank">
                            <i class="fas fa-university"></i> Chuyển khoản
                        </span>
                    @endif
                </div>

                <div class="collect-block">
                    <div class="collect-lbl">Số tiền cần thu</div>
                    @php
                        $canThu = ($hoaDon->trang_thai_thanh_toan === 'CHUA_THANH_TOAN'
                                   && $hoaDon->phuong_thuc_thanh_toan === 'COD')
                                  ? $hoaDon->tong_tien
                                  : 0;
                    @endphp
                    <div class="collect-amount {{ $canThu > 0 ? 'need-collect' : 'no-collect' }}">
                        {{ $canThu > 0 ? number_format($canThu, 0, ',', '.') . ' đ' : '0 đ' }}
                    </div>
                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px">
                        @if($canThu > 0)
                            <i class="fas fa-exclamation-circle" style="color:var(--brand)"></i>
                            Thu tiền mặt khi giao hàng
                        @else
                            <i class="fas fa-check-circle" style="color:var(--green)"></i>
                            Đã thanh toán — không cần thu
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
const _maHoaDon    = {{ $hoaDon->ma_hoa_don }};
const _coHoaTuoi   = {{ $coHoaTuoi ? 'true' : 'false' }};
const _trangThai   = '{{ $hoaDon->trang_thai }}';
const _remainingSec = {{ $remainingSeconds ?? 'null' }};
const _timeoutApplied = {{ $timeoutApplied ? 'true' : 'false' }};

let _countdown = _remainingSec;
let _countdownInterval = null;

function formatTime(sec) {
    sec = Math.floor(sec);
    if (sec <= 0) return '00:00:00';
    const h = Math.floor(sec / 3600);
    const m = Math.floor((sec % 3600) / 60);
    const s = sec % 60;
    return String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
}

function startCountdown() {
    if (!_coHoaTuoi || _trangThai !== 'SHIPPING' || _timeoutApplied || _countdown === null) return;

    const display = document.getElementById('countdown-display');
    const wrap    = document.getElementById('countdown-wrap');
    if (!display) return;

    display.textContent = formatTime(Math.max(0, _countdown));

    _countdownInterval = setInterval(() => {
        _countdown--;

        if (wrap && _countdown < 1800 && !wrap.classList.contains('danger-zone')) {
            wrap.classList.add('danger-zone');
            wrap.querySelector('.countdown-icon').textContent = '🚨';
        }

        display.textContent = formatTime(Math.max(0, _countdown));

        if (_countdown <= 0) {
            clearInterval(_countdownInterval);
            triggerTimeoutCheck();
        }
    }, 1000);
}

function triggerTimeoutCheck() {
    fetch(`/shipper/don-hang/${_maHoaDon}/check-timeout`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfToken },
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'penalty_applied') {
            showToast('⚠️ Đã quá 3 tiếng! Hệ thống đã cộng điểm cho khách và trừ 15.000 đ của bạn.', 'error');
            setTimeout(() => location.reload(), 2500);
        }
    })
    .catch(() => {});
}

document.addEventListener('DOMContentLoaded', () => {
    startCountdown();

    if (_coHoaTuoi && _trangThai === 'SHIPPING' && !_timeoutApplied && _remainingSec !== null) {
        setInterval(() => {
            if (_countdown <= 0) {
                triggerTimeoutCheck();
            }
        }, 60000);
    }
});

function handleDaDenNoi(id) {
    const code = '#HD-' + String(id).padStart(4, '0');
    if (!confirm(`Xác nhận bạn đã đến điểm giao cho đơn ${code}?\nHệ thống sẽ gửi thông báo đến khách hàng.`)) return;

    const btn     = document.getElementById('btn-arrived');
    const spinner = document.getElementById('spinner-arrived');
    const icon    = document.getElementById('icon-arrived');
    const text    = document.getElementById('text-arrived');

    if (btn) btn.disabled = true;
    if (spinner) spinner.style.display = 'inline-block';
    if (icon) icon.style.display = 'none';
    if (text) text.textContent = 'Đang gửi thông báo…';

    fetch(`/shipper/don-hang/${id}/da-den-noi`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfToken },
    })
    .then(r => r.json())
    .then(data => {
        if (spinner) spinner.style.display = 'none';
        if (icon) icon.style.display = '';

        if (data.success) {
            showToast('✅ Đã gửi thông báo đến khách hàng!', 'success');

            const dotArrived = document.getElementById('step-arrived-dot');
            const lblArrived = document.getElementById('step-arrived-lbl');
            if (dotArrived) {
                dotArrived.className = 'step-dot active';
                dotArrived.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
            }
            if (lblArrived) lblArrived.className = 'step-lbl active';

            if (btn) {
                btn.style.background = 'linear-gradient(135deg, #6b7280, #4b5563)';
                btn.style.boxShadow  = 'none';
                btn.style.cursor     = 'default';
                btn.style.opacity    = '.75';
                btn.onclick          = null;
            }
            if (text) text.textContent = '✓ Đã gửi thông báo đến khách';

            const hint = document.getElementById('hint-arrived');
            if (hint) {
                hint.innerHTML = `<i class="fas fa-check-circle" style="color:var(--green)"></i>
                    Khách hàng đã được thông báo. Hãy chờ khách xuống nhận hàng.`;
            }

            const wrap = document.getElementById('finish-wrap');
            if (wrap) {
                wrap.style.maxHeight = wrap.scrollHeight + 120 + 'px';
                wrap.style.opacity   = '1';
                wrap.style.marginTop = '0';
            }
        } else {
            showToast(data.message || 'Có lỗi xảy ra.', 'error');
            if (btn) btn.disabled = false;
            if (text) text.textContent = 'Đã đến điểm giao — Thông báo khách';
        }
    })
    .catch(() => {
        showToast('Có lỗi xảy ra, vui lòng thử lại.', 'error');
        if (btn) btn.disabled = false;
        if (spinner) spinner.style.display = 'none';
        if (icon) icon.style.display = '';
        if (text) text.textContent = 'Đã đến điểm giao — Thông báo khách';
    });
}

function handleUpdateStatus(id, currentStatus) {
    const actionMap = {
        'CONFIRMED': { label: 'nhận hàng và bắt đầu giao', next: 'SHIPPING' },
        'SHIPPING':  { label: 'hoàn thành đơn hàng này',   next: 'DELIVERED' },
    };
    const action = actionMap[currentStatus];
    if (!action) return;

    const code = '#HD-' + String(id).padStart(4, '0');
    if (!confirm(`Xác nhận ${action.label} cho đơn ${code}?`)) return;

    const btn     = document.getElementById('action-btn');
    const spinner = document.getElementById('btn-spinner');
    const icon    = document.getElementById('btn-icon');
    const text    = document.getElementById('btn-text');

    if (btn) btn.disabled = true;
    if (spinner) spinner.style.display = 'inline-block';
    if (icon) icon.style.display = 'none';
    if (text) text.textContent = 'Đang xử lý…';

    fetch(`/shipper/don-hang/${id}/cap-nhat`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfToken },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            if (data.new_status === 'DELIVERED') {
                setTimeout(() => {
                    window.location.href = '{{ route("shipper.dashboard") }}';
                }, 1500);
            } else {
                setTimeout(() => location.reload(), 800);
            }
        } else {
            showToast(data.message, 'error');
            if (btn) btn.disabled = false;
            if (spinner) spinner.style.display = 'none';
            if (icon) icon.style.display = '';
            if (text) {
                text.textContent = currentStatus === 'CONFIRMED'
                    ? 'Nhận hàng & bắt đầu giao'
                    : 'Hoàn thành đơn hàng';
            }
        }
    })
    .catch(() => {
        showToast('Có lỗi xảy ra, vui lòng thử lại.', 'error');
        if (btn) btn.disabled = false;
        if (spinner) spinner.style.display = 'none';
        if (icon) icon.style.display = '';
    });
}
</script>
@endsection