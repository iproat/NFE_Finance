@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.leave_permission_form')
@endsection
<style>
    .datepicker table tr td.disabled,
    .datepicker table tr td.disabled:hover {
        background: none;
        color: red !important;
        cursor: default;
    }

    td {
        color: black !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>

            </ol>
        </div>
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <a href="{{ route('applyForPermission.index') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i
                    class="fa fa-list-ul" aria-hidden="true"></i> @lang('leave.view_leave_permission')</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@lang('leave.leave_permission_form')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">



                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                        aria-hidden="true">×</span></button>
                                @foreach ($errors->all() as $error)
                                    <strong>{!! $error !!}</strong><br>
                                @endforeach
                            </div>
                        @endif
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
                                <strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif

                        {{ Form::open(['route' => 'applyForPermission.store', 'id' => 'leavePermissionForm']) }}
                        <div class="form-body">
                            <div class="row">


                                {!! Form::hidden(
                                    'employee_id',
                                    isset($getEmployeeInfo) ? $getEmployeeInfo->employee_id : '',
                                    $attributes = ['class' => 'employee_id'],
                                ) !!}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('common.employee_name')<span
                                                class="validateRq">*</span></label>
                                        {!! Form::text(
                                            '',
                                            isset($getEmployeeInfo) ? $getEmployeeInfo->first_name . ' ' . $getEmployeeInfo->last_name : '',
                                            $attributes = ['class' => 'form-control', 'readonly' => 'readonly'],
                                        ) !!}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="exampleInput">@lang('common.date')<span class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text(
                                            'permission_date',
                                            Input::old('permission_date'),
                                            $attributes = [
                                                'class' => 'form-control permission_date required',
                                                'readonly' => 'readonly',
                                                'placeholder' => __('common.permission_date'),
                                            ],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('leave.already_approved_permission_count')<span
                                                class="validateRq">*</span></label>
                                        {!! Form::text(
                                            '',
                                            '',
                                            $attributes = [
                                                'class' => 'form-control current_balance required',
                                                'readonly' => 'readonly',
                                                'placeholder' => __('leave.applied_permission_count'),
                                            ],
                                        ) !!}
                                    </div>
                                </div>



                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group bootstrap-timepicker">
                                        <label for="exampleInput">From Time<span class="validateRq">*</span></label>

                                        <input class="form-control timePicker required"
                                            onChange = " findTimeDifference()" type="text" placeholder="From Time"
                                            name="from_time" id = "from_time" readonly>

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group bootstrap-timepicker">
                                        <label for="exampleInput">To Time <span class="validateRq">*</span></label>
                                        <input class="form-control timePicker required"
                                            onChange = " findTimeDifference()" type="text" placeholder="To Time"
                                            name="to_time" id = "to_time" readonly>

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('leave.permission_duration') <small>(Max:3 Hours)</small><span
                                                class="validateRq">*</span></label>

                                        {!! Form::text(
                                            'permission_duration',
                                            Input::old('permission_duration'),
                                            $attributes = [
                                                'class' => 'form-control permission_duration required',
                                                'readonly' => 'readonly',
                                                'min' => '00:00',
                                                'max' => '03:00',
                                                'placeholder' => __('common.permission_duration'),
                                            ],
                                        ) !!}

                                    </div>
                                </div>



                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('leave.purpose')<span
                                                class="validateRq">*</span></label>
                                        {!! Form::textarea(
                                            'purpose',
                                            Input::old('purpose'),
                                            $attributes = [
                                                'class' => 'form-control purpose required',
                                                'id' => 'purpose',
                                                'placeholder' => __('leave.purpose'),
                                                'cols' => '30',
                                                'rows' => '3',
                                            ],
                                        ) !!}
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" id="formSubmit" class="btn btn-info "><i
                                            class="fa fa-paper-plane"></i> @lang('leave.send_application')</button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page_scripts')
<script>
    function findTimeDifference() {
        var valuestart = $('#from_time').val();
        var valuestop = $('#to_time').val();

        if (valuestart !== '' && valuestop !== '') {
            var startTime = new Date('2000-01-01 ' + valuestart);
            var endTime = new Date('2000-01-01 ' + valuestop);

            if (startTime > endTime) {

                $.toast({
                    heading: 'Warning',
                    text: 'Invalid Time selection',
                    position: 'top-right',
                    loaderBg: '#ff6849',
                    icon: 'warning',
                    hideAfter: 3000,
                    stack: 1
                });

                $('#to_time').val('');
                $('.permission_duration').val('');
                $('body').find('#formSubmit').attr('disabled', true);
            } else {
                var timeDiff = endTime - startTime;
                var hours = Math.floor(timeDiff / 3600000);
                var minutes = Math.floor((timeDiff % 3600000) / 60000);

                if (hours > 3 || (hours === 3 && minutes > 0)) {
                    $.toast({
                        heading: 'Warning',
                        text: 'Permission only allows a maximum of 3 hours',
                        position: 'top-right',
                        loaderBg: '#ff6849',
                        icon: 'warning',
                        hideAfter: 3000,
                        stack: 1
                    });
                    $('#to_time').val('');
                }

                $('.permission_duration').val(hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2,
                    '0'));

                $('body').find('#formSubmit').attr('disabled', false);
            }
        } else {
            $('body').find('#formSubmit').attr('disabled', true);
        }
    }



    $(function() {
        $(document).on("focus", ".permission_date", function() {

            $(this).datepicker({
                format: 'dd/mm/yyyy',
                todayHighlight: true,
                clearBtn: true,
                startDate: new Date(),
            }).on('changeDate', function(e) {
                $(this).datepicker('hide');
            });
        });

        if ($(".permission_date").val() == '' && $(".employee_id").val() != '') {
            const date = new Date();
            let day = date.getDate();
            let month = date.getMonth() + 1;
            let year = date.getFullYear();
            var permission_date = `${day}/${month}/${year}`;
            $(".permission_date").val(permission_date);
            var employee_id = $('.employee_id').val();

            var action = "{{ URL::to('applyForPermission/applyForTotalNumberOfPermissions') }}";

            $.ajax({
                type: 'POST',
                url: action,
                data: {
                    'permission_date': permission_date,
                    'employee_id': employee_id,
                    '_token': $('input[name=_token]').val()
                },

                dataType: 'json',
                success: function(data) {

                    $('.current_balance').val(data);

                    if (data >= 2) {
                        $.toast({
                            heading: 'Warning',
                            text: 'You already applied ' + $('.current_balance')
                                .val() + ' days!',
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'warning',
                            hideAfter: 3000,
                            stack: 6
                        });
                        $('body').find('#formSubmit').attr('disabled', true);
                        $('.current_balance').val(data);
                    } else {
                        $('.current_balance').val(data);
                        $('body').find('#formSubmit').attr('disabled', false);
                    }
                }
            });
        }

        $(document).on("change", ".permission_date, .employee_id", function() {
            var permission_date = $('.permission_date').val();
            var employee_id = $('.employee_id').val();

            if (permission_date != '' && employee_id != '') {
                var action = "{{ URL::to('applyForPermission/applyForTotalNumberOfPermissions') }}";

                $.ajax({
                    type: 'POST',
                    url: action,
                    data: {
                        'permission_date': permission_date,
                        'employee_id': employee_id,
                        '_token': $('input[name=_token]').val()
                    },
                    dataType: 'json',
                    success: function(data) {
                        $('.current_balance').val(data);

                        if (data >= 2) {
                            $.toast({
                                heading: 'Warning',
                                text: 'You have already applied for ' + data +
                                    ' days!',
                                position: 'top-right',
                                loaderBg: '#ff6849',
                                icon: 'warning',
                                hideAfter: 3000,
                                stack: 6
                            });
                            $('body').find('#formSubmit').attr('disabled', true);
                        } else {
                            $('.current_balance').val(data);
                            $('body').find('#formSubmit').attr('disabled', false);
                        }
                    }
                });
            } else {
                $('body').find('#formSubmit').attr('disabled', true);
            }
        });
    });
</script>
@endsection
