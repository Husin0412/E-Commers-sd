@extends('layouts.adminLayout.admin_design')

@section('content')

<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> <a href="{{url('/admin/dashboard')}}" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> Home</a> <a href="#">Billboards</a>
     <a href="{{url('/admin/add-banner')}}" class="current">Edit Billboards</a> </div>
    <h1>Billboards</h1>
    @if(Session::has('flash_message_error'))
        <div class="alert alert-dark alert-block" style="background-color:Tomato; color:white; width:21%; margin-left:20px;">
            <button type="button" class="close" data-dismiss="alert">x</button>	
            <strong> {{Session::get('flash_message_error')}}</strong>
        </div>
        @endif  
        @if(Session::has('flash_message_drop'))
        <div class="alert alert-success alert-block" style="background-color:#F08080; color:white; width:21%; margin-left:20px;">
            <button type="button" class="close" data-dismiss="alert" >x</button>	
            <strong> {{Session::get('flash_message_drop')}}</strong>
        </div>
        @endif
        @if(Session::has('flash_message_success'))
        <div class="alert alert-dark alert-block" style="background-color:green; color:white; width:21%; margin-left:20px;">
            <button type="button" class="close" data-dismiss="alert">x</button>	
            <strong> {{Session::get('flash_message_success')}}</strong>
        </div>
    @endif
  </div>
  <div id="loading"></div>
  <div class="container-fluid"><hr>
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title"> <span class="icon"> <i class="icon-info-sign"></i> </span>
            <h5>Edit Billboard</h5>
          </div>
          <div class="widget-content nopadding">
            <form enctype="multipart/form-data" class="form-horizontal" method="post" action="{{url('/admin/edit-billboard/'.$billboard->id)}}" name="edit_billboard" id="edit_billboard">
            {{csrf_field()}}
            <div class="control-group">
                <label class="control-label">Billboard Image</label>
                 <div class="controls">
                  <div class="uploader" id="uniform-undefined">
                     <input type="file" name="image" id="image" size="19" style="opcity: 0;"><span class="filename">NO File Selected</span><span class="action">Choose File</span>
                 </div>
                </div>
                <div class="control-group">
                <label class="control-label">Old Image</label>
                <div class="controls">
                  <img src="{{ asset('images/backend_images/banners/'.$billboard->image) }}" alt="Old Img" style="width: 214px;border: solid;border-style: double;">
                  @if(!empty($billboard->image))
                    <input type="hidden" name="current_image" value="{{$billboard->image}}">
                  @endif
                  {{-- dd($billboard->image) --}}
                </div>
              </div>
              </div>
              <div class="control-group">
                <label class="control-label">Title</label>
                <div class="controls">
                  <input type="text" name="title" id="title" value="{{$billboard->title}}">
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Link</label>
                <div class="controls" >
                  <input type="text" name="link" id="link" value="{{$billboard->link}}">
                </div>
              </div> 
              <div class="control-group">
                <label class="control-label">Enable</label>
                <div class="controls">
                  <input type="checkbox" name="status" id="status" value="1" @if ($billboard->status == 1) checked @endif> 
                </div>
              </div>
              <div class="form-actions">
                <input type="submit" value="Edit Billboard" class="btn btn-success">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection