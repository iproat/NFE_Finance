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
                        <!-- Logo icon image, you can use font-icon also --><b>
                            <!--This is dark logo icon-->
                            <img style="width: 200px;min-width:30px;height:60px;" src="{!! asset('admin_assets/img/nfe_logo.png') !!}"
                                alt="Home" class="dark-logo img-fluid img-responsive hidden-xs" />
                        </b>
                        <!-- Logo text image you can use text also --><span class="hidden-xs">
                            <!--This is dark logo text-->
                        </span>
                    </a>


                </div>
            </li>
            <li><a href="javascript:void(0)" class="open-close waves-effect waves-light menuIcon"><i
                        class="ti-menu tiMenu"></i></a>
            </li>

        </ul>




        <ul class="nav navbar-top-links navbar-right pull-right imageIcon">
            <li class="dropdown">
                <?php
                $employeeInfo = employeeInfo();
                $photoSrc = $employeeInfo[0]->photo ? asset('uploads/employeePhoto/' . $employeeInfo[0]->photo) : asset('admin_assets/img/default.png');
                ?>
                <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#">
                    <img src="{!! $photoSrc !!}" alt="user-img" width="36" height="34" class="img-custom">
                    <span class="hidden-xs" style="color: #fff !important; padding-right: 4px">
                        <span class="text-capitalize">{!! ucwords($employeeInfo[0]->user_name) !!}</span>
                    </span>
                    <span class="caret"></span>
                </a>
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
