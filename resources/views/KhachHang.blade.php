@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/KhachHang.css') }}">
@endpush

@section('content')

<div class="khach-hang-container">


    <div class="kh-stats">
        <div class="kh-stat-item">
            <div class="kh-stat-icon">👤</div>
            <div>
                <span>Tổng khách hàng</span>
                <h2>{{ $khachHangs->count() }}</h2>
            </div>
        </div>

        <div class="kh-stat-item">
            <div class="kh-stat-icon">🎁</div>
            <div>
                <span>Tổng điểm tích lũy</span>
                <h2>{{ number_format($khachHangs->sum('diem_tich_luy')) }}</h2>
            </div>
        </div>

        <div class="kh-stat-item">
            <div class="kh-stat-icon">📈</div>
            <div>
                <span>Điểm trung bình</span>
                <h2>
                    {{ $khachHangs->count() > 0 ? number_format($khachHangs->avg('diem_tich_luy'), 2) : 0 }}
                </h2>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="kh-alert kh-alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="kh-alert kh-alert-error">{{ session('error') }}</div>
    @endif

    <div class="kh-card">

        <div class="kh-toolbar">
            <div>
                <h3>Sắp xếp theo điểm tích lũy</h3>

                <div class="kh-sort-buttons">
                    <a href="{{ route('khach-hang.index', ['sort' => 'desc']) }}"
                       class="kh-btn {{ $sort == 'desc' ? 'active' : '' }}">
                        Điểm từ cao → thấp
                    </a>

                    <a href="{{ route('khach-hang.index', ['sort' => 'asc']) }}"
                       class="kh-btn {{ $sort == 'asc' ? 'active' : '' }}">
                        Điểm từ thấp → cao
                    </a>
                </div>
            </div>

            <div class="kh-search">
                <span>🔍</span>
                <input type="text" id="khachHangSearch" placeholder="Tìm kiếm khách hàng...">
            </div>
        </div>

        <div class="kh-table-wrap">
            <table class="kh-table" id="khachHangTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên khách hàng</th>
                        <th>Số điện thoại</th>
                        <th>Email</th>
                        <th>Địa chỉ</th>
                        <th>Điểm tích lũy</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($khachHangs as $index => $kh)
                        <tr id="row-{{ $kh->ma_khach_hang }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $kh->ten_khach_hang }}</td>
                            <td>{{ $kh->so_dien_thoai }}</td>
                            <td>{{ $kh->email }}</td>
                            <td>{{ $kh->dia_chi ?: '—' }}</td>
                            <td>
                                <strong class="kh-point">
                                    {{ number_format($kh->diem_tich_luy) }}
                                </strong>
                            </td>
                            <td>
                                <form action="{{ route('khach-hang.destroy', $kh->ma_khach_hang) }}"
                                      method="POST"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa khách hàng này? Hành động sẽ xóa luôn tài khoản đăng nhập.')">
                                    @csrf
                                    @method('DELETE')

                                    <input type="hidden" name="sort" value="{{ $sort }}">

                                    <button type="submit" class="kh-delete-btn">
                                        Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="kh-empty">
                                Không có khách hàng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="kh-table-footer">
            Hiển thị {{ $khachHangs->count() }} khách hàng
        </div>

    </div>

</div>

<script>
    const khSearch = document.getElementById('khachHangSearch');
    const khRows = document.querySelectorAll('#khachHangTable tbody tr');

    khSearch?.addEventListener('input', function () {
        const keyword = this.value.toLowerCase().trim();

        khRows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(keyword)
                ? ''
                : 'none';
        });
    });
</script>

@endsection