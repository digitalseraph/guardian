<?php

namespace DigitalSeraph\Guardian\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use DigitalSeraph\Guardian\Notifications\ResetAdminPasswordNotification;
use DigitalSeraph\Guardian\Traits\GuardianAdminUserTrait;

class AdminUser extends Authenticatable
{
    use Notifiable;
    use GuardianAdminUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['first_name', 'last_name', 'email', 'password'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Send password reset notification
     *
     * @param string
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetAdminPasswordNotification($token));
    }
}
