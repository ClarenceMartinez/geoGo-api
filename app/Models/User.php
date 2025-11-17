<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'company_id',
        'branch_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isEmployee(): bool
    {
        // 3 = Manager, 4 = Empleado
        return in_array($this->role_id, [3, 4]);
    }
}
