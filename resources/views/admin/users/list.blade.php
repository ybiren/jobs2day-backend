@extends('admin.layouts.app')
@section('contents')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-md-4">
                    <h3>{{$title ?? 'Users'}} Lists</h3>
                </div>
                <div class="col-md-8 d-none d-xl-block">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">
                                <svg class="stroke-icon">
                                    <use href="{{asset('admin')}}/svg/icon-sprite.svg#stroke-home"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item">Dashboard</li>
                        <li class="breadcrumb-item active">{{$title ?? 'Users'}} Lists</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>



    <div class="container-fluid default-dashboard">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{$title ?? 'Users'}} List</h4>
                        <div class="table-responsive">
                            <table id="users-table" class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Login Type</th>
                                    <th>Phone</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($all_users as $index=>$user)
                                    <tr>
                                        <td>{{$index+1}}</td>
                                        <td><img src="{{asset($user->profile_image ?? 'admin/images/user/dummy-user.webp')}}" style="width: 50px; border-radius: 100%"></td>
                                        <td>{{$user->first_name .' '.$user->last_name}}</td>
                                        <td>{{$user->auth_type}}</td>
                                        <td>{{$user->phone}}</td>
                                        <td>
                                            <a href="{{route('admin.users.show', $user->id)}}">
                                                <button type="button" class="btn btn-primary-gradien btn-sm">View</button>
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

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

    <script>
    function confirmDelete(url) {
        if (confirm("Are you sure you want to delete this user?")) {
            window.location.href = url;
        }
    }
    function confirminactive(url) {
        if (confirm("Are you sure you want to Block this user?")) {
            window.location.href = url;
        }
    }
    function confirmactive(url) {
        if (confirm("Are you sure you want to UnBlock this user?")) {
            window.location.href = url;
        }
    }

</script>
<script>
    $(document).ready(function() {
        $('#users-table').DataTable({
            "paging": true,          // Enable pagination
            "searching": true,       // Enable search bar
            "ordering": true,        // Enable sorting
            "info": true,            // Show info (e.g., "Showing 1 to 10 of 50 entries")
            "lengthChange": true,    // Allow changing the number of records per page
            "autoWidth": false,      // Prevent the table from automatically adjusting column widths
            "pageLength": 50,        // Set the number of records per page to 50
        });
    });
</script>
@endpush
