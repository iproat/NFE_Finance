<?php

namespace App\Http\Controllers\Leave;

use App\Model\OnDuty;
use App\Model\Employee;
use App\Components\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\LeaveRepository;

class requestedOnDutyApplicationController extends Controller
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
            $adminResults  = OnDuty::with('employee')
                ->whereIn('employee_id', array_values($hasSupervisor))
                ->where('status', 1)
                ->where('manager_status', 2)
                ->orderBy('status', 'asc')
                ->orderBy('on_duty_id', 'desc')
                ->paginate();
        }
        $hasOperationManager = Employee::select('employee_id')->where('operation_manager_id', decrypt(session('logged_session_data.employee_id')))->get()->toArray();
        if (count($hasOperationManager) == 0) {
            $operationManagerResults = [];
        } else {
            $operationManagerResults  =  OnDuty::with('employee')
                ->whereIn('employee_id', array_values($hasOperationManager))
                ->where('manager_status', 1)
                ->orderBy('status', 'asc')
                ->orderBy('on_duty_id', 'desc')
                ->paginate();
        }

        return view('admin.leave.onDutyApplication.onDutyApplicationList',   [
            'adminResults' => $adminResults,
            'operationManagerResults' => $operationManagerResults,
        ]);
    }
    public function viewDetails($id)
    {
        $leaveApplicationData = OnDuty::with(['employee' => function ($q) {
            $q->with(['designation']);
        }])->where('on_duty_id', $id)->where('status', 1)->first();

        if (!$leaveApplicationData) {
            return response()->view('errors.404', [], 404);
        }

        return view('admin.leave.onDutyApplicatioin.onDutyDetails', ['leaveApplicationData' => $leaveApplicationData]);
    }
    public function update(Request $request, $id)
    {
        $data = OnDuty::findOrFail($id);
        $input = $request->all();
        if ($request->status == 2) {
            $input['approve_date']     = date('Y-m-d');
            $input['status']           = 1;
            $input['primary_approval'] = 1;
            $input['approve_by']       = decrypt(session('logged_session_data.employee_id'));
            $input['head_remarks']     = $request->leave_remark;
        } else {
            $input['reject_date'] = date('Y-m-d');
            $input['reject_by'] = decrypt(session('logged_session_data.employee_id'));
            $input['primary_approval'] = 2;
            $input['head_remarks']     = $request->leave_remark;
        }

        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        if ($bug == 0) {
            if ($request->status == 2) {


                return redirect('requestedOnDutyApplication')->with('success', 'Leave application approved successfully. ');
            } else {
                return redirect('requestedOnDutyApplication')->with('success', 'Leave application reject successfully. ');
            }
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function approveOrRejectOnDutyApplication(Request $request)
    {
        $data = OnDuty::findOrFail($request->on_duty_id);
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
            info($e);
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
    public function approveOrRejectManagerOnDutyApplication(Request $request)
    {
       
        $data = OnDuty::findOrFail($request->on_duty_id);
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
                'manager_status' => $input['manager_status']
            ]);
            $bug = 0;
        } catch (\Exception $e) {
       
            $bug = 1;
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                $data = OnDuty::findOrFail($request->on_duty_id)->first();
                $employee = Employee::where('employee_id', $data->employee_id)->first();
                $hod = Employee::where('employee_id', $employee->supervisor_id)->first();
                if ($hod != '') {
                    if ($hod->email) {
                        $maildata = Common::mail('emails/mail', $hod->email, 'On Duty Request Notification', ['head_name' => $hod->first_name . ' ' . $hod->last_name, 'request_info' => $employee->first_name . ' ' . $employee->last_name . ', have requested for on duty (Purpose: ' . $data->purpose . ') from ' . ' ' . dateConvertFormtoDB($data->application_from_date) . ' to ' . dateConvertFormtoDB($data->application_to_date), 'status_info' => '']);
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
