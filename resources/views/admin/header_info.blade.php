@extends('admin.layouts.app')
@section('contents')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-xl-4 col-sm-7 box-col-3">
                <h3>Site Informations</h3>
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
                    <li class="breadcrumb-item active">Header Informations</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- Container-fluid starts-->
<div class="container-fluid">
  <div class="row">
      <div class="col-md-12">
          <div class="card height-equal">
              <div class="card-header pb-0">
                  <h4>Header Informations</h4>
                  <p class="f-m-light mt-1">

                  </p>
              </div>
              <div class="card-body custom-input">
                  <form class="row g-3" action="{{route('admin.hearderinfo.update')}}" method="POST" enctype="multipart/form-data">
                      @csrf
                      <div class="col-md-6">
                          <label class="form-label" for="site_name">Site Name</label>
                          <input class="form-control" id="site_name" name="site_name" type="text" value="{{$headerInfo->site_name}}" required >
                      </div>
                      <div class="col-md-6">
                          <label class="form-label" for="site_url">Site URL</label>
                          <input class="form-control" id="site_url" name="site_url" type="url" value="{{$headerInfo->site_url}}" required>
                      </div>
                      <div class="col-md-4">
                          <label class="form-label" for="site_phone">Site Phone</label>
                          <input class="form-control" id="site_phone" name="site_phone" type="text" value="{{$headerInfo->site_phone}}" required>
                      </div>
                      <div class="col-md-4">
                          <label class="form-label" for="site_email">Site Email</label>
                          <input class="form-control" id="site_email" name="site_email" type="email" value="{{$headerInfo->site_email}}" required>
                      </div>
                      <div class="col-md-4">
                          <label class="form-label" for="site_owner">Site Owner</label>
                          <input class="form-control" id="site_owner" name="site_owner" type="text" value="{{$headerInfo->site_owner}}" required>
                      </div>
                      <div class="col-md-12">
                          <label class="form-label" for="site_address">Site Address</label>
                          <textarea class="form-control" id="site_address" name="site_address" rows="3" placeholder="Enter Site Address" required>{{$headerInfo->site_address}}</textarea>
                      </div>
                      <div class="col-md-4">
                          <label class="form-label" for="logo1">Logo 1</label>
                          <input class="form-control" id="logo1" name="logo1" type="file">
                      </div>
                      <div class="col-md-2">
                          <img src="{{asset($headerInfo->logo1)}}" alt="logo1" style="width: 100px">
                      </div>
                      <div class="col-md-4">
                          <label class="form-label" for="logo2">Logo 2</label>
                          <input class="form-control" id="logo2" name="logo2" type="file">
                      </div>
                      <div class="col-md-2">
                          <img src="{{asset($headerInfo->logo2)}}" alt="logo2" style="width: 100px">
                      </div>
                      <div class="col-md-6">
                          <label class="form-label" for="logo1alt">Logo 1 Alt Text</label>
                          <input class="form-control" id="logo1alt" name="logo1alt" type="text" value="{{$headerInfo->logo1alt}}">
                      </div>
                      <div class="col-md-6">
                          <label class="form-label" for="logo2alt">Logo 2 Alt Text</label>
                          <input class="form-control" id="logo2alt" name="logo2alt" type="text" value="{{$headerInfo->logo2alt}}">
                      </div>
                      <div class="col-md-6">
                          <button class="btn btn-primary" type="submit">Submit</button>
                      </div>
                  </form>

              </div>
          </div>
      </div>
  </div>
</div>
@endsection
