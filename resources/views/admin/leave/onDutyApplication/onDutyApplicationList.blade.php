@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.requested_application')
@endsection

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i> @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i
                                    class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i
                                    class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif



                        @if (count($adminResults) > 0)
                            <div class="">
                                <table class="table table-hover manage-u-table">
                                    <thead>
                                        <tr>

                                            <th>@lang('common.serial')</th>
                                            <th>@lang('common.employee_name')</th>
                                            <th>@lang('leave.request_duration')</th>
                                            <th>@lang('leave.request_date')</th>
                                            <th>@lang('leave.number_of_day')</th>
                                            <th>@lang('leave.purpose')</th>
                                            <th>@lang('leave.department_head_status')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {!! $sl = null !!}
                                        @foreach ($adminResults as $value)
                                            <tr>
                                                <td style="width: 100px;">{!! ++$sl !!}</td>
                                                <td>
                                                    @if (isset($value->employee->first_name))
                                                        {!! $value->employee->first_name !!}
                                                    @endif
                                                    @if (isset($value->employee->last_name))
                                                        {!! $value->employee->last_name !!}
                                                    @endif
                                                </td>

                                                <td>{!! dateConvertDBtoForm($value->application_from_date) !!} <b>to</b> {!! dateConvertDBtoForm($value->application_to_date) !!}</td>
                                                <td>{!! dateConvertDBtoForm($value->application_date) !!}</td>
                                                <td>{!! $value->no_of_days !!}</td>
                                                <td>{!! $value->purpose !!}</td>


                                                <td style="width: 100px;"> <a href="javacript:void(0)" data-status=2
                                                        data-on_duty_id="{{ $value->on_duty_id }}"
                                                        class="btn remarksForLeave btn btn-rounded btn-success btn-outline m-r-5"><i
                                                            class="ti-check text-success m-r-5"></i>@lang('common.approve')</a>
                                                    <a href="javacript:void(0)" data-status=3
                                                        data-on_duty_id="{{ $value->on_duty_id }}"
                                                        class="btn-rounded remarksForLeave btn btn-danger btn-outline"><i
                                                            class="ti-close text-danger m-r-5"></i>
                                                        @lang('common.reject')</a>
                            </div>
                            </td>
                            </tr>
                        @endforeach

                        </tbody>
                        </table>
                        <div class="text-center">
                            {{ $adminResults->links() }}
                        </div>
                    </div>
                    @endif
                    @if (count($operationManagerResults) > 0)
                        <div class="">
                            <table class="table table-hover manage-u-table">
                                <thead>
                                    <tr>

                                        <th>@lang('common.serial')</th>
                                        <th>@lang('common.employee_name')</th>
                                        <th>@lang('leave.request_duration')</th>
                                        <th>@lang('leave.request_date')</th>
                                        <th>@lang('leave.number_of_day')</th>
                                        <th>@lang('leave.purpose')</th>
                                        <th>@lang('leave.department_head_status')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $sl = null !!}
                                    @foreach ($operationManagerResults as $value)
                                        <tr>
                                            <td style="width: 100px;">{!! ++$sl !!}</td>
                                            <td>
                                                @if (isset($value->employee->first_name))
                                                    {!! $value->employee->first_name !!}
                                                @endif
                                                @if (isset($value->employee->last_name))
                                                    {!! $value->employee->last_name !!}
                                                @endif
                                            </td>

                                            <td>{!! dateConvertDBtoForm($value->application_from_date) !!} <b>to</b> {!! dateConvertDBtoForm($value->application_to_date) !!}</td>
                                            <td>{!! dateConvertDBtoForm($value->application_date) !!}</td>
                                            <td>{!! $value->no_of_days !!}</td>
                                            <td>{!! $value->purpose !!}</td>


                                            <td style="width: 100px;"> <a href="javacript:void(0)" data-status=2
                                                    data-on_duty_id="{{ $value->on_duty_id }}"
                                                    class="btn remarksForLeave btn btn-rounded btn-success btn-outline m-r-5"><i
                                                        class="ti-check text-success m-r-5"></i>@lang('common.approve')</a>
                                                <a href="javacript:void(0)" data-status=3
                                                    data-on_duty_id="{{ $value->on_duty_id }}"
                                                    class="btn-rounded remarksForLeave btn btn-danger btn-outline"><i
                                                        class="ti-close text-danger m-r-5"></i>
                                                    @lang('common.reject')</a>
                        </div>
                        </td>
                        </tr>
                    @endforeach

                    </tbody>
                    </table>
                    <div class="text-center">
                        {{ $operationManagerResults->links() }}
                    </div>
                </div>
                @endif
             
                @if (count($hrResults ) > 0)
                    <div class="">
                        <table class="table table-hover manage-u-table">
                            <thead>
                                <tr>
                                    <th>@lang('common.serial')</th>
                                    <th>@lang('common.employee_name')</th>
                                    <th>@lang('leave.request_duration')</th>
                                    <th>@lang('leave.request_date')</th>
                                    <th>@lang('leave.number_of_day')</th>
                                    <th>@lang('leave.purpose')</th>
                                    <th>@lang('leave.department_head_status')</th>
                                </tr>
                            </thead>
                            <tbody>
                                {!! $sl = null !!}
                                @foreach ($hrResults as $value)
                                    <tr>
                                        <td style="width: 100px;">{!! ++$sl !!}</td>
                                        <td>
                                            @if (isset($value->employee->first_name))
                                                {!! $value->employee->first_name !!}
                                            @endif
                                            @if (isset($value->employee->last_name))
                                                {!! $value->employee->last_name !!}
                                            @endif
                                        </td>

                                        <td>{!! dateConvertDBtoForm($value->application_from_date) !!} <b>to</b> {!! dateConvertDBtoForm($value->application_to_date) !!}</td>
                                        <td>{!! dateConvertDBtoForm($value->application_date) !!}</td>
                                        <td>{!! $value->no_of_days !!}</td>
                                        <td>{!! $value->purpose !!}</td>

                                        <td style="width: 100px;"> <a href="javacript:void(0)" data-status=2
                                                data-on_duty_id="{{ $value->on_duty_id }}"
                                                class="btn remarksForLeave btn btn-rounded btn-success btn-outline m-r-5"><i
                                                    class="ti-check text-success m-r-5"></i>@lang('common.approve')</a>
                                            <a href="javacript:void(0)" data-status=3
                                                data-on_duty_id="{{ $value->on_duty_id }}"
                                                class="btn-rounded remarksForLeave btn btn-danger btn-outline"><i
                                                    class="ti-close text-danger m-r-5"></i>
                                                @lang('common.reject')</a>
                    </div>
                    </td>
                    </tr>
                @endforeach

                </tbody>
                </table>
                <div class="text-center">
                    {{ $hrResults->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</div>
</div>
</div>
@endsection
