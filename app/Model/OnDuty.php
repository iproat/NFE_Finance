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
        'hr_status',
        'manager_status',
        'accepted_admin',
        'rejected_admin',
        'accepted_superadmin',
        'rejected_superadmin',
        'approved_by', 'approve_date', 'reject_by', 'reject_date', 'manager_approved_by',
        'manager_reject_by', 'manager_approve_date', 'manager_reject_date'
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
        return $this->belongsTo(Employee::class, 'approved_by', 'employee_id');
    }

    public function rejectBy()
    {
        return $this->belongsTo(Employee::class, 'reject_by', 'employee_id');
    }
    public function managerApproveBy()
    {
        return $this->belongsTo(Employee::class, 'manager_approved_by', 'employee_id');
    }

    public function managerRejectBy()
    {
        return $this->belongsTo(Employee::class, 'manager_reject_by', 'employee_id');
    }
}
