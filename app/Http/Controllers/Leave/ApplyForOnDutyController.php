<?php

namespace App\Http\Controllers\Leave;

use DateTime;
use Carbon\Carbon;
use App\Model\OnDuty;
use App\Model\Employee;
use App\Components\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $getEmployeeInfo = $this->commonRepository->getEmployeeInfo(auth()->user()->user_id);

        $totalLeaveTaken       = OnDuty::where('employee_id', auth()->user()->user_id)->where('status', 2)->whereYear('created_at', Carbon::now()->year)->pluck('no_of_days');
        $sumOfLeaveTaken       = (int)$totalLeaveTaken->sum();

        $data = [
            'sumOfLeaveTaken'       => $sumOfLeaveTaken,
            'totalLeaveTaken'      => $totalLeaveTaken,
        ];

        return view('admin.leave.applyForOnduty.form', ['getEmployeeInfo' => $getEmployeeInfo, 'data' => $data]);
    }
    public function store(Request $request)
    {
        // DB::beginTransaction();

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

            $fromDate = new DateTime($input['application_from_date']);
            $toDate = new DateTime($input['application_to_date']);

            if ($fromDate == $toDate) {
                $no_of_days = 1;
            } else {
                $interval = $fromDate->diff($toDate);
                $no_of_days = $interval->d + 1;
            }

            $input['no_of_days'] = $no_of_days;

            try {
                $ifExists = OnDuty::where('application_from_date', '>=', $input['application_from_date'])
                    ->where('application_to_date', '<=', $input['application_to_date'])->where('employee_id', $input['employee_id'])->where('status', '!=', 3)
                    ->where('manager_status', '!=', 3)->first();

                if ($ifExists) {
                    return redirect(route('applyForOnDuty.index'))->with('error', 'On Duty application exists between selected dates. Try different dates.');
                }
                $emp = Employee::find($request->employee_id);
                $hod = Employee::where('employee_id', $emp->supervisor_id)->first();
                $operationManager = Employee::where('employee_id', $emp->operation_manager_id)->first();
                if ($hod->email) {
                    $maildata = Common::mail('emails/mail', $hod->email, 'OnDuty Request Notification', ['head_name' => $hod->first_name . ' ' . $hod->last_name, 'request_info' => $emp->first_name . ' ' . $emp->last_name . ' have requested for On-Duty (for ' . $request->purpose . ') from ' . ' ' . dateConvertFormtoDB($request->application_from_date) . ' to ' . dateConvertFormtoDB($request->application_to_date), 'status_info' => '']);
                }
                if ($operationManager->email) {
                    $maildata = Common::mail('emails/mail', $operationManager->email, 'OnDuty Request Notification', ['head_name' => $operationManager->first_name . ' ' . $operationManager->last_name, 'request_info' => $emp->first_name . ' ' . $emp->last_name . ' have requested for On-Duty (for ' . $request->purpose . ') from ' . ' ' . dateConvertFormtoDB($request->application_from_date) . ' to ' . dateConvertFormtoDB($request->application_to_date), 'status_info' => '']);
                }
            } catch (\Exception $ex) {
                return redirect(route('applyForOnDuty.index'))->with('error',  'Something went wrong!' . $ex->getMessage());
            }

            OnDuty::create($input);
            return redirect(route('applyForOnDuty.index'))->with('success', 'On Duty application sent successfully');
            // DB::commit();
        } catch (\Throwable $th) {
            // DB::rollBack();

            return redirect(route('applyForOnDuty.index'))->with('error',  'Something went wrong!' . $th->getMessage());
        }
    }
}
