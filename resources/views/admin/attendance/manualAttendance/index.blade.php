@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.employee_attendance')
@endsection
<style>
    .branchName {
        position: relative;
    }

    #branch_id-error {
        position: absolute;
        top: 66px;
        left: 0;
        width: 100%;
        width: 100%;
        height: 100%;
    }

    .bootstrap-datetimepicker-widget table td span {
        display: inline-block;
        width: 54px;
        height: 54px;
        line-height: 54px;
        margin: 2px 1.5px;
        cursor: pointer;
        border-radius: 4px;
    }

    .bootstrap-datetimepicker-widget .picker-switch td span {
        line-height: 2.5;
        height: 2.5em;
        width: 100%;
    }

    .table-condensed>tbody>tr>td,
    .table-condensed>tbody>tr>th,
    .table-condensed>tfoot>tr>td,
    .table-condensed>tfoot>tr>th,
    .table-condensed>thead>tr>td,
    .table-condensed>thead>tr>th {
        padding: 5px;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
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
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
                                <i
                                    class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
                                <i
                                    class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif
                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open(['route' => 'manualAttendance.filter', 'id' => 'employeeAttendance', 'method' => 'POST']) }}
                                <div class="col-md-2"></div>
                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.branch')<span
                                            class="validateRq">*</span></label>
                                    <div class="form-group">
                                        <select class="form-control branchName select2 required" required
                                            name="branch_id">
                                            <option value="">---- @lang('common.please_select') ----</option>
                                            @foreach ($branchList as $key => $value)
                                                @if ($key > 0)
                                                    <option value="{{ $key }}"
                                                        @if (isset($_REQUEST['branch_id'])) @if ($_REQUEST['branch_id'] == $key) {{ 'selected' }} @endif
                                                        @endif>
                                                        {{ $value }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.date')" name="date" id="manualDate"
                                            value="@if (isset($_REQUEST['date'])) {{ $_REQUEST['date'] }}@else{{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter" style="margin-top: 25px;height:36px"
                                            class="btn btn-info btn-md" value="@lang('common.filter')">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        @if (count($results) > 0)
                            <div>
                                @include('admin.attendance.manualAttendance.pagination')
                            </div>
                        @endif
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

        var date = $('.dateField').val();
        date = date.split('/');
        var prefix = $.trim(date[2]) + '-' + $.trim(date[1]) + '-';
        var suffix = $.trim(date[0]) + ' 00:00:00';
        date = prefix + suffix
        start_date = new Date(date);
        var end_date = start_date.getDate();
        end_date = prefix + end_date + ' 00:00:00';
        end_date = new Date();

        $('.bootstrap-datetimepicker-widget').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            minDate: start_date,
            maxDate: end_date,
            // minDate: moment().subtract(60, 'days'),
            // maxDate: moment().add(0, 'days')
        }).on('dp.change', function(e) {
            var formatedValue = e.date.format(e.date._f);
            var id = $(this).attr('data-id');
            var value = $(this).attr('value');
            var name = $(this).attr('name');
            var fdate = $('.intime' + id).val();
            var tdate = $('.outtime' + id).val();

            // var splitFdate = fdate.split('-');
            // splitFdate = splitFdate[0] + '-' + splitFdate[1] + '-' + splitFdate[2];
            // var splitTdate = tdate.split('-');
            // splitTdate = splitTdate[0] + '-' + splitTdate[1] + '-' + splitTdate[2];

            // if (tdate != "" && tdate != "" && fdate > tdate) {
            //     if (name == 'in_time') {
            //         $('.intime' + id).val(value)
            //     } else {
            //         $('.outtime' + id).val(value)
            //     }
            //     $.toast({
            //         heading: 'Error',
            //         text: 'Wrong Datetime time',
            //         position: 'top-right',
            //         loaderBg: '#ff6849',
            //         icon: 'warning',
            //         hideAfter: 3000,
            //         stack: 6
            //     });
            // }
        });

    });
</script>

<script>
    $(document).on('click', '.generateReportIndividually', function(e) {
        e.preventDefault();

        var actionTo = $(this).attr('href');

        var qs = actionTo.substring(actionTo.indexOf('?') + 1).split('&');
        for (var i = 0, result = {}; i < qs.length; i++) {
            qs[i] = qs[i].split('=');
            result[qs[i][0]] = decodeURIComponent(qs[i][1]);
        }

        var in_time = $('.intime' + result.finger_print_id).val();
        var out_time = $('.outtime' + result.finger_print_id).val();
        var token = $(this).attr('data-token');
        var id = $(this).attr('data-id');
        console.log(out_time);
        $.ajax({
            url: actionTo + '&in_time=' + in_time + '&out_time=' + out_time,
            type: 'POST',
            data: {
                _method: 'POST',
                _token: token
            },
            success: function(data) {
                console.log(data);
                if (data == 'success') {

                    // toasting success message 
                    $.toast({
                        heading: 'Success',
                        text: 'Manual attendance has been saved...!',
                        position: 'top-right',
                        loaderBg: '#ff6849',
                        icon: 'success',
                        hideAfter: 2000,
                        stack: 6
                    });

                } else {
                    // toasting error message 
                    $.toast({
                        heading: 'Error',
                        text: 'Something went wrong!',
                        position: 'top-right',
                        loaderBg: '#ff6849',
                        icon: 'warning',
                        hideAfter: 3000,
                        stack: 6
                    });
                }

                setInterval(() => {
                    location.reload();
                }, 2000);

            }
        });
    });
</script>

<script>
    $('.data').on('click', '.pagination a', function(e) {
        getData($(this).attr('href').split('page=')[1]);
        e.preventDefault();
    });
</script>
@endsection
