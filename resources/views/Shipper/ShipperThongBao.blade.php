@extends('Shipper.layouts.shipper')

@section('title', 'Thông báo · Shipper')

@section('head-styles')
<link rel="stylesheet" href="{{ asset('css/Shipper/ShipperDashboard.css') }}">
@endsection

@section('extra-styles')
<style>
  
    :root {
        --brand:      #e75480;
        --brand-light:#fce8ef;
        --navy:       #1a1f36;
        --surface:    #f5f7fb;
        --card:       #ffffff;
        --border:     #e8ecf0;
        --text-main:  #1a1f36;
        --text-muted: #6c757d;
        --green:      #22c55e;
        --orange:     #f59e0b;
        --blue:       #3b82f6;
        --red:        #ef4444;
        --radius-lg:  18px;
        --radius-md:  12px;
        --radius-sm:  8px;
        --shadow-sm:  0 2px 8px rgba(0,0,0,.06);
        --shadow-md:  0 6px 24px rgba(0,0,0,.09);
    }

   
    .tb-page {
        max-width: 680px;
        margin: 0 auto;
        padding: 28px 16px 60px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        margin-bottom: 20px;
        transition: color .2s;
    }
    .back-link:hover { color: var(--brand); }

    .page-title {
        font-size: 22px;
        font-weight: 700;
        color: var(--navy);
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }
    .empty-state i { font-size: 42px; margin-bottom: 14px; opacity: .35; display: block; }
    .empty-state p { font-size: 15px; font-weight: 500; margin: 0 0 6px; }
    .empty-state small { font-size: 13px; }

    
    .notif-card {
        background: var(--card);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px 14px;
        margin-bottom: 10px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        box-shadow: var(--shadow-sm);
        transition: opacity .3s, transform .3s;
        position: relative;
    }
    .notif-card.removing {
        opacity: 0;
        transform: translateX(20px);
    }

  
    .notif-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .notif-icon.info    { background: #eff6ff; color: var(--blue); }
    .notif-icon.warning { background: #fffbeb; color: var(--orange); }
    .notif-icon.danger  { background: #fef2f2; color: var(--red); }
    .notif-icon.shipping { background: var(--brand-light); color: var(--brand); }

    .notif-body { flex: 1; min-width: 0; }

    .notif-type {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .4px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .notif-type.info    { color: var(--blue); }
    .notif-type.warning { color: var(--orange); }
    .notif-type.danger  { color: var(--red); }
    .notif-type.shipping { color: var(--brand); }

    .notif-content {
        font-size: 14px;
        color: var(--text-main);
        line-height: 1.6;
        word-break: break-word;
    }

    .notif-time {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .btn-close-notif {
        background: none;
        border: none;
        cursor: pointer;
        color: #c0c5d0;
        font-size: 14px;
        padding: 2px 4px;
        border-radius: 4px;
        transition: color .2s, background .2s;
        flex-shrink: 0;
        align-self: flex-start;
        margin-top: 2px;
    }
    .btn-close-notif:hover { color: var(--red); background: #fef2f2; }

    #toast-tb {
        position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%);
        padding: 12px 24px; border-radius: 40px;
        font-size: 14px; font-weight: 500;
        background: var(--navy); color: #fff;
        opacity: 0; pointer-events: none;
        transition: opacity .3s; z-index: 9999;
        max-width: 90vw; text-align: center;
    }
    #toast-tb.show { opacity: 1; }
    #toast-tb.success { background: #16a34a; }
    #toast-tb.error   { background: #dc2626; }

    .count-chip {
        background: var(--brand);
        color: white;
        font-size: 12px;
        font-weight: 700;
        padding: 2px 10px;
        border-radius: 20px;
        margin-left: 8px;
    }
</style>
@endsection

@section('content')
<div class="tb-page">

    <a href="{{ route('shipper.dashboard') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Quay lại Dashboard
    </a>

    <div class="page-title">
        <i class="fas fa-bell" style="color:var(--brand)"></i>
        Thông báo của tôi
        @if($soThongBao > 0)
            <span class="count-chip">{{ $soThongBao }}</span>
        @endif
    </div>

    <div id="notif-list">
        @forelse($thongBaos as $tb)
            @php
                $loai = $tb['loai'] ?? 'info';
                $iconMap = [
                    'info'     => 'fa-info-circle',
                    'warning'  => 'fa-exclamation-triangle',
                    'danger'   => 'fa-times-circle',
                    'shipping' => 'fa-motorcycle',
                ];
                $labelMap = [
                    'info'     => 'Thông tin',
                    'warning'  => 'Lưu ý quan trọng',
                    'danger'   => 'Cảnh báo',
                    'shipping' => 'Đơn hàng',
                ];
                $icon  = $iconMap[$loai]  ?? 'fa-bell';
                $label = $labelMap[$loai] ?? 'Thông báo';
            @endphp
            <div class="notif-card" id="notif-{{ $tb['id'] }}">
                <div class="notif-icon {{ $loai }}">
                    <i class="fas {{ $icon }}"></i>
                </div>
                <div class="notif-body">
                    <div class="notif-type {{ $loai }}">{{ $label }}</div>
                    <div class="notif-content">{{ $tb['noi_dung'] }}</div>
                    <div class="notif-time">
                        <i class="fas fa-clock"></i> {{ $tb['thoi_gian'] }}
                    </div>
                </div>
                <button class="btn-close-notif"
                        onclick="xoaThongBao('{{ $tb['id'] }}')"
                        title="Xóa thông báo">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @empty
            <div class="empty-state" id="empty-notif">
                <i class="fas fa-bell-slash"></i>
                <p>Không có thông báo nào</p>
                <small>Các thông báo về đơn hàng mới và trạng thái giao sẽ xuất hiện ở đây.</small>
            </div>
        @endforelse
    </div>

</div>

<div id="toast-tb"></div>
@endsection

@section('scripts')
<script>
const _csrfTb = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function showToastTb(msg, type = '') {
    const el = document.getElementById('toast-tb');
    el.textContent = msg;
    el.className = 'show ' + type;
    setTimeout(() => { el.className = ''; }, 3000);
}

function xoaThongBao(id) {
    const card = document.getElementById('notif-' + id);
    if (!card) return;

    fetch(`/shipper/thong-bao/${id}/xoa`, {
        method:  'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrfTb },
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
                            <small>Các thông báo về đơn hàng mới và trạng thái giao sẽ xuất hiện ở đây.</small>
                        </div>`;
                }
            }, 300);
        } else {
            showToastTb('Không thể xóa thông báo.', 'error');
        }
    })
    .catch(() => showToastTb('Có lỗi xảy ra.', 'error'));
}
</script>
@endsection