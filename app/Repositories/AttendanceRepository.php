<?php

namespace App\Repositories;

use App\Lib\Enumerations\LeaveStatus;
use App\Lib\Enumerations\UserStatus;
use App\Model\ApproveOverTime;
use App\Model\CompOff;
use App\Model\Employee;
use App\Model\Incentive;
use App\Model\LeaveApplication;
use App\Model\WeeklyHoliday;
use Illuminate\Support\Facades\DB;

class AttendanceRepository
{

    public function getEmployeeDailyAttendance($date = false, $department_id, $attendance_status)
    {
        if ($date) {
            $data = dateConvertFormtoDB($date);
        } else {
            $data = date("Y-m-d");
        }
        $employee_id = decrypt(session('logged_session_data.employee_id'));
        $queryResults = null;
        
        if (decrypt(session('logged_session_data.role_id')) == 3) {
            $queryResults = DB::select("call `SP_ManagerDailyAttendance`('" . $data . "', '" . $department_id . "','" . $attendance_status . "','" . $employee_id . "')");
        } else {
            $queryResults = DB::select("call `SP_DepartmentDailyAttendance`('" . $data . "', '" . $department_id . "','" . $attendance_status . "')");
        }
        // $queryResults = DB::select("call `SP_DepartmentDailyAttendance`('" . $data . "', '" . $department_id . "','" . $attendance_status . "')");
       
        $results = [];

        foreach ($queryResults as $value) {

            $tempArr = [];
            // $approvedOvertime = null;
            // $compOff = null;
            // $incentive = null;

            // if ($value->approve_over_time_id != null) {
            //     $approvedOvertime = ApproveOverTime::find($value->approve_over_time_id);
            // }
            // if ($value->comp_off_details_id != null) {
            //     $compOff = CompOff::find($value->comp_off_details_id);
            // }
            // if ($value->incentive_details_id != null) {
            //     $incentive = Incentive::find($value->incentive_details_id);
            // }

            $tempArr = $value;
            // $tempArr->overtime_approval = $approvedOvertime;
            // $tempArr->comp_off = $compOff;
            // $tempArr->incentive = $incentive;

            $results[$value->department_name][] = $tempArr;
        }

        return $results;
    }
    public function findAttendanceMusterReport($start_date, $end_date, $employee_id = '', $department_id = '', $branch_id = '')
    {
        $data = findMonthFromToDate($start_date, $end_date);

        $qry = '1 ';

        if ($employee_id != '') {
            $qry .= ' AND employee.employee_id=' . $employee_id;
        }
        if ($department_id != '') {
            $qry .= ' AND employee.department_id=' . $department_id;
        }
        if ($branch_id != '') {
            $qry .= ' AND employee.branch_id=' . $branch_id;
        }
        if (decrypt(session('logged_session_data.role_id')) == 3) {
            $hasSupervisorWiseEmployee = Employee::select('employee_id')->where('operation_manager_id', decrypt(session('logged_session_data.employee_id')))->get()->toArray();
            $employees = Employee::select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'designation_name', 'department_name', 'branch_name', 'finger_id', 'employee_id')
            ->join('designation', 'designation.designation_id', 'employee.designation_id')
            ->join('department', 'department.department_id', 'employee.department_id')
            ->join('branch', 'branch.branch_id', 'employee.branch_id')->orderBy('branch.branch_name', 'ASC')->whereRaw($qry)
            ->whereIn('employee_id', array_values($hasSupervisorWiseEmployee))
            ->where('status', UserStatus::$ACTIVE)->get();
        } else {
        
            $employees = Employee::select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'designation_name', 'department_name', 'branch_name', 'finger_id', 'employee_id')
            ->join('designation', 'designation.designation_id', 'employee.designation_id')
            ->join('department', 'department.department_id', 'employee.department_id')
            ->join('branch', 'branch.branch_id', 'employee.branch_id')->orderBy('branch.branch_name', 'ASC')->whereRaw($qry)
            ->where('status', UserStatus::$ACTIVE)->get();
        }


        $attendance = DB::table('view_employee_in_out_data')->whereBetween('date', [$start_date, $end_date])->get();

        $govtHolidays = DB::select(DB::raw('call SP_getHoliday("' . $start_date . '","' . $end_date . '")'));

        $dataFormat = [];
        $tempArray = [];

        foreach ($employees as $employee) {

            foreach ($data as $key => $value) {

                $tempArray['employee_id'] = $employee->employee_id;
                $tempArray['finger_id'] = $employee->finger_id;
                $tempArray['fullName'] = $employee->fullName;
                $tempArray['designation_name'] = $employee->designation_name;
                $tempArray['department_name'] = $employee->department_name;
                $tempArray['branch_name'] = $employee->branch_name;
                $tempArray['date'] = $value['date'];
                $tempArray['day'] = $value['day'];
                $tempArray['day_name'] = $value['day_name'];

                $hasAttendance = $this->hasEmployeeMusterAttendance($attendance, $employee->finger_id, $value['date']);

                $ifPublicHoliday = $this->ifPublicHoliday($govtHolidays, $value['date']);

                if ($ifPublicHoliday) {
                    $tempArray['attendance_status'] = 'holiday';
                    $tempArray['shift_name'] = $hasAttendance['shift_name'];
                    $tempArray['in_time'] = $hasAttendance['in_time'];
                    $tempArray['out_time'] = $hasAttendance['out_time'];
                    $tempArray['working_time'] = $hasAttendance['working_time'];
                    $tempArray['over_time'] = $hasAttendance['over_time'];
                    // $tempArray['over_time_status'] = $hasAttendance['over_time_status'];
                    $tempArray['employee_attendance_id'] = $hasAttendance['employee_attendance_id'];
                } elseif ($hasAttendance) {
                    $tempArray['attendance_status'] = 'present';
                    $tempArray['shift_name'] = $hasAttendance['shift_name'];
                    $tempArray['in_time'] = $hasAttendance['in_time'];
                    $tempArray['out_time'] = $hasAttendance['out_time'];
                    $tempArray['working_time'] = $hasAttendance['working_time'];
                    $tempArray['over_time'] = $hasAttendance['over_time'];
                    // $tempArray['over_time_status'] = $hasAttendance['over_time_status'];
                    $tempArray['employee_attendance_id'] = $hasAttendance['employee_attendance_id'];
                } else {

                    $tempArray['attendance_status'] = 'absence';
                    $tempArray['shift_name'] = '';
                    $tempArray['in_time'] = '';
                    $tempArray['out_time'] = '';
                    $tempArray['over_time'] = '';
                    $tempArray['working_time'] = '';
                    // $tempArray['over_time_status'] = '';
                    $tempArray['employee_attendance_id'] = '';
                }

                $dataFormat[$employee->finger_id][] = $tempArray;
            }
        }

        return $dataFormat;
    }
    public function findAttendanceMusterReportExcelDump($start_date, $end_date, $employee_id, $department_id, $branch_id)
    {
        $data = findMonthFromToDate($start_date, $end_date);

        $qry = '1 ';

        if ($employee_id != '') {
            $qry .= ' AND employee.employee_id=' . $employee_id;
        }
        if ($department_id != '') {
            $qry .= ' AND employee.department_id=' . $department_id;
        }
        if ($branch_id != '') {
            $qry .= ' AND employee.branch_id=' . $branch_id;
        }

        $employees = Employee::select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'designation_name', 'department_name', 'branch_name', 'finger_id', 'employee_id')
            ->join('designation', 'designation.designation_id', 'employee.designation_id')
            ->join('department', 'department.department_id', 'employee.department_id')
            ->join('branch', 'branch.branch_id', 'employee.branch_id')->orderBy('branch.branch_name', 'ASC')->whereRaw($qry)
            ->where('status', UserStatus::$ACTIVE)->get();

        $attendance = DB::table('view_employee_in_out_data')->whereBetween('date', [$start_date, $end_date])->get();

        $govtHolidays = DB::select(DB::raw('call SP_getHoliday("' . $start_date . '","' . $end_date . '")'));
        $weeklyHolidays = DB::select(DB::raw('call SP_getWeeklyHoliday("' . $start_date . '","' . $end_date . '")'));

        $dataFormat = [];
        $tempArray = [];

        foreach ($employees as $employee) {

            $weeklyHolidaysDates = WeeklyHoliday::where('employee_id', $employee->employee_id)->where('month', date('Y-m', strtotime($start_date)))->first();

            foreach ($data as $key => $value) {

                $tempArray['employee_id'] = $employee->employee_id;
                $tempArray['finger_id'] = $employee->finger_id;
                $tempArray['fullName'] = $employee->fullName;
                $tempArray['designation_name'] = $employee->designation_name;
                $tempArray['department_name'] = $employee->department_name;
                $tempArray['branch_name'] = $employee->branch_name;
                $tempArray['date'] = $value['date'];
                $tempArray['day'] = $value['day'];
                $tempArray['day_name'] = $value['day_name'];

                $hasAttendance = $this->hasEmployeeMusterAttendance($attendance, $employee->finger_id, $value['date']);

                if ($hasAttendance) {

                    $ifHoliday = $this->ifHoliday($govtHolidays, $value['date'], $employee->employee_id, $weeklyHolidays, $weeklyHolidaysDates);
                    // dump($ifHoliday);
                    if ($ifHoliday['govt_holiday'] == true) {
                        $tempArray['attendance_status'] = 'present';
                        $tempArray['shift_name'] = $hasAttendance['shift_name'];
                        $tempArray['in_time'] = $hasAttendance['in_time'];
                        $tempArray['out_time'] = $hasAttendance['out_time'];
                        $tempArray['working_time'] = $hasAttendance['working_time'];
                        $tempArray['over_time'] = $hasAttendance['over_time'];
                        $tempArray['employee_attendance_id'] = $hasAttendance['employee_attendance_id'];
                    } else {
                        $tempArray['attendance_status'] = 'holiday';
                        $tempArray['shift_name'] = $hasAttendance['shift_name'];
                        $tempArray['in_time'] = $hasAttendance['in_time'];
                        $tempArray['out_time'] = $hasAttendance['out_time'];
                        $tempArray['working_time'] = $hasAttendance['working_time'];
                        $tempArray['over_time'] = $hasAttendance['over_time'];
                        $tempArray['employee_attendance_id'] = $hasAttendance['employee_attendance_id'];
                    }
                } else {

                    $tempArray['attendance_status'] = 'absence';
                    $tempArray['shift_name'] = '';
                    $tempArray['in_time'] = '';
                    $tempArray['out_time'] = '';
                    $tempArray['over_time'] = '';
                    $tempArray['working_time'] = '';
                    $tempArray['employee_attendance_id'] = '';
                }

                $dataFormat[$employee->finger_id][] = $tempArray;
            }
        }

        $excelFormat = [];
        $days = [];
        $sl = 1;
        $dataset = [];

        $sl = 0;
        $emptyArr = ['', '', '', '', ''];

        foreach ($dataFormat as $key => $data) {
            $sl++;

            $shiftInfo = ['SHIFT NAME'];
            $inTimeInfo = ['IN TIME'];
            $outTimeInfo = ['OUT TIME'];
            $workingTimeInfo = ['WORKING TIME'];
            $overTimeInfo = ['OVER TIME'];

            for ($i = 0; $i < count($data); $i++) {
                $employeeData = [$sl, $data[0]['branch_name'], $data[0]['finger_id'], $data[0]['fullName'], $data[0]['department_name']];
                $shiftInfo[] = $data[$i]['shift_name'] != null ? $data[$i]['shift_name'] : 'NA';
                $inTimeInfo[] = $data[$i]['in_time'] != null ? date('H:i', strtotime($data[$i]['in_time'])) : '00:00';
                $outTimeInfo[] = $data[$i]['out_time'] != null ? date('H:i', strtotime($data[$i]['out_time'])) : '00:00';
                $workingTimeInfo[] = $data[$i]['working_time'] != null ? date('H:i', strtotime($data[$i]['working_time'])) : '00:00';
                $overTimeInfo[] = $data[$i]['over_time'] != null ? date('H:i', strtotime($data[$i]['over_time'])) : '00:00';
            }

            $excelFormat[] = array_merge($employeeData, $shiftInfo);
            $excelFormat[] = array_merge($emptyArr, $inTimeInfo);
            $excelFormat[] = array_merge($emptyArr, $outTimeInfo);
            $excelFormat[] = array_merge($emptyArr, $workingTimeInfo);
            $excelFormat[] = array_merge($emptyArr, $overTimeInfo);
        }
        // dd($excelFormat);
        return $excelFormat;
    }
    public function hasEmployeeMusterAttendance($attendance, $finger_print_id, $date)
    {
        $dataFormat = [];
        $dataFormat['in_time'] = '';
        $dataFormat['out_time'] = '';
        $dataFormat['over_time'] = '';
        $dataFormat['working_time'] = '';
        // $dataFormat['over_time_status'] = '';
        $dataFormat['shift_name'] = '';
        $dataFormat['employee_attendance_id'] = '';

        foreach ($attendance as $key => $val) {
            // dd($val);
            if (($val->finger_print_id == $finger_print_id && $val->date == $date && $val->in_time != null)) {
                $dataFormat['shift_name'] = $val->shift_name;
                $dataFormat['in_time'] = $val->in_time;
                $dataFormat['out_time'] = $val->out_time;
                $dataFormat['over_time'] = $val->over_time;
                $dataFormat['working_time'] = $val->working_time;
                // $dataFormat['over_time_status'] = $val->over_time_status;
                $dataFormat['employee_attendance_id'] = $val->employee_attendance_id;
                return $dataFormat;
            }
        }
        return $dataFormat;
    }
    public function ifPublicHoliday($govtHolidays, $date)
    {
        $govt_holidays = [];

        foreach ($govtHolidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $govt_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($govt_holidays as $val) {
            if ($val == $date) {
                return true;
            }
        }
        return false;
    }

    public function getEmployeeMonthlyAttendance($from_date, $to_date, $employee_id)
    {
        $monthlyAttendanceData = DB::select("CALL `SP_monthlyAttendance`('" . $employee_id . "','" . $from_date . "','" . $to_date . "')");
        $workingDates = $this->number_of_working_days_date($from_date, $to_date, $employee_id);
        $employeeLeaveRecords = $this->getEmployeeLeaveRecord($from_date, $to_date, $employee_id);
        $employeeHolidayRecords = $this->getEmployeeHolidayRecord($from_date, $to_date, $employee_id);

        $dataFormat = [];
        $tempArray = [];
        $present = null;

        // dd($workingDates);
        if ($workingDates && $monthlyAttendanceData) {
            foreach ($workingDates as $data) {
                $flag = 0;
                foreach ($monthlyAttendanceData as $value) {
                    if ($data == $value->date && ($value->in_time != null || $value->out_time != null)) {
                        $flag = 1;
                        break;
                    }
                }
                $tempArray['total_present'] = null;
                if ($flag == 0) {
                    $tempArray['employee_id'] = $value->employee_id;
                    $tempArray['fullName'] = $value->fullName;
                    $tempArray['department_name'] = $value->department_name;
                    $tempArray['finger_print_id'] = $value->finger_print_id;
                    $tempArray['date'] = $data;
                    $tempArray['working_time'] = '';
                    $tempArray['in_time'] = '';
                    $tempArray['out_time'] = '';
                    $tempArray['lateCountTime'] = '';
                    $tempArray['ifLate'] = '';
                    $tempArray['totalLateTime'] = '';
                    $tempArray['workingHour'] = '';
                    $tempArray['total_present'] = $present;
                    if (in_array($data, $employeeLeaveRecords)) {
                        $tempArray['action'] = 'Leave';
                    } elseif (in_array($data, $employeeHolidayRecords)) {
                        $tempArray['action'] = 'Holiday';
                    } else {
                        $tempArray['action'] = 'Absence';
                    }
                    $dataFormat[] = $tempArray;
                } else {
                    $tempArray['total_present'] = $present += 1;
                    $tempArray['employee_id'] = $value->employee_id;
                    $tempArray['fullName'] = $value->fullName;
                    $tempArray['department_name'] = $value->department_name;
                    $tempArray['finger_print_id'] = $value->finger_print_id;
                    $tempArray['date'] = $value->date;
                    $tempArray['working_time'] = $value->working_time;
                    $tempArray['in_time'] = $value->in_time;
                    $tempArray['out_time'] = $value->out_time;
                    $tempArray['lateCountTime'] = $value->lateCountTime;
                    $tempArray['ifLate'] = $value->ifLate;
                    $tempArray['totalLateTime'] = $value->totalLateTime;
                    $tempArray['workingHour'] = $value->workingHour;
                    $tempArray['action'] = 'Present';
                    $dataFormat[] = $tempArray;
                }
            }
        }
        // dd($dataFormat);
        return $dataFormat;
    }

    public function number_of_working_days_date($from_date, $to_date, $employee_id)
    {
        $holidays = DB::select(DB::raw('call SP_getHoliday("' . $from_date . '","' . $to_date . '")'));
        $public_holidays = [];
        foreach ($holidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $public_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        // $weeklyHolidays     = DB::select(DB::raw('call SP_getWeeklyHoliday()'));
        // $weeklyHolidayArray = [];
        // foreach ($weeklyHolidays as $weeklyHoliday) {
        //     $weeklyHolidayArray[] = $weeklyHoliday->day_name;
        // }

        $weeklyHolidayArray = WeeklyHoliday::select('day_name')
            ->where('employee_id', $employee_id)
            ->where('month', date('Y-m', strtotime($from_date)))
            ->orWhere('month', date('Y-m', strtotime($to_date)))
            ->first();

        $target = strtotime($from_date);
        $workingDate = [];

        while ($target <= strtotime(date("Y-m-d", strtotime($to_date)))) {

            //get weekly  holiday name
            $timestamp = strtotime(date('Y-m-d', $target));
            $dayName = date("l", $timestamp);

            // if (!in_array(date('Y-m-d', $target), $public_holidays) && !in_array($dayName, $weeklyHolidayArray->toArray())) {
            //     array_push($workingDate, date('Y-m-d', $target));
            // }

            // if (!in_array(date('Y-m-d', $target), $public_holidays)) {
            //     array_push($workingDate, date('Y-m-d', $target));
            // }

            \array_push($workingDate, date('Y-m-d', $target));

            if (date('Y-m-d') <= date('Y-m-d', $target)) {
                break;
            }
            $target += (60 * 60 * 24);
        }
        return $workingDate;
    }

    public function getEmployeeLeaveRecord($from_date, $to_date, $employee_id)
    {
        $queryResult = LeaveApplication::select('application_from_date', 'application_to_date')
            ->where('status', LeaveStatus::$APPROVE)
            ->where('application_from_date', '>=', $from_date)
            ->where('application_to_date', '<=', $to_date)
            ->where('employee_id', $employee_id)
            ->get();
        $leaveRecord = [];
        foreach ($queryResult as $value) {
            $start_date = $value->application_from_date;
            $end_date = $value->application_to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $leaveRecord[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }
        return $leaveRecord;
    }

    public function getEmployeeHolidayRecord($from_date, $to_date, $employee_id)
    {
        $queryResult = WeeklyHoliday::select('weekoff_days')
            ->where('employee_id', $employee_id)
            ->whereBetween('month', [date('Y-m', strtotime($from_date)), date('Y-m', strtotime($to_date))])
            ->first();

        $holidayRecord = [];
        if ($queryResult) {
            foreach (\json_decode($queryResult['weekoff_days']) as $value) {
                $holidayRecord[] = $value;
            }
        }
        return $holidayRecord;
    }

    public function findAttendanceSummaryReport($month, $start_date, $end_date)
    {
        $data = findFromDateToDateToAllDate($start_date, $end_date);

        $attendance = DB::table('view_employee_in_out_data')->select('finger_print_id', 'date', 'in_time', 'shift_name', 'inout_status', 'out_time', 'working_time')->whereBetween('date', [$start_date, $end_date])->get();
        $regularEmployeeIds = DB::table('view_employee_in_out_data')->select('finger_print_id')->whereBetween('date', [$start_date, $end_date])->groupBy('finger_print_id')->pluck('finger_print_id')->toArray();
        if (decrypt(session('logged_session_data.role_id')) == 3) {
            $hasSupervisorWiseEmployee = Employee::select('employee_id')->where('operation_manager_id', decrypt(session('logged_session_data.employee_id')))->get()->toArray();
            $employees = Employee::select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'employee.updated_at', 'gender', 'status', 'department_name', 'branch_name', 'designation_name', 'finger_id', 'employee_id')
            ->join('designation', 'designation.designation_id', 'employee.designation_id')
            ->join('department', 'department.department_id', 'employee.department_id')
            ->join('branch', 'branch.branch_id', 'employee.branch_id')
            ->orderBy('branch.branch_name', 'ASC')
             ->where('status', UserStatus::$ACTIVE)
            ->whereIn('employee.employee_id', $hasSupervisorWiseEmployee)->get();
        } else {
            $employees = Employee::select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'employee.updated_at', 'gender', 'status', 'department_name', 'branch_name', 'designation_name', 'finger_id', 'employee_id')
            ->join('designation', 'designation.designation_id', 'employee.designation_id')
            ->join('department', 'department.department_id', 'employee.department_id')
            ->join('branch', 'branch.branch_id', 'employee.branch_id')
            ->orderBy('branch.branch_name', 'ASC')
            ->where('status', UserStatus::$ACTIVE)
            ->whereIn('employee.finger_id', $regularEmployeeIds)->get();
        }
       

        $leave = LeaveApplication::select('application_from_date', 'application_to_date', 'employee_id', 'leave_type_name')
            ->join('leave_type', 'leave_type.leave_type_id', 'leave_application.leave_type_id')
            ->whereRaw("application_from_date >= '" . $start_date . "' and application_to_date <=  '" . $end_date . "'")
            ->where('status', LeaveStatus::$APPROVE)->get();

        $govtHolidays = DB::select(DB::raw('call SP_getHoliday("' . $start_date . '","' . $end_date . '")'));
        $dataFormat = [];
        $tempArray = [];

        foreach ($employees as $employee) {
            $activeUser = $employee->status;
            $leftUser = $employee->status;
            $weeklyHolidaysDates = WeeklyHoliday::where('employee_id', $employee->employee_id)->where('month', date('Y-m', strtotime($start_date)))->first();

            foreach ($data as $key => $value) {
                $tempArray['employee_id'] = $employee->employee_id;
                $tempArray['finger_id'] = $employee->finger_id;
                $tempArray['fullName'] = $employee->fullName;
                $tempArray['designation_name'] = $employee->designation_name;
                $tempArray['department_name'] = $employee->department_name;
                $tempArray['gender'] = $employee->gender;
                $tempArray['status'] = $employee->status;
                $tempArray['date'] = $value['date'];
                $tempArray['day'] = $value['day'];
                $tempArray['day_name'] = $value['day_name'];

                $leftDate = date('Y-m-d', strtotime($employee->updated_at));
                $weeklyHolidays = DB::select(DB::raw('call SP_getWeeklyHoliday("' . $employee->employee_id . '","' . date('Y-m',  strtotime($start_date)) . '")'));

                $hasAttendance = $this->hasEmployeeAttendance($attendance, $employee->finger_id, $value['date']);

                if ($hasAttendance['status'] == true) {
                    $ifHoliday = $this->ifHoliday($govtHolidays, $value['date'], $employee->employee_id, $weeklyHolidays, $weeklyHolidaysDates);
                    if ($ifHoliday['weekly_holiday'] == true) {
                        $tempArray['attendance_status'] = 'present';
                        $tempArray['gov_day_worked'] = 'no';
                        $tempArray['leave_type'] = '';
                        $tempArray['shift_name'] = $hasAttendance['shift_name'];
                        $tempArray['inout_status'] = $hasAttendance['inout_status'];
                    } elseif ($ifHoliday['govt_holiday'] == true) {
                        $tempArray['attendance_status'] = 'present';
                        $tempArray['gov_day_worked'] = 'yes';
                        $tempArray['leave_type'] = '';
                        $tempArray['shift_name'] = $hasAttendance['shift_name'];
                        $tempArray['inout_status'] = $hasAttendance['inout_status'];
                    } else {
                        $tempArray['attendance_status'] = 'present';
                        $tempArray['leave_type'] = '';
                        $tempArray['gov_day_worked'] = 'no';
                        $tempArray['shift_name'] = $hasAttendance['shift_name'];
                        $tempArray['inout_status'] = $hasAttendance['inout_status'];
                    }
                } else {

                    // if ($activeUser === UserStatus::$ACTIVE) {

                    $hasLeave = $this->ifEmployeeWasLeave($leave, $employee->employee_id, $value['date']);
                    $ifApplyLeaveOnHoliday = $this->ifHoliday($govtHolidays, $value['date'], $employee->employee_id, $weeklyHolidays, $weeklyHolidaysDates);

                    if ($hasLeave) {
                        if ($ifApplyLeaveOnHoliday['weekly_holiday'] == true) {
                            $tempArray['attendance_status'] = 'holiday';
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = '';
                            $tempArray['shift_name'] = '';
                            $tempArray['inout_status'] = '';
                        } elseif ($ifApplyLeaveOnHoliday['govt_holiday'] == true) {
                            $tempArray['attendance_status'] = 'publicHoliday';
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = '';
                            $tempArray['shift_name'] = '';
                            $tempArray['inout_status'] = '';
                        } else {
                            $tempArray['inout_status'] = '';
                            $tempArray['attendance_status'] = 'leave';
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = $hasLeave;
                            $tempArray['shift_name'] = '';
                        }
                    } else {
                        if ($value['date'] > date("Y-m-d")) {
                            $tempArray['attendance_status'] = '';
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = '';
                            $tempArray['shift_name'] = '';
                            $tempArray['inout_status'] = '';
                        } elseif ($leftUser === UserStatus::$INACTIVE && $value['date'] >= $leftDate) {
                            $tempArray['attendance_status'] = 'left';
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = '';
                            $tempArray['shift_name'] = '';
                            $tempArray['inout_status'] = '';
                        } else {
                            $ifHoliday = $this->ifHoliday($govtHolidays, $value['date'], $employee->employee_id, $weeklyHolidays, $weeklyHolidaysDates);
                            if ($ifHoliday['weekly_holiday'] == true) {
                                $tempArray['attendance_status'] = 'holiday';
                                $tempArray['gov_day_worked'] = 'no';
                                $tempArray['leave_type'] = '';
                                $tempArray['shift_name'] = '';
                                $tempArray['inout_status'] = '';
                            } elseif ($ifHoliday['govt_holiday'] == true) {
                                $tempArray['attendance_status'] = 'publicHoliday';
                                $tempArray['gov_day_worked'] = 'no';
                                $tempArray['leave_type'] = '';
                                $tempArray['shift_name'] = '';
                                $tempArray['inout_status'] = '';
                            } else {
                                $tempArray['attendance_status'] = 'absence';
                                $tempArray['gov_day_worked'] = 'no';
                                $tempArray['leave_type'] = '';
                                $tempArray['shift_name'] = '';
                                $tempArray['inout_status'] = '';
                            }
                        }
                    }
                    // } elseif (!$activeUser === UserStatus::$INACTIVE && $value['date'] > $leftDate) {
                    //     $tempArray['attendance_status'] = 'left';
                    //     $tempArray['gov_day_worked'] = 'no';
                    //     $tempArray['leave_type'] = '';
                    //     $tempArray['shift_name'] = '';
                    //     $tempArray['inout_status'] = '';
                    // }

                }

                $dataFormat[$employee->finger_id][] = $tempArray;
            }
        }

        return $dataFormat;
    }

    public function hasEmployeeAttendance($attendance, $finger_print_id, $date)
    {
        $temp = [];
        $temp['status'] = false;
        $temp['shift_name'] = '';
        $temp['inout_status'] = '';
        // dump($attendance, $finger_print_id, $date);
        foreach ($attendance as $key => $val) {
            if (($val->finger_print_id == $finger_print_id && $val->date == $date && $val->in_time != null)) {
                $temp['status'] = true;
                $temp['shift_name'] = $val->shift_name;
                $temp['inout_status'] = $val->inout_status;
                return $temp;
            }
        }
        return $temp;
    }

    public function ifEmployeeWasLeave($leave, $employee_id, $date)
    {
        $leaveRecord = [];
        $temp = [];
        foreach ($leave as $value) {
            if ($employee_id == $value->employee_id) {
                $start_date = $value->application_from_date;
                $end_date = $value->application_to_date;
                while (strtotime($start_date) <= strtotime($end_date)) {
                    $temp['employee_id'] = $employee_id;
                    $temp['date'] = $start_date;
                    $temp['leave_type_name'] = $value->leave_type_name;
                    $leaveRecord[] = $temp;
                    $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
                }
            }
        }

        foreach ($leaveRecord as $val) {

            if (($val['employee_id'] == $employee_id && $val['date'] == $date)) {
                return $val['leave_type_name'];
            }
        }

        return false;
    }

    public function ifHoliday($govtHolidays, $date, $employee_id, $weeklyHolidays, $weeklyHolidaysDates)
    {

        $govt_holidays = [];
        $result = [];
        $result['govt_holiday'] = false;
        $result['weekly_holiday'] = false;
        foreach ($govtHolidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $govt_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($govt_holidays as $val) {
            if ($val == $date) {
                $result['govt_holiday'] = true;
            }
        }

        $timestamp = strtotime($date);
        $dayName = date("l", $timestamp);
        foreach ($weeklyHolidays as $v) {
            if ($v->day_name == $dayName && $v->employee_id == $employee_id && isset($weeklyHolidaysDates) && $dayName == $weeklyHolidaysDates['day_name']) {
                $result['weekly_holiday'] = true;
                return $result;
            }
        }
        return $result;
    }
}
