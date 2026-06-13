@extends('layouts.admin')

@section('content')
    <div class="security-logs-container" style="padding: 20px; font-family: 'Inter', sans-serif;">

        <!-- Header -->
        <div style="margin-bottom: 25px;">
            <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;"> Nhật ký tấn công & Quản lý IP Chặn
            </h1>
            <p style="font-size: 14px; color: #64748b; margin: 5px 0 0 0;">Tra cứu logs xâm nhập thời gian thực và quản lý
                danh sách IP bị cấm.</p>
        </div>

        <!-- Message Alerts -->
        @if(session('success'))
            <div
                style="padding: 15px; background-color: #dcfce7; color: #15803d; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div
                style="padding: 15px; background-color: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                {{ session('error') }}
            </div>
        @endif

        <!-- Blocked IPs Section -->
        <div
            style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); margin-bottom: 30px;">
            <h3 style="font-size: 16px; font-weight: 650; color: #1e293b; margin: 0 0 15px 0;"> Danh sách IP đang bị chặn
                trong Cache (Active Bans)</h3>
            @if(count($blockedIps) > 0)
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    @foreach($blockedIps as $bIp)
                        <div
                            style="display: flex; align-items: center; background: #fee2e2; border: 1px solid #fca5a5; padding: 6px 12px; border-radius: 8px;">
                            <span
                                style="font-family: monospace; font-weight: bold; color: #991b1b; margin-right: 10px;">{{ $bIp }}</span>
                            <form method="POST" action="{{ route('admin.security.ips.release') }}" style="margin: 0;">
                                @csrf
                                <input type="hidden" name="ip_address" value="{{ $bIp }}">
                                <button type="submit"
                                    style="background: none; border: none; color: #dc2626; cursor: pointer; font-weight: bold; font-size: 14px;"
                                    title="Gỡ chặn IP">✖</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="font-size: 14px; color: #94a3b8; margin: 0;">Hiện tại không có IP nào bị chặn trong Cache.</p>
            @endif
        </div>

        <!-- Filters -->
        <div
            style="background: #ffffff; padding: 15px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); margin-bottom: 25px;">
            <form method="GET" action="{{ route('admin.security.logs') }}"
                style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                <div style="flex: 1; min-width: 150px;">
                    <label
                        style="display: block; font-size: 12px; color: #64748b; margin-bottom: 5px; font-weight: 600;">Địa
                        chỉ IP</label>
                    <input type="text" name="ip" value="{{ request('ip') }}" placeholder="Nhập IP..."
                        style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                </div>
                <div style="width: 150px;">
                    <label
                        style="display: block; font-size: 12px; color: #64748b; margin-bottom: 5px; font-weight: 600;">Loại
                        tấn công</label>
                    <select name="attack_type"
                        style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <option value="">-- Tất cả --</option>
                        <option value="SQLi" {{ request('attack_type') == 'SQLi' ? 'selected' : '' }}>SQLi</option>
                        <option value="XSS" {{ request('attack_type') == 'XSS' ? 'selected' : '' }}>XSS</option>
                        <option value="Path_Traversal" {{ request('attack_type') == 'Path_Traversal' ? 'selected' : '' }}>Path
                            Traversal</option>
                        <option value="DOS" {{ request('attack_type') == 'DOS' ? 'selected' : '' }}>DOS</option>
                        <option value="Malicious_Upload" {{ request('attack_type') == 'Malicious_Upload' ? 'selected' : '' }}>
                            Malicious Upload</option>
                    </select>
                </div>
                <div style="width: 150px;">
                    <label
                        style="display: block; font-size: 12px; color: #64748b; margin-bottom: 5px; font-weight: 600;">Mức
                        nghiêm trọng</label>
                    <select name="severity"
                        style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        <option value="">-- Tất cả --</option>
                        <option value="LOW" {{ request('severity') == 'LOW' ? 'selected' : '' }}>LOW</option>
                        <option value="MEDIUM" {{ request('severity') == 'MEDIUM' ? 'selected' : '' }}>MEDIUM</option>
                        <option value="HIGH" {{ request('severity') == 'HIGH' ? 'selected' : '' }}>HIGH</option>
                        <option value="CRITICAL" {{ request('severity') == 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
                    </select>
                </div>
                <div>
                    <button type="submit"
                        style="background: #2563eb; color: #ffffff; border: none; padding: 8px 20px; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer;">Lọc
                        kết quả</button>
                    <a href="{{ route('admin.security.logs') }}"
                        style="background: #f1f5f9; color: #334155; padding: 8px 15px; border-radius: 6px; font-size: 13px; text-decoration: none; margin-left: 5px; display: inline-block;">Đặt
                        lại</a>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div
            style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 600;">
                        <th style="padding: 12px 15px;">Địa chỉ IP</th>
                        <th style="padding: 12px 15px;">Tài khoản</th>
                        <th style="padding: 12px 15px;">Thời gian</th>
                        <th style="padding: 12px 15px;">Phương thức & Route</th>
                        <th style="padding: 12px 15px;">Loại tấn công</th>
                        <th style="padding: 12px 15px; text-align: center;">Mức độ</th>
                        <th style="padding: 12px 15px; text-align: center;">Điểm nguy cơ</th>
                        <th style="padding: 12px 15px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr style="border-bottom: 1px solid #f1f5f9; color: #334155;">
                            <td style="padding: 12px 15px; font-family: monospace; font-weight: bold;">{{ $log->ip_address }}
                            </td>
                            <td style="padding: 12px 15px; color: #475569;">
                                @if($log->nguoiDung)
                                    <span
                                        style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                        {{ $log->nguoiDung->ten_dang_nhap }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">Khách</span>
                                @endif
                            </td>
                            <td style="padding: 12px 15px; color: #64748b; font-size: 13px;">
                                {{ $log->created_at->format('H:i:s d/m/Y') }}</td>
                            <td
                                style="padding: 12px 15px; font-size: 13px; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <strong style="color: #475569;">{{ $log->request_method }}</strong>
                                <code
                                    style="background: #f1f5f9; padding: 2px 4px; border-radius: 4px; color: #0f172a; font-family: monospace;">{{ $log->route }}</code>
                            </td>
                            <td style="padding: 12px 15px; font-weight: bold; color: #0f172a;">{{ $log->attack_type }}</td>
                            <td style="padding: 12px 15px; text-align: center;">
                                @php
                                    $badgeStyles = [
                                        'LOW' => 'background: #f1f5f9; color: #475569;',
                                        'MEDIUM' => 'background: #fef9c3; color: #713f12;',
                                        'HIGH' => 'background: #ffedd5; color: #c2410c;',
                                        'CRITICAL' => 'background: #fee2e2; color: #991b1b;',
                                    ];
                                    $style = $badgeStyles[$log->severity] ?? 'background: #f1f5f9; color: #334155;';
                                @endphp
                                <span
                                    style="display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; {{ $style }}">
                                    {{ $log->severity }}
                                </span>
                            </td>
                            <td style="padding: 12px 15px; text-align: center;">
                                <strong
                                    style="color: {{ $log->threat_score >= 80 ? '#dc2626' : ($log->threat_score >= 40 ? '#d97706' : '#16a34a') }};">
                                    {{ $log->threat_score }}
                                </strong>
                            </td>
                            <td style="padding: 12px 15px; font-size: 13px; color: #475569;">
                                @if($log->action_taken == 'LOCKED_ACCOUNT')
                                    <span style="color: #b91c1c; font-weight: 600;">Khóa tài khoản</span>
                                @elseif($log->action_taken == 'BLOCKED_IP')
                                    <span style="color: #ea580c; font-weight: 600;">Chặn IP</span>
                                @elseif($log->action_taken == 'PENDING_MODERATION')
                                    <span style="color: #d97706; font-weight: 600;">Chờ phê duyệt</span>
                                @else
                                    <span style="color: #64748b;">Ghi log</span>
                                @endif
                            </td>
                        </tr>
                        @if($log->payload)
                            <tr style="background: #f8fafc; font-size: 12px; border-bottom: 1px solid #f1f5f9;">
                                <td colspan="8" style="padding: 8px 15px; color: #64748b; font-family: monospace;">
                                    <strong>Payload nhận diện:</strong> {{ $log->payload }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" style="padding: 30px; text-align: center; color: #94a3b8;">Không ghi nhận sự kiện
                                tấn công nào trong hệ thống.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="margin-top: 20px;">
            {{ $logs->appends(request()->query())->links() }}
        </div>

    </div>
@endsection