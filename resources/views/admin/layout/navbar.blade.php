<!-- ============================================================== -->
<!-- Topbar header - style you can find in pages.scss -->
<!-- ============================================================== -->
@php
    use App\Model\Branch;
@endphp
<nav class="navbar navbar-default navbar-static-top">
    <div class="navbar-header">
        <!-- Search input and Toggle icon -->

        <ul class="nav navbar-top-links navbar-left">
            <li>
                <div class="logo-visiability hidden-xs" style="max-width: 240px;min-width:30px;padding:0 18px 0 8px">
                    <!-- Logo -->
                    <a class="logo" href="{{ url('dashboard') }}">
                        <!-- Logo icon image, you can use a font icon as well -->
                        <b>
                            <!-- This is a dark logo icon -->
                            <p style="object-fit: cover;" title="Home" class="dark-logo img-fluid img-responsive hidden">
                                Home
                            </p>
                            <img src="{{ url('admin_assets/img/nfe_logo.png') }}" alt="Home"
                                class="dark-logo img-fluid img-responsive hidden-xs"
                                style="width: 200px; height: 60px; padding-top:5px;" />
                            <!-- Set the width and height as per your requirements -->
                        </b>
                        <!-- Logo text image - you can use text here -->
                        <span class="hidden-xs">
                            <!-- This is dark logo text -->
                        </span>
                    </a>


                </div>
            </li>
            <li><a href="javascript:void(0)" class="open-close waves-effect waves-light menuIcon"><i
                        class="ti-menu tiMenu"></i></a>
            </li>

        </ul>
        {{-- @if (auth()->user()->role_id == 1)
            <ul class="nav navbar-nav hidden-xs">
                <li class="user user-menu dropdown">
                    <a href="#" id="authBranches" class="dropdown-toggle" data-toggle="dropdown">
                        <span id="branchText" style="font-weight:bold;">My Branches</span>
                    </a>

                    <ul class="dropdown-menu" id="authBranchLi" style="min-width: 200px;">
                        <li class="user-footer" style="background: #F2EEFC;">
                            <span class="pull-left"
                                style="background: #7E67B0; color: #F2EEFC; padding: 2px 8px; border-radius: 4px;">
                                List of Branches
                            </span>
                            <span class="pull-right" title="Clear"
                                style="cursor: pointer; background: #EFE8FD; border: 1px solid #FFFFFF; border-radius: 4px; color: red; padding: 1px 10px; font-size: 12px;">
                                <i class="fa fa-remove"></i>
                            </span>

                            @php
                                $branches = Branch::get();
                            @endphp

                            @if (auth()->user())
                                @foreach ($branches as $key => $branch)
                                    <a class="text-left"
                                        style="color: #7E67B0; background: #F2EEFC; display: block; padding: 10px;">
                                        <button data-branch-id="{{ $branch['branch_id'] }}"
                                            data-branch-name="{{ $branch['branch_name'] }}"
                                            style="background: #7E67B0; color: #F2EEFC; font-weight: normal; border-radius: 4px; border: none; text-align: left; padding: 5px 10px;">
                                            <i class="fa fa-arrow-right"></i>
                                            <span>{{ $key + 1 . '. ' }}</span>
                                            {{ $branch['branch_name'] }}
                                        </button>
                                    </a>
                                @endforeach
                            @endif
                        </li>
                    </ul>
                </li>
            </ul>
        @endif --}}



        <ul class="nav navbar-top-links navbar-right pull-right imageIcon">
            <li class="dropdown">
                <form role="search" class="app-search hidden-sm hidden-xs m-r-10" hidden>
                    <input type="text" placeholder="Search..." name="search" class="form-control">
                    <a href="{{ route('search') }}"><i class="fa fa-search"></i></a>
                </form>
            </li>
            <li class="dropdown">
                <?php
                    $employeeInfo = employeeInfo();
                    if($employeeInfo[0]->photo != ''){
                    ?>
                <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#">
                    <img src="{!! asset('uploads/employeePhoto/' . $employeeInfo[0]->photo) !!}" alt="user-img" width="36" height="34" class="img-custom">
                    <b class="hidden-xs " style="color: #000 !important;padding-right: 4px"><span
                            class="text-capitalize">{!! ucwords($employeeInfo[0]->user_name) !!}</span></b>
                    <span class="caret"></span>
                </a>
                <?php  }else{ ?>
                <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#">
                    <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user-img" width="36" height="34"
                        class="img-custom"><span style="color: #000 !important;"></span>
                    <b class="hidden-xs" style="color: #000 !important; padding-right: 4px"><span class="hideMenu"
                            style="color: #000 !important;">{!! ucwords($employeeInfo[0]->user_name) !!}</span></b>
                    <span class="caret hideMenu"></span>
                </a>
                <?php } ?>
                <ul class="dropdown-menu dropdown-user animated stripMove imageDropdown">
                    <li><a href="{{ url('profile') }}"><i class="ti-user"></i> @lang('common.my_profile')</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="{{ url('changePassword') }}"><i class="ti-settings"></i> @lang('common.change_password')</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="javascript:void(0);" onclick="logoutWithAjax()"><i class="fa fa-power-off"></i>
                            @lang('common.logout')</a></li>
                </ul>

            </li>



        </ul>
    </div>
</nav>
<script>
    function logoutWithAjax() {
        var actionTo = "{{ URL::to('/logout') }}";
        $.ajax({
            type: 'GET',
            url: actionTo,
            success: function(response) {
                $.toast({
                    heading: 'success',
                    text: 'Logout successfully!',
                    position: 'top-right',
                    loaderBg: '#ff6849',
                    icon: 'success',
                    hideAfter: 12000,
                    stack: 6
                });

                sessionStorage.clear();
                setTimeout(function() {
                    window.location.href = "{{ url('login') }}";
                }, 1000);

            }
        });
    }

    $(document).ready(function() {
        var defaultText = "My Branches";

        // var branch_name = sessionStorage.getItem("branch_name");
        var branch_name = "{{ session()->get('branch_name') }}";
        if (branch_name != null) {
            $('#branchText').text(branch_name);
        } else {
            $('#branchText').text(defaultText);
        }

    });


    document.addEventListener('DOMContentLoaded', function() {
        const branchButtons = document.querySelectorAll('[data-branch-id]');
        branchButtons.forEach(button => {
            button.addEventListener('click', function() {
                var branchId = button.getAttribute('data-branch-id');
                var actionTo = "{{ URL::to('/store-branch') }}";

                $.ajax({
                    type: 'POST',
                    url: actionTo,
                    data: {
                        branch_id: branchId,
                    },
                    success: function(response) {

                        location.reload();
                        branch_name = button.getAttribute('data-branch-name');



                        $('#branchText').text(response.name);
                    },
                    error: function(error) {
                        console.error('Error storing branch and role IDs:', error);
                    }
                });
            });
        });
    });
</script>
