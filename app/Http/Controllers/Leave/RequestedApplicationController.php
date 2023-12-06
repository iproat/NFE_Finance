<?php

namespace App\Http\Controllers\Leave;

use App\Model\Employee;
use App\Components\Common;
use Illuminate\Http\Request;
use App\Model\LeaveApplication;
use App\Http\Controllers\Controller;
use App\Repositories\LeaveRepository;

class RequestedApplicationController extends Controller
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
            $adminresults = [];
        } else {
            $adminresults  = LeaveApplication::with('employee')
                ->whereIn('employee_id', array_values($hasSupervisor))
                ->where('status', 1)
                ->where('manager_status', 2)
                ->orderBy('status', 'asc')
                ->orderBy('leave_application_id', 'desc')
                ->paginate();
        }
        $hasOperationManager = Employee::select('employee_id')->where('operation_manager_id', decrypt(session('logged_session_data.employee_id')))->get()->toArray();
        if (count($hasOperationManager) == 0) {
            $managerresults = [];
        } else {
            $managerresults  =  LeaveApplication::with('employee')
                ->whereIn('employee_id', array_values($hasOperationManager))
                ->where('manager_status', 1)
                ->orderBy('status', 'asc')
                ->orderBy('leave_application_id', 'desc')
                ->paginate();
        }

//         $isAuthorizedPerson = Employee::where('employee_id', decrypt(session('logged_session_data.employee_id')))
//             ->whereHas('userName', function ($q) {
//                 return $q->whereIn('role_id', [1, 2, 3]);
//             })->exists();

//         $isHod = Employee::where('employee_id', decrypt(session('logged_session_data.employee_id')))
//             ->whereHas('userName', function ($q) {
//                 return $q->where('role_id', 4);
//             })->exists();

//         $departmentWiseEmployee = Employee::select('employee_id')
//             ->where('department_id', decrypt(session('logged_session_data.department_id')))
//             ->get()->toArray();

//         $totalEmployee = Employee::select('employee_id')->get()->toArray();

//         $hasSupervisorWiseEmployee = Employee::select('employee_id')
//             ->where('supervisor_id', decrypt(session('logged_session_data.employee_id')))
//             ->get()->toArray();

//         if ($isAuthorizedPerson) {
//             $results = LeaveApplication::with(['employee', 'leaveType'])
//                 ->whereIn('employee_id', array_values($totalEmployee))
//                 ->orderBy('status', 'asc')
//                 ->orderBy('leave_application_id', 'desc')
//                 ->get();
// dd($results);


//         } elseif ($isHod) {
//             $results = LeaveApplication::with(['employee', 'leaveType'])
//                 ->whereIn('employee_id', array_values($departmentWiseEmployee))
//                 ->orderBy('status', 'asc')
//                 ->orderBy('leave_application_id', 'desc')
//                 ->get();


//         } elseif (count($hasSupervisorWiseEmployee) > 0) {
//             $results = LeaveApplication::with(['employee', 'leaveType'])
//                 ->whereIn('employee_id', array_values($hasSupervisorWiseEmployee))
//                 ->orderBy('status', 'asc')
//                 ->orderBy('leave_application_id', 'desc')
//                 ->get();

//         }
// dd($results);
        return view('admin.leave.leaveApplication.leaveApplicationList', ['managerresults' => $managerresults,'adminresults'=>$adminresults]);
    }

    public function viewDetails($id)
    {
        $leaveApplicationData = LeaveApplication::with(['employee' => function ($q) {
            $q->with(['designation']);
        }])->with('leaveType')->where('leave_application_id', $id)->where('status', 1)->first();

        if (!$leaveApplicationData) {
            return response()->view('errors.404', [], 404);
        }
        

        $leaveBalanceArr = $this->leaveRepository->calculateEmployeeLeaveBalanceArray($leaveApplicationData->leave_type_id, $leaveApplicationData->employee_id);

        return view('admin.leave.leaveApplication.leaveDetails', ['leaveApplicationData' => $leaveApplicationData, 'leaveBalanceArr' => $leaveBalanceArr]);
    }

    public function update(Request $request, $id)
    {
// dd($request->all());
        $data = LeaveApplication::findOrFail($id);
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
                return redirect('requestedApplication')->with('success', 'Leave application approved successfully. ');
            } else {
                return redirect('requestedApplication')->with('success', 'Leave application reject successfully. ');
            }
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function approveOrRejectLeaveApplication(Request $request)
    {

        $data = LeaveApplication::findOrFail($request->leave_application_id);
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
    public function approveOrRejectManagerLeaveApplication(Request $request)
    {
        $data = LeaveApplication::findOrFail($request->leave_application_id);
        $input = $request->all();

        if ($request->status == 2) {
            $input['manager_status'] = 2;
        } else {
            $input['manager_status'] = 3;
        }

        try {
            $data->update([
                'manager_status' => $input['manager_status']
            ]);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                $data = LeaveApplication::findOrFail($request->leave_application_id)->first();
                $employee = Employee::where('employee_id', $data->employee_id)->select('supervisor_id')->first();
                $hod = Employee::where('employee_id', $employee->supervisor_id)->first();
                if ($hod != '') {
                    if ($hod->email) {
                        $maildata = Common::mail('emails/mail', $hod->email, 'Leave Request Notification', ['head_name' => $hod->first_name . ' ' . $hod->last_name, 'request_info' => $employee->first_name . ' ' . $employee->last_name . ', have requested for Leave (Purpose: ' . $request->purpose . ') from ' . ' ' . dateConvertFormtoDB($request->application_from_date) . ' to ' . dateConvertFormtoDB($request->application_to_date), 'status_info' => '']);
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
