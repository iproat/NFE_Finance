@php
    use App\Model\Device;
@endphp
@extends('admin.master')

@section('content')
@section('title')
    @lang('attendance.attendance_record')
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
        $("#attendanceRecord").validate();
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

    <hr>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">

                        <div class="row">
                            <div id="searchBox">

                                {{ Form::open(['route' => 'attendanceRecord.attendanceRecord', 'id' => 'attendanceRecord']) }}

                                <div class="form-group">

                                    <div class="col-sm-1"></div>



                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label class="control-label" for="date">@lang('common.from_date')<span
                                                    class="validateRq">*</span>:</label>
                                            <input type="text" class="form-control dateField required"
                                                style="height: 35px;" readonly placeholder="@lang('common.date')"
                                                id="date" name="from_date"
                                                value="@if (isset($from_date)) {{ $from_date }}@else {{ dateConvertDBtoForm(date('Y-m-d')) }} @endif"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label class="control-label" for="date">@lang('common.to_date')<span
                                                    class="validateRq">*</span>:</label>
                                            <input type="text" class="form-control dateField required"
                                                style="height: 35px;" readonly placeholder="@lang('common.date')"
                                                id="date" name="to_date"
                                                value="@if (isset($to_date)) {{ $to_date }}@else {{ dateConvertDBtoForm(date('Y-m-d')) }} @endif"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-sm-3" hidden>
                                        @php
                                            $devices = allDevices();
                                        @endphp
                                        <label class="control-label" for="device_name">@lang('common.device'):</label>
                                        <div class="form-group">
                                            <select name="device_name" class="form-control device_name select2">
                                                <option value="">--- All devices ---</option>
                                                @foreach ($devices as $value)
                                                    <option value="{{ $value }}"
                                                        @if ($value == $device_name) {{ 'selected' }} @endif>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <input type="submit" id="filter" style="margin-top: 25px;"
                                                class="btn btn-info " value="@lang('common.filter')">
                                        </div>
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        <br>
                        <div class="table-responsive">
                            <table id="newDataTable" class="table table-bordered" style="font-size: 12px">
                                <thead class="tr_header">
                                    <tr>
                                        <th style="width:80px;">@lang('common.serial')</th>
                                        <th>Employee Id</th>
                                        <th>@lang('common.name')</th>
                                        <th>DateTime</th>
                                        <th>In/Out</th>
                                        <th>Device Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{ $serial = null }}
                                    @foreach ($results as $value)
                                        @if (isset($value['employee']))
                                            <tr>
                                                <td style="width:100px;">
                                                    <p style="color: black">{{ ++$serial }}</p>
                                                </td>
                                                <td>
                                                    <p style="color: black">{{ $value['ID'] }} </p>
                                                </td>
                                                <td>
                                                    <p style="color: black">
                                                        {{ $value['employee']['first_name'] . ' ' . $value['employee']['last_name'] }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p style="color: black">{{ $value['datetime'] }} </p>
                                                </td>
                                                <td>
                                                    <p style="color: black">
                                                        @if ($value['type'] == 1)
                                                            {{ 'IN' }}
                                                        @elseif($value['type'] == 2)
                                                            {{ 'OUT' }}
                                                        @else
                                                            {{ $value['type'] }}
                                                        @endif
                                                    </p>
                                                </td>
                                                <td>
                                                    <span style="color: black">
                                                        {{ $value['device_name'] }}
                                                    </span>
                                                </td>
                                                </td>
                                            </tr>
                                        @endif
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
