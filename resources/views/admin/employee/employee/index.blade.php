@extends('admin.master')

@section('content')

@section('title')
    @lang('employee.employee_list')
@endsection


<div class="container-fluid">

    <div class="row bg-title">

        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">

            <ol class="breadcrumb">

                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>

                <li>@yield('title')</li>

            </ol>

        </div>

        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">

            <a href="{{ route('employee.create') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i
                    class="fa fa-plus-circle" aria-hidden="true"></i> @lang('employee.add_employee')</a>

        </div>

    </div>



    <div class="row">

        <div class="col-sm-12">

            <div class="panel panel-info">

                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')
                    <a href="{{ route('sync.t_usr') }}" class="pull-right fa fa-refresh btn btn-sm bg-white text-dark"
                        style="display: none">
                        Sync Employee </a>
                </div>

                <div class="panel-wrapper collapse in" aria-expanded="true">

                    <div class="panel-body">
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;
                                <strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i class="glyphicon glyphicon-remove"></i>&nbsp;
                                <strong>{{ session()->get('error') }}</strong>

                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger alert-block alert-dismissable">
                                <ul>
                                    <button type="button" class="close" data-dismiss="alert">x</button>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="border"
                            style="border: 1px solid #EFEEEF;border-radius:4px;margin:12px;padding:12px">
                            <a class="pull-right" href="{{ route('templates.employeeTemplate') }}">
                                <div id="template1" class="btn btn-info btn-sm template1" value="Template"
                                    type="submit">
                                    <i class="fa fa-download" aria-hidden="true"></i><span>
                                    Download Template</span>
                                </div>
                            </a>
                            <div class="row hidden-xs hidden-sm">
                                <p class="border" style="margin-left:18px">
                                    <span><i class="fa fa-upload"></i></span>
                                    <span style="margin-left: 4px"> Import employee info excel file.Default
                                        Password(demo1234)</span>
                                </p>
                                <form action="{{ route('employee.import') }}" method="post"
                                    enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="row">
                                        <div>
                                            <div class="col-md-4 text-right" style="margin-left:14px">
                                                <input type="file" name="select_file"
                                                    class="form-control custom-file-upload">
                                            </div>
                                            <div class="col-sm-1">
                                                <button class="btn btn-success btn-sm" type="submit"><span><i
                                                            class="fa fa-upload" aria-hidden="true"></i></span>
                                                    Upload</button>
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>

                        <br>

                        <div class="row">
                            <div class="pull-right" style="padding-right:32px;">
                                <a href="{{ route('employee.export') }}"> <button class="btn btn-success btn-sm"><span>
                                            <i class="fa fa-download" aria-hidden="true"></i>
                                        </span>Export Employee Details</button></a>
                            </div>
                        </div>

                        <div class="data" style="margin: 8px;padding:8px">

                            @include('admin.employee.employee.pagination')

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
    $(function() {

        $('.data').on('click', '.pagination a', function(e) {

            getData($(this).attr('href').split('page=')[1]);

            e.preventDefault();

        });





    });



    function getData(page) {

        var employee_name = $('.employee_name').val();

        var department_id = $('.department_id').val();

        var designation_id = $('.designation_id').val();

        var role_id = $('.role_id').val();




        $.ajax({

            url: '?page=' + page + "&employee_name=" + employee_name + "&department_id=" + department_id +
                "&designation_id=" + designation_id + "&role_id=" + role_id,
            datatype: "html",

        }).done(function(data) {

            $('.data').html(data);

            $("html, body").animate({
                scrollTop: 0
            }, 800);

        }).fail(function() {

            alert('No response from server');

        });

    }
</script>

<style>
    .bdColor {

        color: #8d9ea7;

    }

    #custom-search-input .search-query {

        padding-right: 3px;

        padding-right: 4px \9;

        padding-left: 3px;

        padding-left: 4px \9;

        /* IE7-8 doesn't have border-radius, so don't indent the padding */



        margin-bottom: 0;

        -webkit-border-radius: 3px;

        -moz-border-radius: 3px;

        border-radius: 3px;

    }



    #custom-search-input button {

        border: 0;

        background: none;

        /** belows styles are working good */

        padding: 2px 5px;

        margin-top: 2px;

        position: relative;

        left: -28px;

        /* IE7-8 doesn't have border-radius, so don't indent the padding */

        margin-bottom: 0;

        -webkit-border-radius: 3px;

        -moz-border-radius: 3px;

        border-radius: 3px;

        color: #ddd;

    }



    .search-query:focus+button {

        z-index: 3;

    }

    .panel-blue a,
    .panel-info a {

        color: black;

    }
</style>
@endsection
