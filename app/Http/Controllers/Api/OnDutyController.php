<?php

namespace App\Http\Controllers\Api;

use App\Model\OnDuty;
use App\Model\Employee;
use App\Components\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\CommonRepository;


class OnDutyController extends Controller
{
    protected $commonRepository;

    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }

    public function index(Request $request)
    {
        $results = OnDuty::with('employee')
            ->where('employee_id', $request->employee_id)
            ->select('on_duty.*', DB::raw('no_of_days As number_of_day'))
            ->orderBy('on_duty_id', 'desc')
            ->paginate(10);

        if ($results) {
            return response()->json([
                'message' => 'OnDuty Data Successfully Received',
                'data'    => $results,
                'status' => true,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No Data Found',
                'status' => false,
            ], 200);
        }
    }

    public function create()
    {
        return response()->json([
            'data'    => [],
            'status' => true,
        ], 200);
    }

    public function store(Request $request)
    {
        $fromDate = dateConvertFormtoDB($request->application_from_date);
        $toDate = dateConvertFormtoDB($request->application_to_date);

        if ($fromDate > $toDate) {
            return response()->json([
                'message' => "The from date must be earlier than the application to date.",
                'status' => false,
            ], 200);
        }
        $employee = Employee::where('employee_id', $request->employee_id)->first();
        $input = $request->all();

        $input['application_from_date'] = $fromDate;
        $input['application_to_date'] = $toDate;
        $input['no_of_days'] =  $request->number_of_day;
        $input['purpose'] = $request->purpose;
        $input['application_date'] = date('Y-m-d');
        $input['branch_id'] = $employee->branch_id;


        if ($employee->supervisor_id == '') {
            return response()->json([
                'message' => 'Department Head Data Not Given',
                'status' => false,
            ], 200);
        } elseif ($employee->operation_manager_id == '') {
            return response()->json([
                'message' => 'Operation Manager Data Not Given',
                'status' => false,
            ], 200);
        } elseif ($request->application_from_date == '') {
            return response()->json([
                'message' => 'Application From Date Not Given',
                'status' => false,
            ], 200);
        } elseif ($request->application_to_date == '') {
            return response()->json([
                'message' => 'Application To Date Not Given',
                'status' => false,
            ], 200);
        } elseif ($request->number_of_day == '') {
            return response()->json([
                'message' => 'Number Of Day Not Given',
                'status' => false,
            ], 200);
        } elseif ($request->purpose == '') {
            return response()->json([
                'message' => 'Purpose Not Given',
                'status' => false,
            ], 200);
        } else {
            try {
                // info(!23);
                // DB::beginTransaction();
                $checkOD = OnDuty::where('application_from_date', '>=', $input['application_from_date'])->where('application_to_date', '<=', $input['application_to_date'])
                    ->where('employee_id', $employee->employee_id)->where('status', '!=', 3)->where('manager_status', '!=', 3)->first();

                if (!$checkOD) {
                    $data = OnDuty::create($input);
                    // info($data);
                    $hod = Employee::where('employee_id', $employee->supervisor_id)->first();
                    $manager = Employee::where('employee_id', $employee->manager_id)->first();
                    if ($hod != '') {
                        if ($hod->email) {
                            $maildata = Common::mail('emails/mail', $hod->email, 'On Duty Request Notification', ['head_name' => $hod->first_name . ' ' . $hod->last_name, 'request_info' => $employee->first_name . ' ' . $employee->last_name . ', have requested for on duty (Purpose: ' . $request->purpose . ') from ' . ' ' . dateConvertFormtoDB($request->application_from_date) . ' to ' . dateConvertFormtoDB($request->application_to_date), 'status_info' => '']);
                        }
                    }
                    if ($manager != '') {
                        if ($manager->email) {
                            $maildata = Common::mail('emails/mail', $manager->email, 'On Duty Request Notification', ['head_name' => $manager->first_name . ' ' . $manager->last_name, 'request_info' => $employee->first_name . ' ' . $employee->last_name . ', have requested for on duty (Purpose: ' . $request->purpose . ') from ' . ' ' . dateConvertFormtoDB($request->application_from_date) . ' to ' . dateConvertFormtoDB($request->application_to_date), 'status_info' => '']);
                        }
                    }

                    if ($data) {
                        return response()->json([

                            'message' => 'On Duty application successfully send.',
                            'status' => true,
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'message' => 'On Duty application already exists.',
                        'status' => false,
                    ], 200);
                }
                // DB::commit();
            } catch (\Exception $e) {
                info($e->getMessage());
                // DB::rollBack();
                return response()->json([
                    'message' => $e->getMessage(),
                    'status' => false,

                ], 200);
            }
        }
    }
}
