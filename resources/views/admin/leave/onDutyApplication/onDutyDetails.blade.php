@extends('admin.master')
@section('content')
@section('title', 'Requested Application Details')
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i> Dashboard</a></li>
                <li>@yield('title')</li>

            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>Application Details</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="box-title">OnDuty Approval</h3>
                                <hr>
                                {{ Form::open(['route' => ['requestedOnDutyApplication.update', $leaveApplicationData->on_duty_id], 'method' => 'PUT', 'files' => 'true', 'id' => 'onDutyApproveOrRejectForm']) }}
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-4 ">From Date :</label>
                                    <p class="col-sm-8"><input type="text" readonly class="form-control"
                                            value="@if (isset($leaveApplicationData->application_date)) {{ dateConvertDBtoForm($leaveApplicationData->application_from_date) }} @endif">
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-4 ">To Date :</label>
                                    <p class="col-sm-8"><input type="text" readonly class="form-control"
                                            value="@if (isset($leaveApplicationData->application_to_date)) {{ dateConvertDBtoForm($leaveApplicationData->application_to_date) }} @endif">
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-4 ">Number of days :</label>
                                    <p class="col-sm-8"> <input type="text" class="form-control"
                                            value="@if (isset($leaveApplicationData->application_date)) {{ $leaveApplicationData->no_of_days }} @endif"
                                            readonly></p>
                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-4">Remarks :</label>
                                    <p class="col-sm-8">
                                        <textarea class="form-control" cols="10" rows="6" name="remarks" required placeholder="Enter remarks....."
                                            value="@if (isset($leaveApplicationData->remarks)) {{ $leaveApplicationData->remarks }} @endif"></textarea>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-4"></label>
                                    <p class="col-sm-8">
                                        <button type="submit" name="status" class="btn btn-info btn_style"
                                            value="2">Approve</button>
                                        <button type="submit" name="status" class="btn btn-danger btn_style"
                                            value="3"> Reject</button>
                                    </p>
                                </div>
                                {{ Form::close() }}

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
