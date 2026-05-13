@extends('layouts.main')

@section('content')
<div class="card mx-auto" style="max-width: 1700px;">
    <div class="card-header text-center">
        <h3 class="mb-0 fw-semibold">All Activity / Incident Reports</h3>
    </div>

    <div class="card-body p-4 p-md-5">
        @include('partials.data-table', ['reports' => $reports])
    </div>
</div>
@endsection