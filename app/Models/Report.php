<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Report extends Model
{
    protected $fillable = [
        'incident',
        'requestor',
        'requestor_email',
        'request_date',
        'report_time',
        'response_time',
        'resolved_time',
        'restored_time',
        'servicerestored_time',
        'total_internal_duration',
        'restoration_evidence',
        'apps',
        'type',
        'description',
        'rca',
        'severity',
        'assigned_to',
        'scope',
        'resolution',
        'status',
        'handled_by',
        'file_downtime_evidence',
        'created_at',
        'closed_at'
    ];

    protected $casts = [
        'request_date' => 'date',
        'resolved_time' => 'datetime',
        'restored_time' => 'integer',
        'servicerestored_time' => 'datetime',
        'status' => 'integer',
        'handled_by' => 'boolean',
        'restoration_evidence' => 'array',
        'file_downtime_evidence' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function externalTeams()
    {
        return $this->hasMany(ReportExternalTeam::class, 'report_id');
    }

    public function isExternal()
    {
        return $this->handled_by === true;
    }

    public function isInternal()
    {
        return $this->handled_by === false;
    }
}
