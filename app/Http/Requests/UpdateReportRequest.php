<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'requestor'        => 'required|string|max:255',
            'requestor_email'  => 'required|email',
            'request_date'     => 'required|date',
            'report_time'      => 'required',
            'servicerestored_time'  => 'nullable|date',
            'created_at' => 'nullable|date',
            'total_internal_duration' => 'nullable',
            'restoration_evidence'    => 'nullable|array',
            'restoration_evidence.*'    => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,csv,xls,xlsx,zip',
            'apps'             => 'required|string',
            'description'      => 'required|string',
            'rca'              => 'nullable|string',
            'type'             => 'required|string',
            'severity'         => 'required|string',
            'assigned_to'      => 'required|string',
            'scope'            => 'required|string',
            'scope_other'      => 'nullable|string|max:255',
            'resolution'       => 'nullable|string',
            'status'           => 'required|integer',
            'handled_by'       => 'nullable|boolean',
            'resolved_time'    => 'nullable',
            'file_downtime_evidence'      => 'nullable|max:10240',
            'closed_at'        => 'nullable'
        ];
    }
}
