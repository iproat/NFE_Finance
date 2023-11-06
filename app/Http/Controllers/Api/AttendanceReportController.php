<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Model\MsSql;
use App\Model\Employee;
use App\Model\LeaveType;
use Illuminate\Http\Request;
use App\Model\PrintHeadSetting;
use Barryvdh\DomPDF\Facade as PDF;
use App\Http\Controllers\Controller;
use App\Repositories\ApiAttendanceRepository;


class AttendanceReportController extends Controller
{
    protected $apiAttendanceRepository;
    protected $controller;

    public function __construct(ApiAttendanceRepository $apiAttendanceRepository, Controller $controller)
    {
        $this->apiAttendanceRepository = $apiAttendanceRepository;
        $this->controller = $controller;
    }



    public function myAttendanceReport(Request $request)
    {
        $emp_id = $request->employee_id;
        $employee = Employee::findOrFail($emp_id);
        $results      = [];
        $to_date = date('Y-m-d', strtotime($request->date .  '+1 days'));
        $results = MsSql::where('ID', $employee->finger_id)->whereBetween('datetime', [date('Y-m-d H:i:s', strtotime($request->date . ' 05:00:00')), date('Y-m-d H:i:s', strtotime($to_date . ' 08:00:00'))])->orderByDesc('datetime')->orderByDesc('primary_id')->get();
        if (!empty($results)) {
            return $this->success("Attendacne details received successfully", $results);
        } else {
            return $this->error();
        }
    }


    public function downloadMyAttendance(Request $request)
    {

        $emp_id = $request->employee_id;
        $from_date = dateConvertFormtoDB($request->from_date);
        $to_date =  dateConvertFormtoDB($request->to_date);
        $employeeInfo = Employee::with('department')->where('employee_id', $emp_id)->first();
        $printHead    = PrintHeadSetting::first();
        $results      = $this->apiAttendanceRepository->getEmployeeMonthlyAttendance($from_date, $to_date, $emp_id);
        if (isset($results['status'])) {
            return $results['error'];
        }
        $data         = [
            'results'         => $results,
            'form_date'       => dateConvertFormtoDB($request->from_date),
            'to_date'         => dateConvertFormtoDB($request->to_date),
            'printHead'       => $printHead,
            'employee_name'   => $employeeInfo->first_name . ' ' . $employeeInfo->last_name,
            'department_name' => $employeeInfo->department->department_name,
        ];

        $pdf = PDF::loadView('admin.attendance.report.pdf.mySummaryReportPdf', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download("my-attendance.pdf");
    }

    // public function attendanceSummaryReport(Request $request)
    // {
    //     if ($request->month) {
    //         $month = $request->month;
    //     } else {
    //         $month = date("Y-m");
    //     }

    //     $monthAndYear = explode('-', $month);
    //     $month_data   = $monthAndYear[1];
    //     $dateObj      = DateTime::createFromFormat('!m', $month_data);
    //     $monthName    = $dateObj->format('F');

    //     $monthToDate = findMonthToAllDate($month);
    //     $leaveType   = LeaveType::get();
    //     $result      = $this->attendanceRepository->findAttendanceSummaryReport($month);

    //     return view('admin.attendance.report.summaryReport', ['results' => $result, 'monthToDate' => $monthToDate, 'month' => $month, 'leaveTypes' => $leaveType, 'monthName' => $monthName]);
    // }

    // public function downloadAttendanceSummaryReport($month)
    // {
    //     $monthToDate = findMonthToAllDate($month);
    //     $leaveType   = LeaveType::get();
    //     $result      = $this->attendanceRepository->findAttendanceSummaryReport($month);

    //     $monthAndYear = explode('-', $month);
    //     $month_data   = $monthAndYear[1];
    //     $dateObj      = DateTime::createFromFormat('!m', $month_data);
    //     $monthName    = $dateObj->format('F');

    //     $data = [
    //         'results'     => $result,
    //         'month'       => $month,
    //         'monthToDate' => $monthToDate,
    //         'leaveTypes'  => $leaveType,
    //         'monthName'   => $monthName,
    //     ];
    //     $pdf = PDF::loadView('admin.attendance.report.pdf.attendanceSummaryReportPdf', $data);
    //     $pdf->setPaper('A4', 'landscape');
    //     return $pdf->download("attendance-summaryReport.pdf");
    // }
}
