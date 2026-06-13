@extends('layouts.admin')

@section('content')
    <div class="security-reports-container" style="padding: 20px; font-family: 'Inter', sans-serif;">

        <!-- Header -->
        <div style="margin-bottom: 25px;">
            <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;"> Báo cáo đánh giá an ninh ( AI)
            </h1>
            <p style="font-size: 14px; color: #64748b; margin: 5px 0 0 0;">Phân tích logs, nhận diện hành vi tấn công và đề
                xuất tự động từ AI khi quản trị viên vắng mặt.</p>
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

        <!-- Request New Report Form -->
        <div
            style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); margin-bottom: 30px;">
            <h3 style="font-size: 16px; font-weight: 650; color: #1e293b; margin: 0 0 15px 0;"> Yêu cầu AI tổng hợp báo cáo
            </h3>
            <form method="POST" action="{{ route('admin.security.reports.generate') }}"
                style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                @csrf
                <div style="width: 200px;">
                    <label style="display: block; font-size: 12px; color: #64748b; margin-bottom: 5px; font-weight: 600;">Từ
                        ngày (Start Date)</label>
                    <input type="date" name="start_date" required
                        style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                </div>
                <div style="width: 200px;">
                    <label
                        style="display: block; font-size: 12px; color: #64748b; margin-bottom: 5px; font-weight: 600;">Đến
                        ngày (End Date)</label>
                    <input type="date" name="end_date" required
                        style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                </div>
                <div>
                    <button type="submit"
                        style="background: #6366f1; color: #ffffff; border: none; padding: 10px 20px; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        Yêu cầu phân tích
                    </button>
                </div>
            </form>
        </div>

        <!-- Reports History -->
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">

            <!-- Left: List of Reports -->
            <div
                style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); max-height: 500px; overflow-y: auto;">
                <h3 style="font-size: 16px; font-weight: 650; color: #1e293b; margin: 0 0 15px 0;"> Lịch sử báo cáo đã tạo
                </h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @forelse($reports as $index => $report)
                        <div class="report-item" onclick="showReport({{ $index }})"
                            style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s;"
                            onmouseover="this.style.borderColor='#6366f1'" onmouseout="this.style.borderColor='#e2e8f0'">
                            <strong style="display: block; font-size: 14px; color: #334155;">Khoảng thời gian:</strong>
                            <span
                                style="font-size: 13px; color: #475569; font-family: monospace;">{{ $report->start_date->format('d/m/Y') }}
                                - {{ $report->end_date->format('d/m/Y') }}</span>

                            <div
                                style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #64748b;">
                                <span>Đợt chặn: <strong>{{ $report->total_attacks }}</strong></span>
                                <span style="color: #ef4444;">Nguy cấp: <strong>{{ $report->critical_events }}</strong></span>
                            </div>
                        </div>
                    @empty
                        <p style="font-size: 14px; color: #94a3b8; margin: 0;">Chưa có báo cáo nào được tạo.</p>
                    @endforelse
                </div>
            </div>

            <!-- Right: Report View Detail -->
            <div
                style="background: #ffffff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); min-height: 400px;">
                <div id="no-report-selected"
                    style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 350px; color: #94a3b8;">
                    <span style="font-size: 48px; margin-bottom: 15px;"></span>
                    <p style="font-size: 14px; margin: 0;">Chọn một báo cáo ở danh sách bên trái để đọc phân tích từ
                        AI.</p>
                </div>

                <div id="report-detail-container" style="display: none;">
                    <div
                        style="border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h2 id="report-title" style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Báo
                                cáo bảo mật RoseShop</h2>
                            <span id="report-dates"
                                style="font-size: 13px; color: #64748b; font-family: monospace; display: block; margin-top: 5px;"></span>
                        </div>
                        <div style="font-size: 13px; color: #475569;">
                            Số đợt chặn: <strong id="report-count" style="color: #2563eb;">0</strong> | Sự kiện nguy cấp:
                            <strong id="report-critical" style="color: #dc2626;">0</strong>
                        </div>
                    </div>

                    <!-- Markdown Content Rendered -->
                    <div id="report-markdown-content"
                        style="font-size: 15px; line-height: 1.6; color: #334155; max-height: 500px; overflow-y: auto;">
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Markdown rendering library -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        const reports = {!! json_encode($reports) !!};

        function showReport(index) {
            const report = reports[index];
            if (!report) return;

            // Hide placeholder, show content container
            document.getElementById('no-report-selected').style.display = 'none';
            document.getElementById('report-detail-container').style.display = 'block';

            // Parse date strings to display format dd/mm/yyyy
            const formatDate = (dateStr) => {
                const date = new Date(dateStr);
                return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
            };

            // Set header info
            document.getElementById('report-title').innerText = `🛡️ Báo cáo đánh giá an ninh RoseShop`;
            document.getElementById('report-dates').innerText = `Khoảng thời gian: ${formatDate(report.start_date)} - ${formatDate(report.end_date)}`;
            document.getElementById('report-count').innerText = report.total_attacks;
            document.getElementById('report-critical').innerText = report.critical_events;

            // Render Markdown content
            const markdownContent = report.summary;
            document.getElementById('report-markdown-content').innerHTML = marked.parse(markdownContent);
        }
    </script>

    <style>
        /* Styling Markdown headers inside report */
        #report-markdown-content h1,
        #report-markdown-content h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 5px;
        }

        #report-markdown-content h3 {
            font-size: 15px;
            font-weight: 650;
            color: #1e293b;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        #report-markdown-content p {
            margin-bottom: 15px;
        }

        #report-markdown-content ul,
        #report-markdown-content ol {
            margin-bottom: 15px;
            padding-left: 20px;
        }

        #report-markdown-content li {
            margin-bottom: 5px;
        }

        #report-markdown-content blockquote {
            background: #f8fafc;
            border-left: 4px solid #6366f1;
            padding: 10px 15px;
            margin: 15px 0;
            color: #475569;
        }
    </style>
@endsection