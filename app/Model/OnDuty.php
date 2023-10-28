<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OnDuty extends Model
{
    protected $table = 'on_duty';
    protected $primaryKey = 'on_duty_id';

    protected $fillable = [
        'on_duty_id',
        'employee_id',
        'application_from_date',
        'application_to_date',
        'application_date',
        'no_of_days',
        'is_work_from_home',
        'purpose',
        'remark_admin',
        'remark_superadmin',
        'status',
        'accepted_admin',
        'rejected_admin',
        'accepted_superadmin',
        'rejected_superadmin',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id')->withDefault(
            [
                'employee_id' => 0,
                'user_id' => 0,
                'department_id' => 0,
                'email' => 'unknown email',
                'first_name' => 'unknown',
                'last_name' => 'unknown last name'

            ]
        );
    }
    public function approveBy()
    {
        return $this->belongsTo(Employee::class, 'accepted_admin', 'employee_id')->withDefault(
            [
                'employee_id' => 0,
                'user_id' => 0,
                'department_id' => 0,
                'email' => 'unknown email',
                'first_name' => 'unknown',
                'last_name' => 'unknown last name'

            ]
        );
    }
}
