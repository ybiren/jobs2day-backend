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
                        <li class="breadcrumb-item ">Reports</li>
                        <li class="breadcrumb-item active">{{ $posts->id ?? 'N/A' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="edit-profile">
            <div class="row">
                <div class="col-md-12 mt-3">
                    <h3 class="pb-3 ">Reports</h3>

                    <table class="table table-bordered">
                        <thead class="thead-light">
                        <tr>
                            <th>Sr.</th>
                            <th>Title</th>
                            <th>Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($reports as $index=>$report)
                            <tr>
                                <td>{{$index+1}}</td>
                                <td>{{$report->title}}</td>
                                <td>{{$report->description}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>


@endsection
