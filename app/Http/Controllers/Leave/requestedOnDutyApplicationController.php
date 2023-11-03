<?php

namespace App\Http\Controllers\Leave;

use App\Model\OnDuty;
use App\Model\Employee;
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
                ->where('primary_approval', 0)
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
                ->where('status', 1)
                ->where('primary_approval', 1)
                ->orderBy('status', 'asc')
                ->orderBy('on_duty_id', 'desc')
                ->paginate();
        }
        $hasHr = Employee::select('employee_id')->where('hr_id', decrypt(session('logged_session_data.employee_id')))->get()->toArray();

        if (count($hasHr) == 0) {
            $hrResults = [];
        } else {
            $hrResults  =  OnDuty::with('employee')
                ->whereIn('employee_id', array_values($hasHr))
                ->where('status', 1)
                ->where('primary_approval', 1)
                ->orderBy('status', 'asc')
                ->orderBy('on_duty_id', 'desc')
                ->paginate();
        }
        return view('admin.leave.onDutyApplication.onDutyApplicationList',   [
            'adminResults' => $adminResults,
            'operationManagerResults' => $operationManagerResults,
            'hrResults' => $hrResults,
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

        return view('admin.leave.onDutyApplication.onDutyDetails', ['leaveApplicationData' => $leaveApplicationData]);
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
