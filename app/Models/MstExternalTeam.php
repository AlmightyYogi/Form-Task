<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MstExternalTeam extends Model
{
    protected $table = 'mst_external_teams';

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function reportExternalTeams()
    {
        return $this->hasMany(ReportExternalTeam::class, 'external_teams');
    }
}