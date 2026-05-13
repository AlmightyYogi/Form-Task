<?php

namespace App\Services;

use App\Models\Report;
use Carbon\Carbon;

class IncidentService
{
    public function generateCode($type)
    {
        $prefix = match(strtolower($type)) {
            'incident' => 'INC',
            'request'  => 'REQ',
            'activity' => 'ACT',
            default    => 'INC',
        };

        $last = Report::where('incident', 'like', $prefix . '%')
                ->latest('id')
                ->first();

        $nextNumber = 1;

        if ($last && $last->incident) {
            $number = (int) substr($last->incident, strlen($prefix));
            $nextNumber = $number + 1;
        }

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function createReport(array $data)
    {
        if (!empty($data['scope_other'] ?? '')) {
            $data['scope'] = $data['scope_other'];
        }
        unset($data['scope_other']);

        $data['incident'] = $this->generateCode($data['type'] ?? 'Incident');

        if (!empty($data['report_time']) && !empty($data['request_date'])) {
            $data['response_time'] = Carbon::now();
        } else {
            $data['response_time'] = null;
        }

        $data['restored_time']          = null;
        $data['servicerestored_time']   = null;
        $data['total_internal_duration'] = null;

        return Report::create($data);
    }
}