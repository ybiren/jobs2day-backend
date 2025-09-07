
<div class="page-header">
    <div class="header-wrapper row m-0">
        <div class="header-logo-wrapper col-auto p-0">
            <div class="logo-wrapper"><a href="{{route('admin.business.users.list')}}">
                    <img class="img-fluid for-light" src="{{asset('logo/jobs2day_full-removebg-preview (1).png')}}" alt="">
                    <img class="img-fluid for-dark" src="{{asset('logo/jobs2day_full-removebg-preview (1).png')}}" alt=""></a></div>
            <div class="toggle-sidebar">
                <svg class="sidebar-toggle">
                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#stroke-animation"></use>
                </svg>
            </div>
        </div>
        <form class="col-sm-4 form-inline search-full d-none d-xl-block" action="#" method="get">
{{--            <div class="form-group">--}}
{{--                <div class="Typeahead Typeahead--twitterUsers">--}}
{{--                    <div class="u-posRelative">--}}
{{--                        <input disabled class="demo-input Typeahead-input form-control-plaintext w-100" type="text"--}}
{{--                               placeholder="Type to Search .." name="q" title="" autofocus="">--}}
{{--                        <svg class="search-bg svg-color">--}}
{{--                            <use href="{{asset('admin')}}/svg/icon-sprite.svg#search"></use>--}}
{{--                        </svg>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
        </form>
        <div class="nav-right col-xl-8 col-lg-12 col-auto pull-right right-header p-0">
            <ul class="nav-menus">
                <li class="serchinput">
                    <div class="serchbox">
                        <svg>
                            <use href="{{asset('admin')}}/svg/icon-sprite.svg#search"></use>
                        </svg>
                    </div>
                    <div class="form-group search-form">
                        <input type="text" placeholder="Search here...">
                    </div>
                </li>

                <li>
                    <div class="mode">
                        <svg class="for-dark">
                            <use href="{{asset('admin')}}/svg/icon-sprite.svg#moon"></use>
                        </svg>
                        <svg class="for-light">
                            <use href="{{asset('admin')}}/svg/icon-sprite.svg#Sun"></use>
                        </svg>
                    </div>
                </li>
                <li class="profile-nav onhover-dropdown pe-0 py-0">
                    <div class="d-flex align-items-center profile-media"><img class="b-r-25" src="{{asset('admin')}}/images/dashboard/profile.png" alt="Admin">
                        <div class="flex-grow-1 user"><span>Super Admin</span>
                            <p class="mb-0 font-nunito">Admin
                                <svg>
                                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#header-arrow-down"></use>
                                </svg>
                            </p>
                        </div>
                    </div>
                    <ul class="profile-dropdown onhover-show-div">
                        <li><a href=""><i data-feather="user"></i><span>Account </span></a></li>
                        <li><a href="{{route('admin.logout')}}"> <i data-feather="log-in"></i><span>Log Out</span></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
