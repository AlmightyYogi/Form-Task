<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use Illuminate\Support\Facades\Storage;
use App\Services\IncidentService;
use App\Models\Report;
use App\Models\ReportExternalTeam;
use Carbon\Carbon;
use App\Exports\ReportsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    protected $incidentService;

    public function __construct(IncidentService $incidentService)
    {
        $this->incidentService = $incidentService;
    }

    public function index(Request $request)
    {
        $query = Report::query();

        if ($request->search) {
            $search = $request->search;

            $statusMap = [
                'closed' => 0,
                'open' => 1,
                'restored'=> 2,
            ];

            $searchLower = strtolower($search);

            $query->where(function ($q) use ($search, $searchLower, $statusMap) {
                $q->where('incident', 'like', "%$search%")
                ->orWhere('requestor', 'like', "%$search%")
                ->orWhere('requestor_email', 'like', "%$search%")
                ->orWhere('apps', 'like', "%$search%")
                ->orWhere('assigned_to', 'like', "%$search%")
                ->orWhere('scope', 'like', "%$search%")
                ->orWhere('severity', 'like', "%$search%");

                if (isset($statusMap[$searchLower])) {
                    $q->orWhere('status', $statusMap[$searchLower]);
                }
            });
        }

        if ($request->start_date) {
            $query->whereDate('request_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('request_date', '<=', $request->end_date);
        }

        $reports = $query->orderBy('created_at', 'desc')
        ->paginate(15)
        ->withQueryString();

        return view('report.index', compact('reports'));
    }

    public function create()
    {
        return view('report.create');
    }

    public function store(StoreReportRequest $request)
    {
        try {
            $data = $request->validated();
            
            $data['created_at'] = now();

            if ($request->hasFile('file_downtime_evidence')) {
                $file     = $request->file('file_downtime_evidence');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('file_downtime_evidences', $filename, 'public');
                $data['file_downtime_evidence'] = $filename;
            }

            $this->incidentService->createReport($data);

            return redirect()
                ->route('report.index')
                ->with('success', 'Report created successfully');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create report');
                // dd($e->getMessage());
        }
    }

    public function show($uuid)
    {
        $report = Report::where('uuid', $uuid)->firstOrFail();

        return view('report.show', compact('report'));
    }

    public function edit($uuid)
    {
        if (auth()->user()->isViewer()) {
            abort(403, 'You do not have permission to edit this report.');
        }

        $report = Report::where('uuid', $uuid)->firstOrFail();

        return view('report.edit', compact('report'));
    }

    public function update(UpdateReportRequest $request, $uuid)
    {
        try {
            $report = Report::where('uuid', $uuid)->firstOrFail();

            $data = $request->validated();

            if (!empty($data['rca'])) {
                $data['rca'] = clean($data['rca']);
            }

            if (!empty($data['scope_other'] ?? '')) {
                $data['scope'] = $data['scope_other'];
            }
            unset($data['scope_other']);

            if (isset($data['type']) && $data['type'] !== $report->type) {
                $data['incident'] = $this->incidentService->generateCode($data['type']);
            }

            if (isset($data['status'])) {
                if ($data['status'] == 0) {
                    $data['closed_at'] = now();
                    if ($report->status == 1 || $report->status == 2) {
                        $data['resolved_time'] = now();
                    }
                } else if ($data['status'] == 1) {
                    $data['closed_at'] = null;
                    $data['resolved_time'] = null;
                }
            }

            $data['handled_by'] = $request->input('handled_by', 0) ? 1 : 0;

            if (!empty($data['servicerestored_time'])) {
                if (!$report->restored_time) {
                    $serviceRestored = Carbon::parse($data['servicerestored_time']);
                    $createdAt       = Carbon::parse($report->created_at);
                    $data['restored_time'] = abs($serviceRestored->diffInSeconds($createdAt));
                } else {
                    $data['restored_time'] = $report->restored_time;
                }
            } else {
                $data['restored_time'] = null;
            }

            if (!is_null($data['restored_time'])) {
                $totalExternal = ReportExternalTeam::where('report_id', $report->id)
                    ->sum('duration');
                $data['total_internal_duration'] = max(0, $data['restored_time'] - ($totalExternal * 60));
            } else {
                $data['total_internal_duration'] = null;
            }

            $existingDowntimeFiles = $request->input('existing_downtime_evidence', []);
            
            if ($request->hasFile('file_downtime_evidence')) {
                if (!empty($report->file_downtime_evidence)) {
                    foreach ($report->file_downtime_evidence as $oldFile) {
                        if (!in_array($oldFile, $existingDowntimeFiles)) {
                            Storage::disk('public')->delete('file_downtime_evidences/' . $oldFile);
                        }
                    }
                }

                $newFiles = [];
                foreach ($request->file('file_downtime_evidence') as $file) {
                    $filename   = time() . '_' . Str::random(8) . '_' . $file->getClientOriginalName();
                    $file->storeAs('file_downtime_evidences', $filename, 'public');
                    $newFiles[] = $filename;
                }

                $data['file_downtime_evidence'] = array_merge($existingDowntimeFiles, $newFiles);
            } else {
                if (!empty($report->file_downtime_evidence)) {
                    foreach ($report->file_downtime_evidence as $oldFile) {
                        if (!in_array($oldFile, $existingDowntimeFiles)) {
                            Storage::disk('public')->delete('file_downtime_evidences/' . $oldFile);
                        }
                    }
                }
                $data['file_downtime_evidence'] = $existingDowntimeFiles ?: null;
            }

            $existingRestorationFiles = $request->input('existing_restoration_files', []);

            if ($request->hasFile('restoration_evidence')) {
                if (!empty($report->restoration_evidence)) {
                    foreach ($report->restoration_evidence as $oldFile) {
                        if (!in_array($oldFile, $existingRestorationFiles)) {
                            Storage::disk('public')->delete('restoration_evidence/' . $oldFile);
                        }
                    }
                }

                $newFiles = [];
                foreach ($request->file('restoration_evidence') as $file) {
                    $filename = time() . '_' . \Illuminate\Support\Str::random(8) . '_' . $file->getClientOriginalName();
                    $file->storeAs('restoration_evidence', $filename, 'public');
                    $newFiles[] = $filename;
                }

                $data['restoration_evidence'] = array_merge($existingRestorationFiles, $newFiles);
            } else {
                if (!empty($report->restoration_evidence)) {
                    foreach ($report->restoration_evidence as $oldFile) {
                        if (!in_array($oldFile, $existingRestorationFiles)) {
                            Storage::disk('public')->delete('restoration_evidence/' . $oldFile);
                        }
                    }
                }
                $data['restoration_evidence'] = $existingRestorationFiles ?: null;
            }

            $report->update($data);

            return redirect()->back()
                ->with('success', 'Report updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to edit report');
            // dd($e->getMessage());
        }
    }

    public function markRestored(Request $request, $uuid)
    {
        $report = Report::where('uuid', $uuid)->firstOrFail();

        if ($report->servicerestored_time) {
            return response()->json(['success' => false, 'message' => 'Already restored']);
        }

        $serviceRestored = Carbon::now();
        $createdAt       = Carbon::parse($report->created_at);
        $restoredTime    = abs($serviceRestored->diffInMinutes($createdAt));
        $totalExternal   = ReportExternalTeam::where('report_id', $report->id)->sum('duration');

        $restoredSeconds = abs($serviceRestored->diffInSeconds($createdAt));

        $report->update([
            'servicerestored_time'    => $serviceRestored,
            'restored_time'           => $restoredSeconds,
            'total_internal_duration' => max(0, $restoredSeconds - ($totalExternal * 60)),
            'status'                  => 2,
        ]);

        return response()->json([
            'success'              => true,
            'servicerestored_time' => $serviceRestored->translatedFormat('j F Y H:i'),
            'restored_time'        => $restoredSeconds,
            'total_internal'       => max(0, $restoredSeconds - ($totalExternal * 60)),
            'total_external'       => $totalExternal * 60,
        ]);
    }

    public function toggleHandled(Request $request, $uuid)
    {
        $report = Report::where('uuid', $uuid)->firstOrFail();

        $report->update([
            'handled_by' => $request->input('handled_by', 0) ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'handled_by' => $report->handled_by]);
    }

    public function export(Request $request)
    {
        $query = Report::query();

        if ($request->search) {
            $search = $request->search;
            $statusMap = ['closed' => 0, 'open' => 1, 'restored' => 2];
            $searchLower = strtolower($search);

            $query->where(function ($q) use ($search, $searchLower, $statusMap) {
                $q->where('incident', 'like', "%$search%")
                  ->orWhere('requestor', 'like', "%$search%")
                  ->orWhere('requestor_email', 'like', "%$search%")
                  ->orWhere('apps', 'like', "%$search%")
                  ->orWhere('assigned_to', 'like', "%$search%")
                  ->orWhere('scope', 'like', "%$search%")
                  ->orWhere('severity', 'like', "%$search%");
                if (isset($statusMap[$searchLower])) {
                    $q->orWhere('status', $statusMap[$searchLower]);
                }
            });
        }

        if ($request->start_date) {
            $query->whereDate('request_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('request_date', '<=', $request->end_date);
        }

        $page = $request->get('page', 1);
        $perPage = 15;
        $reports = $query->orderBy('created_at', 'desc')
                         ->forPage($page, $perPage)
                         ->get();

        // $filename = 'reports_page' . $page . '_' . now()->format('Ymd_His') . '.xlsx';
        $filename = 'Reports Page' . '.xlsx';

        return Excel::download(new ReportsExport($reports), $filename);
    }

    public function chart(Request $request) 
    { 
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Report::selectRaw('type, COUNT(*) as total')
                        ->groupBy('type');

        if ($startDate && $endDate) {
            $query->whereBetween('request_date', [$startDate, $endDate]);
        }
        else {
            if ($period === 'week') {
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(), 
                    Carbon::now()->endOfWeek()
                ]);
            } elseif ($period === 'month') {
                $query->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month);
            } elseif ($period === 'year') {
                $query->whereYear('created_at', Carbon::now()->year);
            }
        }

        $data = $query->pluck('total', 'type')->toArray();

        $types = ['Incident', 'Request', 'Activity'];
        $chartData = [];
        foreach ($types as $type) {
            $chartData[$type] = $data[$type] ?? 0;
        }

        return view('report.chart', compact('chartData', 'period'));
    }
}
