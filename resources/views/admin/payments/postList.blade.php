@extends('admin.layouts.app')
@section('contents')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-xl-4 col-sm-7 box-col-3">
                    <h3>Jobs Payments</h3>
                </div>

                <div class="col-xl-12 col-sm-12 box-col-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">
                                <svg class="stroke-icon">
                                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#stroke-home"></use>
                                </svg></a>
                        </li>
                        <li class="breadcrumb-item">Dashboard</li>
                        <li class="breadcrumb-item active">Jobs Payments</li>
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
                        <style>
                            .custom-table thead th {
                                background-color: #FF8947;
                                color: white;
                                text-align: center;
                                padding: 10px;
                            }
                            .custom-table tbody td {
                                text-align: center;
                                vertical-align: middle;
                            }
                            .custom-table a {
                                text-decoration: none;
                                font-weight: bold;
                                color: #FF8947;
                            }
                            .custom-table a:hover {
                                color: #d9733a;
                            }
                        </style>

                        <div class="table-responsive custom-scrollbar">
                            <table class="table table-bordered custom-table">
                                <thead>
                                <tr>
                                    <th rowspan="2" class="align-middle">Post</th>
                                    <th colspan="3">Job Applications</th>
                                    <th colspan="3">Transactions</th>
                                    <th rowspan="2" class="align-middle">Action</th>

                                </tr>
                                <tr>
                                    <th>Applicants</th>
                                    <th>Rejected</th>
                                    <th>Pending</th>

                                    <th>Approved</th>
                                    <th>Pending</th>
                                    <th>Rejected</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($posts as $post)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.post.view', ['id' => $post->id]) }}">
                                                {{ $post->job_role ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $post->jobApplications->where('status', '2')->count() }}</td>
                                        <td>{{ $post->jobApplications->where('status', '0')->count() }}</td>
                                        <td>{{ $post->jobApplications->where('status', '1')->count() }}</td>
                                        <td>{{ $post->jobApplications->flatMap->transactions->where('admin_status', '0')->count() }}</td>
                                        <td>{{ $post->jobApplications->flatMap->transactions->where('admin_status', '2')->count() }}</td>
                                        <td>{{ $post->jobApplications->flatMap->transactions->where('admin_status', '1')->count() }}</td>
                                        <td>
                                            <a href="{{ route('admin.payments.post.view', [$post->id]) }}" class="btn btn-sm btn-primary text-white">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


