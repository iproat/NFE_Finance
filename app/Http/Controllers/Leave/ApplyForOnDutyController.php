<?php

namespace App\Http\Controllers\Leave;

use DateTime;
use Carbon\Carbon;
use App\Model\OnDuty;
use App\Model\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\LeaveRepository;
use App\Repositories\CommonRepository;

class ApplyForOnDutyController extends Controller
{
    protected $commonRepository;
    protected $leaveRepository;

    public function __construct(CommonRepository $commonRepository, LeaveRepository $leaveRepository)
    {
        $this->commonRepository = $commonRepository;
        $this->leaveRepository  = $leaveRepository;
    }

    public function index()
    {
        $results = OnDuty::with(['employee'])
            ->where('employee_id', decrypt(session('logged_session_data.employee_id')))
            ->orderBy('application_date', 'desc')
            ->paginate(10);
        return view('admin.leave.applyForOnduty.index', ['results' => $results]);
    }


    public function create()
    {
        // $employeeList   = $this->commonRepository->employeeList();
        $getEmployeeInfo = $this->commonRepository->getEmployeeInfo(auth()->user()->user_id);

        $totalLeaveTaken       = OnDuty::where('employee_id', auth()->user()->user_id)->where('status', 2)->whereYear('created_at', Carbon::now()->year)->pluck('no_of_days');
        $sumOfLeaveTaken       = (int)$totalLeaveTaken->sum();
        // $checkLeaveEligibility = $sumOfLeaveTaken <= $permissableLeave;
        // $leaveBalance          = $leaveType - $sumOfLeaveTaken;

        $data = [
            // 'checkLeaveEligibility' => $checkLeaveEligibility == true ? 'Eligibile' : 'Not Eligibile',
            // 'leaveType'             => $leaveType,
            'sumOfLeaveTaken'       => $sumOfLeaveTaken,
            // 'leaveBalance'          => $leaveBalance,
            // 'leaveTypeList'         => $leaveTypeList,
            // 'permissableLeave'      => $permissableLeave,
            'totalLeaveTaken'      => $totalLeaveTaken,
            // 'totalPaidLeaveTaken'      => $totalPaidLeaveTaken,
            // 'employeeList' => $employeeList
        ];

        return view('admin.leave.applyForOnduty.form', ['getEmployeeInfo' => $getEmployeeInfo, 'data' => $data]);
    }
    public function store(Request $request)
    {
        try {
            $input = $request->all();
            $employee = Employee::where('employee_id', $request->employee_id)->first();
            $input['application_from_date'] = dateConvertFormtoDB($input['application_from_date']);
            $input['application_to_date'] = dateConvertFormtoDB($input['application_to_date']);
            $input['application_date']      = date('Y-m-d');
            $input['employee_id'] = $employee->employee_id;
            if ($input['is_work_from_home'] == 'Y') {
                $input['is_work_from_home'] = 1;
            } else {
                $input['is_work_from_home'] = 0;
            }
            $input['accepted_admin'] = null;
            $input['approve_date'] = null;
            $input['remark_admin'] = null;
            $input['status'] = 1;

            $fromDate = new DateTime($input['application_from_date']);
            $toDate = new DateTime($input['application_to_date']);

            if ($fromDate == $toDate) {
                $no_of_days = 1;
            } else {
                $interval = $fromDate->diff($toDate);
                $no_of_days = $interval->d + 1;
            }

            $input['no_of_days'] = $no_of_days;

            $ifExists = OnDuty::where('application_from_date', '>=', $input['application_from_date'])
                ->where('application_to_date', '<=', $input['application_to_date'])->where('employee_id', $input['employee_id'])->first();

            if ($ifExists) {
                return redirect(route('applyForOnDuty.index'))->with('error', 'On Duty application exists between selected dates. Try different dates.');
            }
            OnDuty::create($input);
            return redirect(route('applyForOnDuty.index'))->with('success', 'On Duty application sent successfully');
        } catch (\Throwable $th) {
            return redirect(route('applyForOnDuty.index'))->with('error',  'Something went wrong!' . $th->getMessage());
        }
    }
}
