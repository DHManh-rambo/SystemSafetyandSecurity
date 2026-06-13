@extends('layouts.admin')

@section('content')
    <div class="security-users-container" style="padding: 20px; font-family: 'Inter', sans-serif;">

        <!-- Header -->
        <div style="margin-bottom: 25px;">
            <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;">Duyệt tài khoản nghi ngờ / Bị khóa
            </h1>
            <p style="font-size: 14px; color: #64748b; margin: 5px 0 0 0;">Phê duyệt mở khóa hoặc cấm vĩnh viễn các tài
                khoản bị hệ thống chặn tự động.</p>
        </div>

        <!-- Message Alerts -->
        @if(session('success'))
            <div
                style="padding: 15px; background-color: #dcfce7; color: #15803d; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Pending Users Table -->
        <div
            style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 600;">
                        <th style="padding: 12px 15px;">Tên đăng nhập</th>
                        <th style="padding: 12px 15px;">Họ và tên</th>
                        <th style="padding: 12px 15px;">Vai trò</th>
                        <th style="padding: 12px 15px;">Số điện thoại / Email</th>
                        <th style="padding: 12px 15px; text-align: center;">Trạng thái</th>
                        <th style="padding: 12px 15px; text-align: right;">Hành động xử lý</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr style="border-bottom: 1px solid #f1f5f9; color: #334155;">
                            <td style="padding: 12px 15px; font-weight: bold; color: #2563eb;">{{ $user->ten_dang_nhap }}</td>
                            <td style="padding: 12px 15px; color: #475569;">
                                @if($user->isKhachHang() && $user->khachHang)
                                    {{ $user->khachHang->ten_khach_hang }}
                                @elseif($user->isNhanVien() && $user->nhanVien)
                                    {{ $user->nhanVien->ten_nhan_vien }}
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">Chưa cập nhật hồ sơ</span>
                                @endif
                            </td>
                            <td style="padding: 12px 15px; font-size: 12px; font-weight: bold;">
                                <span style="background: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 4px;">
                                    {{ $user->vai_tro }}
                                </span>
                            </td>
                            <td style="padding: 12px 15px; font-size: 13px; color: #64748b;">
                                @if($user->isKhachHang() && $user->khachHang)
                                    SDT: {{ $user->khachHang->so_dien_thoai }} <br> Email: {{ $user->khachHang->email }}
                                @elseif($user->isNhanVien() && $user->nhanVien)
                                    SDT: {{ $user->nhanVien->so_dien_thoai }} <br> Email: {{ $user->nhanVien->email }}
                                @else
                                    -
                                @endif
                            </td>
                            <td style="padding: 12px 15px; text-align: center;">
                                @if($user->trang_thai == 'CHO_DUYET')
                                    <span
                                        style="background: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                        Chờ duyệt
                                    </span>
                                @elseif($user->trang_thai == 'KHOA')
                                    <span
                                        style="background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                        Đang khóa
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 12px 15px; text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <form method="POST"
                                        action="{{ route('admin.security.users.approve', $user->ma_nguoi_dung) }}">
                                        @csrf
                                        <button type="submit"
                                            style="background: #10b981; color: #ffffff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer;">
                                            Kích hoạt lại
                                        </button>
                                    </form>
                                    @if($user->trang_thai == 'CHO_DUYET')
                                        <form method="POST"
                                            action="{{ route('admin.security.users.block', $user->ma_nguoi_dung) }}">
                                            @csrf
                                            <button type="submit"
                                                style="background: #dc2626; color: #ffffff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer;">
                                                Khóa vĩnh viễn
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 30px; text-align: center; color: #94a3b8;">Hiện tại không có tài
                                khoản nào bị khóa hoặc đang chờ phê duyệt.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection