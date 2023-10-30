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
            $results = OnDuty::with(['employee'])
                ->whereIn('employee_id', array_values($totalEmployee))
                ->orderBy('status', 'asc')
                ->orderBy('on_duty_id', 'desc')
                ->get();
        } elseif ($isHod) {
            $results = OnDuty::with(['employee'])
                ->whereIn('employee_id', array_values($departmentWiseEmployee))
                ->orderBy('status', 'asc')
                ->orderBy('on_duty_id', 'desc')
                ->get();
        } elseif (count($hasSupervisorWiseEmployee) > 0) {
            $results = OnDuty::with(['employee'])
                ->whereIn('employee_id', array_values($hasSupervisorWiseEmployee))
                ->orderBy('status', 'asc')
                ->orderBy('on_duty_id', 'desc')
                ->get();
        }
        // dd($results);
        return view('admin.leave.onDutyApplication.onDutyApplicationList', ['results' => $results]);
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
