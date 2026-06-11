<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>Ticket Activity / Incident Tracker</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/light.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9fd 100%);
            min-height: 100vh;
            padding-bottom: 3rem;
        }

        .navbar {
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand img {
            height: 54px;
            width: auto;
        }

        .nav-link {
            color: white !important;
            font-weight: 500;
        }

        .nav-link.active {
            color: #e0e7ff !important;
            border-bottom: 2px solid #e0e7ff;
        }

        .card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-bottom: none;
            padding: 1.5rem 2rem;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
        }

        .table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        td, th {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table thead {
            background: #6366f1;
            color: white;
        }

        fieldset {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            background-color: #ffffff;
        }

        legend {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4f46e5;
            padding: 0 12px;
            width: auto;
        }

        .rca-content p {
            margin-bottom: 8px;
        }

        .rca-content ul {
            padding-left: 20px;
        }

        .rca-content strong {
            font-weight: 600;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('images/lintasarta-logo.png') }}" alt="Company Logo"
                    class="img-fluid" style="max-height: 54px; height: auto;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @if(auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}">Add User</a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('report.create') ? 'active' : '' }}"
                            href="{{ route('report.create') }}">Create Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('report.index') ? 'active' : '' }}"
                            href="{{ route('report.index') }}">View Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('report.chart') ? 'active' : '' }}"
                            href="{{ route('report.chart') }}">Chart Report</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-5 mb-5 d-flex justify-content-center">
        <div style="width: 100%; max-width: 1700px;">
             @yield('content')
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                confirmButtonColor: '#3085d6'
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                confirmButtonColor: '#d33'
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#d33'
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            flatpickr("input[type='date']", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                locale: "id",
                allowInput: true,
                placeholder: "dd/mm/yyyy"
            });

            flatpickr("input[type='time']", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                allowInput: true,
                placeholder: "HH:mm"
            });

        });
    </script>

    @stack('scripts')

    <script>
        function preventBackNavigation() {
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', window.location.href);
                
                window.addEventListener('popstate', function (event) {
                    window.history.pushState(null, '', window.location.href);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            preventBackNavigation();
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                preventBackNavigation();
            }
        });

        @if(session('replace_history'))
            (function () {
                setTimeout(() => {
                    preventBackNavigation();
                }, 300);
            })();
        @endif
    </script>

</body>

</html>