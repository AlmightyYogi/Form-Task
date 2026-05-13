<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
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
            'requestor' => 'required|string|max:255',
            'requestor_email' => 'required|email',
            'request_date' => 'required|date',
            'report_time' => 'required',
            'apps' => 'required|string',
            'type' => 'required|string',
            'description' => 'required|string',
            'severity' => 'required|string',
            'assigned_to' => 'required',
            'scope' => 'required',
            'file_downtime_evidence'      => 'nullable|max:10240',
            'status' => 'nullable|integer'
        ];
    }
}
