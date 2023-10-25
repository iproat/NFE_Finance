@extends('admin.master')
@section('content')
    <div id="preloader" class="preloader hidden"></div>
@section('title')
    @lang('attendance.calculate_attendance')
@endsection
<style>
    .departmentName {
        position: relative;
    }

    #department_id-error {
        position: absolute;
        top: 66px;
        left: 0;
        width: 100%;
        width: 100%;
        height: 100%;
    }

    .fade1 {
        background-color: white;
        opacity: 0.9;
        top: 0px;
        left: 0px;
        right: 0px;
        bottom: 0px;
        margin: 0px;
        width: 100%;
        height: auto;
        position: fixed;
        z-index: 1040;
        display: none;
    }

    .fade1 .spin {
        position: absolute;
        top: 48%;
        left: 48%;
    }
</style>
<script>
    jQuery(function() {
        $("#employeeAttendance").validate();
    });
</script>
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
                                {{-- {{ Form::open(['route' => 'generateReport.generateReport', 'id' => 'generateReport', 'method' => 'GET']) }} --}}
                                <div class="col-md-2"></div>
                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.from_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required from_date" readonly
                                            placeholder="@lang('common.from_date')" name="from_date"
                                            value="@if (isset($from_date)) {{ $from_date }}@else{{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.to_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required to_date" readonly
                                            placeholder="@lang('common.to_date')" name="to_date"
                                            value="@if (isset($to_date)) {{ $to_date }}@else{{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter"
                                            style="margin-top: 25px;height:36px;width: 150px;"
                                            class="btn btn-instagram btn-md generateReport" value="Recompute">
                                    </div>
                                </div>
                                {{-- {{ Form::close() }} --}}
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
    $(".generateReport").click(function() {
        var from_date = $('.from_date').val();
        var to_date = $('.to_date').val();
        window.open(
            "{{ route('calculateAttendance.calculateAttendance', ['ajaxcall' => 1]) }}" + '&from_date=' +
            from_date + '&to_date=' + to_date,
            '_blank');

    });
</script>


<?php if (isset($_GET['ajaxcall'])){ ?>

<script>
    var from_date = $('.from_date').val();
    var to_date = $('.to_date').val();
    $('.fade1').show();

    $.ajax({
        type: "GET",
        url: "{{ route('generateReport.generateReport') }}",
        data: {
            from_date: from_date,
            to_date: to_date,
            _token: $('input[name=_token]').val()
        },
        success: function(data) {
            $('.fade1').hide();
            swal({
                title: "Attendance Report!",
                text: "Success! Operation has been completed.",
                type: "success"
            });
            setTimeout(() => {
                window.close();
            }, 5000);

        }
    });
</script>
<?php } ?>
@endsection
