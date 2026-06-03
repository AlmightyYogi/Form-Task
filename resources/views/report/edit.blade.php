@extends('layouts.main')
@section('content')
<div class="container">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 950px; border-radius: 16px;">
        
        <div class="card-header bg-white border-0 text-center py-4">
            <h4 class="fw-bold mb-0">Edit Report</h4>
        </div>
        <div class="card-body px-4 px-md-5 py-4">
            <form method="POST" action="{{ route('report.update', $report->uuid) }}" enctype="multipart/form-data" id="editForm">
                @csrf
                @method('PATCH')
                @if(!$report->status && !auth()->user()->isAdmin())
                    <div class="alert alert-warning text-center mb-4">
                        <strong>Ticket ini sudah Closed.</strong><br>
                        Hanya Admin yang dapat mengedit ticket Closed.
                    </div>
                @endif
                <input type="hidden" name="status" value="{{ $report->status }}" id="statusInput">
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3 text-muted">Basic Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Code</label>
                            <input type="text" class="form-control bg-light" value="{{ $report->incident ?? 'No Incident' }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Requestor <span class="text-danger">*</span></label>
                            <input type="text" name="requestor" class="form-control"
                                value="{{ old('requestor', $report->requestor) }}" required
                                @if(!auth()->user()->isAdmin()) readonly @endif>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Email <span class="text-danger">*</span></label>
                            <input type="email" name="requestor_email" class="form-control"
                                value="{{ old('requestor_email', $report->requestor_email) }}" required
                                @if(!auth()->user()->isAdmin()) readonly @endif>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Date <span class="text-danger">*</span></label>
                            <input type="date" name="request_date" class="form-control"
                                value="{{ old('request_date', $report->request_date->format('Y-m-d')) }}" required
                                @if(!auth()->user()->isAdmin()) disabled @endif>
                            @if(!auth()->user()->isAdmin())
                                <input type="hidden" name="request_date" value="{{ $report->request_date->format('Y-m-d') }}">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Time <span class="text-danger">*</span></label>
                            <input type="time" name="report_time" class="form-control"
                                value="{{ old('report_time', $report->report_time) }}" required
                                @if(!auth()->user()->isAdmin()) disabled @endif>
                            @if(!auth()->user()->isAdmin())
                                <input type="hidden" name="report_time" value="{{ $report->report_time }}">
                            @endif
                        </div>
                    </div>
                </div>
                <hr class="my-4">
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3 text-muted">Application & Type</h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Application <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                @foreach(["B2B Portal", "MPR", "SQA Portal", "SARAS", "SECM Portal", "My Dashboard", "DBEST", "PSSHUB", "IOT Middleware TransJakarta"] as $app)
                                <div class="col-6">
                                    <div class="form-check">
                                        <input type="radio" name="apps" value="{{ $app }}" class="form-check-input"
                                            {{ old('apps', $report->apps) == $app ? 'checked' : '' }}
                                            @if(!auth()->user()->isAdmin()) disabled @endif>
                                        <label class="form-check-label small">{{ $app }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @if(!auth()->user()->isAdmin())
                                <input type="hidden" name="apps" value="{{ $report->apps }}">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                @foreach(["Incident", "Request", "Activity"] as $type)
                                <div class="form-check">
                                    <input type="radio" name="type" value="{{ $type }}" class="form-check-input type-radio"
                                        {{ old('type', $report->type ?? 'Incident') == $type ? 'checked' : '' }}
                                        @if(!auth()->user()->isAdmin()) disabled @endif>
                                    <label class="form-check-label">{{ $type }}</label>
                                </div>
                                @endforeach
                            </div>
                            @if(!auth()->user()->isAdmin())
                                <input type="hidden" name="type" value="{{ $report->type }}">
                            @endif
                        </div>
                    </div>
                </div>
                <hr class="my-4">
                <div id="dynamicFields" style="display:none;">
                    <h6 class="fw-semibold mb-3 text-muted">Details</h6>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label small text-muted" id="priorityLegend"></label>
                            <div id="priorityOptions"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Assigned To <span class="text-danger">*</span></label>
                            <div id="assignedOptions"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Scope <span class="text-danger">*</span></label>
                            <div id="scopeOptions"></div>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small text-muted">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="4" required
                        @if(!auth()->user()->isAdmin()) readonly @endif>{{ old('description', $report->description) }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label small text-muted">Upload Evidence <small class="text-muted">(Max 5 Files)</small></label>
                    <div id="downtimeEvidenceFields">
                        @if(!empty($report->file_downtime_evidence))
                            @foreach($report->file_downtime_evidence as $existingFile)
                                <div class="downtime-evidence-row d-flex align-items-center gap-2 mb-2 existing-downtime-row">
                                    <input type="text" class="form-control bg-light" value="{{ $existingFile }}" readonly>
                                    <input type="hidden" name="existing_downtime_files[]" value="{{ $existingFile }}">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-existing-downtime" title="Remove file">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        @endif

                        @php $existingDowntimeCount = !empty($report->file_downtime_evidence) ? count($report->file_downtime_evidence) : 0; @endphp
                        @if($existingDowntimeCount < 5)
                        <div class="downtime-evidence-row d-flex align-items-center gap-2 mb-2">
                            <input type="file" name="file_downtime_evidence[]" class="form-control downtime-evidence-input"
                                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                            <button type="button" class="btn btn-success btn-sm btn-add-downtime-evidence" title="Add file">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        @endif
                    </div>
                    <small class="text-muted fw-semibold">Current Files:</small>
                    <div id="previewDowntimeFiles" class="mt-2 d-flex flex-wrap gap-2"></div>
                </div>

                <hr class="mb-4" id="hrExternalTeam" style="display: {{ $report->type === 'Incident' ? 'block' : 'none' }};">

                <div class="mb-5" id="sectionExternalTeam" style="display: {{ $report->type === 'Incident' ? 'block' : 'none' }};">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-semibold mb-1">Handled by External Team</h6>
                            <small class="text-muted">External Handling Duration: 
                                <span id="externalDuration">
                                    @php
                                        $totalMin = $report->externalTeams->sum('duration');
                                        $h = intdiv($totalMin, 60);
                                        $m = $totalMin % 60;
                                    @endphp
                                    {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}:00
                                </span>
                            </small>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="handled_by" value="1">
                            <input class="form-check-input" type="checkbox" 
                                   name="handled_by" value="1" id="handledByToggle"
                                   @if(!auth()->user()->isAdmin() && !$report->status) disabled @endif
                                   {{ old('handled_by', $report->handled_by) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="handledByToggle"></label>
                        </div>
                    </div>

                    <div id="externalTeamSection" style="display: {{ $report->isExternal() ? 'block' : 'none' }};">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button type="button" id="btnAddExternal" class="btn btn-primary btn-sm px-3"
                                @if(!auth()->user()->isAdmin() && !$report->status) disabled @endif>
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="externalTeamTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>External Team</th>
                                        <th>PIC</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Duration</th>
                                        <th>Files</th>
                                        <th width="120">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($report->externalTeams as $ext)
                                    <tr data-id="{{ $ext->id }}"
                                        data-start="{{ $ext->start_time ? $ext->start_time->format('Y-m-d\TH:i') : '' }}"
                                        data-end="{{ $ext->end_time ? $ext->end_time->format('Y-m-d\TH:i') : '' }}">
                                        <td>{{ $ext->externalTeam->name ?? '-' }}</td>
                                        <td>{{ $ext->pic ?? '-' }}</td>
                                        <td>{{ $ext->start_time ? $ext->start_time->translatedFormat('j F Y H:i') : '-' }}</td>
                                        <td>{{ $ext->end_time ? $ext->end_time->translatedFormat('j F Y H:i') : '-' }}</td>
                                        <td>
                                            @if($ext->duration !== null)
                                                @php
                                                    $h = intdiv($ext->duration, 60);
                                                    $m = $ext->duration % 60;
                                                @endphp
                                                {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}:00
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($ext->evidence_file_external))
                                                <button type="button" class="btn btn-sm btn-info btn-view-files me-1"
                                                    data-files="{{ json_encode($ext->evidence_file_external) }}">
                                                    View files
                                                </button>
                                            @else
                                                <span class="text-muted small">No files</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-edit-external"data-files='@json($ext->evidence_file_external ?? [])'
                                                @if(!auth()->user()->isAdmin() && !$report->status) disabled @endif>Edit</button>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-external"
                                                @if(!auth()->user()->isAdmin() && !$report->status) disabled @endif>Del</button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">
                                            <i class="fas fa-inbox me-1"></i> There is no External Handle data yet
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <hr class="mb-4" id="hrServiceRestoration" style="display: {{ $report->type === 'Incident' ? 'block' : 'none' }};">

                <div class="mb-5" id="sectionServiceRestoration" style="display: {{ $report->type === 'Incident' ? 'block' : 'none' }};">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-semibold mb-1">Service Restoration</h6>
                            <div id="restorationInfo">
                                @if($report->servicerestored_time)
                                    <small class="text-muted">
                                        Restored on {{ $report->servicerestored_time->translatedFormat('D, j M Y') }} at 
                                        {{ $report->servicerestored_time->format('H:i') }}
                                    </small><br>
                                    <small class="text-muted">
                                        Total: 
                                        @php
                                            $totalSec = (int) ($report->restored_time ?? 0);
                                            $extSec   = (int) $report->externalTeams->sum('duration') * 60;
                                            $intSec   = max(0, $totalSec - $extSec);
                                            $totalH   = intdiv($totalSec, 3600); $totalM = intdiv($totalSec % 3600, 60); $totalS = $totalSec % 60;
                                            $intH     = intdiv($intSec, 3600);   $intM   = intdiv($intSec % 3600, 60);   $intS   = $intSec % 60;
                                            $extH     = intdiv($extSec, 3600);   $extM   = intdiv($extSec % 3600, 60);   $extS   = $extSec % 60;
                                        @endphp
                                        {{ str_pad($totalH,2,'0',STR_PAD_LEFT) }}:{{ str_pad($totalM,2,'0',STR_PAD_LEFT) }}:{{ str_pad($totalS,2,'0',STR_PAD_LEFT) }}
                                        &bull; Internal: {{ str_pad($intH,2,'0',STR_PAD_LEFT) }}:{{ str_pad($intM,2,'0',STR_PAD_LEFT) }}:{{ str_pad($intS,2,'0',STR_PAD_LEFT) }}
                                        &bull; External: {{ str_pad($extH,2,'0',STR_PAD_LEFT) }}:{{ str_pad($extM,2,'0',STR_PAD_LEFT) }}:{{ str_pad($extS,2,'0',STR_PAD_LEFT) }}
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="servicerestored_active" value="0">
                            <input class="form-check-input" type="checkbox"
                                id="serviceRestoredToggle"
                                {{ $report->servicerestored_time ? 'checked disabled' : '' }}
                                {{ $report->servicerestored_time || (!auth()->user()->isAdmin() && !$report->status) ? 'disabled' : '' }}>
                            <label class="form-check-label fw-semibold" for="serviceRestoredToggle"></label>
                        </div>
                    </div>

                    <input type="hidden" name="servicerestored_time" id="servicerestoredTimeInput"
                        value="{{ $report->servicerestored_time ? 
                            $report->servicerestored_time->format('Y-m-d H:i:s') : '' }}">

                    <div id="serviceRestorationContent" style="display: {{ $report->servicerestored_time ? 'block' : 'none' }};">

                        <div class="mb-4">
                            <label class="form-label small text-muted">Resolution <span class="text-danger">*</span></label>
                            <textarea name="resolution" id="resolutionTextArea" class="form-control" rows="4" 
                                placeholder="Jelaskan langkah resolusi..."
                                @if(!auth()->user()->isAdmin() && !$report->status) readonly @endif>{{ old('resolution', $report->resolution) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small text-muted">Restoration Evidence <small class="text-muted">(Max 5 Files)</small></label>
                            <div id="restorationEvidenceFields">
                                @if(!empty($report->restoration_evidence))
                                    @foreach($report->restoration_evidence as $existingFile)
                                        <div class="restoration-evidence-row d-flex align-items-center gap-2 mb-2 existing-restoration-row">
                                            <input type="text" class="form-control bg-light" value="{{ $existingFile }}" readonly>
                                            <input type="hidden" name="existing_restoration_files[]" value="{{ $existingFile }}">
                                            @if(auth()->user()->isAdmin() || $report->status)
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-existing-restoration" title="Remove file">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                @php $existingCount = !empty($report->restoration_evidence) ? count($report->restoration_evidence) : 0; @endphp
                                    @if((auth()->user()->isAdmin() || $report->status) && $existingCount < 5)
                                    <div class="restoration-evidence-row d-flex align-items-center gap-2 mb-2">
                                        <input type="file" name="restoration_evidence[]" class="form-control restoration-evidence-input"
                                            accept="image/*, .gif, .pdf, .doc, .docx, .xls, .xlsx, .zip"
                                            @if(!auth()->user()->isAdmin() && !$report->status) disabled @endif>
                                        <button type="button" class="btn btn-success btn-sm btn-add-restoration-evidence" title="Add file"
                                            @if(!auth()->user()->isAdmin() && !$report->status) disabled @endif>
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    @endif
                            </div>
                                <small class="text-muted fw-semibold">Current Files:</small>
                            <div id="previewRestorationFiles" class="mt-2 d-flex flex-wrap gap-2"></div>

                        </div>

                    </div>
                </div>

                <hr class="mb-4" id="hrRca" style="display: {{ $report->type === 'Incident' ? 'block' : 'none' }};">

                <div class="mb-4" id="sectionRca" style="display: {{ $report->type === 'Incident' ? 'block' : 'none' }};">
                    <label class="form-label small text-muted">Root Cause Analysis (RCA)</label>
                    <textarea name="rca" id="rcaEditor"
                        @if(!auth()->user()->isAdmin()) readonly @endif>{{ old('rca', $report->rca) }}</textarea>
                </div>
                
                <div class="d-flex justify-content-between align-items-center gap-2 mt-4">
                    <div>
                        @if($report->status == 0)
                            <span class="badge bg-secondary px-3 py-2 fs-6">Ticket Closed</span>
                        @else
                            @php
                                $canClose = $report->servicerestored_time 
                                    && !empty($report->resolution)
                                    && !empty($report->rca)
                                    && !empty($report->restoration_evidence);
                            @endphp
                            <button type="button" id="btnCloseTicket" class="btn btn-danger px-4"
                                {{ !$canClose ? 'disabled' : '' }}>
                                Close Ticket
                            </button>
                            {{-- <div id="closeTicketHints" class="mt-1">
                                @if(!$report->servicerestored_time)
                                    <small class="text-muted d-block"><i class="fas fa-times-circle text-danger"></i> Service Restoration belum diaktifkan</small>
                                @endif
                                @if(empty($report->resolution))
                                    <small class="text-muted d-block"><i class="fas fa-times-circle text-danger"></i> Resolution belum diisi</small>
                                @endif
                                @if(empty($report->rca))
                                    <small class="text-muted d-block"><i class="fas fa-times-circle text-danger"></i> RCA belum diisi</small>
                                @endif
                                @if(empty($report->restoration_evidence))
                                    <small class="text-muted d-block"><i class="fas fa-times-circle text-danger"></i> Restoration Evidence belum diupload</small>
                                @endif
                            </div> --}}
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('report.show', $report->uuid) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        @if(auth()->user()->isAdmin() || $report->status == 1 || $report->status == 2)
                            <button type="submit" id="submitBtn" class="btn btn-primary px-4">
                                Save Changes
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary px-4" disabled>
                                Ticket Closed - Cannot Edit
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal untuk Add dan Edit External Team --}}
<div class="modal fade" id="externalModal" tabindex="-1" aria-labelledby="externalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="externalModalLabel">Add External Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="externalForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="report_id" value="{{ $report->uuid }}">
                    <input type="hidden" name="id" id="external_id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">External Team <span class="text-danger">*</span></label>
                            <select name="external_teams" id="external_teams" class="form-select" required>
                                <option value="">Pilih External Team</option>
                                @foreach(\App\Models\MstExternalTeam::where('is_active', true)->get() as $team)
                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">PIC Name</label>
                            <input type="text" name="pic" id="pic" class="form-control" placeholder="PIC Name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_time" id="start_time" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End Time</label>
                            <input type="datetime-local" name="end_time" id="end_time" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Upload Evidence <small class="text-muted">(Max 5 files)</small></label>
                            <div id="evidenceFields">
                                <div class="evidence-fields-row d-flex align-items-center gap-2 mb-2">
                                    <input type="file" name="evidence_file_external[]" class="form-control evidence-input"
                                            accept="image/*, .gif, .pdf, .doc, .docx, .xls, .xlsx, .zip">
                                    <button type="button" class="btn btn-success btn-sm btn-add-evidence" title="Add file">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="previewFiles" class="mt-2 d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnSaveExternal" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="filesModal" tabindex="-1" aria-labelledby="filesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filesModalLabel">Evidence Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="filesModalContent" class="row g-3"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
window.addEventListener('load', function () {
    if (typeof ClassicEditor === 'undefined') return;

    const isClosed = @json(!$report->status);
    const isAdmin = @json(auth()->user()->isAdmin());

    ClassicEditor.create(document.querySelector('#rcaEditor'), {
        toolbar: (isClosed && !isAdmin) ? [] : ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo'],
        height: 320,
        placeholder: 'Masukkan analisis akar penyebab di sini...'
    }).then(editor => {
        if (isClosed && !isAdmin) editor.enableReadOnlyMode('rca-lock');
    }).catch(() => {});
});

document.addEventListener('DOMContentLoaded', function () {
    const handledByToggle = document.getElementById('handledByToggle');
    const externalTeamSection = document.getElementById('externalTeamSection');
    const modal = new bootstrap.Modal(document.getElementById('externalModal'));
    const filesModal = new bootstrap.Modal(document.getElementById('filesModal'));
    const alreadyHandled = handledByToggle.checked;
    let currentEditId = null;

    function formatSeconds(seconds) {
        seconds = Math.abs(Math.round(seconds));
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    }

    function toggleExternalSection() {
        if (handledByToggle.checked) {
            externalTeamSection.style.display = 'block';
        } else {
            externalTeamSection.style.display = 'none';
        }
    }

    handledByToggle.addEventListener('change', function () {
        if (this.checked) {
            Swal.fire({
                title: 'Activate External Team?',
                text: 'Once activated, this toggle cannot be deactivated again. Continue?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Activate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    handledByToggle.disabled = true;

                    fetch('{{ route("report.toggleHandled", $report->uuid) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ handled_by: 1 })
                    })
                    .then(res => {
                        const contentType = res.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) return res.json();
                        return { success: true };
                    })
                    .catch(err => console.error('Toggle handled_by error:', err));

                    toggleExternalSection();
                } else {
                    handledByToggle.checked = false;
                }
            });
        }
    });

    if (alreadyHandled) {
        handledByToggle.disabled = true;
    }

    toggleExternalSection();

    const serviceRestoredToggle  = document.getElementById('serviceRestoredToggle');
    const serviceRestorationContent = document.getElementById('serviceRestorationContent');
    const servicerestoredTimeInput  = document.getElementById('servicerestoredTimeInput');

    const alreadyRestored = servicerestoredTimeInput.value !== '';

    if (serviceRestoredToggle) {
        serviceRestoredToggle.addEventListener('change', function (e) {
            if (alreadyRestored) {
                e.preventDefault();
                return;
            }

            if (this.checked) {
                this.checked = false;

                Swal.fire({
                    title: 'Service Restored Confirmation',
                    text: 'The service restoration time will be recorded now and cannot be changed. Continue?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Continue',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        serviceRestoredToggle.disabled = true;
                        serviceRestoredToggle.checked = true;

                        fetch('{{ route("report.markRestored", $report->uuid) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({})
                        })
                        .then(res => {
                            const contentType = res.headers.get('content-type');
                            if (contentType && contentType.includes('application/json')) {
                                return res.json();
                            }
                            return { success: true, servicerestored_time: null, restored_time: 0, total_internal: 0, total_external: 0 };
                        })
                        .then(data => {
                            if (data.success) {
                                servicerestoredTimeInput.value = data.servicerestored_time ?? '';
                                serviceRestorationContent.style.display = 'block';

                                const statusInput = document.getElementById('statusInput');
                                if (statusInput) statusInput.value = '2';

                                if (data.servicerestored_time) {
                                    document.getElementById('restorationInfo').innerHTML = `
                                        <small class="text-muted">Restored on <strong>${data.servicerestored_time}</strong></small><br>
                                        <small class="text-muted">
                                            Total: ${formatSeconds(data.restored_time)}
                                            &bull; Internal: ${formatSeconds(data.total_internal)}
                                            &bull; External: ${formatSeconds(data.total_external)}
                                        </small>
                                    `;

                                    const btnCloseTicket = document.getElementById('btnCloseTicket');
                                    if (btnCloseTicket) {
                                        btnCloseTicket.disabled = false;
                                        btnCloseTicket.title = '';
                                    }
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Restoration error:', err);
                            serviceRestorationContent.style.display = 'block';
                        });
                    }
                });
            } else {
                serviceRestorationContent.style.display = 'none';
                servicerestoredTimeInput.value = '';
            }
        });
    }

    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    startTimeInput.addEventListener('change', function () {
        if (this.value) {
            endTimeInput.min = this.value;

            if (endTimeInput.value && endTimeInput.value < this.value) {
                endTimeInput.value = '';
            }
        } else {
            endTimeInput.min = '';
        }
    });

    const MAX_DOWNTIME_FILES = 5;
    const downtimeEvidenceFields = document.getElementById('downtimeEvidenceFields');
    const previewDowntimeFiles = document.getElementById('previewDowntimeFiles');

    function getDowntimeFileRows() {
        return downtimeEvidenceFields.querySelectorAll('.downtime-evidence-row');
    }

    downtimeEvidenceFields.querySelectorAll('.btn-remove-existing-downtime').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('.downtime-evidence-row').remove();
            renderDowntimePreview();
        });
    });

    function renderDowntimePreview() {
        previewDowntimeFiles.innerHTML = '';
        getDowntimeFileRows().forEach(row => {
            const input     = row.querySelector('.downtime-evidence-input');
            const textInput = row.querySelector('input[type="text"]');

            if (input && input.files && input.files[0]) {
                const file    = input.files[0];
                const isImage = file.type.startsWith('image/');
                const wrapper = document.createElement('div');
                wrapper.className = 'border rounded p-1 text-center';
                wrapper.style.cssText = 'width:90px; font-size:11px; overflow:hidden;';

                if (isImage) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        wrapper.innerHTML = `
                            <img src="${e.target.result}" style="width:80px;height:60px;object-fit:cover;" class="rounded mb-1">
                            <div class="text-truncate text-muted">${file.name}</div>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    wrapper.innerHTML = `
                        <div style="width:80px;height:60px;line-height:60px;font-size:24px;" class="text-center text-muted">📄</div>
                        <div class="text-truncate text-muted">${file.name}</div>
                    `;
                }
                previewDowntimeFiles.appendChild(wrapper);
            } else if (textInput) {
                const filename = textInput.value;
                const ext      = filename.split('.').pop().toLowerCase();
                const isImage  = ['jpg','jpeg','png','gif','webp'].includes(ext);
                const fileUrl  = `/storage/file_downtime_evidences/${filename}`;
                const wrapper  = document.createElement('div');
                wrapper.className = 'border rounded p-1 text-center';
                wrapper.style.cssText = 'width:90px; font-size:11px; overflow:hidden;';

                if (isImage) {
                    wrapper.innerHTML = `
                        <a href="${fileUrl}" target="_blank">
                            <img src="${fileUrl}" style="width:80px;height:60px;object-fit:cover;" class="rounded mb-1">
                        </a>
                        <div class="text-truncate text-muted">${filename}</div>
                    `;
                } else {
                    wrapper.innerHTML = `
                        <div style="width:80px;height:60px;line-height:60px;font-size:24px;" class="text-center text-muted">📄</div>
                        <div class="text-truncate text-muted">${filename}</div>
                    `;
                }
                previewDowntimeFiles.appendChild(wrapper);
            }
        });
    }

    function addDowntimeEvidenceRow() {
        if (getDowntimeFileRows().length >= MAX_DOWNTIME_FILES) return;

        const row = document.createElement('div');
        row.className = 'downtime-evidence-row d-flex align-items-center gap-2 mb-2';
        row.innerHTML = `
            <input type="file" name="file_downtime_evidence[]" class="form-control downtime-evidence-input"
                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">
            <button type="button" class="btn btn-success btn-sm btn-add-downtime-evidence" title="Add file">
                <i class="fas fa-plus"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm btn-remove-downtime-evidence" title="Remove field">
                <i class="fas fa-times"></i>
            </button>
        `;
        downtimeEvidenceFields.appendChild(row);

        row.querySelector('.downtime-evidence-input').addEventListener('change', renderDowntimePreview);
        row.querySelector('.btn-add-downtime-evidence').addEventListener('click', addDowntimeEvidenceRow);
        row.querySelector('.btn-remove-downtime-evidence').addEventListener('click', function () {
            row.remove();
            renderDowntimePreview();
        });
    }

    if (downtimeEvidenceFields) {
        const firstDowntimeInput  = downtimeEvidenceFields.querySelector('.downtime-evidence-input');
        const firstDowntimeAddBtn = downtimeEvidenceFields.querySelector('.btn-add-downtime-evidence');
        if (firstDowntimeInput)  firstDowntimeInput.addEventListener('change', renderDowntimePreview);
        if (firstDowntimeAddBtn) firstDowntimeAddBtn.addEventListener('click', addDowntimeEvidenceRow);

        renderDowntimePreview();
    }

    const MAX_FILES = 5;
    const evidenceFields = document.getElementById('evidenceFields');
    const previewFiles = document.getElementById('previewFiles');

    function getFileRows() {
        return evidenceFields.querySelectorAll('.evidence-field-row');
    }

    function updateAddButtons() {
        const rows = getFileRows();
        evidenceFields.querySelectorAll('.btn-add-evidence').forEach(btn => {
            btn.style.display = rows.length >= MAX_FILES ? 'none' : 'inline-flex';
        });
    }

    function updateRemoveButtons() {
        const rows = getFileRows();
        evidenceFields.querySelectorAll('.btn-remove-evidence').forEach(btn => {
            btn.style.display = rows.length <= 1 ? 'none' : 'inline-flex';
        });
    }

    function renderPreview() {
        previewFiles.innerHTML = '';
        
        const fileRows = evidenceFields.querySelectorAll('.evidence-field-row');
        
        fileRows.forEach(row => {
            const input = row.querySelector('.evidence-input');
            const textInput = row.querySelector('input[type="text"]');

            let filename = '';
            let isImage = false;
            let previewUrl = '';

            if (input && input.files && input.files[0]) {
                const file = input.files[0];
                filename = file.name;
                isImage = file.type.startsWith('image/');

                if (isImage) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        createPreviewElement(e.target.result, filename, true);
                    };
                    reader.readAsDataURL(file);
                    return;
                }
            } 
            else if (textInput) {
                filename = textInput.value;
                const ext = filename.split('.').pop().toLowerCase();
                isImage = ['jpg','jpeg','png','gif','webp'].includes(ext);
                previewUrl = `/storage/external_evidence/${filename}`;
            }

            if (filename) {
                createPreviewElement(previewUrl || '', filename, isImage);
            }
        });
    }

    function createPreviewElement(src, filename, isImage) {
        const wrapper = document.createElement('div');
        wrapper.className = 'border rounded p-1 text-center';
        wrapper.style.cssText = 'width:90px; font-size:11px; overflow:hidden;';

        if (isImage && src) {
            wrapper.innerHTML = `
                <img src="${src}" style="width:80px;height:60px;object-fit:cover;" class="rounded mb-1">
                <div class="text-truncate text-muted">${filename}</div>
            `;
        } else {
            wrapper.innerHTML = `
                <div style="width:80px;height:60px;line-height:60px;font-size:24px;" class="text-center text-muted">📄</div>
                <div class="text-truncate text-muted">${filename}</div>
            `;
        }
        previewFiles.appendChild(wrapper);
    }

    function addEvidenceRow() {
        if (getFileRows().length >= MAX_FILES) return;

        const row = document.createElement('div');
        row.className = 'evidence-field-row d-flex align-items-center gap-2 mb-2';
        row.innerHTML = `
            <input type="file" name="evidence_file_external[]" class="form-control evidence-input"
               accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">
            <button type="button" class="btn btn-success btn-sm btn-add-evidence" title="Add file">
                <i class="fas fa-plus"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm btn-remove-evidence" title="Remove field">
                <i class="fas fa-times"></i>
            </button>
        `;
        evidenceFields.appendChild(row);

        row.querySelector('.evidence-input').addEventListener('change', renderPreview);
        row.querySelector('.btn-add-evidence').addEventListener('click', addEvidenceRow);
        row.querySelector('.btn-remove-evidence').addEventListener('click', function () {
            row.remove();
            renderPreview();
            updateAddButtons();
            updateRemoveButtons();
        });

        updateAddButtons();
        updateRemoveButtons();
    }

    evidenceFields.querySelector('.evidence-input').addEventListener('change', renderPreview);
    evidenceFields.querySelector('.btn-add-evidence').addEventListener('click', addEvidenceRow);

    const MAX_RESTORATION_FILES = 5;
    const restorationEvidenceFields = document.getElementById('restorationEvidenceFields');
    const previewRestorationFiles = document.getElementById('previewRestorationFiles');

    function getRestorationFileRows() {
        return restorationEvidenceFields.querySelectorAll('.restoration-evidence-row');
    }

    function getTotalRestorationCount() {
        return getRestorationFileRows().length;
    }

    restorationEvidenceFields.querySelectorAll('.btn-remove-existing-restoration').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('.restoration-evidence-row').remove();
        });
    });

    function renderRestorationPreview() {
        previewRestorationFiles.innerHTML = '';
        getRestorationFileRows().forEach(row => {
            const input = row.querySelector('.restoration-evidence-input');
            const textInput = row.querySelector('input[type="text"]');

            if (input && input.files && input.files[0]) {
                const file = input.files[0];
                const isImage = file.type.startsWith('image/');
                const wrapper = document.createElement('div');
                wrapper.className = 'border rounded p-1 text-center';
                wrapper.style.cssText = 'width:90px; font-size:11px; overflow:hidden;';

                if (isImage) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        wrapper.innerHTML = `
                            <img src="${e.target.result}" style="width:80px;height:60px;object-fit:cover;" class="rounded mb-1">
                            <div class="text-truncate text-muted">${file.name}</div>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    wrapper.innerHTML = `
                        <div style="width:80px;height:60px;line-height:60px;font-size:24px;" class="text-center text-muted">📄</div>
                        <div class="text-truncate text-muted">${file.name}</div>
                    `;
                }
                previewRestorationFiles.appendChild(wrapper);
            } else if (textInput) {
                // Existing file preview
                const filename = textInput.value;
                const ext = filename.split('.').pop().toLowerCase();
                const isImage = ['jpg','jpeg','png','gif','webp'].includes(ext);
                const fileUrl = `/storage/restoration_evidence/${filename}`;
                const wrapper = document.createElement('div');
                wrapper.className = 'border rounded p-1 text-center';
                wrapper.style.cssText = 'width:90px; font-size:11px; overflow:hidden;';

                if (isImage) {
                    wrapper.innerHTML = `
                        <a href="${fileUrl}" target="_blank">
                            <img src="${fileUrl}" style="width:80px;height:60px;object-fit:cover;" class="rounded mb-1">
                        </a>
                        <div class="text-truncate text-muted">${filename}</div>
                    `;
                } else {
                    wrapper.innerHTML = `
                        <div style="width:80px;height:60px;line-height:60px;font-size:24px;" class="text-center text-muted">📄</div>
                        <div class="text-truncate text-muted">${filename}</div>
                    `;
                }
                previewRestorationFiles.appendChild(wrapper);
            }
        });
    }

    function addRestorationEvidenceRow() {
        if (getTotalRestorationCount() >= MAX_RESTORATION_FILES) return;

        const row = document.createElement('div');
        row.className = 'restoration-evidence-row d-flex align-items-center gap-2 mb-2';
        row.innerHTML = `
            <input type="file" name="restoration_evidence[]" class="form-control restoration-evidence-input"
                accept="image/*,.gif,.pdf,.doc,.docx,.xls,.xlsx,.zip">
            <button type="button" class="btn btn-success btn-sm btn-add-restoration-evidence" title="Add file">
                <i class="fas fa-plus"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm btn-remove-restoration-evidence" title="Remove field">
                <i class="fas fa-times"></i>
            </button>
        `;
        restorationEvidenceFields.appendChild(row);

        row.querySelector('.restoration-evidence-input').addEventListener('change', renderRestorationPreview);
        row.querySelector('.btn-add-restoration-evidence').addEventListener('click', addRestorationEvidenceRow);
        row.querySelector('.btn-remove-restoration-evidence').addEventListener('click', function () {
            row.remove();
            renderRestorationPreview();
        });
    }

    if (restorationEvidenceFields) {
        const firstAddBtn = restorationEvidenceFields.querySelector('.btn-add-restoration-evidence');
        const firstInput = restorationEvidenceFields.querySelector('.restoration-evidence-input');
        if (firstInput) firstInput.addEventListener('change', renderRestorationPreview);
        if (firstAddBtn) firstAddBtn.addEventListener('click', addRestorationEvidenceRow);

        renderRestorationPreview();
    }

    document.getElementById('btnAddExternal').addEventListener('click', function () {
        currentEditId = null;
        document.getElementById('externalModalLabel').textContent = 'Add External Team';
        document.getElementById('externalForm').reset();

        evidenceFields.innerHTML = `
            <div class="evidence-field-row d-flex align-items-center gap-2 mb-2">
                <input type="file" name="evidence_file_external[]" class="form-control evidence-input"
                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                <button type="button" class="btn btn-success btn-sm btn-add-evidence" title="Add file">
                    <i class="fas fa-plus"></i>
                </button>
            </div>`;
        previewFiles.innerHTML = '';

        evidenceFields.querySelector('.evidence-input').addEventListener('change', renderPreview);
        evidenceFields.querySelector('.btn-add-evidence').addEventListener('click', addEvidenceRow);

        modal.show();
    });

    document.getElementById('btnSaveExternal').addEventListener('click', function () {
        const startVal = startTimeInput.value;
        const endVal = endTimeInput.value;

        if (!startVal) {
            Swal.fire({ icon: 'warning', title: 'Required', text: 'Start Time is required' });
            return;
        }

        if (endVal && endVal < startVal) {
            Swal.fire({ icon: 'warning', title: 'End Time Failed', text: 'End Time cannot be earlier than Start Time' });
            return;
        }

        const form = document.getElementById('externalForm');
        const saveBtn = this;

        if (currentEditId) {
            let methodInput = form.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                form.appendChild(methodInput);
            }
            methodInput.value = 'PUT';
            form.action = '{{ url("report-external-teams") }}/' + currentEditId;
        } else {
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.remove();
            form.action = '{{ route("report.external.store") }}';
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving...`;

        form.method = 'POST';
        form.submit();
    });

    function addNewFileInputRow() {
        const evidenceFields = document.getElementById('evidenceFields');
        const currentRows = evidenceFields.querySelectorAll('.evidence-field-row').length;

        if (currentRows >= MAX_FILES) {
            return;
        }

        const row = document.createElement('div');
        row.className = 'evidence-field-row d-flex align-items-center gap-2 mb-2';
        row.innerHTML = `
            <input type="file" name="evidence_file_external[]" class="form-control evidence-input"
                accept="image/*,.gif,.pdf,.doc,.docx,.xls,.xlsx,.zip">
            <button type="button" class="btn btn-success btn-sm btn-add-evidence" title="Add more file">
                <i class="fas fa-plus"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm btn-remove-evidence" title="Remove field">
                <i class="fas fa-times"></i>
            </button>
        `;

        evidenceFields.appendChild(row);

        const fileInput = row.querySelector('.evidence-input');
        fileInput.addEventListener('change', renderPreview);

        row.querySelector('.btn-remove-evidence').addEventListener('click', function () {
            if (evidenceFields.querySelectorAll('.evidence-field-row').length > 1) {
                row.remove();
                renderPreview();
            }
        });

        renderPreview();
    }

    document.querySelectorAll('.btn-edit-external').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            const id = row.dataset.id;
            const files = JSON.parse(this.dataset.files || '[]');

            const cells = row.querySelectorAll('td');
            const teamName = cells[0].textContent.trim();
            const pic = cells[1].textContent.trim();
            const startRaw = row.dataset.start;
            const endRaw = row.dataset.end;

            currentEditId = id;
            document.getElementById('externalModalLabel').textContent = 'Edit External Team';
            document.getElementById('externalForm').reset();

            document.getElementById('pic').value = pic !== '-' ? pic : '';

            const select = document.getElementById('external_teams');
            Array.from(select.options).forEach(opt => {
                opt.selected = opt.text.trim() === teamName;
            });

            document.getElementById('start_time').value = startRaw || '';
            document.getElementById('end_time').value = endRaw || '';
            if (startRaw) endTimeInput.min = startRaw;

            const evidenceFields = document.getElementById('evidenceFields');
            evidenceFields.innerHTML = '';

            if (files.length > 0) {
                files.forEach(filename => {
                    const fileRow = document.createElement('div');
                    fileRow.className = 'evidence-field-row d-flex align-items-center gap-2 mb-2 existing-file-row';
                    fileRow.dataset.filename = filename;

                    fileRow.innerHTML = `
                        <input type="text" class="form-control bg-light" value="${filename}" readonly>
                        <input type="hidden" name="existing_files[]" value="${filename}">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-existing" title="Remove this file">
                            <i class="fas fa-times"></i>
                        </button>
                    `;

                    evidenceFields.appendChild(fileRow);

                    fileRow.querySelector('.btn-remove-existing').addEventListener('click', function () {
                        fileRow.remove();
                        renderPreview();
                    });
                });
            }

            addNewFileInputRow();

            const addHandler = function (e) {
                if (e.target.closest('.btn-add-evidence')) {
                    addNewFileInputRow();
                }
            };

            evidenceFields.removeEventListener('click', addHandler);
            evidenceFields.addEventListener('click', addHandler);

            renderPreview();
            modal.show();
        });
    });

    document.querySelectorAll('.btn-delete-external').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            const id = row.dataset.id;

            Swal.fire({
                title: 'Delete Data?',
                text: "This data will deleted permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteForm = document.createElement('form');
                    deleteForm.method = 'POST';
                    deleteForm.action = `{{ url('report-external-teams') }}/${id}`;

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';

                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';

                    deleteForm.appendChild(csrf);
                    deleteForm.appendChild(method);
                    document.body.appendChild(deleteForm);
                    deleteForm.submit();
                }
            });
        });
    });

    document.querySelectorAll('.btn-view-files').forEach(btn => {
        btn.addEventListener('click', function () {
            const files = JSON.parse(this.dataset.files || '[]');
            const container = document.getElementById('filesModalContent');
            container.innerHTML = '';

            if (files.length === 0) {
                container.innerHTML = '<p class="text-muted">Tidak ada file.</p>';
            } else {
                files.forEach(filename => {
                    const ext = filename.split('.').pop().toLowerCase();
                    const isImage = ['jpg','jpeg','png','gif','webp'].includes(ext);
                    const fileUrl = `/storage/external_evidence/${filename}`;

                    const col = document.createElement('div');
                    col.className = 'col-md-4 text-center';

                    if (isImage) {
                        col.innerHTML = `
                            <a href="${fileUrl}" target="_blank">
                                <img src="${fileUrl}" class="img-fluid rounded mb-2" style="max-height:150px;object-fit:cover;">
                            </a>
                            <div class="small text-truncate text-muted">${filename}</div>
                            <a href="${fileUrl}" download class="btn btn-sm btn-outline-primary mt-1">Download</a>
                        `;
                    } else {
                        col.innerHTML = `
                            <div style="font-size:48px;">📄</div>
                            <div class="small text-truncate text-muted mb-1">${filename}</div>
                            <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-secondary me-1">View</a>
                            <a href="${fileUrl}" download class="btn btn-sm btn-outline-primary">Download</a>
                        `;
                    }
                    container.appendChild(col);
                });
            }

            filesModal.show();
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('editForm');
    const submitBtn = document.getElementById('submitBtn');
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const dynamicFields = document.getElementById('dynamicFields');
    const priorityLegend = document.getElementById('priorityLegend');
    const priorityOptions = document.getElementById('priorityOptions');
    const assignedOptions = document.getElementById('assignedOptions');
    const scopeOptions = document.getElementById('scopeOptions');
    
    let isInitialLoad = true;
    const isClosed = @json(!$report->status);
    const isAdmin = @json(auth()->user()->isAdmin());

    const configs = {
        'Incident': {
            priorityLabel: 'Severity Level',
            priorityOptions: [{ label: '1 - Emergency (Full Down)' }, { label: '2 - Critical (Major Full Down)' }, { label: '3 - Major (Partial Issue)' }, { label: '4 - Minor' }],
            assignedOptions: [{ label: 'B2B Applications Operations' }, { label: 'Application Owner (L3)' }],
            scopeOptions: [{ label: 'Application Bug' }, { label: 'Infrastructure' }, { label: 'Human Error' }, { label: 'Deployment Issue' }, { label: 'Third Party' }, { label: 'Security Issue/Breach' }, { label: 'Unknown' }]
        },
        'Request': {
            priorityLabel: 'Priority Level',
            priorityOptions: [{ label: '1 – High' }, { label: '2 – Medium' }, { label: '3 – Low' }],
            assignedOptions: [{ label: 'B2B Apps Ops Support' }, { label: 'App Owner' }],
            scopeOptions: [{ label: 'User Management' }, { label: 'Apps Improvement' }, { label: 'Monitoring' }, { label: 'Update/Patching' }, { label: 'Licence/Certificate Management' }]
        },
        'Activity': {
            priorityLabel: 'Impact',
            priorityOptions: [{ label: '1 – High Impact' }, { label: '2 – Medium Impact' }, { label: '3 – Low Impact' }, { label: '4 – No Impact' }],
            assignedOptions: [{ label: 'B2B Apps Ops Support' }, { label: 'App Owner/L3' }],
            scopeOptions: [{ label: 'Application/System/Code' }, { label: 'Infrastructure' }]
        }
    };

    const btnCloseTicket = document.getElementById('btnCloseTicket');
    if (btnCloseTicket) {
        btnCloseTicket.addEventListener('click', function () {
            Swal.fire({
                title: 'Closed Ticket?',
                text: 'Closed tickets cannot be edited by non-admins. Continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Closed Ticket',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('statusInput').value = '0';
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Closing...`;
                    form.submit();
                }
            });
        });
    }

    function updateFields(type) {
        if (!configs[type]) return;
        dynamicFields.style.display = 'block';
        priorityLegend.innerHTML = `${configs[type].priorityLabel} <span class="text-danger">*</span>`;
        priorityOptions.innerHTML = '';
        assignedOptions.innerHTML = '';
        scopeOptions.innerHTML = '';

        const isIncident = type === 'Incident';
        const sectionsIncidentOnly = [
            'sectionExternalTeam', 'hrExternalTeam',
            'sectionServiceRestoration', 'hrServiceRestoration',
            'sectionRca', 'hrRca'
        ];
        sectionsIncidentOnly.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = isIncident ? 'block' : 'none';
        });

        const resolutionTextArea = document.getElementById('resolutionTextArea');
        if (resolutionTextArea) {
            if (isIncident) {
                resolutionTextArea.setAttribute('required', 'required');
            } else {
                resolutionTextArea.removeAttribute('required');
            }
        }

        const severityValue = isInitialLoad ? '{{ old("severity", $report->severity ?? "") }}' : '';
        const assignedValue = isInitialLoad ? '{{ old("assigned_to", $report->assigned_to ?? "") }}' : '';
        const scopeValue = isInitialLoad ? '{{ old("scope", $report->scope ?? "") }}' : '';

        configs[type].priorityOptions.forEach(opt => {
            const selected = severityValue === opt.label ? 'checked' : '';
            priorityOptions.innerHTML += `<div class="form-check">
                <input type="radio" ${isAdmin ? 'name="severity" required' : 'disabled'} value="${opt.label}" class="form-check-input" ${selected}>
                <label class="form-check-label">${opt.label}</label>
            </div>`;
        });

        configs[type].assignedOptions.forEach(opt => {
            const selected = assignedValue === opt.label ? 'checked' : '';
            assignedOptions.innerHTML += `<div class="form-check">
                <input type="radio" ${isAdmin ? 'name="assigned_to"' : 'disabled'} value="${opt.label}" class="form-check-input" ${selected}>
                <label class="form-check-label">${opt.label}</label>
            </div>`;
        });

        const assignedOtherValue = assignedValue && !configs[type].assignedOptions.some(o => o.label === assignedValue) ? assignedValue : '';
        assignedOptions.innerHTML += `<div class="mt-3">
            <input type="text" class="form-control" id="assigned_other_input" placeholder="Others (sebutkan)"
                   value="${assignedOtherValue}" ${isAdmin ? '' : 'readonly'}>
        </div>`;

        configs[type].scopeOptions.forEach(opt => {
            const selected = scopeValue === opt.label ? 'checked' : '';
            scopeOptions.innerHTML += `<div class="form-check">
                <input type="radio" name="scope" value="${opt.label}" class="form-check-input" ${selected}>
                <label class="form-check-label">${opt.label}</label>
            </div>`;
        });

        const scopeOtherValue = scopeValue && !configs[type].scopeOptions.some(o => o.label === scopeValue) ? scopeValue : '';
        scopeOptions.innerHTML += `<div class="mt-3">
            <input type="text" class="form-control" id="scope_other_input" placeholder="Others (sebutkan)"
                value="${scopeOtherValue}">
        </div>`;

        const assignedOther = document.getElementById('assigned_other_input');
        const scopeOther = document.getElementById('scope_other_input');

        if (isAdmin) {
            if (assignedOther && assignedOther.value.trim() !== '') assignedOther.name = 'assigned_to';

            assignedOther.addEventListener('input', function () {
                if (this.value.trim() !== '') {
                    assignedOptions.querySelectorAll('input[name="assigned_to"]').forEach(r => r.checked = false);
                    this.name = 'assigned_to';
                } else {
                    this.removeAttribute('name');
                }
            });

            assignedOptions.querySelectorAll('input[name="assigned_to"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    assignedOther.value = '';
                    assignedOther.removeAttribute('name');
                });
            });
        }

        if (scopeOther && scopeOther.value.trim() !== '') scopeOther.name = 'scope';

        scopeOther.addEventListener('input', function () {
            if (this.value.trim() !== '') {
                scopeOptions.querySelectorAll('input[name="scope"]').forEach(r => r.checked = false);
                this.name = 'scope';
            } else {
                this.removeAttribute('name');
            }
        });

        scopeOptions.querySelectorAll('input[name="scope"]').forEach(radio => {
            radio.addEventListener('change', function () {
                scopeOther.value = '';
                scopeOther.removeAttribute('name');
            });
        });

        if (!isAdmin) {
            priorityOptions.innerHTML += `<input type="hidden" name="severity" value="${severityValue}">`;
            assignedOptions.innerHTML += `<input type="hidden" name="assigned_to" value="${assignedValue}">`;
        }

        isInitialLoad = false;
    }

    typeRadios.forEach(radio => {
        radio.addEventListener('change', () => updateFields(radio.value));
        if (radio.checked) updateFields(radio.value);
    });

    form.addEventListener('submit', function (e) {
        if (isClosed && !isAdmin) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Diizinkan',
                text: 'Hanya Admin yang dapat mengedit ticket yang sudah Closed.',
                confirmButtonColor: '#d33'
            });
            return;
        }

        const assignedValid = assignedOptions.querySelectorAll('input[name="assigned_to"]:checked').length > 0 ||
                              (document.getElementById('assigned_other_input') && document.getElementById('assigned_other_input').value.trim() !== '');

        const scopeValid = scopeOptions.querySelectorAll('input[name="scope"]:checked').length > 0 ||
                           (document.getElementById('scope_other_input') && document.getElementById('scope_other_input').value.trim() !== '');

        if (!assignedValid && isAdmin) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Required', text: 'Assigned To wajib diisi' });
            return;
        }
        if (!scopeValid && isAdmin) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Required', text: 'Scope wajib diisi' });
            return;
        }

        e.preventDefault();
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Perubahan data akan disimpan permanen",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Submitting...`;
                form.submit();
            }
        });
    });
});
</script>
@endpush