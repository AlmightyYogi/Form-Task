<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReportExternalTeam extends Model
{
    protected $table = 'report_external_teams';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'report_id',
        'external_teams',
        'pic',  
        'start_time',
        'end_time',
        'duration',
        'total_external_duration',
        'evidence_file_external'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'evidence_file_external' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function report()
    {
        return $this->belongsTo(Report::class,'report_id');
    }

    public function externalTeam()
    {
        return $this->belongsTo(MstExternalTeam::class, 'external_teams');
    }
}