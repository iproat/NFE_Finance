<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
  

    protected $table = 'employee';
    protected $primaryKey = 'employee_id';
    protected $fillable = [
        'employee_id', 'branch_id', 'user_id', 'finger_id', 'department_id', 'incentive', 'salary_limit', 'designation_id', 'branch_id', 'work_shift', 'supervisor_id',
        'work_shift_id', 'email', 'first_name',
        'last_name', 'date_of_birth', 'date_of_joining', 'date_of_leaving',
        'gender', 'marital_status', 'work_hours', 'hr_id', 'operation_manager_id', 'nationality',
        'document_title16', 'document_name16', 'expiry_date16',
        'document_title17', 'document_name17', 'expiry_date17',
        'document_title18', 'document_name18', 'expiry_date18',
        'document_title19', 'document_name19', 'expiry_date19',
        'document_title20', 'document_name20', 'expiry_date20',
        'document_title21', 'document_name21', 'expiry_date21',
        'document_title8', 'document_name8', 'expiry_date8',
        'document_title9', 'document_name9', 'expiry_date9','document_title10',
         'document_name10', 'expiry_date10','document_title11', 'document_name11', 'expiry_date11',
        'photo', 'address', 'emergency_contacts', 'phone', 'document_title',
        'document_name', 'document_expiry', 'document_title2', 'document_name2',
        'document_expiry2', 'document_title3', 'document_name3', 'document_expiry3',
        'document_title4', 'document_name4', 'document_expiry4', 'document_title5',
        'document_name5', 'document_expiry5', 'status', 'created_by', 'updated_by', 'religion',
        'pay_grade_id', 'hourly_salaries_id', 'esi_card_number', 'pf_account_number', 'device_employee_id',
    ];

    // protected $encryptable = [
    //     'email', 'first_name','last_name', 'gender', 'marital_status',
    //     'photo', 'address', 'emergency_contacts', 'phone', 'religion', 'device_employee_id',
    // ];

    // protected $with = ['branch:branch_id,branch_name', 'department:department_id,department_name', 'designation:designation_id,designation_name'];

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeFilter($query, $request)
    {
        return $query->where('department_id', $request['department_id'])->where('branch_id', $request['branch_id'])->where('employee_id', $request['employee_id']);
    }

    public function userName()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function payGrade()
    {
        return $this->belongsTo(PayGrade::class, 'pay_grade_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function hourlySalaries()
    {
        return $this->belongsTo(HourlySalary::class, 'hourly_salaries_id');
    }

    public function workShift()
    {
        return $this->belongsTo(WorkShift::class, 'work_shift_id');
    }

    // public function scopeFilter($query, $request)
    // {
    //     return $query->where('employee_id', $request['employee_id'])->where('department', $request['department'])->where('finger_id', $request['finger_id']);
    // }
    // public function scopeStatus($query, $status)
    // {
    //     return $query->where('status', $status);
    // }
}
