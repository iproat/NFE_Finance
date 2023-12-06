@extends('admin.master')
@section('content')
@section('title')
@lang('dashboard.dashboard')
@endsection
<style>
    .dash_image {
        width: 60px;
    }

    .my-custom-scrollbar {
        position: relative;
        height: 280px;
        overflow: auto;
    }

    .table-wrapper-scroll-y {
        display: block;
    }

    tbody {
        display: block;
        height: 300px;
        overflow: auto;
    }

    thead,
    tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    thead {
        width: calc(100%);
    }


    .leaveApplication {
        overflow-x: hidden;
        height: 210px;
    }

    .noticeBord {
        overflow-x: hidden;
        height: 210px;
    }

    .preloader {
        position: fixed;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        z-index: 9999;

        opacity: .8;
    }


    .scroll-hide {
        -ms-overflow-style: none;

        scrollbar-width: none;

    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a>
                </li>
            </ol>
        </div>
    </div>

    <!-- Upcoming Birthdays -->
    @if (count($upcoming_birtday) > 0)
    <div class="col-md-6">
        <div class="white-box">
            <h3 class="box-title">@lang('dashboard.upcoming_birthday')</h3>
            <hr>
            <div class="leaveApplication">
                @foreach ($upcoming_birtday as $employee_birthdate)
                <div class="comment-center p-t-10">
                    <div class="comment-body">
                        @if ($employee_birthdate->photo != '')
                        <div class="user-img"> <img height="40" width="40" src="{!! asset('uploads/employeePhoto/' . $employee_birthdate->photo) !!}" alt="user" class="img-circle">
                        </div>
                        @else
                        <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user" class="img-circle"></div>
                        @endif
                        <div class="mail-contnet">

                            @php
                            $date_of_birth = $employee_birthdate->date_of_birth;
                            $separate_date = explode('-', $date_of_birth);

                            $date_current_year = date('Y') . '-' . $separate_date[1] . '-' . $separate_date[2];

                            $create_date = date_create($date_current_year);
                            @endphp

                            <h5>{{ $employee_birthdate->first_name }}
                                {{ $employee_birthdate->last_name }}
                            </h5><span class="time">{{ date_format(date_create($employee_birthdate->date_of_birth), 'D dS F Y') }}</span>
                            <br />

                            <span class="mail-desc">
                                @if ($date_current_year == date('Y-m-d'))
                                <b>Today is
                                    @if ($employee_birthdate->gender == 'Male')
                                    His
                                    @else
                                    Her
                                    @endif
                                    Birthday Wish
                                    @if ($employee_birthdate->gender == 'Male')
                                    Him
                                    @else
                                    Her
                                    @endif
                                </b>
                                @else
                                Wish
                                @if ($employee_birthdate->gender == 'Male')
                                Him
                                @else
                                Her
                                @endif
                                on {{ date_format($create_date, 'D dS F Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Leave Applications -->
    @if (count($leaveApplication) > 0)
    <div class="col-md-6">
        <div class="white-box">
            <h3 class="box-title">@lang('dashboard.recent_leave_application')</h3>
            <hr>
            <div class="leaveApplication">
                @foreach ($leaveApplication as $leaveApplication)
                <div class="comment-center p-t-10 {{ $leaveApplication->leave_application_id }}">
                    <div class="comment-body">
                        @if ($leaveApplication->employee->photo != '')
                        <div class="user-img"> <img height="40" width="40" src="{!! asset('uploads/employeePhoto/' . $leaveApplication->employee->photo) !!}" alt="user" class="img-circle">
                        </div>
                        @else
                        <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user" class="img-circle"></div>
                        @endif
                        <div class="mail-contnet">
                            @php
                            $d = strtotime($leaveApplication->created_at);
                            @endphp
                            <h5>{{ $leaveApplication->employee->first_name }}
                                {{ $leaveApplication->employee->last_name }}
                            </h5><span class="time">{{ date('d M Y h:i: a', $d) }}</span>
                            <span class="label label-rouded label-info">PENDING</span>
                            <br /><span class="mail-desc" style="max-height: none">
                                @lang('leave.leave_type') :
                                {{ $leaveApplication->leaveType->leave_type_name }}<br>
                                @lang('leave.request_duration') :
                                {{ dateConvertDBtoForm($leaveApplication->application_from_date) }}
                                To
                                {{ dateConvertDBtoForm($leaveApplication->application_to_date) }}<br>
                                @lang('leave.number_of_day') : {{ $leaveApplication->number_of_day }}
                                <br>
                                @lang('leave.purpose') : {{ $leaveApplication->purpose }}
                            </span>

                            <a href="javacript:void(0)" data-status=2 data-leave_application_id="{{ $leaveApplication->leave_application_id }}" class="btn remarksForManagerLeave btn btn-rounded btn-success btn-outline m-r-5"><i class="ti-check text-success m-r-5"></i>@lang('common.approve')</a>
                            <a href="javacript:void(0)" data-status=3 data-leave_application_id="{{ $leaveApplication->leave_application_id }}" class="btn-rounded remarksForManagerLeave btn btn-danger btn-outline"><i class="ti-close text-danger m-r-5"></i>@lang('common.reject')</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Permission Applications -->
    @if (count($permissionApplication) > 0)
    <div class="col-md-6">
        <div class="white-box">
            <h3 class="box-title">@lang('dashboard.recent_permission_application')</h3>
            <hr>
            <div class="permissionApplication" style="max-height: 300px; overflow-y: auto;">
                @foreach ($permissionApplication as $permissionApplication)
                <div class="comment-center p-t-10 {{ $permissionApplication->leave_permission_id }}">
                    <div class="comment-body">
                        @if ($permissionApplication->employee->photo != '')
                        <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $permissionApplication->employee->photo) !!}" alt="user" class="img-circle"></div>
                        @else
                        <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user" class="img-circle"></div>
                        @endif
                        <div class="mail-contnet">
                            @php
                            $d = strtotime($permissionApplication->created_at);
                            @endphp
                            <h5>{{ $permissionApplication->employee->first_name }}
                                {{ $permissionApplication->employee->last_name }}
                            </h5><span class="time">{{ date('d M Y h:i: a', $d) }}</span>
                            <span class="label label-rouded label-info">PENDING</span>
                            <br /><span class="mail-desc" style="max-height: none">

                                @lang('leave.request_duration') :
                                {{ $permissionApplication->from_time }}
                                To
                                {{ $permissionApplication->to_time }}<br>
                                @lang('leave.total_duration') :
                                {{ $permissionApplication->permission_duration }}
                                <br>
                                @lang('leave.purpose') :
                                {{ $permissionApplication->leave_permission_purpose }}
                            </span>

                            <a href="javacript:void(0)" data-status=2 data-leave_permission_id="{{ $permissionApplication->leave_permission_id }}" class="btn remarksForManagerPermission btn btn-rounded btn-success btn-outline m-r-5"><i class="ti-check text-success m-r-5"></i>@lang('common.approve')</a>
                            <a href="javacript:void(0)" data-status=3 data-leave_permission_id="{{ $permissionApplication->leave_permission_id }}" class="btn-rounded remarksForManagerPermission btn btn-danger btn-outline"><i class="ti-close text-danger m-r-5"></i>@lang('common.reject')</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Recent On-Duty Applications -->
    @if (count($ondutyApplication) > 0)
    <div class="col-md-6">
        <div class="white-box">
            <h3 class="box-title">@lang('dashboard.recent_onduty_application')</h3>
            <hr>
            <div class="OnDutyApplication" style="max-height: 260px; overflow-y: auto;">
                @foreach ($ondutyApplication as $ondutyApplication)
                <div class="comment-center p-t-10 {{ $ondutyApplication->on_duty_id }}">
                    <div class="comment-body">
                        @if ($ondutyApplication->employee->photo != '')
                        <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $ondutyApplication->employee->photo) !!}" alt="user" class="img-circle"></div>
                        @else
                        <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user" class="img-circle"></div>
                        @endif
                        <div class="mail-contnet">
                            @php
                            $d = strtotime($ondutyApplication->created_at);
                            @endphp
                            <h5>{{ $ondutyApplication->employee->first_name }}
                                {{ $ondutyApplication->employee->last_name }}
                            </h5><span class="time">{{ date('d M Y h:i: a', $d) }}</span>
                            <span class="label label-rouded label-info">PENDING</span>
                            <br /><span class="mail-desc" style="max-height: none">

                                @lang('leave.request_duration') :
                                {{ dateConvertDBtoForm($ondutyApplication->application_from_date) }}
                                To
                                {{ dateConvertDBtoForm($ondutyApplication->application_to_date) }}<br>
                                @lang('leave.number_of_day') : {{ $ondutyApplication->no_of_days }}
                                <br>
                                @lang('leave.purpose') : {{ $ondutyApplication->purpose }}
                            </span>

                            <a href="javacript:void(0)" data-status=2 data-on_duty_id="{{ $ondutyApplication->on_duty_id }}" class="btn remarksForManagerOnDuty btn btn-rounded btn-success btn-outline m-r-5"><i class="ti-check text-success m-r-5"></i>@lang('common.approve')</a>
                            <a href="javacript:void(0)" data-status=3 data-on_duty_id="{{ $ondutyApplication->on_duty_id }}" class="btn-rounded remarksForManagerOnDuty btn btn-danger btn-outline"><i class="ti-close text-danger m-r-5"></i>@lang('common.reject')</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

@endsection


@section('page_scripts')
<script type="text/javascript">
    document.onreadystatechange = function() {
        switch (document.readyState) {
            case "loading":
                window.documentLoading = true;
                break;
            case "complete":
                window.documentLoading = false;
                break;
            default:
                window.documentLoading = false;
        }
    }

    function loading($bool) {
        if ($bool == true) {
            $.toast({
                heading: 'success',
                text: 'Processing Please Wait !',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'success',
                hideAfter: 3000,
                stack: 1
            });
            window.setTimeout(function() {
                location.reload()
            }, 3000);
        }
        $("#preloaders").fadeOut(1000);
    }
</script>

<link href="{!! asset('admin_assets/plugins/bower_components/news-Ticker-Plugin/css/site.css') !!}" rel="stylesheet" type="text/css" />
<script src="{!! asset('admin_assets/plugins/bower_components/news-Ticker-Plugin/scripts/jquery.bootstrap.newsbox.min.js') !!}"></script>
<script type="text/javascript">
    $(document).on('click', '.remarksForLeave', function() {

        var actionTo = "{{ URL::to('approveOrRejectManagerLeaveApplication') }}";
        var leave_application_id = $(this).attr('data-leave_application_id');
        var status = $(this).attr('data-status');

        if (status == 2) {
            var statusText = "Are you want to approve leave application?";
            var btnColor = "#2cabe3";
        } else {
            var statusText = "Are you want to reject leave application?";
            var btnColor = "red";
        }

        swal({
                title: "",
                text: statusText,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: btnColor,
                confirmButtonText: "Yes",
                closeOnConfirm: false
            },
            function(isConfirm) {
                var token = '{{ csrf_token() }}';
                if (isConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: actionTo,
                        data: {
                            leave_application_id: leave_application_id,
                            status: status,
                            _token: token
                        },
                        success: function(data) {
                            if (data == 'approve') {
                                swal({
                                        title: "Approved!",
                                        text: "Leave application approved.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_application_id).fadeOut();
                                        }
                                    });

                            } else {
                                swal({
                                        title: "Rejected!",
                                        text: "Leave application rejected.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_application_id).fadeOut();
                                        }
                                    });
                            }
                        }

                    });
                } else {
                    swal("Cancelled", "Your data is safe .", "error");
                }
            });
        return false;

    });
    $(document).on('click', '.remarksForManagerPermission', function() {

        var actionTo = "{{ URL::to('approveOrRejectManagerPermissionApplication') }}";
        var leave_permission_id = $(this).attr('data-leave_permission_id');
        var status = $(this).attr('data-status');

        if (status == 2) {
            var statusText = "Are you want to approve Permission application?";
            var btnColor = "#2cabe3";
        } else {
            var statusText = "Are you want to reject Permission application?";
            var btnColor = "red";
        }

        swal({
                title: "",
                text: statusText,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: btnColor,
                confirmButtonText: "Yes",
                closeOnConfirm: false
            },
            function(isConfirm) {
                var token = '{{ csrf_token() }}';
                if (isConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: actionTo,
                        data: {
                            leave_permission_id: leave_permission_id,
                            status: status,
                            _token: token
                        },
                        success: function(data) {
                            if (data == 'approve') {
                                swal({
                                        title: "Approved!",
                                        text: "Permission application approved.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_permission_id).fadeOut();
                                        }
                                    });

                            } else {
                                swal({
                                        title: "Rejected!",
                                        text: "Permission application rejected.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_permission_id).fadeOut();
                                        }
                                    });
                            }
                        }

                    });
                } else {
                    swal("Cancelled", "Your data is safe .", "error");
                }
            });
        return false;

    });
    $(document).on('click', '.remarksForManagerOnDuty', function() {

        var actionTo = "{{ URL::to('approveOrRejectManagerOnDutyApplication') }}";
        var on_duty_id = $(this).attr('data-on_duty_id');
        var status = $(this).attr('data-status');

        if (status == 2) {
            var statusText = "Are you want to approve OnDuty application?";
            var btnColor = "#2cabe3";
        } else {
            var statusText = "Are you want to reject OnDuty application?";
            var btnColor = "red";
        }

        swal({
                title: "",
                text: statusText,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: btnColor,
                confirmButtonText: "Yes",
                closeOnConfirm: false
            },
            function(isConfirm) {
                var token = '{{ csrf_token() }}';
                if (isConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: actionTo,
                        data: {
                            on_duty_id: on_duty_id,
                            status: status,
                            _token: token
                        },
                        success: function(data) {
                            if (data == 'approve') {
                                swal({
                                        title: "Approved!",
                                        text: "OnDuty application approved.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + on_duty_id).fadeOut();
                                        }
                                    });

                            } else {
                                swal({
                                        title: "Rejected!",
                                        text: "OnDuty application rejected.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + on_duty_id).fadeOut();
                                        }
                                    });
                            }
                        }

                    });
                } else {
                    swal("Cancelled", "Your data is safe .", "error");
                }
            });
        return false;

    });
</script>



@endsection