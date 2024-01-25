@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.employee_onduty_report')
@endsection
<style>
    .employeeName {
        position: relative;
    }

    #employee_id-error {
        position: absolute;
        top: 66px;
        left: 0;
        width: 100%he;
        width: 100%;
        height: 100%;
    }
</style>
<script>
    jQuery(function() {
        $("#ondutyReport").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>

    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open(['route' => 'ondutyReport.ondutyReport', 'id' => 'ondutyReport']) }}
                                <div class="col-md-1"></div>
                                <div class="col-md-3">
                                    <div class="form-group department_name">
                                        <label class="control-label" for="email">@lang('common.department')<span
                                                class="validateRq"> </span>:</label>
                                        <select class="form-control department_id select2" name="department_id">
                                            <option value="">---- @lang('common.please_select') ----</option>
                                            @foreach ($departmentList as $value)
                                                <option value="{{ $value->department_id }}"
                                                    @if (@$value->department_id == $department_id) {{ 'selected' }} @endif>
                                                    {{ $value->department_name }} {{ $value->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.from_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.from_date')" name="from_date"
                                            value="@if (isset($from_date)) {{ $from_date }}@else {{ dateConvertDBtoForm(date('Y-01-01')) }} @endif">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.to_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.from_date')" name="to_date"
                                            value="@if (isset($to_date)) {{ $to_date }}@else {{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter" style="margin-top: 25px; width: 100px;"
                                            class="btn btn-info " value="@lang('common.filter')">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        <hr>

                        <div class="table-responsive" style="font-size: 12px;">
                            <table id="myDataTable" class="table table-bordered">
                                <thead class="tr_header">
                                    <tr>
                                        <th style="width:100px;">@lang('common.serial')</th>
                                        <th>@lang('leave.employee')</th>
                                        <th>@lang('leave.employee_id')</th>
                                        <th>@lang('leave.department')</th>
                                        <th>@lang('leave.applied_date')</th>
                                        <th>@lang('leave.od_duration')</th>
                                        <th>@lang('leave.approve_by')</th>
                                        <th>@lang('leave.approve_date')</th>
                                        <th>@lang('leave.reject_by')</th>
                                        <th>@lang('leave.reject_date')</th>
                                        <th>@lang('leave.manager_approve_by')</th>
                                        <th>@lang('leave.manager_approve_date')</th>
                                        <th>@lang('leave.manager_reject_by')</th>
                                        <th>@lang('leave.manager_reject_date')</th>
                                        <th>@lang('leave.purpose')</th>
                                        <th>@lang('leave.number_of_day')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{ $sl = null }}
                                    @foreach ($results as $value)
                                        {{-- @php
                                    // dd($value);
                                      
                                  @endphp --}}
                                        <tr>
                                            <td>{{ ++$sl }}</td>
                                            <td>{{ $value->employee->first_name . ' ' . $value->employee->last_name }}
                                            </td>
                                            <td>{{ $value->employee->finger_id }}</td>
                                            @php
                                                $dept = App\Model\Department::where('department_id', $value->employee->department_id)->first();
                                            @endphp
                                            <td>{{ $dept ? $dept->department_name : 'NA' }}
                                            </td>

                                            <td>{{ dateConvertDBtoForm($value->application_date) }}</td>
                                            <td>{{ dateConvertDBtoForm($value->application_from_date) }}
                                                <b>to</b>
                                                {{ dateConvertDBtoForm($value->application_to_date) }}
                                            </td>
                                            @if ($value->approveBy != null)
                                                <td>

                                                    @if ($value->approveBy->first_name != null)
                                                        {{ $value->approveBy->first_name }}
                                                        {{ $value->approveBy->last_name }}
                                                    @endif
                                                </td>
                                            @else
                                                <td>{{ '--' }}</td>
                                                <td>{{ '--' }}</td>
                                            @endif
                                            <td>{{ dateConvertDBtoForm($value->approve_date) }}</td>
                                            @if ($value->rejectBy != null)
                                                <td>

                                                    @if ($value->rejectBy->first_name != null)
                                                        {{ $value->rejectBy->first_name }}
                                                        {{ $value->rejectBy->last_name }}
                                                    @endif
                                                </td>
                                                <td>{{ dateConvertDBtoForm($value->reject_date) }}</td>
                                            @else
                                                <td>{{ '--' }}</td>
                                                <td>{{ '--' }}</td>
                                            @endif
                                            @if ($value->managerApproveBy != null)
                                                <td>
                                                    @if ($value->managerApproveBy->first_name != null)
                                                        {{ $value->managerApproveBy->first_name }}
                                                        {{ $value->managerApproveBy->last_name }}
                                                    @endif
                                                </td>
                                                <td>{{ dateConvertDBtoForm($value->manager_approve_date) }}</td>
                                            @else
                                                <td>{{ '--' }}</td>
                                                <td>{{ '--' }}</td>
                                            @endif
                                            @if ($value->managerRejectBy != null)
                                                <td>

                                                    @if ($value->managerRejectBy->first_name != null)
                                                        {{ $value->managerRejectBy->first_name }}
                                                        {{ $value->managerRejectBy->last_name }}
                                                    @endif
                                                </td>
                                                <td>{{ dateConvertDBtoForm($value->manager_reject_date) }}</td>
                                            @else
                                                <td>{{ '--' }}</td>
                                                <td>{{ '--' }}</td>
                                            @endif
                                            {{-- <td>{{ dateConvertDBtoForm($value->approve_date) }}</td> --}}
                                            <td width="300px;word-wrap: break-word">{{ $value->purpose }}</td>
                                            <td>{{ $value->no_of_days }}</td>
                                        </tr>
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
@endsection
