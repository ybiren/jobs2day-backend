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
                    <h3 style="color: #FF8947">{{ $user->email ?? 'N/A' }}</h3>
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
                        <li class="breadcrumb-item ">User</li>
                        <li class="breadcrumb-item active">{{ $user->id ?? 'N/A' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row">
                <div class="col-md-12">
                    @if(count($user->posts) > 0)
                        <div class="row p-3">
                            <h3  class="pb-3">Company Job Posts</h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped ">
                                    <thead style="background: #FF8947; color: white">
                                    <tr>
                                        <th>Job Role</th>
                                        <th>Job Description</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($user->posts as $post)
                                        <tr>
                                            <td>{{ $post->job_role ?? 'N/A' }}</td>
                                            <td>{{ $post->job_description ?? 'N/A' }}</td>
                                            <td>
                                            @if($post->status== '0')
                                                    <span class="badge badge-light-success">Finished</span>
                                                @elseif($post->status== '1')
                                                    <span class="badge badge-light-primary done-badge">Hiring</span>
                                                @elseif($post->status== '2')
                                                    <span class="badge badge-light-danger">Working</span>
                                                @else
                                                    <span class="badge badge-light-danger">Error</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{route('admin.post.view', [$post->id])}}" class="btn btn-sm text-success" style="font-size: 18px">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No posts available.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    @endif
                </div>
                <div class="col-md-5">

                    <h3 class="pb-3 mt-3">User Details</h3>
                    <table class="table table-striped table-bordered custom-table">
                        <tbody>
                        <tr><td>Name</td><td>{{ $user->name ?? 'N/A' }}</td></tr>
                        <tr><td>Email</td><td>{{ $user->email ?? 'N/A' }}</td></tr>
                        <tr><td>Phone</td><td>{{ $user->phone ?? 'N/A' }}</td></tr>
                        <tr><td>First Name</td><td>{{ $user->first_name ?? 'N/A' }}</td></tr>
                        <tr><td>Last Name</td><td>{{ $user->last_name ?? 'N/A' }}</td></tr>
                        <tr><td>Login Type</td><td>{{ $user->auth_type ?? 'N/A' }}</td></tr>
                        <tr><td>Gender</td><td>{{ ucfirst($user->gender ?? 'N/A') }}</td></tr>
                        <tr><td>Date of Birth</td><td>{{ $user->dob ?? 'N/A' }}</td></tr>
                        <tr><td>Description</td><td>{{ $user->description ?? 'N/A' }}</td></tr>
                        <tr><td>Also a Candidate</td><td>{{ $user->is_onboarding_person == '1' ? 'Yes' : 'No' }}</td></tr>
                        <tr><td>Also a Business</td><td>{{ $user->is_onboarding_business == '1' ? 'Yes' : 'No' }}</td></tr>
                        <tr>
                            <td>Average Rating</td>
                            <td>
                                @php
                                    $rating = ceil($user->avg_rating); // Round up to the nearest full star
                                @endphp
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $rating)
                                        <i class="fa fa-star" style="color: #FF8947;"></i> <!-- Filled Star -->
                                    @else
                                        <i class="fa fa-star" style="color: #FF8947; opacity: 0.5;"></i> <!-- Empty Star -->
                                    @endif
                                @endfor
                            </td>


                        </tr>
                        <tr><td>Rating Count</td><td>{{ $user->rating_count ?? 'N/A' }}</td></tr>

                        <tr><td>Location</td><td>{{ $user->coordinates ?? 'N/A' }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-7 mt-3">
                    @if($user->is_onboarding_business== '1')
                        <div class="row">
                            <div class="col-md-12">
                                <h3  class="pb-3">Company Details</h3>
                                <table class="table table-striped table-bordered custom-table">
                                    <tbody>
                                    <tr>
                                        <th>Company Name</th>
                                        <td>{{ optional($user->companyDetails)->id ?? 'N/A' }}</td>
                                    </tr> <tr>
                                        <th>Registration no.</th>
                                        <td>{{ optional($user->companyDetails)->registration_no ?? 'N/A' }}</td>
                                    </tr><tr>
                                        <th>Fields</th>
                                        <td>{{ optional($user->companyDetails)->field ?? 'N/A' }}</td>
                                    </tr><tr>
                                        <th>Company Email</th>
                                        <td>{{ optional($user->companyDetails)->company_email ?? 'N/A' }}</td>
                                    </tr><tr>
                                        <th>Description</th>
                                        <td>{{ optional($user->companyDetails)->details ?? 'N/A' }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                </div>

            </div>
        </div>
    </div>


@endsection
