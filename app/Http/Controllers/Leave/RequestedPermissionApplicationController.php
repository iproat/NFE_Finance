<?php

namespace App\Http\Controllers\Leave;

use App\Model\Employee;
use App\Components\Common;
use Illuminate\Http\Request;
use App\Model\LeavePermission;
use App\Http\Controllers\Controller;
use App\Repositories\LeaveRepository;

class RequestedPermissionApplicationController extends Controller
{

    protected $leaveRepository;

    public function __construct(LeaveRepository $leaveRepository)
    {
        $this->leaveRepository = $leaveRepository;
    }

    public function index()
    {
        $hasSupervisor = Employee::select('employee_id')->where('supervisor_id', decrypt(session('logged_session_data.employee_id')))->get()->toArray();

        if (count($hasSupervisor) == 0) {
            $adminResults = [];
        } else {
            $adminResults  = LeavePermission::with('employee')
                ->whereIn('employee_id', array_values($hasSupervisor))
                ->where('status', 1)
                ->where('manager_status', 2)
                ->orderBy('status', 'asc')
                ->orderBy('leave_permission_id', 'desc')
                ->paginate();
        }
        $hasOperationManager = Employee::select('employee_id')->where('operation_manager_id', decrypt(session('logged_session_data.employee_id')))->get()->toArray();
        if (count($hasOperationManager) == 0) {
            $operationManagerResults = [];
        } else {
            $operationManagerResults  =  LeavePermission::with('employee')
                ->whereIn('employee_id', array_values($hasOperationManager))
                ->where('manager_status', 1)
                ->orderBy('status', 'asc')
                ->orderBy('leave_permission_id', 'desc')
                ->paginate();
        }
        // $results = [];

        // $isAuthorizedPerson = Employee::where('employee_id', decrypt(session('logged_session_data.employee_id')))
        //     ->whereHas('userName', function ($q) {
        //         return $q->whereIn('role_id', [1, 2, 3]);
        //     })->exists();

        // $isHod = Employee::where('employee_id', decrypt(session('logged_session_data.employee_id')))
        //     ->whereHas('userName', function ($q) {
        //         return $q->where('role_id', 4);
        //     })->exists();

        // $departmentWiseEmployee = Employee::select('employee_id')
        //     ->where('department_id', decrypt(session('logged_session_data.department_id')))
        //     ->get()->toArray();

        // $totalEmployee = Employee::select('employee_id')->get()->toArray();

        // $hasSupervisorWiseEmployee = Employee::select('employee_id')
        //     ->where('supervisor_id', decrypt(session('logged_session_data.employee_id')))
        //     ->get()->toArray();

        // if ($isAuthorizedPerson) {
        //     $results = LeavePermission::with(['employee'])
        //         ->whereIn('employee_id', array_values($totalEmployee))
        //         ->orderBy('status', 'asc')
        //         ->orderBy('leave_permission_id', 'desc')
        //         ->get();
        // } elseif ($isHod) {
        //     $results = LeavePermission::with(['employee'])
        //         ->whereIn('employee_id', array_values($departmentWiseEmployee))
        //         ->orderBy('status', 'asc')
        //         ->orderBy('leave_permission_id', 'desc')
        //         ->get();
        // } elseif (count($hasSupervisorWiseEmployee) > 0) {
        //     $results = LeavePermission::with(['employee'])
        //         ->whereIn('employee_id', array_values($hasSupervisorWiseEmployee))
        //         ->orderBy('status', 'asc')
        //         ->orderBy('leave_permission_id', 'desc')
        //         ->get();
        // }
        //  dd($operationManagerResults);
        return view('admin.leave.permissionApplication.permissionApplicationList', [  'adminResults' => $adminResults,
        'operationManagerResults' => $operationManagerResults,]);
    }

    public function viewDetails($id)
    {
        $leaveApplicationData = LeavePermission::with(['employee' => function ($q) {
            $q->with(['designation']);
        }])->where('leave_permission_id', $id)->where('status', 1)->first();

        if (!$leaveApplicationData) {
            return response()->view('errors.404', [], 404);
        }

        return view('admin.leave.permissionApplication.permissionDetails', ['leaveApplicationData' => $leaveApplicationData]);
    }

    public function update(Request $request, $id)
    {

        $data = LeavePermission::findOrFail($id);
        $input = $request->all();
        if ($request->status == 2) {
            $input['approve_date'] = date('Y-m-d');
            $input['approve_by'] = decrypt(session('logged_session_data.employee_id'));
        } else {
            $input['reject_date'] = date('Y-m-d');
            $input['reject_by'] = decrypt(session('logged_session_data.employee_id'));
        }

        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                return redirect('requestedPermissionApplication')->with('success', 'Permission application approved successfully. ');
            } else {
                return redirect('requestedPermissionApplication')->with('error', 'Permission application reject successfully. ');
            }
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function approveOrRejectPermissionApplication(Request $request)
    {
        $data = LeavePermission::findOrFail($request->leave_permission_id);
        $input = $request->all();

        if ($request->status == 2) {
            $input['approve_date'] = date('Y-m-d');
            $input['approved_by'] = decrypt(session('logged_session_data.employee_id'));
        } else {
            $input['reject_date'] = date('Y-m-d');
            $input['reject_by'] = decrypt(session('logged_session_data.employee_id'));
        }

        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                echo "approve";
            } else {
                echo "reject";
            }
        } else {
            echo "error";
        }
    }
    public function approveOrRejectManagerPermissionApplication(Request $request)
    {
        $data = LeavePermission::findOrFail($request->leave_permission_id);
        $input = $request->all();

        if ($request->status == 2) {
            $input['manager_status'] = 2;
            $input['manager_approve_date'] = date('Y-m-d');
            $input['manager_approved_by'] = decrypt(session('logged_session_data.employee_id'));    
        } else {
            $input['manager_status'] = 3;
            $input['manager_reject_date'] = date('Y-m-d');
            $input['manager_reject_by'] = decrypt(session('logged_session_data.employee_id'));
        }

        try {
            
            $data->update([
                'manager_status' => $input['manager_status'],
                'manager_reject_date' => $input['manager_reject_date'],
                'manager_reject_by' => $input['manager_reject_by'],
                'manager_approve_date' => $input['manager_approve_date'],
                'manager_approved_by' => $input['manager_approved_by']
            ]);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                $data = LeavePermission::findOrFail($request->leave_permission_id)->first();
                $employee = Employee::where('employee_id', $data->employee_id)->select('supervisor_id')->first();
                $hod = Employee::where('employee_id', $employee->supervisor_id)->first();
                if ($hod != '') {
                    if ($hod->email) {
                        $maildata = Common::mail('emails/mail', $hod->email, 'Permission Request Notification', ['head_name' => $hod->first_name . ' ' . $hod->last_name, 'request_info' => $employee->first_name . ' ' . $employee->last_name . ' have requested for Permission (for ' . $request->purpose . ')Application Date ' . ' ' . dateConvertFormtoDB($request->permission_date) . ' fromTime ' . ' ' . $request->from_time . ' toTime ' . $request->from_time, 'status_info' => '']);
                    }
                }
                echo "approve";
            } else {
                echo "reject";
            }
        } else {
            echo "error";
        }
    }
}
