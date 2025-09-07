
<div class="sidebar-wrapper" data-layout="stroke-svg">
    <div>
        <div class="logo-wrapper"><a href="{{route('admin.dashboard')}}"> <img class="img-fluid for-light admin-logo" src="{{asset('logo/jobs2day_full-removebg-preview (1).png')}}
                    " alt=""  style="max-width: 155px;"><img class="img-fluid for-dark admin-logo" src="{{asset('logo/jobs2day_full-removebg-preview (1).png')}}
                    " alt=""  style="max-width: 155px;"></a>
            <div class="toggle-sidebar">
                <svg class="sidebar-toggle">
                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#toggle-icon"></use>
                </svg>
            </div>
        </div>
        <div class="logo-icon-wrapper"><a href="{{route('admin.dashboard')}}"><img class="img-fluid admin-logo" src="{{asset('logo/jobs2day_full-removebg-preview (1).png')}}
                    " alt=""></a></div>
        <nav class="sidebar-main">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                    <li class="back-btn"><a href="{{route('admin.dashboard')}}"><img class="img-fluid admin-logo" src="{{asset('logo/jobs2day_full-removebg-preview (1).png')}}
                                " alt=""></a>
                        <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                    </li>
                    <li class="pin-title sidebar-main-title">
                        <div>
                            <h6>Pinned</h6>
                        </div>
                    </li>
{{--                    <li class="sidebar-main-title">--}}
{{--                        <div>--}}
{{--                            <h6 class="lan-1">General</h6>--}}
{{--                        </div>--}}
{{--                    </li>--}}
{{--                    <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>--}}
{{--                        <a class="sidebar-link sidebar-title link-nav" href="{{route('admin.users.list')}}">--}}
{{--                            <svg class="stroke-icon">--}}
{{--                                <use href="{{asset('admin')}}/svg/icon-sprite.svg#stroke-home"></use>--}}
{{--                            </svg>--}}
{{--                            <svg class="fill-icon">--}}
{{--                                <use href="{{asset('admin')}}/svg/icon-sprite.svg#fill-home"></use>--}}
{{--                            </svg><span class="lan-3">Dashboard</span></a>--}}

{{--                    </li>--}}
{{--                    <li class="sidebar-list "><i class="fa fa-thumb-tack"></i>--}}
{{--                        <a class="sidebar-link sidebar-title link-nav" href="{{route('admin.hearderinfo')}}">--}}
{{--                            <svg class="stroke-icon">--}}
{{--                                <use href="{{asset('admin')}}/svg/icon-sprite.svg#stroke-animation"></use>--}}
{{--                            </svg>--}}
{{--                            <svg class="fill-icon">--}}
{{--                                <use href="{{asset('admin')}}/svg/icon-sprite.svg#fill-animation"></use>--}}
{{--                            </svg><span>Site Info</span>--}}
{{--                        </a>--}}
{{--                    </li>--}}


                    <li class="sidebar-main-title">
                        <div>
                            <h6>Components</h6>
                        </div>
                    </li>



                    <li class="sidebar-main-title">
                        <div>
                            <h6>Management</h6>
                        </div>
                    </li>
                    <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                            <svg class="stroke-icon">
                                <use href="{{asset('admin')}}/svg/icon-sprite.svg#stroke-widget"></use>
                            </svg>
                            <svg class="fill-icon">
                                <use href="{{asset('admin')}}/svg/icon-sprite.svg#fill-widget"></use>
                            </svg><span >Payments</span></a>
                        <ul class="sidebar-submenu">
                            <li><a href="{{route('admin.payments.post.list')}}">Jobs Payments</a></li>
                            <li><a href="{{route('admin.payments.post.list')}}">Closed</a></li>
                        </ul>
                    </li>
                    <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                            <svg class="stroke-icon">
                                <use href="{{asset('admin')}}/svg/icon-sprite.svg#stroke-widget"></use>
                            </svg>
                            <svg class="fill-icon">
                                <use href="{{asset('admin')}}/svg/icon-sprite.svg#fill-widget"></use>
                            </svg><span >Users</span></a>
                        <ul class="sidebar-submenu">
                            <li><a href="{{route('admin.business.users.list')}}">Business</a></li>
                            <li><a href="{{route('admin.candidate.users.list')}}">Candidates</a></li>
                        </ul>
                    </li>
                    <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                            <svg class="stroke-icon">
                                <use href="{{asset('admin')}}/svg/icon-sprite.svg#stroke-widget"></use>
                            </svg>
                            <svg class="fill-icon">
                                <use href="{{asset('admin')}}/svg/icon-sprite.svg#fill-widget"></use>
                            </svg><span >Reports</span></a>
                        <ul class="sidebar-submenu">
                            <li><a href="{{route('admin.reports.list')}}">List</a></li>
                        </ul>
                    </li>


                </ul>
            </div>
            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </nav>
    </div>
</div>
