@extends('admin.layouts.app')
@section('contents')
    <style>
        /* Custom table header */
        .theme-color{
            color: #FF8947;
        }
        .custom-table thead {
            background-color: #FF8947;
            color: white;
        }

        /* Set odd-numbered rows to white */
        .custom-table tbody tr:nth-child(odd) {
            background-color: white;
        }

        /* Set even-numbered rows to a lighter shade of #FF8947 */
        .custom-table tbody tr:nth-child(even) {
            background-color: #FFC4A1; /* Adjust shade as needed */
        }

    </style>
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-xl-4 col-sm-7 box-col-3">
                    <h3 style="color: #FF8947">{{ $posts->job_role ?? 'N/A' }}</h3>
                </div>
                <div class="col-5 d-none d-xl-block">
                </div>
                <div class="col-xl-3 col-sm-5 box-col-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">
                                <svg class="stroke-icon">
                                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#stroke-home"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item">Home</li>
                        <li class="breadcrumb-item ">Post</li>
                        <li class="breadcrumb-item active">{{ $posts->id ?? 'N/A' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row">
                <div class="col-md-5">
                    <h3 class="pb-3 mt-3">Post Details</h3>
                    <table class="table table-striped table-bordered custom-table">
                        <tbody>
                        <tr><td>Job Owner</td><td>
                                <a href="{{ route('admin.users.show', $posts->user_id) }}">
                                    {{ $posts->user->first_name.' '.$posts->user->last_name ?? 'N/A' }}
                                </a>                            </td></tr>
                        <tr><td>Job Role</td><td>{{ $posts->job_role ?? 'N/A' }}</td></tr>
                        <tr><td>Location</td><td>{{ $posts->coordinates ?? 'N/A' }}</td></tr>
                        <tr><td>Field</td><td>{{ $posts->field ?? 'N/A' }}</td></tr>
                        <tr><td>Subdomain</td><td>{{ $posts->subdomain ?? 'N/A' }}</td></tr>
                        <tr><td>Fixed Salary</td><td>{{ $posts->fixed_salary ?? 'N/A' }}</td></tr>
                        <tr><td>Availability</td><td>{{ $posts->availability== '1' ? 'Yes' : 'No' }}</td></tr>
                        <tr><td>Min Offered Salary</td><td>{{ $posts->min_offered_salary ?? 'N/A' }}</td></tr>
                        <tr><td>Max Offered Salary</td><td>{{ $posts->max_offered_salary ?? 'N/A' }}</td></tr>
                        <tr><td>Transport</td><td>{{ $posts->transport== '1' ? 'Yes' : 'No' }}</td></tr>
                        <tr><td>Job Description</td><td>{{ $posts->job_description ?? 'N/A' }}</td></tr>
                        <tr><td>Status</td><td>{{ $posts->status ?? 'N/A' }}</td></tr>
                        <tr><td>Total Positions</td><td>{{ $posts->total_positions ?? 'N/A' }}</td></tr>
                        <tr><td>Remaining Positions</td><td>{{ $posts->remaining_positions ?? 'N/A' }}</td></tr>
                        <tr><td>Total Application Requests</td><td>{{ $posts->total_application_requests ?? 'N/A' }}</td></tr>
                        <tr><td>Is Remote</td><td>{{ $posts->is_remote== '1' ? 'Yes' : 'No'  }}</td></tr>
                        <tr><td>Work Type</td><td>{{ $posts->work_type== '1' ? 'Full Time' : 'Part Time'  }}</td></tr>
                        <tr><td>Start Time</td><td>{{ $posts->start_time ?? 'N/A' }}</td></tr>
                        <tr><td>End Time</td><td>{{ $posts->end_time ?? 'N/A' }}</td></tr>                        </tbody>
                    </table>
                </div>
                <div class="col-md-7 mt-3">
                    <h3 class="pb-3 ">Post Applications</h3>

                    <table class="table table-bordered">
                        <thead class="thead-light">
                        <tr>
                            <th>User</th>
                            <th>Note</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($posts->jobApplications as $applications)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $applications->user->id) }}">
                                        {{ $applications->user->first_name . ' ' . $applications->user->last_name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>{{ $applications->note ?? 'N/A' }}</td>
                                <td>
                                    @if($applications->status== '0')
                                    <span class="badge badge-light-warning">Pending</span>
                                    @elseif($applications->status== '1')
                                        <span class="badge badge-light-primary done-badge">Hired</span>
                                    @elseif($applications->status== '2')
                                        <span class="badge badge-light-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-light-danger">Error</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>


@endsection
