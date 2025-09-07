@extends('admin.layouts.app')
@section('contents')
    <style>
        /* Custom table header */
        .theme-color {
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
                                </svg>
                            </a></li>
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
                        <tr>
                            <td>Job Owner</td>
                            <td>
                                <a href="{{ route('admin.users.show', $posts->user_id) }}">
                                    {{ $posts->user->first_name.' '.$posts->user->last_name ?? 'N/A' }}
                                </a></td>
                        </tr>
                        <tr>
                            <td>Job Role</td>
                            <td>{{ $posts->job_role ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Location</td>
                            <td>{{ $posts->coordinates ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Field</td>
                            <td>{{ $posts->field ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Subdomain</td>
                            <td>{{ $posts->subdomain ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Fixed Salary</td>
                            <td>{{ $posts->fixed_salary ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Availability</td>
                            <td>{{ $posts->availability== '1' ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr>
                            <td>Min Offered Salary</td>
                            <td>{{ $posts->min_offered_salary ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Max Offered Salary</td>
                            <td>{{ $posts->max_offered_salary ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Transport</td>
                            <td>{{ $posts->transport== '1' ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr>
                            <td>Job Description</td>
                            <td>{{ $posts->job_description ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>{{ $posts->status ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Total Positions</td>
                            <td>{{ $posts->total_positions ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Remaining Positions</td>
                            <td>{{ $posts->remaining_positions ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Total Application Requests</td>
                            <td>{{ $posts->total_application_requests ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Is Remote</td>
                            <td>{{ $posts->is_remote== '1' ? 'Yes' : 'No'  }}</td>
                        </tr>
                        <tr>
                            <td>Work Type</td>
                            <td>{{ $posts->work_type== '1' ? 'Full Time' : 'Part Time'  }}</td>
                        </tr>
                        <tr>
                            <td>Start Time</td>
                            <td>{{ $posts->start_time ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>End Time</td>
                            <td>{{ $posts->end_time ?? 'N/A' }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-7 mt-3">
                    <h3 class="pb-3 ">Post Applications</h3>

                    <table class="table table-bordered">
                        <thead class="thead-light">
                        <tr>
                            <th>Sr.</th>
                            <th>User</th>
                            <th>User Status</th>
                            <th>Admin Verification</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($posts->jobApplications as $index=>$applications)
                            <tr>
                                <td>{{$index+1}}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $applications->user->id) }}">
                                        {{ $applications->user->first_name . ' ' . $applications->user->last_name ?? 'N/A' }}
                                    </a>
                                </td>
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
                                @php
                                    $approvedTransactions = $applications->transactions->where('admin_status', 1)->count();
                                @endphp
                                <td>
                                    @php
                                        $transaction = $applications->transactions->first(); // Get the first transaction
                                    @endphp

                                    @if($transaction && $transaction->admin_status == '0')
                                        <span class="badge badge-light-primary done-badge">Check Transaction</span>
                                    @elseif($transaction && $transaction->admin_status == '1')
                                        <span class="badge badge-light-success done-badge">Transaction Approved</span>
                                    @elseif($transaction && $transaction->admin_status == '2')
                                        <span class="badge badge-light-danger done-badge">Transaction Rejected</span>
                                    @else
                                        <span class="badge badge-light-warning">Error</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary-gradien btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop{{$index}}">
                                        Verify
                                    </button>
                                </td>
                            </tr>


                            <!-- Modal -->
                            <div class="modal fade" id="staticBackdrop{{$index}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel{{$index}}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="staticBackdropLabel{{$index}}">Transaction Details {{$index+1}}</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-4 fw-bold">Payment Type</div>
                                                <div class="col-8" style="text-transform: uppercase;">{{$transaction->payment_type}}</div>
                                            </div><div class="row">
                                                <div class="col-4 fw-bold">Amount Received</div>
                                                <div class="col-8 text-success">{{$transaction->amount}}</div>
                                            </div><div class="row">
                                                <div class="col-4 fw-bold">Tranzila Status</div>
                                                <div class="col-8" style="text-transform: uppercase;">{{$transaction->status}}</div>
                                            </div><div class="row">
                                                <div class="col-4 fw-bold">Card Type</div>
                                                <div class="col-8" style="text-transform: uppercase;">{{$transaction->cred_type}}</div>
                                            </div><div class="row">
                                                <div class="col-4 fw-bold">Card NO.</div>
                                                <div class="col-8" style="text-transform: uppercase;">{{$transaction->ccno}}</div>
                                            </div>
                                            <div class="row">
                                                <div class="col-4 fw-bold">Card CVV</div>
                                                <div class="col-8" style="text-transform: uppercase;">{{$transaction->cvv}}</div>
                                            </div> <div class="row">
                                                <div class="col-4 fw-bold">Card Exp</div>
                                                <div class="col-8" style="text-transform: uppercase;">{{$transaction->expdate}}</div>
                                            </div>


                                        </div>
                                        <div class="modal-footer">
                                            <form>
                                                <div class="row">
                                                    <div class="col-md-12 fw-bold text-center">Admin Status on Transaction</div>
                                                    <div class="col-md-12 mt-3 mb-4">
                                                        <select name="" class="form-select">
                                                            <option>Pending Status</option>
                                                            <option>I have Approved the Transaction</option>
                                                            <option>I have Rejected the Transaction</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <button type="button" class="btn btn-primary" name="submit">Submit</button>
                                                    </div>
                                                </div>

                                            </form>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>


@endsection
