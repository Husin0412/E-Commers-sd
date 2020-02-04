@extends('layouts.adminLayout.admin_design')

@section('content')


<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> <a href="{{url('/admin/dashboard')}}" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> Home</a> <a href="#">Coupons</a>
     <a href="{{url('/admin/add-category')}}" class="current">Edit Coupon </a> </div>
    <h1>Coupons</h1>
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
            <h5>Edit Coupon</h5>
          </div>
          <div class="widget-content nopadding">
            <form class="form-horizontal" method="post" action="{{url('/admin/edit-coupon/'.$couponDetails->id)}}" name="edit_coupon" id="edit_coupon">
            {{csrf_field()}}
        
              <div class="control-group">
                <label class="control-label">Coupon Code</label>
                <div class="controls">
                  <input value="{{$couponDetails->coupon_code}}" type="text" name="coupon_code" id="coupon_code" maxlength="15" required>
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Amount</label>
                <div class="controls">
                  <input value="{{$couponDetails->amount}}" type="number" name="amount" id="amount" min="1" required>
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Amount Type</label>
                <div class="controls">
                   <select name="amount_type" id="amount_type" style="width:220px;">
                     <option @if($couponDetails->amount_type == "Persentage") selected @endif value="Persentage">Persentage</option>
                     <option @if($couponDetails->amount_type == "Fixed") selected @endif value="Fixed">Fixed</option>
                   </select> 
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Expiry Date</label> 
                <div class="controls">
                 <input value="{{$couponDetails->expiry_date}}" type="text" name="expiry_date" id="expiry_date" autocomplete="off" required> 
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Enable</label>
                <div class="controls">
                  <input type="checkbox" name="status" id="status" value="1" @if($couponDetails->status == "1") checked @endif>
                  </div>
              </div>
              <div class="form-actions">
                <input type="submit" value="Edit Coupon" class="btn btn-success">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection