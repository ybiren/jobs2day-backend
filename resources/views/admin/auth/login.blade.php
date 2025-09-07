
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Furqan">
    <link rel="icon" href="{{asset('admin')}}/images/favicon.png" type="image/x-icon">
    <link rel="shortcut icon" href="{{asset('admin')}}/images/favicon.png" type="image/x-icon">
    <title>Admin</title>
    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="{{asset('admin')}}/css2?family=Nunito+Sans:wght@200;300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
    <link href="{{asset('admin')}}/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('admin')}}/css/font-awesome.css">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="{{asset('admin')}}/css/vendors/icofont.css">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="{{asset('admin')}}/css/vendors/themify.css">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="{{asset('admin')}}/css/vendors/flag-icon.css">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="{{asset('admin')}}/css/vendors/feather-icon.css">
    <!-- Plugins css start-->
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{asset('admin')}}/css/vendors/bootstrap.css">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{asset('admin')}}/css/style.css">
    <link id="color" rel="stylesheet" href="{{asset('admin')}}/css/color-1.css" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{asset('admin')}}/css/responsive.css">
    <link rel="stylesheet" type="text/css" href="{{asset('admin/css/custom.css')}}">
    <style>
        .bg-image {
            position: relative;
            background-image: url('{{ asset("admin/images/login/3.jpg") }}');
            background-size: cover;
            background-position: center;
            height: 100vh; /* Adjust this as needed */
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .overlay {
            text-align: center;
            color: white;
            background: #ff894787;
            padding: 20px;
            border-radius: 10px;
            margin-top: 90px;
        }
        /* Custom checkbox styling */
        .checkbox input[type="checkbox"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ccc;
            border-radius: 4px;
            position: relative;
            cursor: pointer;
        }

        .checkbox input[type="checkbox"]:checked {
            background-color: #2b5f60 !important;
            border-color: #007bff;
        }
        .form-check-input:checked{
            background-color: #2b5f60 !important;
        }

        .checkbox input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 2px;
            width: 6px;
            height: 10px;
            border: solid #fff;
            border-width: 0 3px 3px 0;
            transform: rotate(45deg);
        }


    </style>
</head>
<body>
<!-- login page start-->
<div class="container-fluid">
    <div class="row">
{{--        <div class="col-xl-5 bg-image">--}}
{{--            <div class="overlay">--}}
{{--                <p style="    font-size: 40px;--}}
{{--    font-weight: 900;">Welcome to</p>--}}
{{--                <h1 class="text-white">{{$login_head->site_name ?? 'Jobs2Day'}}</h1>--}}
{{--            </div>--}}
{{--        </div>--}}
        <div class="col-xl-12 p-0">
            <div class="login-card login-dark login-bg">
                <div>
                    <div><a class="logo text-center" href=""><img class="img-fluid for-light admin-logo" src="{{asset($login_head->logo1 ??'logo/jobs2day_full-removebg-preview (1).png')}}" alt="looginpage">
                            <img class="img-fluid for-dark admin-logo" src="{{asset($login_head->logo2 ?? 'logo/jobs2day_full-removebg-preview (1).png')}}" alt="looginpage"></a></div>
                    <div class="login-main">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form class="theme-form" action="{{ route('admin.login.submit') }}" method="POST">
                            @csrf
                            <h4>Sign in to account</h4>
                            <p>Enter your email & password to login</p>
                            <div class="form-group">
                                <label class="col-form-label">Email Address</label>
                                <input class="form-control" type="email" name="email" required  placeholder="Email" value="{{ old('email', request()->cookie('email')) }}">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Password</label>
                                <div class="form-input position-relative">
                                    <input class="form-control" type="password" name="password" required placeholder="*********" id="password">
                                    <div class="show-hide">
                                        <span id="togglePasswordText" class="toggle-text"></span>
                                    </div>
                                </div>


                            </div>
                            <div class="form-group mb-0">
                                <div class="form-check check-box">
                                    <input class="form-check-input" type="checkbox" name="remember" id="checkbox1">
                                    <label class="form-check-label" for="checkbox1">Remember me</label>
                                </div>

                                <button class="btn btn-primary btn-block w-100" type="submit">Sign in</button>
                            </div>


                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- latest jquery-->
    <script src="{{asset('admin')}}/js/jquery.min.js"></script>
    <!-- Bootstrap js-->
    <script src="{{asset('admin')}}/js/bootstrap/bootstrap.bundle.min.js"></script>
    <!-- feather icon js-->
    <script src="{{asset('admin')}}/js/icons/feather-icon/feather.min.js"></script>
    <script src="{{asset('admin')}}/js/icons/feather-icon/feather-icon.js"></script>
    <!-- scrollbar js-->
    <!-- Sidebar jquery-->
    <script src="{{asset('admin')}}/js/config.js"></script>
    <!-- Plugins JS start-->
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="{{asset('admin')}}/js/script.js"></script>
    <!-- Plugin used-->
    <script>
        document.getElementById('togglePasswordText').addEventListener('click', function () {
            // Get the password input field
            const passwordField = document.getElementById('password');

            // Toggle the password field type
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;

            // Update the text inside the toggle button
            if (type === 'password') {
                this.textContent = '';
            } else {
                this.textContent = '';
            }
        });
    </script>


</div>
</body>
</html>
