@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.daily_attendance')
@endsection
<script>
    jQuery(function() {
        $("#dailyAttendanceReport").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
    </div>
    @php
        // dd($departmentList);
    @endphp
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div id="searchBox">
                            <div class="col-md-2"></div>
                            {{ Form::open([
                                'route' => 'dailyAttendance.dailyAttendance',
                                'id' => 'dailyAttendanceReport',
                                'class' => 'form-horizontal',
                            ]) }}

                            @php
                                $listStatus = [
                                    '1' => 'Present',
                                    '9' => 'Missing in Punch',
                                    '8' => 'Missing out punch',
                                    '10' => 'Less Hours',
                                    '2' => 'Absent',
                                    '3' => 'Leave',
                                    '4' => 'Holiday',
                                    '11' => 'Comp Off',
                                ];
                                
                            @endphp

                            @php
                                if (decrypt(session('logged_session_data.role_id')) != 1 && decrypt(session('logged_session_data.role_id')) != 2) {
                                    $listStatus = [];
                                    $departmentList = [];
                                }
                            @endphp


                            <div class="form-group">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label" for="department_id">@lang('common.department'):</label>
                                        <select name="department_id" class="form-control department_id  select2">
                                            <option value="">--- @lang('common.all') ---</option>
                                            @foreach ($departmentList as $value)
                                                <option value="{{ $value->department_id }}"
                                                    @if ($value->department_id == $department_id) {{ 'selected' }} @endif>
                                                    {{ $value->department_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2" style="margin-left:12px;">
                                    <div class="form-group">
                                        <label class="control-label" for="email">@lang('common.status'):</label>
                                        <select name="attendance_status"
                                            class="form-control attendance_status  select2">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($listStatus as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if ($key == $attendance_status) {{ 'selected' }} @endif>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label class="control-label" for="email">@lang('common.date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.date')" name="date"
                                            value="@if (isset($date)) {{ $date }}@else {{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <input type="submit" id="filter" style="margin-top: 28px;"
                                            class="btn btn-info btn-md" value="@lang('common.filter')">
                                    </div>
                                </div>
                            </div>
                            {{ Form::close() }}

                        </div>
                        <hr>

                        {{-- @if (count($results) > 0 && $results != '')
                            <h4 class="text-right">
                                <div  style="margin-top: 13px;margin-bottom: 12px;margin-right: 12px;">
                                    <button id="excelexport" onclick="" class="btn btn-success">Export
                                        Report .xls</button>
                                </div>
                            </h4>
                        @endif --}}

                        <div id="btableData">
                            <div class="table-responsive">
                                <table id="myDataTable" class="table table-bordered" style="font-size: 12px;">
                                    <thead class="tr_header bg-title">
                                        <tr>
                                            <th style="width:50px;">@lang('common.serial')</th>
                                            <th style="font-size:12px;">@lang('common.date')</th>
                                            <th style="font-size:12px;">@lang('common.employee_name')</th>
                                            <th style="font-size:12px;">@lang('common.id')</th>
                                            <th style="font-size:12px;">@lang('attendance.department')</th>
                                            <th style="font-size:12px;">@lang('attendance.shift')</th>
                                            <th style="font-size:12px;">@lang('attendance.in_time')</th>
                                            <th style="font-size:12px;">@lang('attendance.out_time')</th>
                                            <th style="font-size:12px;">@lang('attendance.duration')</th>
                                            <th style="font-size:12px;">@lang('attendance.early_by')</th>
                                            <th style="font-size:12px;">@lang('attendance.late_by')</th>
                                            {{-- <th style="font-size:12px;">@lang('attendance.over_time')</th>
                                            <th style="font-size:12px;">@lang('attendance.comp_off')</th>
                                            <th style="font-size:12px;">@lang('attendance.incentive')</th> --}}
                                            <th style="font-size:12px;">@lang('attendance.history_of_records')</th>
                                            <th style="font-size:12px;;">@lang('attendance.status')</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        {{ $sl = null }}
                                        @foreach ($results as $dept => $result)
                                            @foreach ($result as $key => $value)
                                                @php
                                                    $zero = '00:00';
                                                    $isHoliday = false;
                                                    $holidayDate = '';
                                                @endphp
                                                <tr>
                                                    <td style="font-size:12px;">{{ ++$sl }}</td>
                                                    <td style="font-size:12px;">{{ $value->date }}</td>
                                                    <td style="font-size:12px;">{{ $value->fullName }}</td>
                                                    <td style="font-size:12px;">{{ $value->finger_print_id }}</td>
                                                    <td style="font-size:12px;">{{ $dept }}</td>
                                                    <td style="font-size:12px;">{{ $value->shift_name ?? 'N/A' }}</td>
                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->in_time != '') {
                                                                echo $value->in_time;
                                                            } else {
                                                                echo $zero;
                                                            }
                                                        @endphp
                                                    </td>
                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->out_time != '') {
                                                                echo $value->out_time;
                                                            } else {
                                                                echo $zero;
                                                            }
                                                        @endphp
                                                    </td>
                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->working_time != null) {
                                                                echo date('H:i', strtotime($value->working_time));
                                                                // echo "<b style='color: black'>" . date('H:i', strtotime($value->working_time)) . '</b>';
                                                            } else {
                                                                echo $zero;
                                                            }
                                                        @endphp
                                                    </td>
                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->early_by != null) {
                                                                echo date('H:i', strtotime($value->early_by));
                                                            } else {
                                                                echo $zero;
                                                            }
                                                        @endphp
                                                    </td>
                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->late_by != null) {
                                                                echo date('H:i', strtotime($value->late_by));
                                                            } else {
                                                                echo $zero;
                                                            }
                                                        @endphp
                                                    </td>
                                                    {{-- <td style="font-size:12px;">
                                                        @php
                                                            if (isset($value->overtime_approval->actual_overtime) && $value->overtime_approval->actual_overtime != null) {
                                                                echo 'OT Hr: ' . date('H:i', strtotime($value->overtime_approval->actual_overtime)) . '<br>' . 'Appr. Hr: ' . date('H:i', strtotime($value->overtime_approval->approved_overtime)) . '<br>' . 'Remark: ' . $value->overtime_approval->remark;
                                                            } else {
                                                                echo 'OT Hr: ' . ($value->over_time ? date('H:i', strtotime($value->over_time)) : '-') . '<br>' . 'Appr. Hr: ' . '-' . '<br>' . 'Remark: ' . '-';
                                                            }
                                                        @endphp
                                                    </td> --}}
                                                    {{-- <td style="font-size:12px;">
                                                        @php
                                                            if (isset($value->comp_off->off_date) && $value->comp_off->working_date != null) {
                                                                echo 'Off Date: ' . dateConvertDBtoForm($value->comp_off->off_date) . '<br>' . 'Wrk.Date: ' . dateConvertDBtoForm($value->comp_off->working_date) . '<br>' . 'Day: ' . fullOrHalfDay($value->comp_off->off_timing) . '<br>' . 'Comment: ' . $value->comp_off->comment;
                                                            } else {
                                                                echo 'Off Date: ' . '-' . '<br>' . 'Wrk.Date: ' . '-' . '<br>' . 'Day: ' . '' . '<br>' . 'Comment: ' . '-';
                                                            }
                                                        @endphp
                                                    </td> --}}
                                                    {{-- <td style="font-size:12px;">
                                                        @php
                                                            if (isset($value->incentive->incentive_date) && $value->incentive->working_date != null) {
                                                                echo 'Incentive Date: ' . dateConvertDBtoForm($value->incentive->incentive_date) . '<br>' . 'Wrk.Date: ' . dateConvertDBtoForm($value->incentive->working_date) . '<br>' . 'Comment: ' . $value->incentive->comment;
                                                            } else {
                                                                echo 'Incentive Date: ' . '-' . '<br>' . 'Wrk. Date: ' . '-' . '<br>'  . 'Comment: ' . '-';
                                                            }
                                                        @endphp
                                                    </td> --}}
                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->in_out_time != null) {
                                                                echo $value->in_out_time;
                                                            } else {
                                                                echo $zero;
                                                            }
                                                        @endphp
                                                    </td>

                                                    <td style="font-size:12px;">
                                                        <?php
                                                        echo attStatus($value->attendance_status);
                                                        ?>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page_scripts')
<script>
    $(document).ready(function() {
        $("#excelexport").click(function(e) {
            //getting values of current time for generating the file name
            var dt = new Date();
            var day = dt.getDate();
            var month = dt.getMonth() + 1;
            var year = dt.getFullYear();
            var hour = dt.getHours();
            var mins = dt.getMinutes();
            var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
            //creating a temporary HTML link element (they support setting file names)
            var a = document.createElement('a');
            //getting data from our div that contains the HTML table
            var data_type = 'data:application/vnd.ms-excel';
            var table_div = document.getElementById('btableData');
            var table_html = table_div.outerHTML.replace(/ /g, '%20');
            a.href = data_type + ', ' + table_html;
            //setting the file name
            a.download = 'attendance_details_' + postfix + '.xls';
            //triggering the function
            a.click();
            //just in case, prevent default behaviour
            e.preventDefault();
        });


    });
</script>
@endsection
