<?php

namespace App\Http\Controllers;

use App\Models\ReportExternalTeam;
use App\Models\Report;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\StoreReportExternalTeamRequest;

class ReportExternalTeamController extends Controller
{
    public function store(StoreReportExternalTeamRequest $request)
    {
        try {
            $data = $request->validated();

            $report = Report::where('uuid', $data['report_id'])->firstOrFail();
            $data['report_id'] = $report->id;

            if (!empty($data['start_time']) && !empty($data['end_time'])) {
                $start = Carbon::parse($data['start_time']);
                $end = Carbon::parse($data['end_time']);
                $data['duration'] = abs($end->diffInMinutes($start));
            } else {
                $data['duration'] = 0;
            }

            if ($request->hasFile('evidence_file_external')) {
                $files = [];
                foreach ($request->file('evidence_file_external') as $file) {
                    $filename = time() . '_' . Str::random(8) . '_' . $file->getClientOriginalName();
                    $file->storeAs('external_evidence', $filename, 'public');
                    $files[] = $filename;
                }
                $data['evidence_file_external'] = $files;
            } else {
                $data['evidence_file_external'] = [];
            }

            ReportExternalTeam::create($data);

            $this->recalculateTotalDuration($report->id);

            return redirect()
                ->route('report.edit', $request->report_id)
                ->with('success', 'Data external team created successfully');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create report external team');
                // dd($e->getMessage());
        }
    }

    public function update(StoreReportExternalTeamRequest $request, $id)
    {
        try {
            $externalTeam = ReportExternalTeam::findOrFail($id);
        
            $data = $request->validated();

            unset($data['report_id']);

            if (!empty($data['start_time']) && !empty($data['end_time'])) {
                $start = \Carbon\Carbon::parse($data['start_time']);
                $end   = \Carbon\Carbon::parse($data['end_time']);
                $data['duration'] = abs($end->diffInMinutes($start));
            } else {
                $data['duration'] = 0;
            }

            $existingFiles = $request->input('existing_files', []);

            if ($request->hasFile('evidence_file_external')) {
                $newFiles = [];
                foreach ($request->file('evidence_file_external') as $file) {
                    if ($file->isValid()) {
                        $filename = time() . '_' . Str::random(8) . '_' . $file->getClientOriginalName();
                        $file->storeAs('external_evidence', $filename, 'public');
                        $newFiles[] = $filename;
                    }
                }
                $data['evidence_file_external'] = array_merge($existingFiles, $newFiles);
            } else {
                $data['evidence_file_external'] = $existingFiles;
            }

            $externalTeam->update($data);

            $this->recalculateTotalDuration($externalTeam->report_id);

            return redirect()
                ->route('report.edit', $externalTeam->report->uuid)
                ->with('success', 'External team data has been successfully updated.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update report external team');
                // dd($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $externalTeam = ReportExternalTeam::findOrFail($id);

            $reportUuid = $externalTeam->report->uuid;

            if (!empty($externalTeam->evidence_file_external)) {
                foreach ($externalTeam->evidence_file_external as $file) {
                    Storage::disk('public')->delete('external_evidence/' . $file);
                }
            }

            $externalTeam->delete();

            $reportId   = $externalTeam->report_id;
            $reportUuid = $externalTeam->report->uuid;

            $externalTeam->delete();

            $this->recalculateTotalDuration($reportId);

            return redirect()
                ->route('report.edit', $reportUuid)
                ->with('success', 'External data successfully deleted');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete report external team');
                // dd($e->getMessage());
        }
    }

    private function recalculateTotalDuration(int $reportId): void
    {
        $total = ReportExternalTeam::where('report_id', $reportId)->sum('duration');

        ReportExternalTeam::where('report_id', $reportId)
            ->update(['total_external_duration' => $total]);
    }
}
