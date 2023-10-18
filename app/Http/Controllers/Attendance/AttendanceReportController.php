<?php

namespace App\Http\Controllers\Attendance;

use App\Exports\MonthlyAttendanceReportExport;
use App\Exports\SummaryAttendanceReportExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\View\EmployeeAttendaceController;
use App\Lib\Enumerations\UserStatus;
use App\Model\Department;
use App\Model\Employee;
use App\Model\LeaveType;
use App\Model\ManualAttendance;
use App\Model\MsSql;
use App\Model\PrintHeadSetting;
use App\Repositories\AttendanceRepository;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{

    protected $attendanceRepository;
    protected $employeeAttendaceController;

    public function __construct(AttendanceRepository $attendanceRepository, EmployeeAttendaceController $employeeAttendaceController)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->employeeAttendaceController = $employeeAttendaceController;
    }

    public function dailyAttendance(Request $request)
    {
        \set_time_limit(0);
        $departmentList = Department::get();

        $results = [];

        if ($_POST) {

            $results = $this->attendanceRepository->getEmployeeDailyAttendance($request->date, $request->department_id, $request->attendance_status);
           
            // if (decrypt(session('logged_session_data.role_id')) == 1 || decrypt(session('logged_session_data.role_id')) == 2) {
            //     $results = $dailyAttendance;
            // }
            //  else {

            //     $departmentName = Department::find(decrypt(session('logged_session_data.department_id')));

            //     $array = isset($dailyAttendance[$departmentName->department_name]) ? $dailyAttendance[$departmentName->department_name] : [];

            //     foreach ($array as $key => $value) {

            //         if (decrypt(session('logged_session_data.finger_id')) == $value->finger_print_id) {
            //             $results['Production'][] = $value;
            //             break;
            //         }
            //     }

            //     foreach ($array as $key => $value) {

            //         if (decrypt(session('logged_session_data.employee_id')) == $value->supervisor_id) {
            //             $results['Production'][] = $value;
            //         }
            //     }
            // }
        }
        return view('admin.attendance.report.dailyAttendance', ['results' => json_decode(json_encode($results)), 'departmentList' => $departmentList, 'date' => $request->date, 'department_id' => $request->department_id, 'attendance_status' => $request->attendance_status]);
    }

    public function monthlyAttendance(Request $request)
    {
        set_time_limit(0);

        $employeeList = Employee::where('supervisor_id', decrypt(session('logged_session_data.employee_id')))->orwhere('employee_id', decrypt(session('logged_session_data.employee_id')))->get();

        if (decrypt(session('logged_session_data.role_id')) == 1 || decrypt(session('logged_session_data.role_id')) == 2) {
            $employeeList = Employee::get();
        }

        $results = [];
        if ($_POST) {
            $results = $this->attendanceRepository->getEmployeeMonthlyAttendance(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), $request->employee_id);
        }

        return view('admin.attendance.report.monthlyAttendance', ['results' => $results, 'employeeList' => $employeeList, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'employee_id' => $request->employee_id]);
    }

    public function myAttendanceReport(Request $request)
    {
        set_time_limit(0);

        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->where('employee_id', decrypt(session('logged_session_data.employee_id')))->get();
        $results = [];
        if ($_POST) {
            $results = $this->attendanceRepository->getEmployeeMonthlyAttendance(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), decrypt(session('logged_session_data.employee_id')));
        } else {
            $results = $this->attendanceRepository->getEmployeeMonthlyAttendance(date('Y-m-01'), date("Y-m-t", strtotime(date('Y-m-01'))), decrypt(session('logged_session_data.employee_id')));
        }

        return view('admin.attendance.report.mySummaryReport', ['results' => $results, 'employeeList' => $employeeList, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'employee_id' => $request->employee_id]);
    }

    public function downloadDailyAttendance(Request $request)
    {
        set_time_limit(0);

        $printHead = PrintHeadSetting::first();
        $departmentList = Department::where('department_id', $request->department_id)->first();

        $results = $this->attendanceRepository->getEmployeeDailyAttendance($request->date, $request->department_id, $request->attendance_status);

        $data = [
            'results' => $results,
            'date' => $request->date,
            'printHead' => $printHead,
            'department_id' => $departmentList->department_id,
            'department_name' => $departmentList->department_name,

        ];
        $pdf = PDF::loadView('admin.attendance.report.pdf.dailyAttendancePdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $pageName = "daily-attendance.pdf";
        return $pdf->download($pageName);
    }

    public function downloadMonthlyAttendance(Request $request)
    {
        set_time_limit(0);

        $employeeInfo = Employee::with('department')->where('employee_id', $request->employee_id)->first();
        $printHead = PrintHeadSetting::first();
        $results = $this->attendanceRepository->getEmployeeMonthlyAttendance(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), $request->employee_id);

        $data = [
            'results' => $results,
            'form_date' => dateConvertFormtoDB($request->from_date),
            'to_date' => dateConvertFormtoDB($request->to_date),
            'printHead' => $printHead,
            'employee_name' => $employeeInfo->first_name . ' ' . $employeeInfo->last_name,
            'department_name' => $employeeInfo->department->department_name,
        ];

        $pdf = PDF::loadView('admin.attendance.report.pdf.monthlyAttendancePdf', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download("monthly-attendance.pdf");
    }

    public function downloadMyAttendance(Request $request)
    {
        set_time_limit(0);

        $employeeInfo = Employee::with('department')->where('employee_id', $request->employee_id)->first();
        $printHead = PrintHeadSetting::first();
        $results = $this->attendanceRepository->getEmployeeMonthlyAttendance(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), $request->employee_id);
        $data = [
            'results' => $results,
            'form_date' => dateConvertFormtoDB($request->from_date),
            'to_date' => dateConvertFormtoDB($request->to_date),
            'printHead' => $printHead,
            'employee_name' => $employeeInfo->first_name . ' ' . $employeeInfo->last_name,
            'department_name' => $employeeInfo->department->department_name,
        ];

        $pdf = PDF::loadView('admin.attendance.report.pdf.mySummaryReportPdf', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download("my-attendance.pdf");
    }

    public function attendanceSummaryReport(Request $request)
    {
        set_time_limit(0);
        if ($request->from_date && $request->to_date) {
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            // dd(dateConvertFormtoDB($from_date), dateConvertFormtoDB($to_date));
        } else {
            $from_date = date("01/m/Y");
            $to_date = date("t/m/Y");
        }

        $month = date('Y-m', strtotime(dateConvertFormtoDB($from_date)));
        $monthAndYear = explode('-', $month);
        $month_data = $monthAndYear[1];
        $dateObj = DateTime::createFromFormat('!m', $month_data);
        $monthName = $dateObj->format('F');

        $monthToDate = findFromDateToDateToAllDate(dateConvertFormtoDB($from_date), dateConvertFormtoDB($to_date));
        $leaveType = LeaveType::get();
        $result = $this->attendanceRepository->findAttendanceSummaryReport($month, dateConvertFormtoDB($from_date), dateConvertFormtoDB($to_date));

        return view('admin.attendance.report.summaryReport', ['results' => $result, 'monthToDate' => $monthToDate, 'month' => $month, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'leaveTypes' => $leaveType, 'monthName' => $monthName]);
    }

    public function downloadAttendanceSummaryReport($from_date, $to_date)
    {
        $printHead = PrintHeadSetting::first();
        $month = date('Y-m', strtotime($from_date));
        $monthToDate = findMonthToAllDate($month);
        $leaveType = LeaveType::get();
        $result = $this->attendanceRepository->findAttendanceSummaryReport($month, $from_date, $to_date);

        $monthAndYear = explode('-', $month);
        $month_data = $monthAndYear[1];
        $dateObj = DateTime::createFromFormat('!m', $month_data);
        $monthName = $dateObj->format('F');

        $data = [
            'results' => $result,
            'month' => $month,
            'printHead' => $printHead,
            'monthToDate' => $monthToDate,
            'leaveTypes' => $leaveType,
            'monthName' => $monthName,
        ];
        $pdf = PDF::loadView('admin.attendance.report.pdf.attendanceSummaryReportPdf', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download("attendance-summaryReport.pdf");
    }

    public function monthlyExcel(Request $request)
    {
        \set_time_limit(0);

        $employeeList = Employee::get();
        $employeeInfo = Employee::with('department')->where('employee_id', $request->employee_id)->first();
        $printHead = PrintHeadSetting::first();
        $results = [];

        if ($request->from_date && $request->to_date && $request->employee_id) {
            $results = $this->attendanceRepository->getEmployeeMonthlyAttendance(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), $request->employee_id);
        }

        $excel = new MonthlyAttendanceReportExport('admin.attendance.report.monthlyAttendancePagination', [
            'printHead' => $printHead, 'employeeInfo' => $employeeInfo, 'results' => $results, 'employeeList' => $employeeList,
            'from_date' => $request->from_date, 'to_date' => $request->to_date, 'employee_id' => $request->employee_id,
            'employee_name' => $employeeInfo->first_name . ' ' . $employeeInfo->last_name,
            'department_name' => $employeeInfo->department->department_name,
        ]);

        $excelFile = Excel::download($excel, 'monthlyReport.xlsx');

        return $excelFile;
    }
    public function summaryExcel(Request $request)
    {
        \set_time_limit(0);

        $monthToDate = findMonthToAllDate($request->month);
        $leaveType = LeaveType::get();
        $start_date = $request->month . '-01';
        $end_date = date("Y-m-t", strtotime($start_date));
        $result = $this->attendanceRepository->findAttendanceSummaryReport($request->month, $start_date, $end_date);
        $employeeInfo = Employee::with('department')->where('employee_id', $request->employee_id)->first();
        $monthAndYear = explode('-', $request->month);
        $month_data = $monthAndYear[1];
        $dateObj = DateTime::createFromFormat('!m', $month_data);
        $monthName = $dateObj->format('F');

        $data = [
            'results' => $result,
            'month' => $request->month,
            'monthToDate' => $monthToDate,
            'leaveTypes' => $leaveType,
            'monthName' => $monthName,
        ];

        $excel = new SummaryAttendanceReportExport('admin.attendance.report.summaryReportPagination', $data);

        $excelFile = Excel::download($excel, 'summaryReport' . date('Ym', strtotime($request->month)) . date('His') . '.xlsx');

        return $excelFile;
    }

    public function attendanceRecord(Request $request)
    {
        set_time_limit(0);
        $results = [];
        $ms_sql = MsSql::with('employee:finger_id,first_name,last_name')->whereDate('datetime', date('Y-m-d'))->orderBy('ms_sql.datetime')->get()->toArray();
        $manual_attendance = ManualAttendance::with('employee:finger_id,first_name,last_name')->whereDate('datetime', date('Y-m-d'))->orderBy('manual_attendance.datetime')->get()->toArray();
        $results = (object) array_merge($ms_sql, $manual_attendance);

        if ($_POST) {

            // $from_date = dateConvertFormtoDB($request->from_date) . ' 00:00:00';
            // $to_date = dateConvertFormtoDB($request->to_date) . ' 23:59:59';
            $from_date = dateConvertFormtoDB($request->from_date);
            $to_date = dateConvertFormtoDB($request->to_date);

            if ($request->device_name != null) {
                $request->device_name = $request->device_name == 'N/A' ? null : $request->device_name;
                $ms_sql = MsSql::where('device_name', $request->device_name)->whereDate('datetime', '>=', $from_date)->whereDate('datetime', '<=', $to_date)
                    ->with('employee:finger_id,first_name,last_name')->get()->toArray();
                $manual_attendance = ManualAttendance::where('device_name', $request->device_name)->whereDate('datetime', '>=', $from_date)->whereDate('datetime', '<=', $to_date)
                    ->with('employee:finger_id,first_name,last_name')->get()->toArray();
                $results = (object) array_merge($ms_sql, $manual_attendance);
            } elseif ($request->from_date && $request->to_date) {
                $ms_sql = MsSql::whereDate('datetime', '>=', $from_date)->whereDate('datetime', '<=', $to_date)
                    ->with('employee:finger_id,first_name,last_name')->get()->toArray();
                $manual_attendance = ManualAttendance::whereDate('datetime', '>=', $from_date)->whereDate('datetime', '<=', $to_date)
                    ->with('employee:finger_id,first_name,last_name')->get()->toArray();
                $results = (object) array_merge($ms_sql, $manual_attendance);
            }
        }

        return \view('admin.attendance.report.attendanceRecord', ['results' => $results, 'device_name' => $request->device_name, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'employee_id ' => $request->employee_id]);
    }

    public function report(Request $request)
    {
        return view('admin.attendance.calculateAttendance.index');
    }
    public function calculateReport(Request $request)
    {

        $dates = CarbonPeriod::create(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date))->toArray();

        $this->employeeAttendaceController->attendance(null, false, null, $dates);

        return redirect()->back()->with('success', 'reports generated successfully');
    }
}
