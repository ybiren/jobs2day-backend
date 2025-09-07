<!DOCTYPE html>
<html lang="en">
@include('admin.layouts.common.head')
<body>
<div class="loader-wrapper">
    <div class="theme-loader">
        <div class="loader-p"></div>
    </div>
</div>
<div class="tap-top"><i data-feather="chevrons-up"></i></div>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
@if(Auth::guard('admin')->check())
        @include('admin.layouts.common.header')

        <div class="page-body-wrapper">
        @include('admin.layouts.common.sidebar')
            <div class="page-body">
            @yield('contents')
            </div>

            @include('admin.layouts.common.footer_content')

        </div>
@else
        <script>
            window.location.href = "{{ route('admin.login') }}";
        </script>
@endif

</div>

@include('admin.layouts.common.footer')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            showConfirmButton: true,
        });
        @endif

        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            showConfirmButton: true,
        });
        @endif
    });
</script>
@stack('scripts')

</body>
</html>
