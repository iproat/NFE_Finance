<?php

namespace App\Http\Controllers\Leave;

use App\User;
use Carbon\Carbon;
use App\Model\LeaveType;
use Illuminate\Http\Request;
use App\Model\LeaveApplication;
use App\Model\PaidLeaveApplication;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\LeaveRepository;
use App\Repositories\CommonRepository;
use App\Notifications\LeaveNotification;
use App\Http\Requests\ApplyForLeaveRequest;
use Illuminate\Support\Facades\Notification;

class ApplyForLeaveController extends Controller
{

    protected $commonRepository;
    protected $leaveRepository;

    public function __construct(CommonRepository $commonRepository, LeaveRepository $leaveRepository)
    {
        $this->commonRepository = $commonRepository;
        $this->leaveRepository = $leaveRepository;
    }

    public function index()
    {
        $results = LeaveApplication::with(['employee', 'leaveType', 'approveBy', 'rejectBy'])
            ->where('employee_id', decrypt(session('logged_session_data.employee_id')))
            ->orderBy('leave_application_id', 'desc')
            ->paginate(10);

        return view('admin.leave.applyForLeave.index', ['results' => $results]);
    }

    public function create()
    {
        $leaveTypeList = $this->commonRepository->leaveTypeList();
        $getEmployeeInfo = $this->commonRepository->getEmployeeInfo(Auth::user()->user_id);

        $leaveType = LeaveType::sum('num_of_day');
        $totalPaidLeaveTaken = PaidLeaveApplication::where('employee_id', Auth::user()->user_id)->where('status', 2)->where('created_at', Carbon::now()->year)->pluck('number_of_day');
        $totalLeaveTaken = LeaveApplication::where('employee_id', Auth::user()->user_id)->where('status', 2)->whereYear('created_at', Carbon::now()->year)->pluck('number_of_day');
        $sumOfLeaveTaken = (int) $totalLeaveTaken->sum();
        $permissableLeave = $leaveType;
        $checkLeaveEligibility = $sumOfLeaveTaken <= $permissableLeave;
        $leaveBalance = $leaveType - $sumOfLeaveTaken;

        $data = [
            'checkLeaveEligibility' => $checkLeaveEligibility == true ? 'Eligibile' : 'Not Eligibile',
            'leaveType' => $leaveType,
            'sumOfLeaveTaken' => $sumOfLeaveTaken,
            'leaveBalance' => $leaveBalance,
            'leaveTypeList' => $leaveTypeList,
            'permissableLeave' => $permissableLeave,
            'totalLeaveTaken' => $totalLeaveTaken,
            'totalPaidLeaveTaken' => $totalPaidLeaveTaken,
        ];

        return view('admin.leave.applyForLeave.leave_application_form', ['leaveTypeList' => $leaveTypeList, 'data' => $data, 'getEmployeeInfo' => $getEmployeeInfo]);
    }

    public function getEmployeeLeaveBalance(Request $request)
    {
        $leave_type_id = $request->leave_type_id;
        $employee_id = $request->employee_id;
        if ($leave_type_id != '' && $employee_id != '') {
            return $this->leaveRepository->calculateEmployeeLeaveBalance($leave_type_id, $employee_id);
        }
    }

    public function applyForTotalNumberOfDays(Request $request)
    {
        Log::info($request->all());
        $application_from_date = dateConvertFormtoDB($request->application_from_date);
        $application_to_date = dateConvertFormtoDB($request->application_to_date);
        return $this->leaveRepository->calculateTotalNumberOfLeaveDays($application_from_date, $application_to_date);
    }

    public function store(ApplyForLeaveRequest $request)
    {
        $input = $request->all();
        $input['application_from_date'] = dateConvertFormtoDB($request->application_from_date);
        $input['application_to_date'] = dateConvertFormtoDB($request->application_to_date);
        $input['application_date'] = date('Y-m-d');

        $authorizedUsers = User::whereHas('role', function ($query) {
            $query->whereIn('role_id', [1, 2, 3, 4]);
        })->get();

        try {
            LeaveApplication::create($input);
            $leaveType = LeaveType::where('leave_type_id', $input['leave_type_id'])->first();

            $dataSet = [
                'leave_type' => $leaveType->leave_type_name,
                'time_period' => $input['application_from_date'] . ' to ' . $input['application_to_date'],
                'date' => $input['application_date'],
                'name' => decrypt(session('logged_session_data.user_name')),
            ];

            Notification::send($authorizedUsers, new LeaveNotification($dataSet));

            // foreach ($authorizedUsers as $key => $user) {
            //     $user->notify(new LeaveNotification($dataSet));
            // }

            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('applyForLeave')->with('success', 'Leave application successfully send.');
        } else {
            return redirect('applyForLeave')->with('error', 'Something error found !, Please try again.');
        }
    }
}
