<?php

namespace App;

use App\Model\Role;
use App\Model\Employee;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{

    use Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'role_id', 'user_name', 'password', 'status', 'created_by', 'updated_by', 'device_employee_id', 'google2fa_secret','org_password'];

    protected $hidden = [
        'password', 'remember_token',
    ];



    public static function scopeUserRole($query, $role)
    {
        return $query->where('role_id', $role);
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'role_id', 'role_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function getIsAdminAttribute()
    {
        return $this->role()->where('role_id', 1)->exists();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
