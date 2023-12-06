<?php

namespace App\Http\Controllers\Leave;

use Carbon\Carbon;
use App\Model\Employee;
use App\Components\Common;
use Illuminate\Http\Request;
use App\Model\LeavePermission;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\LeaveRepository;
use App\Repositories\CommonRepository;
use App\Http\Requests\ApplyForPermissionRequest;

class ApplyForPermissionController extends Controller
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
        $results = LeavePermission::with(['employee', 'approveBy'])
            ->where('employee_id', decrypt(session('logged_session_data.employee_id')))
            ->orderBy('leave_permission_date', 'desc')
            ->paginate(10);

        return view('admin.leave.applyForPermission.index', ['results' => $results]);
    }

    public function create()
    {
        $getEmployeeInfo = $this->commonRepository->getEmployeeInfo(Auth::user()->user_id);

        $Year  = Carbon::now()->year;
        $Month = DATE('m');
        $takenpermissions = LeavePermission::whereMonth('leave_permission_date', '=', $Month)->whereYear('leave_permission_date', '=', $Year)
            ->where('employee_id', $getEmployeeInfo->employee_id)
            ->where('status', 1)->count();
        $appliedpermissions = LeavePermission::whereMonth('leave_permission_date', '=', $Month)->whereYear('leave_permission_date', '=', $Year)
            ->where('employee_id', $getEmployeeInfo->employee_id)->count();

        return view('admin.leave.applyForPermission.leave_permission_form', [
            'getEmployeeInfo' => $getEmployeeInfo,
            'takenPermissions' => $takenpermissions, 'appliedpermissions' => $appliedpermissions
        ]);
    }



    public function applyForTotalNumberOfPermissions(Request $request)
    {

        $permission_date = dateConvertFormtoDB($request->permission_date);
        $employee_id = $request->employee_id;
        $Year  = date("Y", strtotime($permission_date));
        $Month = (int)date("m", strtotime($permission_date));
        $checkpermissions = LeavePermission::whereMonth('leave_permission_date', '=', $Month)->whereYear('leave_permission_date', '=', $Year)
            ->where('employee_id', $employee_id)->where('status', 2)->count();

        return $checkpermissions;
    }

    public function store(ApplyForPermissionRequest $request)
    {

        $input                            = $request->all();
        $input['leave_permission_date']   = dateConvertFormtoDB($request->permission_date);
        $input['permission_duration']     = $request->permission_duration;
        $input['leave_permission_purpose'] = $request->purpose;
        $input['from_time']               = $request->from_time;
        $input['to_time']                 = $request->to_time;
        $input['created_at']      = date('Y-m-d H:i:s');
        $input['updated_at']      = date('Y-m-d H:i:s');
        $input['status'] = 1;

        $isemployeeIsManager = Employee::with('user')->Where('employee_id', $request->employee_id)->first();
        if (isset($isemployeeIsManager) && $isemployeeIsManager->user->role_id == 3) {
            $if_exists = LeavePermission::where('employee_id', $request->employee_id)->where('leave_permission_date', dateConvertFormtoDB($request->permission_date))->first();

            if ($if_exists) {
                return redirect('applyForPermission')->with('error', 'Request Already Exist');
            } 
            $emp = Employee::find($request->employee_id);
            $hod = Employee::where('employee_id', $emp->supervisor_id)->first();
            if ($hod->email) {
                $maildata = Common::mail('emails/mail', $hod->email, 'Permission Request Notification', ['head_name' => $hod->first_name . ' ' . $hod->last_name, 'request_info' => $emp->first_name . ' ' . $emp->last_name . ' have requested for Permission (for ' . $request->purpose . ')Application Date ' . ' ' . dateConvertFormtoDB($request->permission_date) . ' fromTime ' . ' ' . $request->from_time . ' toTime ' . $request->from_time, 'status_info' => '']);
            }
            $input['manager_status'] =2;
        }else{
            $if_exists = LeavePermission::where('employee_id', $request->employee_id)->where('leave_permission_date', dateConvertFormtoDB($request->permission_date))->first();

            if ($if_exists) {
                return redirect('applyForPermission')->with('error', 'Request Already Exist');
            } 
            $emp = Employee::find($request->employee_id);
            $hod = Employee::where('employee_id', $emp->supervisor_id)->first();
            $operationManager = Employee::where('employee_id', $emp->operation_manager_id)->first();
           
            if ($operationManager->email) {
                $maildata = Common::mail('emails/mail', $operationManager->email, 'Permission Request Notification', ['head_name' => $operationManager->first_name . ' ' . $operationManager->last_name, 'request_info' => $emp->first_name . ' ' . $emp->last_name . ' have requested for Permission (for ' . $request->purpose . ')Application Date ' . ' ' . dateConvertFormtoDB($request->permission_date) . ' fromTime ' . ' ' . $request->from_time . ' toTime ' . $request->from_time, 'status_info' => '']);
            }
        }
       

            try {
                LeavePermission::create($input);
                $bug = 0;
            } catch (\Exception $e) {
                $bug = 1;
            }


            if ($bug == 0) {
                return redirect('applyForPermission')->with('success', 'Permission Request successfully send.');
            } else {
                return redirect('applyForPermission')->with('error', 'Something error found !, Please try again.');
            }
        
    }
    public function permissionrequest()
    {
        $permissionresults = LeavePermission::where('status', 1)->paginate(10);

        return view('admin.leave.applyForPermission.permission_requests', ['permissionresults' => $permissionresults]);
    }
}
