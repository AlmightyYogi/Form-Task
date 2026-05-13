<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use App\Notifications\VerifyEmailCustom;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasUuids;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'lockout_end'       => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Relasi ke Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Helper methods untuk pengecekan role
     */
    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }

    public function isViewer(): bool
    {
        return $this->role?->name === 'viewer';
    }

    public function isUser(): bool
    {
        return $this->role?->name === 'user';
    }

    // public function sendEmailVerificationNotification()
    // {
    //     (new VerifyEmailCustom)->send($this);
    // }
}