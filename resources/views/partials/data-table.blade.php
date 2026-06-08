<div class="mb-4">
    <form method="GET" action="{{ route('report.index') }}">
        
        <div class="input-group input-group-lg mb-3">
            <span class="input-group-text bg-light border-end-0">
                <i class="bi bi-search"></i>
            </span>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                class="form-control" 
                placeholder="Search..."
            >
            <button type="submit" class="btn btn-primary">Search</button>
        </div>

        <div class="d-flex justify-content-between align-items-end gap-3">
            
            <div class="d-flex gap-3">
                <div>
                    <label for="startDate" class="form-label fw-semibold small">Start Date</label>
                    <input 
                        type="date" 
                        name="start_date" 
                        value="{{ request('start_date') }}" 
                        id="startDate" 
                        class="form-control"
                        placeholder="dd/mm/yyyy"
                    >
                </div>

                <div>
                    <label for="endDate" class="form-label fw-semibold small">End Date</label>
                    <input 
                        type="date" 
                        name="end_date" 
                        value="{{ request('end_date') }}" 
                        id="endDate" 
                        class="form-control"
                        placeholder="dd/mm/yyyy"
                    >
                </div>

                <div class="d-flex align-items-end">
                    <a href="{{ route('report.index') }}" 
                       class="btn btn-outline-secondary">
                        Reset Filter
                    </a>
                </div>
            </div>

            <div>
                <button type="button" id="btnExportExcel" class="btn btn-success btn-lg px-4 py-2 shadow-sm">
                    <i class="fas fa-file-excel me-2"></i>
                    Export Excel
                </button>
            </div>

            {{-- Loading Overlay --}}
            <div id="exportOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                <div class="bg-white rounded-4 shadow-lg p-5 text-center" style="min-width:340px; max-width:420px;">
                    <div class="mb-4">
                        <div class="spinner-border text-success mb-3" style="width:3rem;height:3rem;" role="status"></div>
                        <h5 class="fw-bold mb-1" id="exportStatusTitle">Mempersiapkan Export</h5>
                        <p class="text-muted small mb-0" id="exportStatusDesc">Sedang menghitung jumlah data...</p>
                    </div>

                    <div class="progress mb-3" style="height:10px; border-radius:10px;">
                        <div id="exportProgressBar"
                            class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                            style="width: 0%; transition: width 0.5s ease;">
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2 flex-wrap" id="exportSteps">
                        <span class="badge bg-secondary px-3 py-2" id="step1">
                            <i class="fas fa-database me-1"></i> Menghitung Data
                        </span>
                        <span class="badge bg-secondary px-3 py-2" id="step2">
                            <i class="fas fa-cogs me-1"></i> Memproses
                        </span>
                        <span class="badge bg-secondary px-3 py-2" id="step3">
                            <i class="fas fa-file-excel me-1"></i> Membuat File
                        </span>
                        <span class="badge bg-secondary px-3 py-2" id="step4">
                            <i class="fas fa-download me-1"></i> Mengunduh
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </form>
</div>

<div class="table-responsive">
    <table id="reportsTable" class="table table-hover table-bordered align-middle">
        <thead>
            <tr>
                <th>No</th>
                <th>Code</th>
                <th>Requestor</th>
                <th>Date & Time</th>
                <th>Application</th>
                <th>Description</th>
                <th>Severity</th>
                <th>Assigned To</th>
                {{-- <th>Response Time</th>
                <th>Restored Time</th>
                <th>Resolved Time</th> --}}
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $report)
                <tr data-id="{{ $report->uuid }}">
                    <td>{{ $index + 1 + ($reports->currentPage() - 1) * $reports->perPage() }}</td>
                    <td>
                        @if($report->incident)
                            {{ $report->incident }}
                        @else
                            <span class="text-muted">No Incident</span>
                        @endif
                    </td>
                    <td>{{ $report->requestor }}</td>
                    <td style="white-space: pre-wrap; word-break: break-word;" data-iso-date="{{ $report->request_date->format('Y-m-d') }}" data-time="{{ $report->report_time }}"></td>
                    <td>{{ $report->apps }}</td>
                    <td title="{{ $report->description }}">
                        <div style="
                            display: -webkit-box;
                            -webkit-line-clamp: 3;
                            -webkit-box-orient: vertical;
                            overflow: hidden;
                            white-space: normal;
                            word-break: break-word;
                            max-width: 250px;
                        ">{{ $report->description }}</div>
                    </td>
                    <td>
                        <span class="badge bg-{{ $report->severity <= 2 ? 'danger' : ($report->severity == 3 ? 'warning' : 'secondary') }}">
                            {{ $report->severity }}
                        </span>
                    </td>
                    <td>{{ $report->assigned_to }}</td>
                    {{-- <td>
                        @if($report->response_time && $report->created_at)
                            @php
                                $issueTime = \Carbon\Carbon::parse($report->request_date->format('Y-m-d') . ' ' . $report->report_time);
                                $responseDuration = $report->created_at->diff($issueTime);
                            @endphp
                            @if($responseDuration->d > 0) {{ $responseDuration->d }} Hari @endif
                            @if($responseDuration->h > 0) {{ $responseDuration->h }} Jam @endif
                            @if($responseDuration->i > 0) {{ $responseDuration->i }} Menit @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td>
                        @if($report->servicerestored_time && $report->created_at)
                            @php
                                $createdAt    = \Carbon\Carbon::parse($report->created_at);
                                $restoredTime = \Carbon\Carbon::parse($report->servicerestored_time);
                                $duration     = $restoredTime->diff($createdAt);
                            @endphp

                            @if($duration->d > 0) {{ $duration->d }} Hari @endif
                            @if($duration->h > 0) {{ $duration->h }} Jam @endif
                            @if($duration->i > 0) {{ $duration->i }} Menit @endif

                            @if($duration->d == 0 && $duration->h == 0 && $duration->i == 0)
                                <span class="text-success">Immediate</span>
                            @endif
                        @else
                            @if($report->type === 'Activity' || $report->type === 'Request')
                                <span class="text-muted">-</span>
                            @else
                                <span class="text-muted">Not yet restored</span>
                            @endif
                        @endif
                    </td>

                    <td>
                        @if($report->closed_at && $report->created_at)
                            @php
                                $closedAt = \Carbon\Carbon::parse($report->closed_at);
                                $createdAt = \Carbon\Carbon::parse($report->created_at);
                                $resolvedDuration = $closedAt->diff($createdAt);
                            @endphp

                            @if($resolvedDuration->d > 0) {{ $resolvedDuration->d }} Hari @endif
                            @if($resolvedDuration->h > 0) {{ $resolvedDuration->h }} Jam @endif
                            @if($resolvedDuration->i > 0) {{ $resolvedDuration->i }} Menit @endif

                            @if($resolvedDuration->d == 0 && $resolvedDuration->h == 0 && $resolvedDuration->i == 0)
                                <span class="text-success">Immediate</span>
                            @endif
                        @else
                            @if($report->type === 'Activity' || $report->type === 'Request')
                                <span class="text-muted">-</span>
                            @else
                                <span class="text-muted">Not yet resolved</span>
                            @endif
                        @endif
                    </td> --}}
                    <td>
                        @if($report->status == 0)
                            <span class="badge bg-danger">Closed</span>
                        @elseif($report->status == 1)
                            <span class="badge bg-success">Open</span>
                        @elseif($report->status == 2)
                            <span class="badge bg-warning text-dark">Restored</span>
                        @elseif($report->status == 4)
                            <span class="badge bg-success">Done</span>
                        @elseif($report->status == 5)
                            <span class="badge bg-warning text-dark">Done Partial</span>
                        @elseif($report->status == 6)
                            <span class="badge bg-danger">Rollback</span>
                        @else
                            <span class="badge bg-secondary">Unknown</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-5">
                        No reports found matching your search
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
    <div class="text-muted small">
        Showing {{ $reports->firstItem() ?? 0 }} 
        to {{ $reports->lastItem() ?? 0 }} 
        of {{ $reports->total() }} results
    </div>
    <div>
        {{ $reports->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
</div>

<script>
function formatIndonesianDate(isoDate, time) {
    if (!isoDate) return '';
    const dateObj = new Date(isoDate + 'T' + (time || '00:00:00'));
    if (isNaN(dateObj.getTime())) return '';

    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    return `${days[dateObj.getDay()]}, ${dateObj.getDate()} ${months[dateObj.getMonth()]} ${dateObj.getFullYear()} ${dateObj.getHours().toString().padStart(2,'0')}:${dateObj.getMinutes().toString().padStart(2,'0')}`;
}

document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.querySelector('#reportsTable tbody');

    document.querySelectorAll('td[data-iso-date]').forEach(cell => {
        if (cell.dataset.isoDate) {
            cell.textContent = formatIndonesianDate(
                cell.dataset.isoDate,
                cell.dataset.time
            );
        }
    });

    tbody.addEventListener('click', function(e) {
        const row = e.target.closest('tr');
        if (!row || row.cells.length === 1) return;
        const id = row.dataset.id;
        if (id) window.location.href = `/reportShow/${id}`;
    });
});

document.getElementById('btnExportExcel').addEventListener('click', function () {
    const overlay      = document.getElementById('exportOverlay');
    const progressBar  = document.getElementById('exportProgressBar');
    const statusTitle  = document.getElementById('exportStatusTitle');
    const statusDesc   = document.getElementById('exportStatusDesc');
    const steps        = ['step1', 'step2', 'step3', 'step4'];

    function setStep(stepIndex, progress, title, desc) {
        progressBar.style.width = progress + '%';
        statusTitle.textContent = title;
        statusDesc.textContent  = desc;

        steps.forEach((id, i) => {
            const el = document.getElementById(id);
            el.className = 'badge px-3 py-2 ' + (
                i < stepIndex  ? 'bg-success' :
                i === stepIndex ? 'bg-primary'  : 'bg-secondary'
            );
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    const exportUrl = new URL('{{ route("report.export") }}', window.location.origin);

    if (urlParams.has('search')) exportUrl.searchParams.set('search', urlParams.get('search'));
    if (urlParams.has('start_date')) exportUrl.searchParams.set('start_date', urlParams.get('start_date'));
    if (urlParams.has('end_date')) exportUrl.searchParams.set('end_date', urlParams.get('end_date'));
    if (urlParams.has('page')) exportUrl.searchParams.set('page', urlParams.get('page'));

    overlay.style.display = 'flex';
    setStep(0, 10, 'Mempersiapkan Export', 'Sedang menghitung jumlah data...');

    const countUrl = new URL('{{ route("report.exportCount") }}', window.location.origin);
    if (urlParams.has('search')) countUrl.searchParams.set('search', urlParams.get('search'));
    if (urlParams.has('start_date')) countUrl.searchParams.set('start_date', urlParams.get('start_date'));
    if (urlParams.has('end_date')) countUrl.searchParams.set('end_date', urlParams.get('end_date'));

    fetch(countUrl.toString())
        .then(res => res.json())
        .then(data => {
            const count = data.count || 0;
            setStep(1, 35, 'Memproses Data', `Ditemukan ${count.toLocaleString('id-ID')} data...`);

            setTimeout(() => {
                setStep(2, 65, 'Membuat File Excel', 'Sedang menyusun format dan styling...');

                setTimeout(() => {
                    setStep(3, 85, 'Mengunduh File', 'File sedang diunduh...');

                    window.location.href = exportUrl.toString();

                    setTimeout(() => {
                        setStep(4, 100, 'Selesai!', 'File berhasil diunduh');
                        progressBar.classList.remove('progress-bar-animated');

                        setTimeout(() => {
                            overlay.style.display = 'none';
                            progressBar.style.width = '0%';
                            progressBar.classList.add('progress-bar-animated');
                        }, 1800);
                    }, 1200);
                }, 900);
            }, 800);
        })
        .catch(() => {
            overlay.style.display = 'none';
            alert('Gagal memulai export. Silakan coba lagi.');
        });
});
</script>