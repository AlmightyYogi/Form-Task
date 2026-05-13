@extends('layouts.main')

@section('content')
<div class="card mx-auto" style="max-width: 1200px;">
    <div class="card-header text-center">
        <h3 class="mb-0 fw-semibold">Report Type Statistics</h3>
    </div>

    <div class="card-body p-4 p-md-5">

        <form method="GET" action="{{ route('report.chart') }}">

            <div class="mb-4 text-center">
                <div class="btn-group" role="group">
                    <a href="{{ route('report.chart', ['period' => 'week']) }}" 
                       class="btn {{ $period === 'week' ? 'btn-primary' : 'btn-outline-primary' }}">
                        This Week
                    </a>
                    <a href="{{ route('report.chart', ['period' => 'month']) }}" 
                       class="btn {{ $period === 'month' ? 'btn-primary' : 'btn-outline-primary' }}">
                        This Month
                    </a>
                    <a href="{{ route('report.chart', ['period' => 'year']) }}" 
                       class="btn {{ $period === 'year' ? 'btn-primary' : 'btn-outline-primary' }}">
                        This Year
                    </a>
                </div>
            </div>

            <div class="row g-3 align-items-end mb-5">
                <div class="col-md-4">
                    <label for="startDate" class="form-label fw-semibold">Start Date</label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ request('start_date') }}" 
                           id="startDate" 
                           class="form-control"
                           placeholder="dd/mm/yyyy">
                </div>
                <div class="col-md-4">
                    <label for="endDate" class="form-label fw-semibold">End Date</label>
                    <input type="date" 
                           name="end_date" 
                           value="{{ request('end_date') }}" 
                           id="endDate" 
                           class="form-control"
                           placeholder="dd/mm/yyyy">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('report.chart') }}" 
                       class="btn btn-outline-secondary w-100">
                        Reset
                    </a>
                </div>
            </div>

        </form>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <canvas id="typeChart" height="450"></canvas>
            </div>
        </div>

        <!-- Legend + Numbers -->
        <div class="row mt-5 justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-center gap-5">
                    <!-- Incident -->
                    <div class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                            <div style="width: 20px; height: 20px; background: #ef4444; border-radius: 5px;"></div>
                            <strong>Incident</strong>
                        </div>
                        <span class="fs-3 fw-bold text-danger">{{ $chartData['Incident'] ?? 0 }}</span>
                    </div>

                    <!-- Request -->
                    <div class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                            <div style="width: 20px; height: 20px; background: #3b82f6; border-radius: 5px;"></div>
                            <strong>Request</strong>
                        </div>
                        <span class="fs-3 fw-bold text-primary">{{ $chartData['Request'] ?? 0 }}</span>
                    </div>

                    <!-- Activity -->
                    <div class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                            <div style="width: 20px; height: 20px; background: #10b981; border-radius: 5px;"></div>
                            <strong>Activity</strong>
                        </div>
                        <span class="fs-3 fw-bold text-success">{{ $chartData['Activity'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 text-center">
            <a href="{{ route('report.index') }}" class="btn btn-outline-secondary btn-lg px-5">
                ← Back to Report List
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('typeChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Incident', 'Request', 'Activity'],
            datasets: [{
                label: 'Number of Reports',
                data: [
                    {{ $chartData['Incident'] ?? 0 }},
                    {{ $chartData['Request'] ?? 0 }},
                    {{ $chartData['Activity'] ?? 0 }}
                ],
                backgroundColor: ['#ef4444', '#3b82f6', '#10b981'],
                borderColor: ['#b91c1c', '#1e40af', '#047857'],
                borderWidth: 2,
                borderRadius: 8,
                barThickness: 90
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' Reports';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 5,
                        font: { size: 14 }
                    }
                },
                x: {
                    ticks: {
                        font: { size: 15, weight: '600' }
                    }
                }
            }
        }
    });
});
</script>
@endpush