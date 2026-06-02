@extends('layouts.main')

@section('content')
<div class="card mx-auto" style="max-width: 1150px;">
    <div class="card-header text-center">
        <h3 class="mb-0 fw-semibold">Incident / Activity Report Detail</h3>
    </div>

    <div class="card-body p-4 p-md-5">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <tbody>
                    <tr>
                        <th>Requestor</th>
                        <td>{{ $report->requestor }}</td>
                    </tr>
                    <tr>
                        <th>Requestor Email</th>
                        <td>{{ $report->requestor_email }}</td>
                    </tr>
                    <tr>
                        <th>Report Date</th>
                        <td>
                            @php
                                $dateObj = \Carbon\Carbon::parse($report->request_date);
                                $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            @endphp
                            {{ $days[$dateObj->dayOfWeek] }}, {{ $dateObj->day }} {{ $months[$dateObj->month - 1] }} {{ $dateObj->year }}
                        </td>
                    </tr>
                    <tr>
                        <th>Report Time</th>
                        <td>{{ \Carbon\Carbon::parse($report->report_time)->format('H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Application</th>
                        <td>{{ $report->apps }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $report->description }}</td>
                    </tr>
                    <tr>
                        <th>Severity</th>
                        <td>
                            <span class="badge bg-{{ $report->severity <= 2 ? 'danger' : ($report->severity == 3 ? 'warning' : 'secondary') }}">
                                {{ $report->severity }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Assigned To</th>
                        <td>{{ $report->assigned_to }}</td>
                    </tr>
                    <tr>
                        <th>Scope / Root Cause</th>
                        <td>{{ $report->scope }}</td>
                    </tr>
                    <tr>
                        <th>Response Time</th>
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
                    </tr>

                    <tr>
                        <th>Restored Time</th>
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
                                    <span class="text-success">Immediate Restoration</span>
                                @endif
                            @else
                                <span class="text-muted">Not yet restored</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>Resolved Time</th>
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
                    </tr>
                    <tr>
                        <th>Status</th>
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
                    <tr>
                        <th>INC (if any)</th>
                        <td>{{ $report->incident ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Resolution</th>
                        <td style="white-space: pre-wrap;">{{ $report->resolution ?? 'Not yet resolved' }}</td>
                    </tr>

                    <tr>
                        <th>Closed At</th>
                        <td>
                            @if($report->closed_at)
                                @php
                                    $closedDate = \Carbon\Carbon::parse($report->closed_at);
                                    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                @endphp
                                {{ $days[$closedDate->dayOfWeek] }}, 
                                {{ $closedDate->day }} 
                                {{ $months[$closedDate->month - 1] }} 
                                {{ $closedDate->year }} 
                                pukul {{ $closedDate->format('H:i') }}
                            @else
                                <span class="text-muted">Belum ditutup</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>File Downtime Evidence</th>
                        <td>
                            @if($report->file_downtime_evidence)
                                @php
                                    $ext = strtolower(pathinfo($report->file_downtime_evidence, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                @endphp
                                
                                <a href="{{ $report->file_downtime_evidence_url }}" 
                                   target="_blank" 
                                   class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                                    <i class="bi {{ $isImage ? 'bi-image' : 'bi-file-earmark' }}"></i>
                                    {{ $report->file_downtime_evidence }}
                                </a>
                                
                                <small class="text-muted d-block mt-1">
                                    @if($isImage)
                                        Klik untuk melihat gambar
                                    @else
                                        Klik untuk download file
                                    @endif
                                </small>
                            @else
                                <span class="text-muted">Tidak ada file terlampir</span>
                            @endif
                        </td>
                    </tr>
                    
                    @if(!empty($report->rca))
                    <tr>
                        <th>Root Cause Analysis (RCA)</th>
                        <td>
                            <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-2" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#rcaCollapse"
                                    id="rcaToggleBtn">
                                <span>RCA</span>
                                <span id="rcaIcon">▼</span>
                            </button>
                            <div class="collapse mt-3" id="rcaCollapse">
                                <div class="card card-body rca-content">
                                    {!! $report->rca !!}
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 align-items-center">
                
                {{-- @if(!auth()->user()->isViewer())
                    
                    @if(auth()->user()->isAdmin() || $report->status == 1)
                        <a href="{{ route('report.edit', $report->uuid) }}" 
                        class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-edit me-2"></i> Edit Report
                        </a>
                    @else
                        <button class="btn btn-secondary btn-lg px-5" disabled>
                            <i class="fas fa-lock me-2"></i> Edit Report (Closed)
                        </button>
                    @endif

                @endif --}}

                <a href="{{ route('report.edit', $report->uuid) }}" 
                    class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-edit me-2"></i> Edit Report
                </a>

                <a href="{{ route('report.index') }}" 
                class="btn btn-outline-secondary btn-lg px-5">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>

            </div>
        </div>

    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    const collapse = document.getElementById('rcaCollapse');
    const icon = document.getElementById('rcaIcon');

    if (collapse && icon) {
        collapse.addEventListener('show.bs.collapse', function () {
            icon.textContent = '▲';
        });

        collapse.addEventListener('hide.bs.collapse', function () {
            icon.textContent = '▼';
        });
    }
});
</script>