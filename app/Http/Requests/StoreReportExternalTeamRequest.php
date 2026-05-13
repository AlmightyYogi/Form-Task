<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportExternalTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_id' => 'required|exists:reports,uuid',
            'external_teams' => 'required|exists:mst_external_teams,id',
            'pic'   => 'nullable|string|max:255',
            'start_time'    => 'required|date_format:Y-m-d\TH:i',
            'end_time'  => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start_time',
            'evidence_file_external'    => 'nullable|array',
            'evidence_file_external.*'    => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,csv,xls,xlsx,zip',
        ];
    }

    public function messages(): array
    {
        return [
            'external_team.required'    => 'External team is required.',
            'start_time.required'   => 'Start time is requied.',
            'end_time.required' => 'End time must be equal or greater than start time',
            'evidence_file_external.*.max'  => 'File size cannot be greater than 10 mb',
        ];
    }
}