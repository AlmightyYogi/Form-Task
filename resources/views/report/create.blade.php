@extends('layouts.main')

@section('content')
<div class="container">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 900px; border-radius: 16px;">
        
        <div class="card-header bg-white border-0 text-center py-4">
            <h4 class="fw-bold mb-0">Activity / Incident Report</h4>
            <!-- <small class="text-muted">Silakan isi form dengan lengkap</small> -->
        </div>

        <div class="card-body px-4 px-md-5 py-4">

            <form method="POST" action="{{ route('report.store') }}" id="createForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="status" value="1">

                <div class="mb-4">
                    <h6 class="fw-semibold mb-3 text-muted">Basic Information</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Requestor <span class="text-danger">*</span></label>
                            <input type="text" name="requestor" class="form-control" placeholder="Nama Anda" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Email <span class="text-danger">*</span></label>
                            <input type="email" name="requestor_email" class="form-control" placeholder="email@perusahaan.com" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Date <span class="text-danger">*</span></label>
                            <input type="date" name="request_date" class="form-control" placeholder="dd/mm/yyyy" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="report_time" class="form-control" placeholder="HH:mm" required>
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
                                @foreach([
                                    "B2B Portal", "MPR", "SQA Portal", "SARAS",
                                    "SECM Portal", "My Dashboard", "DBEST",
                                    "PSSHUB", "IOT Middleware TransJakarta"
                                ] as $app)
                                <div class="col-6">
                                    <div class="form-check">
                                        <input type="radio" name="apps" value="{{ $app }}" class="form-check-input" required>
                                        <label class="form-check-label small">{{ $app }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                @foreach(["Incident", "Request", "Activity"] as $type)
                                <div class="form-check">
                                    <input type="radio" name="type" value="{{ $type }}" class="form-check-input type-radio" required>
                                    <label class="form-check-label">{{ $type }}</label>
                                </div>
                                @endforeach
                            </div>
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

                <hr class="my-4">

                <div class="mb-4">
                    <label class="form-label small text-muted">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Jelaskan detail..." required></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label small text-muted">Upload Downtime Evidence</label>
                    <input type="file" name="file_downtime_evidence" class="form-control"
                        accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                    <small class="text-muted">Format: image, PDF, Word, Excel, ZIP. Max 10MB.</small>
                </div>

                <div class="text-end">
                    <button type="submit" id="submitBtn" class="btn btn-primary px-4">
                        Submit
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
function slugify(text) {
    return text.toString().toLowerCase().trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('createForm');
    const submitBtn = document.getElementById('submitBtn');

    const dateInput = document.querySelector('input[name="request_date"]');
    if (dateInput) {
        flatpickr(dateInput, {
            maxDate: "today",
            time_24hr: true,
            dateFormat: "Y-m-d",
            allowInput: true,
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
                },
                months: {
                    shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                    longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
                }
            }
        });
    }

    const typeRadios = document.querySelectorAll('input[name="type"]');
    const dynamicFields = document.getElementById('dynamicFields');
    const priorityLegend = document.getElementById('priorityLegend');
    const priorityOptions = document.getElementById('priorityOptions');
    const assignedOptions = document.getElementById('assignedOptions');
    const scopeOptions = document.getElementById('scopeOptions');

    const configs = {
        'Incident': {
            priorityLabel: 'Severity Level',
            priorityOptions: [
                { value: 1, label: '1 - Emergency (Full Down)' },
                { value: 2, label: '2 - Critical (Major Full Down)' },
                { value: 3, label: '3 - Major (Partial Issue)' },
                { value: 4, label: '4 - Minor' }
            ],
            assignedOptions: [
                { label: 'B2B Applications Operations', isOther: false },
                { label: 'Application Owner (L3)', isOther: false },
                { label: 'Others', isOther: true }
            ],
            scopeOptions: [
                { label: 'Application Bug', isOther: false },
                { label: 'Infrastructure', isOther: false },
                { label: 'Human Error', isOther: false },
                { label: 'Deployment Issue', isOther: false },
                { label: 'Third Party', isOther: false },
                { label: 'Security Issue/Breach', isOther: false },
                { label: 'Unknown', isOther: false },
                { label: 'Others', isOther: true }
            ]
        },
        'Request': {
            priorityLabel: 'Priority Level',
            priorityOptions: [
                { value: 1, label: '1 – High' },
                { value: 2, label: '2 – Medium' },
                { value: 3, label: '3 – Low' }
            ],
            assignedOptions: [
                { label: 'B2B Apps Ops Support', isOther: false },
                { label: 'App Owner', isOther: false },
                { label: 'Others', isOther: true }
            ],
            scopeOptions: [
                { label: 'User Management', isOther: false },
                { label: 'Apps Improvement', isOther: false },
                { label: 'Monitoring', isOther: false },
                { label: 'Update/Patching', isOther: false },
                { label: 'Licence/Certificate Management', isOther: false },
                { label: 'Others', isOther: true }
            ]
        },
        'Activity': {
            priorityLabel: 'Impact',
            priorityOptions: [
                { value: 1, label: '1 – High Impact' },
                { value: 2, label: '2 – Medium Impact' },
                { value: 3, label: '3 – Low Impact' },
                { value: 4, label: '4 – No Impact' }
            ],
            assignedOptions: [
                { label: 'B2B Apps Ops Support', isOther: false },
                { label: 'App Owner/L3', isOther: false },
                { label: 'Others', isOther: true }
            ],
            scopeOptions: [
                { label: 'Application/System/Code', isOther: false },
                { label: 'Infrastructure', isOther: false },
                { label: 'Others', isOther: true }
            ]
        }
    };

    function handleOtherToggle(radioName, inputId) {
        const radios = document.querySelectorAll(`input[name="${radioName}"]`);
        const input = document.getElementById(inputId);

        if (!input) return;

        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.checked) {
                    input.value = '';
                }
            });
        });

        input.addEventListener('input', () => {
            if (input.value.trim() !== '') {
                radios.forEach(r => r.checked = false);
                input.setAttribute('name', radioName);
                radios.forEach(r => r.removeAttribute('required'));
            } else {
                input.removeAttribute('name');
                radios.forEach(r => r.setAttribute('required', 'required'));
            }
        });
    }

    function updateFields(type) {
        if (!configs[type]) return;

        dynamicFields.style.display = 'block';
        priorityLegend.innerHTML = `${configs[type].priorityLabel} <span class="text-danger">*</span>`;

        priorityOptions.innerHTML = '';
        configs[type].priorityOptions.forEach(opt => {
            priorityOptions.innerHTML += `
                <div class="form-check">
                    <input type="radio" name="severity" value="${opt.label}" class="form-check-input" required>
                    <label class="form-check-label">${opt.label}</label>
                </div>
            `;
        });

        assignedOptions.innerHTML = '';
        configs[type].assignedOptions.forEach(opt => {
            if (opt.isOther) {
                assignedOptions.innerHTML += `
                    <div class="mb-3">
                        <input type="text" class="form-control" id="assigned_other_input" placeholder="Others (sebutkan)">
                    </div>
                `;
            } else {
                assignedOptions.innerHTML += `
                    <div class="form-check">
                        <input type="radio" name="assigned_to" value="${opt.label}" class="form-check-input" required>
                        <label class="form-check-label">${opt.label}</label>
                    </div>
                `;
            }
        });

        scopeOptions.innerHTML = '';
        configs[type].scopeOptions.forEach(opt => {
            if (opt.isOther) {
                scopeOptions.innerHTML += `
                    <div class="mb-3">
                        <input type="text" class="form-control" id="scope_other_input" placeholder="Others (sebutkan)">
                    </div>
                `;
            } else {
                scopeOptions.innerHTML += `
                    <div class="form-check">
                        <input type="radio" name="scope" value="${opt.label}" class="form-check-input" required>
                        <label class="form-check-label">${opt.label}</label>
                    </div>
                `;
            }
        });

        handleOtherToggle('assigned_to', 'assigned_other_input');
        handleOtherToggle('scope', 'scope_other_input');
    }

    typeRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            updateFields(this.value);
        });
    });

    form.addEventListener('submit', function (e) {
        const assignedRadios = document.querySelectorAll('input[name="assigned_to"]');
        const assignedOther = document.getElementById('assigned_other_input');

        const scopeRadios = document.querySelectorAll('input[name="scope"]');
        const scopeOther = document.getElementById('scope_other_input');

        const assignedChecked = Array.from(assignedRadios).some(r => r.checked);
        const assignedFilled = assignedOther && assignedOther.value.trim() !== '';

        const scopeChecked = Array.from(scopeRadios).some(r => r.checked);
        const scopeFilled = scopeOther && scopeOther.value.trim() !== '';

        if (!assignedChecked && !assignedFilled) {
            e.preventDefault();
            alert('Assigned To wajib diisi');
            return;
        }

        if (!scopeChecked && !scopeFilled) {
            e.preventDefault();
            alert('Scope wajib diisi');
            return;
        }

        if (submitBtn.disabled) {
            e.preventDefault();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Submitting...
        `;
    });
});
</script>
@endpush