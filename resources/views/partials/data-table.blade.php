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
                <a href="{{ route('report.export', array_merge(request()->only('search', 'start_date', 'end_date'), ['page' => request('page', 1)])) }}" 
                   class="btn btn-success btn-lg px-4 py-2 shadow-sm">
                    <i class="fas fa-file-excel me-2"></i> 
                    Export Excel
                </a>
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
                <th>Severity</th>
                <th>Response Time</th>
                <th>Restored Time</th>
                <th>Resolved Time</th>
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
                    <td data-iso-date="{{ $report->request_date->format('Y-m-d') }}" data-time="{{ $report->report_time }}"></td>
                    <td>{{ $report->apps }}</td>
                    <td>
                        <span class="badge bg-{{ $report->severity <= 2 ? 'danger' : ($report->severity == 3 ? 'warning' : 'secondary') }}">
                            {{ $report->severity }}
                        </span>
                    </td>
                    <td>
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
                        @if($report->servicerestored_time && $report->report_time && $report->request_date)
                            @php
                                $reportTime   = \Carbon\Carbon::parse($report->request_date->format('Y-m-d') . ' ' . $report->report_time);
                                $restoredTime = \Carbon\Carbon::parse($report->servicerestored_time);
                                $duration     = $restoredTime->diff($reportTime);
                            @endphp
                            @if($duration->d > 0) {{ $duration->d }} Hari @endif
                            @if($duration->h > 0) {{ $duration->h }} Jam @endif
                            @if($duration->i > 0) {{ $duration->i }} Menit @endif

                            @if($duration->d == 0 && $duration->h == 0 && $duration->i == 0)
                                <span class="text-success">Immediate</span>
                            @endif
                        @else
                            <span class="text-muted">Not yet restored</span>
                        @endif
                    </td>

                    <td>
                        @if($report->resolved_time && $report->created_at)
                            @php
                                $resolvedDuration = $report->resolved_time->diff($report->created_at);
                            @endphp
                            @if($resolvedDuration->d > 0) {{ $resolvedDuration->d }} Hari @endif
                            @if($resolvedDuration->h > 0) {{ $resolvedDuration->h }} Jam @endif
                            @if($resolvedDuration->i > 0) {{ $resolvedDuration->i }} Menit @endif
                        @else
                            <span class="text-muted">Not yet resolved</span>
                        @endif
                    </td>
                    <td>
                        @if($report->status == 0)
                            <span class="badge bg-danger">Closed</span>
                        @elseif($report->status == 1)
                            <span class="badge bg-success">Open</span>
                        @else
                            <span class="badge bg-warning">Restored</span>
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
</script>