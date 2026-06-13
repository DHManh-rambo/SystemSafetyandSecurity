@extends('layouts.admin')

@section('content')
    <div class="security-dashboard-container" style="padding: 20px; font-family: 'Inter', sans-serif;">

        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <div>
                <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;"> AI Security Center</h1>
                <p style="font-size: 14px; color: #64748b; margin: 5px 0 0 0;">Giám sát xâm nhập (Smart IPS/IDS) & Cảnh báo
                    an ninh thông minh</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <span
                    style="display: inline-flex; align-items: center; padding: 6px 12px; background: #dcfce7; color: #15803d; border-radius: 20px; font-size: 13px; font-weight: 600;">
                    <span
                        style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%; margin-right: 6px; display: inline-block;"></span>
                    Hệ thống đang được bảo vệ
                </span>
            </div>
        </div>

        <!-- Alert Success/Error -->
        @if(session('success'))
            <div
                style="padding: 15px; background-color: #dcfce7; color: #15803d; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Stats Grid -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">

            <!-- Total Attacks -->
            <div
                style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05); border-left: 5px solid #6366f1;">
                <p style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin: 0;">Tổng số
                    sự kiện chặn</p>
                <h3 style="font-size: 28px; font-weight: 700; color: #1e293b; margin: 10px 0 0 0;">{{ $totalLogs }}</h3>
            </div>

            <!-- Critical -->
            <div
                style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05); border-left: 5px solid #ef4444;">
                <p style="font-size: 12px; font-weight: 600; color: #ef4444; text-transform: uppercase; margin: 0;">Nguy cấp
                    (Critical)</p>
                <h3 style="font-size: 28px; font-weight: 700; color: #ef4444; margin: 10px 0 0 0;">
                    {{ $severityCounts['CRITICAL'] }}</h3>
            </div>

            <!-- High -->
            <div
                style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05); border-left: 5px solid #f97316;">
                <p style="font-size: 12px; font-weight: 600; color: #f97316; text-transform: uppercase; margin: 0;">Nghiêm
                    trọng (High)</p>
                <h3 style="font-size: 28px; font-weight: 700; color: #f97316; margin: 10px 0 0 0;">
                    {{ $severityCounts['HIGH'] }}</h3>
            </div>

            <!-- Medium -->
            <div
                style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05); border-left: 5px solid #eab308;">
                <p style="font-size: 12px; font-weight: 600; color: #eab308; text-transform: uppercase; margin: 0;">Trung
                    bình (Medium)</p>
                <h3 style="font-size: 28px; font-weight: 700; color: #eab308; margin: 10px 0 0 0;">
                    {{ $severityCounts['MEDIUM'] }}</h3>
            </div>

        </div>

        <!-- Charts Section -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px;">

            <!-- Line Chart -->
            <div
                style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);">
                <h3 style="font-size: 16px; font-weight: 650; color: #1e293b; margin: 0 0 20px 0;"> Tần suất các cuộc tấn
                    công (7 ngày qua)</h3>
                <div style="height: 300px; position: relative;">
                    <canvas id="attackHistoryChart"></canvas>
                </div>
            </div>

            <!-- Pie Chart -->
            <div
                style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);">
                <h3 style="font-size: 16px; font-weight: 650; color: #1e293b; margin: 0 0 20px 0;"> Phân loại hình thức tấn
                    công</h3>
                <div
                    style="height: 250px; position: relative; display: flex; justify-content: center; align-items: center;">
                    <canvas id="attackTypeChart"></canvas>
                </div>
            </div>

        </div>

        <!-- Details Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

            <!-- Top Attacking IPs -->
            <div
                style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);">
                <h3 style="font-size: 16px; font-weight: 650; color: #1e293b; margin: 0 0 15px 0;"> Top IP tấn công nguy
                    hiểm nhất</h3>
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #f1f5f9; color: #64748b; font-weight: 600;">
                            <th style="padding: 10px 5px;">Địa chỉ IP</th>
                            <th style="padding: 10px 5px; text-align: center;">Số lần vi phạm</th>
                            <th style="padding: 10px 5px; text-align: right;">Lần cuối hoạt động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topAttackerIps as $ipData)
                            <tr style="border-bottom: 1px solid #f1f5f9; color: #334155;">
                                <td style="padding: 12px 5px; font-family: monospace; font-weight: bold; color: #2563eb;">
                                    {{ $ipData->ip_address }}</td>
                                <td style="padding: 12px 5px; text-align: center;">
                                    <span
                                        style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                                        {{ $ipData->count }}
                                    </span>
                                </td>
                                <td style="padding: 12px 5px; text-align: right; color: #64748b;">{{ $ipData->last_attack }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="padding: 20px; text-align: center; color: #94a3b8;">Không ghi nhận dữ
                                    liệu tấn công.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- AI Security Rules Status -->
            <div
                style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);">
                <h3 style="font-size: 16px; font-weight: 650; color: #1e293b; margin: 0 0 15px 0;"> Trạng thái quy tắc bảo
                    mật</h3>
                <div style="display: flex; flex-direction: column; gap: 15px;">

                    <div
                        style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f8fafc; border-radius: 8px;">
                        <div>
                            <strong style="display: block; font-size: 14px; color: #334155;">Bộ phân loại Payload (AI
                                WAF)</strong>
                            <span style="font-size: 12px; color: #64748b;">Ngăn chặn SQL Injection, XSS, Path
                                Traversal</span>
                        </div>
                        <span
                            style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">ĐANG
                            BẬT</span>
                    </div>

                    <div
                        style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f8fafc; border-radius: 8px;">
                        <div>
                            <strong style="display: block; font-size: 14px; color: #334155;">Ngăn chặn tải tệp tin độc
                                hại</strong>
                            <span style="font-size: 12px; color: #64748b;">Quét sâu nhị phân (EXIF/EXEs/Shells inside
                                Image)</span>
                        </div>
                        <span
                            style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">ĐANG
                            BẬT</span>
                    </div>

                    <div
                        style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f8fafc; border-radius: 8px;">
                        <div>
                            <strong style="display: block; font-size: 14px; color: #334155;">Hệ thống Rate Limiter
                                (DOS)</strong>
                            <span style="font-size: 12px; color: #64748b;">Giới hạn tối đa 60 request/phút mỗi IP</span>
                        </div>
                        <span
                            style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;">60
                            REQ/MIN</span>
                    </div>

                </div>
            </div>

        </div>

    </div>

    <!-- Chart.js Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // --- 1. Line Chart: Tần suất tấn công ---
            const dates = {!! json_encode($dates) !!};
            const attackSeries = {!! json_encode($attackSeries) !!};

            const types = Object.keys(attackSeries);
            const datasets = types.map((type, index) => {
                const colors = {
                    'SQLi': '#3b82f6',
                    'XSS': '#ec4899',
                    'Path_Traversal': '#f59e0b',
                    'DOS': '#8b5cf6',
                    'Malicious_Upload': '#ef4444'
                };
                const color = colors[type] || '#64748b';

                return {
                    label: type,
                    data: dates.map(date => attackSeries[type][date] || 0),
                    borderColor: color,
                    backgroundColor: color + '1a',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                };
            });

            const ctxHistory = document.getElementById('attackHistoryChart').getContext('2d');
            new Chart(ctxHistory, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: datasets.length > 0 ? datasets : [{
                        label: 'Không có dữ liệu',
                        data: dates.map(() => 0),
                        borderColor: '#cbd5e1',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    },
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // --- 2. Doughnut Chart: Phân loại hình thức tấn công ---
            const attackTypeCounts = {!! json_encode($attackTypeCounts) !!};
            const pieLabels = Object.keys(attackTypeCounts);
            const pieData = Object.values(attackTypeCounts);
            const pieColors = pieLabels.map(label => {
                const colors = {
                    'SQLi': '#3b82f6',
                    'XSS': '#ec4899',
                    'Path_Traversal': '#f59e0b',
                    'DOS': '#8b5cf6',
                    'Malicious_Upload': '#ef4444'
                };
                return colors[label] || '#64748b';
            });

            const ctxType = document.getElementById('attackTypeChart').getContext('2d');
            new Chart(ctxType, {
                type: 'doughnut',
                data: {
                    labels: pieLabels.length > 0 ? pieLabels : ['An toàn'],
                    datasets: [{
                        data: pieData.length > 0 ? pieData : [1],
                        backgroundColor: pieColors.length > 0 ? pieColors : ['#e2e8f0'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>
@endsection