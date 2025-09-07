@extends('admin.layouts.app')
@section('contents')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-xl-4 col-sm-7 box-col-3">
                    <h3>Default Dashboard</h3>
                </div>

                <div class="col-xl-12 col-sm-12 box-col-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">
                                <svg class="stroke-icon">
                                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#stroke-home"></use>
                                </svg></a>
                        </li>
                        <li class="breadcrumb-item">Dashboard</li>
                        <li class="breadcrumb-item active">Default</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- Container-fluid starts-->
    <div class="container-fluid default-dashboard">
        <div class="row">
            <div class="col-xl-12 col-md-12 box-col-12 proorder-md-4">
                <div class="card">
                    <div class="card-header card-no-border">
                        <div class="header-top">
                            <h4>Received Payments </h4>
                            <div class="dropdown icon-dropdown setting-menu">
                                <button class="btn dropdown-toggle" id="userdropdown3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg>
                                        <use href="{{asset('admin')}}/svg/icon-sprite.svg#setting"></use>
                                    </svg>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdown3"><a class="dropdown-item" href="#">Weekly</a><a class="dropdown-item" href="#">Monthly </a><a class="dropdown-item" href="#">Yearly</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive custom-scrollbar">
                            <table class="table" id="last-orders">
                                <thead>
                                <tr>
                                    <th>Post</th>
                                    <th>Job Business</th>
                                    <th>Job Candidate</th>
                                    <th>Paid Amount</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($payments as $itom)
                                <tr>
                                    <td>
                                        <div class="user-data">
                                            <div>
                                                @if($itom->jobApplication?->post?->id)
                                                    <a href="{{ route('admin.post.view', ['id' => $itom->jobApplication->post->id]) }}">
                                                        <h4>{{ $itom->jobApplication->post->job_role }}</h4>
                                                    </a>
                                                @else
                                                    <h4>N/A</h4>
                                                @endif
                                            </div>

                                        </div>
                                    </td>
                                    <td>
                                        @if($itom->jobApplication?->post?->user?->id)
                                            <a href="{{ route('admin.users.show', ['user' => $itom->jobApplication->post?->user->id]) }}">
                                                {{ $itom->jobApplication->post?->user->first_name }}
                                            </a>
                                        @else
                                            {{ $itom->jobApplication?->post?->user_id ?? 'N/A' }}
                                        @endif
                                    </td>                                    <td>
                                        @if($itom->jobApplication?->user?->id)
                                            <a href="{{ route('admin.users.show', ['user' => $itom->jobApplication->user->id]) }}">
                                                {{ $itom->jobApplication->user->first_name }}
                                            </a>
                                        @else
                                            {{ $itom->jobApplication?->user_id ?? 'N/A' }}
                                        @endif
                                    </td>

                                    <td>{{$itom->amount ?? 'null'}}</td>
                                    <td>{{ $itom->created_at ? $itom->created_at->format('d M Y') : 'N/A' }}</td>
                                    <td>
                                            <button type="button" class="btn btn-primary-gradien btn-sm">View</button>
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 box-col-7 proorder-md-1">
                <div class="card">
                    <div class="card-body premium-card">
                        <div class="row premium-courses-card">
                            <div class="col-md-5 premium-course">
                                <h1 class="f-w-700">Get Thousands Premium Courses.</h1><span class="f-light f-w-400 f-14">Upgrade to watch, play, learn, make and discover. </span><a class="btn btn-square btn-primary f-w-700" href="pricing.html.htm">Go Premium</a>
                            </div>
                            <div class="col-md-7 premium-course-img">
                                <div class="premium-message"><img class="img-fluid" src="{{asset('admin')}}/images/dashboard/massage.gif" alt="massage"></div>
                                <div class="premium-books"><img class="img-fluid" src="{{asset('admin')}}/images/dashboard/books.gif" alt="books"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 box-col-5 col-md-6 proorder-md-2">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="header-top">
                            <h4>Recent Activity</h4>
                            <div class="dropdown icon-dropdown setting-menu">
                                <button class="btn dropdown-toggle" id="userdropdown1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg>
                                        <use href="{{asset('admin')}}/svg/icon-sprite.svg#setting"></use>
                                    </svg>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdown1"><a class="dropdown-item" href="#">Weekly </a><a class="dropdown-item" href="#">Monthly </a><a class="dropdown-item" href="#">Yearly </a></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="activity-day">
                            <h6>Today</h6>
                        </div>
                        <div class="recent-activity-card">
                            <ul>
                                <li class="activity-removed">
                                    <div class="recent-activity-data">
                                        <div class="activity-name"><span>
                                <svg>
                                  <use href="{{asset('admin')}}/svg/icon-sprite.svg#Chat"> </use>
                                </svg></span><a href="letter-box.html.htm">Nick Mark mentioned Sara</a></div>
                                        <div class="view-btn"><a href="#!">View
                                                <svg>
                                                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#Arrow-Right-2"></use>
                                                </svg></a></div>
                                    </div>
                                </li>
                                <li>
                                    <div class="recent-activity-data">
                                        <div class="activity-name"> <span>
                                <svg>
                                  <use href="{{asset('admin')}}/svg/icon-sprite.svg#Download"> </use>
                                </svg></span><a href="letter-box.html.htm">The Post name was removed...</a></div>
                                        <div class="view-btn"><a href="#!">View
                                                <svg>
                                                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#Arrow-Right-2"></use>
                                                </svg></a></div>
                                    </div>
                                </li>
                                <li>
                                    <div class="recent-activity-data">
                                        <div class="activity-name"><span>
                                <svg>
                                  <use href="{{asset('admin')}}/svg/icon-sprite.svg#Swap"> </use>
                                </svg></span><a href="letter-box.html.htm">Patrick Sullivan published... </a></div>
                                        <div class="view-btn"><a href="#!">View
                                                <svg>
                                                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#Arrow-Right-2"></use>
                                                </svg></a></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="activity-day mt-4">
                            <h6>Yesterday</h6>
                        </div>
                        <div class="recent-activity-card">
                            <ul>
                                <li>
                                    <div class="recent-activity-data">
                                        <div class="activity-name"><span>
                                <svg>
                                  <use href="{{asset('admin')}}/svg/icon-sprite.svg#Send"> </use>
                                </svg></span><a href="letter-box.html.htm">Nick Mark mentioned Sara</a></div>
                                        <div class="view-btn"><a href="#!">View
                                                <svg>
                                                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#Arrow-Right-2"></use>
                                                </svg></a></div>
                                    </div>
                                </li>
                                <li>
                                    <div class="recent-activity-data">
                                        <div class="activity-name"><span>
                                <svg>
                                  <use href="{{asset('admin')}}/svg/icon-sprite.svg#Work"> </use>
                                </svg></span><a href="letter-box.html.htm">The Post name was removed...</a></div>
                                        <div class="view-btn"> <a href="#!">View
                                                <svg>
                                                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#Arrow-Right-2"></use>
                                                </svg></a></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 proorder-md-3 box-col-6">
                <div class="card">
                    <div class="card-header custom-border-bottom">
                        <div class="header-top">
                            <h4>Sales Stats</h4>
                            <div class="dropdown icon-dropdown setting-menu">
                                <button class="btn dropdown-toggle" id="userdropdown2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg>
                                        <use href="{{asset('admin')}}/svg/icon-sprite.svg#setting"> </use>
                                    </svg>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdown2"><a class="dropdown-item" href="#">Weekly</a><a class="dropdown-item" href="#">Monthly </a><a class="dropdown-item" href="#">Yearly </a></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div id="salesStatsRadialChart"> </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-6 proorder-md-5">
                <div class="card latest-sales">
                    <div class="card-header d-flex justify-content-between align-items-center custom-border-bottom">
                        <h4>Latest Sales</h4>
                        <div class="d-flex align-items-center justify-content-center latest-sales-btn">
                            <button class="btn btn-square btn-outline-primary btn-sm" type="button" title="btn btn-square btn-outline-primary btn-sm">Yearly</button>
                            <button class="btn btn-square btn-outline-primary btn-sm" type="button" title="btn btn-square btn-outline-primary btn-sm">Monthly</button>
                            <button class="btn btn-square btn-outline-primary btn-sm active" type="button" title="btn btn-square btn-outline-primary btn-sm">Weekly</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="lastOrdersChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-12 box-col-12 proorder-md-4">
                <div class="card">
                    <div class="card-header card-no-border">
                        <div class="header-top">
                            <h4>Last Orders </h4>
                            <div class="dropdown icon-dropdown setting-menu">
                                <button class="btn dropdown-toggle" id="userdropdown3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg>
                                        <use href="{{asset('admin')}}/svg/icon-sprite.svg#setting"></use>
                                    </svg>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdown3"><a class="dropdown-item" href="#">Weekly</a><a class="dropdown-item" href="#">Monthly </a><a class="dropdown-item" href="#">Yearly</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive custom-scrollbar">
                            <table class="last-orders-table table" id="last-orders">
                                <thead>
                                <tr>
                                    <th>Name </th>
                                    <th>Order No.  </th>
                                    <th>Amount</th>
                                    <th>Payment Type </th>
                                    <th>Date</th>
                                    <th>Action </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <div class="user-data">
                                            <div><img src="{{asset('admin')}}/images/dashboard/avtar/2.jpg" alt="avatar"></div>
                                            <div> <a href="user-profile.html.htm">
                                                    <h4>Dmitriy Groshev</h4></a><span>Switzerland</span></div>
                                        </div>
                                    </td>
                                    <td>#790841</td>
                                    <td>$2.499</td>
                                    <td>Credit Card</td>
                                    <td>1 Oct, 14:43</td>
                                    <td>
                                        <div class="dropdown icon-dropdown">
                                            <button class="btn dropdown-toggle" id="userdropdownes4" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <div class="drop-menu"><i class="icon-more-alt"></i></div>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdownes4"><a class="dropdown-item" href="#">Weekly</a><a class="dropdown-item" href="#">Monthly</a><a class="dropdown-item" href="#">Yearly</a></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="user-data">
                                            <div><img src="{{asset('admin')}}/images/dashboard/avtar/17.jpg" alt="avatar"></div>
                                            <div><a href="user-profile.html.htm">
                                                    <h4>Patrick Beverley</h4></a><span> Germany</span></div>
                                        </div>
                                    </td>
                                    <td>#454489</td>
                                    <td>$2.499</td>
                                    <td>Paypal</td>
                                    <td>30 Sep, 23:01</td>
                                    <td>
                                        <div class="dropdown icon-dropdown">
                                            <button class="btn dropdown-toggle" id="userdropdownes1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <div class="drop-menu"><i class="icon-more-alt"></i></div>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdownes1"><a class="dropdown-item" href="#">Weekly</a><a class="dropdown-item" href="#">Monthly</a><a class="dropdown-item" href="#">Yearly</a></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="user-data">
                                            <div><img src="{{asset('admin')}}/images/avtar/18.jpg" alt="avatar"></div>
                                            <div><a href="user-profile.html.htm">
                                                    <h4>Kevin Greem</h4></a><span> Canada</span></div>
                                        </div>
                                    </td>
                                    <td>#594579</td>
                                    <td>$2.499</td>
                                    <td>Credit Card</td>
                                    <td>29 Sep,09:31</td>
                                    <td>
                                        <div class="dropdown icon-dropdown">
                                            <button class="btn dropdown-toggle" id="userdropdownes2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <div class="drop-menu"><i class="icon-more-alt"></i></div>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdownes2"><a class="dropdown-item" href="#">Weekly</a><a class="dropdown-item" href="#">Monthly</a><a class="dropdown-item" href="#">Yearly</a></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="user-data">
                                            <div><img src="{{asset('admin')}}/images/dashboard/avtar/19.jpg" alt="avatar"></div>
                                            <div><a href="user-profile.html.htm">
                                                    <h4>William Barton</h4></a><span>United States</span></div>
                                        </div>
                                    </td>
                                    <td>#478495</td>
                                    <td>$2.499</td>
                                    <td>Credit Card</td>
                                    <td>28 Sep, 04:34</td>
                                    <td>
                                        <div class="dropdown icon-dropdown">
                                            <button class="btn dropdown-toggle" id="userdropdownes3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <div class="drop-menu"><i class="icon-more-alt"></i></div>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdownes3"><a class="dropdown-item" href="#">Weekly</a><a class="dropdown-item" href="#">Monthly</a><a class="dropdown-item" href="#">Yearly      </a></div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 proorder-md-6 box-col-6">
                <div class="card">
                    <div class="card-header">
                        <div class="header-top">
                            <h4>Monthly Invoices</h4>
                            <div class="dropdown icon-dropdown setting-menu">
                                <button class="btn dropdown-toggle" id="userdropdown4" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg>
                                        <use href="{{asset('admin')}}/svg/icon-sprite.svg#setting"> </use>
                                    </svg>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdown4"><a class="dropdown-item" href="#">Weekly</a><a class="dropdown-item" href="#">Monthly</a><a class="dropdown-item" href="#">Yearly</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="monthly-invoices">
                            <ul>
                                <li>
                                    <div class="monthly-data">
                                        <div class="invoice-data-icon"><span>
                                <svg>
                                  <use href="{{asset('admin')}}/svg/icon-sprite.svg#bookmark"></use>
                                </svg></span>
                                            <div><a href="invoice-1.html.htm">
                                                    <h4>Camelun ios Development</h4></a>
                                                <h5>20 paid month out of 24</h5>
                                            </div>
                                        </div>
                                        <div class="invoice-price"><span>$12,453.00</span><span>Per Month</span></div>
                                    </div>
                                </li>
                                <li>
                                    <div class="monthly-data">
                                        <div class="invoice-data-icon"><span>
                                <svg>
                                  <use href="{{asset('admin')}}/svg/icon-sprite.svg#Ticket-Star"></use>
                                </svg></span>
                                            <div><a href="">
                                                    <h4>ReactDT Motions</h4></a>
                                                <h5>Late payment  12 week - pay invoice</h5>
                                            </div>
                                        </div>
                                        <div class="invoice-price"><span>$54,459.00</span><span>Per month</span></div>
                                    </div>
                                </li>
                                <li>
                                    <div class="monthly-data">
                                        <div class="invoice-data-icon"><span>
                                <svg>
                                  <use href="{{asset('admin')}}/svg/icon-sprite.svg#Discovery"></use>
                                </svg></span>
                                            <div><a href="invoice-2.html.htm">
                                                    <h4>Arocca Store</h4></a>
                                                <h5>Error sending payment - Detail information</h5>
                                            </div>
                                        </div>
                                        <div class="invoice-price"><span>$87,884.00</span><span>Per month</span></div>
                                    </div>
                                </li>
                                <li>
                                    <div class="monthly-data">
                                        <div class="invoice-data-icon"><span>
                                <svg>
                                  <use href="{{asset('admin')}}/svg/icon-sprite.svg#Shield-Done"></use>
                                </svg></span>
                                            <div><a href="invoice-3.html.htm">
                                                    <h4>EnterPress Project</h4></a>
                                                <h5>25 paid days out of 113</h5>
                                            </div>
                                        </div>
                                        <div class="invoice-price"><span>$79,955.00 </span><span>Per month</span></div>
                                    </div>
                                </li>
                                <li>
                                    <button class="btn btn-outline-light txt-dark active w-100" type="button"><a class="d-flex gap-2 align-items-center" href="projects.html.htm">
                                            <svg>
                                                <use href="{{asset('admin')}}/svg/icon-sprite.svg#Plus-Circle"></use>
                                            </svg>Add Project</a></button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 proorder-md-7 box-col-6">
                <div class="card custom-statistics">
                    <div class="card-header">
                        <div class="header-top">
                            <h4>Recent Statistics  </h4>
                            <div class="dropdown">
                                <button class="btn dropdown-toggle custom-date-btn" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span>19 Aug - 25 Aug</span>
                                    <div class="custom-date">
                                        <svg>
                                            <use href="{{asset('admin')}}/svg/icon-sprite.svg#Left-Arrow"></use>
                                        </svg>
                                        <svg>
                                            <use href="{{asset('admin')}}/svg/icon-sprite.svg#Right-Arrow"></use>
                                        </svg>
                                    </div>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton"><a class="dropdown-item" href="#">11 Aug - 21 Aug</a><a class="dropdown-item" href="#">07 Sep - 23 Sep</a><a class="dropdown-item" href="#">01 Otc - 15 Otc</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pb-0 pt-0">
                        <div class="recent-statistics">
                            <ul>
                                <li>
                                    <div class="arrow-up">
                                        <svg>
                                            <use href="{{asset('admin')}}/svg/icon-sprite.svg#Arrow-Up"></use>
                                        </svg>
                                    </div><span>$5.850</span>
                                </li>
                                <li>
                                    <div class="arrow-down">
                                        <svg>
                                            <use href="{{asset('admin')}}/svg/icon-sprite.svg#Arrow-Down"></use>
                                        </svg>
                                    </div><span>$1.750</span>
                                </li>
                            </ul>
                        </div>
                        <div id="orderoverview"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 proorder-md-8 box-col-6">
                <div class="card">
                    <div class="card-header">
                        <div class="header-top">
                            <h4>All Notification (10)</h4>
                            <div class="dropdown icon-dropdown setting-menu">
                                <button class="btn dropdown-toggle" id="userdropdown5" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg>
                                        <use href="{{asset('admin')}}/svg/icon-sprite.svg#setting"> </use>
                                    </svg>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdown5"><a class="dropdown-item" href="#">Weekly </a><a class="dropdown-item" href="#">Monthly</a><a class="dropdown-item" href="#">Yearly </a></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="notification-card">
                            <ul>
                                <li>
                                    <div class="user-notification">
                                        <div><img src="{{asset('admin')}}/images/dashboard/avtar/2.jpg" alt="avatar"></div>
                                        <div class="user-description"><a href="letter-box.html.htm">
                                                <h4>You have new comments in real finical law agency landing page design.</h4></a><span>Today 11:45pm</span></div>
                                    </div>
                                    <div class="notification-btn">
                                        <button class="btn btn-pill btn-primary" type="button" title="btn btn-pill btn-primary">Accpet</button>
                                        <button class="btn btn-pill btn-secondary" type="button" title="btn btn-pill btn-primary">Decline</button>
                                    </div>
                                    <div class="show-btn"><a href="index.html.htm"> <span>Show</span></a></div>
                                </li>
                                <li>
                                    <div class="user-notification">
                                        <div><img src="{{asset('admin')}}/images/dashboard/avtar/17.jpg" alt="avatar"></div>
                                        <div class="user-description"><a href="letter-box.html.htm">
                                                <h4>Congrats! you complete all task for today. just need to join meting.</h4></a><span>Today 01:05pm</span></div>
                                    </div>
                                    <div class="notification-btn">
                                        <button class="btn btn-pill btn-primary" type="button" title="btn btn-pill btn-primary">Accpet</button>
                                        <button class="btn btn-pill btn-secondary" type="button" title="btn btn-pill btn-primary">Decline</button>
                                    </div>
                                    <div class="show-btn"><a href="index.html.htm"> <span>Show</span></a></div>
                                </li>
                                <li>
                                    <div class="user-notification">
                                        <div> <img src="{{asset('admin')}}/images/dashboard/avtar/18.jpg" alt="avatar"></div>
                                        <div class="user-description"><a href="letter-box.html.htm">
                                                <h4>You have new comments in real finical law agency landing page design.</h4></a><span>Today 06:55pm</span></div>
                                    </div>
                                    <div class="notification-btn">
                                        <button class="btn btn-pill btn-primary" type="button" title="btn btn-pill btn-primary">Accpet</button>
                                        <button class="btn btn-pill btn-secondary" type="button" title="btn btn-pill btn-primary">Decline</button>
                                    </div>
                                    <div class="show-btn"><a href="index.html.htm"> <span>Show</span></a></div>
                                </li>
                                <li>
                                    <div class="user-notification">
                                        <div><img src="{{asset('admin')}}/images/dashboard/avtar/19.jpg" alt="avatar"></div>
                                        <div class="user-description"><a href="letter-box.html.htm">
                                                <h4>Congrats! you complete all task for today. just need to join meting.</h4></a><span>Today 06:55pm</span></div>
                                    </div>
                                    <div class="notification-btn">
                                        <button class="btn btn-pill btn-primary" type="button" title="btn btn-pill btn-primary">Accpet</button>
                                        <button class="btn btn-pill btn-secondary" type="button" title="btn btn-pill btn-primary">Decline</button>
                                    </div>
                                    <div class="show-btn"><a href="index.html.htm"><span>Show </span></a></div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-sm-12 proorder-md-9 box-col-6">
                <div class="card">
                    <div class="card-header">
                        <div class="header-top">
                            <h4>Project Summary </h4>
                            <div class="dropdown icon-dropdown setting-menu">
                                <button class="btn dropdown-toggle" id="userdropdown22" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg>
                                        <use href="{{asset('admin')}}/svg/icon-sprite.svg#setting"> </use>
                                    </svg>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdown22"><a class="dropdown-item" href="#">Weekly</a><a class="dropdown-item" href="#">Monthly</a><a class="dropdown-item" href="#">Yearly </a></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive custom-scrollbar">
                            <table class="project-summary table" id="project-summary">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Project Name</th>
                                    <th>Team</th>
                                    <th>Project Cost</th>
                                    <th>Project Status</th>
                                    <th>Payment</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td class="f-w-600">A908</td>
                                    <td> <a href="projects.html.htm">Amazon Website</a></td>
                                    <td class="team-avtar">
                                        <div class="customers d-inline-block avatar-group">
                                            <ul>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/8.jpg" alt=""></li>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/10.jpg" alt=""></li>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/2.jpg" alt=""></li>
                                                <li class="d-inline-block">
                                                    <p>26+</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>$23,490</td>
                                    <td>Completed</td>
                                    <td class="txt-success">Done </td>
                                </tr>
                                <tr>
                                    <td>R840</td>
                                    <td> <a href="projects.html.htm">Paytm bank app</a></td>
                                    <td class="team-avtar">
                                        <div class="customers d-inline-block avatar-group">
                                            <ul>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/2.jpg" alt=""></li>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/20.jpg" alt=""></li>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/12.jpg" alt=""></li>
                                                <li class="d-inline-block">
                                                    <p>6+</p>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>$45,459</td>
                                    <td>In Progress</td>
                                    <td class="txt-danger">Pending</td>
                                </tr>
                                <tr>
                                    <td class="f-w-600">V908</td>
                                    <td class="f-w-600"> <a href="projects.html.htm">Cytrust Dashboard</a></td>
                                    <td class="team-avtar">
                                        <div class="customers d-inline-block avatar-group">
                                            <ul>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/14.jpg" alt=""></li>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/13.jpg" alt=""></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="f-w-600">$78,748 </td>
                                    <td class="f-w-600">Completed</td>
                                    <td class="txt-success f-w-600">Done</td>
                                </tr>
                                <tr>
                                    <td class="f-w-600">K450</td>
                                    <td> <a href="projects.html.htm">Project Mecha</a></td>
                                    <td class="team-avtar">
                                        <div class="customers d-inline-block avatar-group">
                                            <ul>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/22.jpg" alt=""></li>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/15.jpg" alt=""></li>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/2.jpg" alt=""></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="f-w-600">	$98,878</td>
                                    <td class="f-w-600">In Progress</td>
                                    <td class="txt-danger f-w-600">Pending</td>
                                </tr>
                                <tr>
                                    <td class="f-w-600">D480</td>
                                    <td class="f-w-600"><a href="projects.html.htm">Dynamic Program</a></td>
                                    <td class="team-avtar">
                                        <div class="customers d-inline-block avatar-group">
                                            <ul>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/21.jpg" alt=""></li>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/23.jpg" alt=""></li>
                                                <li class="d-inline-block"><img class="img-40 rounded-circle" src="{{asset('admin')}}/images/dashboard/avtar/24.jpg" alt=""></li>
                                                <li class="d-inline-block">
                                                    <p>5+                           </p>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="f-w-600">$23,490</td>
                                    <td class="f-w-600">In Progress</td>
                                    <td class="txt-danger f-w-600">Pending</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 proorder-md-10 box-col-12">
                <div class="card">
                    <div class="card-header custom-border-bottom">
                        <div class="header-top">
                            <h4>Sales Statistic</h4>
                            <div class="dropdown icon-dropdown setting-menu">
                                <button class="btn dropdown-toggle" id="userdropdown9" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg>
                                        <use href="{{asset('admin')}}/svg/icon-sprite.svg#setting"> </use>
                                    </svg>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdown9"><a class="dropdown-item" href="#">Weekly</a><a class="dropdown-item" href="#">Monthly</a><a class="dropdown-item" href="#">Yearly </a></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="groupBarChart"></div>
                        <div class="sales-data">
                            <ul>
                                <li>
                                    <div class="total-sales">
                                        <div>
                                            <h5>$4,875 </h5><span>Total Sales </span>
                                        </div>
                                        <div class="total-reached"><span>1,00,00</span><span>85% goal reached</span></div>
                                    </div>
                                    <div class="progress-data">
                                        <div class="progress sm-progress-bar progress-border-primary">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"> </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="total-sales">
                                        <div>
                                            <h5>$7,560</h5><span>Total Income</span>
                                        </div>
                                        <div class="total-reached"> <span>1,00,00</span><span>45% goal reached   </span></div>
                                    </div>
                                    <div class="progress-data">
                                        <div class="progress sm-progress-bar progress-border-secondary">
                                            <div class="progress-bar bg-secondary" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

