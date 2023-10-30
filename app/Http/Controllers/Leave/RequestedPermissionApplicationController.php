<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Model\Employee;
use App\Model\LeavePermission;
use App\Repositories\LeaveRepository;
use Illuminate\Http\Request;

class RequestedPermissionApplicationController extends Controller
{

    protected $leaveRepository;

    public function __construct(LeaveRepository $leaveRepository)
    {
        $this->leaveRepository = $leaveRepository;
    }

    public function index()
    {

        $results = [];

        $isAuthorizedPerson = Employee::where('employee_id', decrypt(session('logged_session_data.employee_id')))
            ->whereHas('userName', function ($q) {
                return $q->whereIn('role_id', [1, 2, 3]);
            })->exists();

        $isHod = Employee::where('employee_id', decrypt(session('logged_session_data.employee_id')))
            ->whereHas('userName', function ($q) {
                return $q->where('role_id', 4);
            })->exists();

        $departmentWiseEmployee = Employee::select('employee_id')
            ->where('department_id', decrypt(session('logged_session_data.department_id')))
            ->get()->toArray();

        $totalEmployee = Employee::select('employee_id')->get()->toArray();

        $hasSupervisorWiseEmployee = Employee::select('employee_id')
            ->where('supervisor_id', decrypt(session('logged_session_data.employee_id')))
            ->get()->toArray();

        if ($isAuthorizedPerson) {
            $results = LeavePermission::with(['employee'])
                ->whereIn('employee_id', array_values($totalEmployee))
                ->orderBy('status', 'asc')
                ->orderBy('leave_permission_id', 'desc')
                ->get();
        } elseif ($isHod) {
            $results = LeavePermission::with(['employee'])
                ->whereIn('employee_id', array_values($departmentWiseEmployee))
                ->orderBy('status', 'asc')
                ->orderBy('leave_permission_id', 'desc')
                ->get();
        } elseif (count($hasSupervisorWiseEmployee) > 0) {
            $results = LeavePermission::with(['employee'])
                ->whereIn('employee_id', array_values($hasSupervisorWiseEmployee))
                ->orderBy('status', 'asc')
                ->orderBy('leave_permission_id', 'desc')
                ->get();
        }
        // dd($results);
        return view('admin.leave.permissionApplication.permissionApplicationList', ['results' => $results]);
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
                echo "approve";
            } else {
                echo "reject";
            }
        } else {
            echo "error";
        }
    }
}
