@extends('layouts.adminLayout.admin_design')

@section('content')

<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> <a href="{{url('/admin/dashboard')}}" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> Home</a> <a href="#">Products</a>
     <a href="{{url('/admin/add-category')}}" class="current">Edit Product</a> </div>
    <h1>Products</h1>
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
            <h5>Edit Product</h5>
          </div>
          <div class="widget-content nopadding">
            <form enctype="multipart/form-data" class="form-horizontal" method="post" action="{{url('/admin/edit-product/'.$productDetails->id)}}" name="edit_product" id="edit_product" novalidate="novalidate">
            {{csrf_field()}}
            <div class="control-group">
                <label class="control-label">Under Category </label>
                <div class="controls">
                   <select name="category_id" id="category_id" style="width:220px;">
                    <?php echo $categories_dropdown ?> 
                   </select> 
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Product Name</label>
                <div class="controls">
                  <input type="text" name="product_name" id="product_name" value="{{$productDetails->product_name}}">
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Product Code</label>
                <div class="controls">
                  <input type="text" name="product_code" id="product_code" value="{{$productDetails->product_code}}">
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Product Color</label>
                <div class="controls">
                  <input type="text" name="product_color" id="product_color" value="{{$productDetails->product_color}}">
                </div>
              </div> 
              <div class="control-group">
                <label class="control-label">Description</label> 
                <div class="controls">
                 <textarea name="description" id="description">{{$productDetails->description}}</textarea>
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Material & care </label> 
                <div class="controls">
                 <textarea name="care" id="care">{{$productDetails->care}}</textarea>
                </div>
              </div>
              <div class="control-group">
                <label class="control-label ">Price</label>
                <div class="price-input-usd controls">
                  <input class="price-input-usd" type="text" name="price" id="price" value="{{$productDetails->price}}">
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Old Image</label>
                <div class="controls">
               @if(!empty($productDetails->image))
                 <img src="{{asset('images/backend_images/products/small/'.$productDetails->image)}}" alt="image product" width="110px;"> | <a rel="{{$productDetails->id}}" rel1="delete-product-image" rel2="Old Image" href="javascript:" class="deleteProd btn btn-danger btn-mini" id="" >Delete</a>
               @endif
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">New Image</label>
                <div class="controls">
                  <input type="file" name="image" id="image"> 
                  <input type="hidden" name="current-image" value="{{$productDetails->image}}"> 
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Enable</label>
                <div class="controls">
                  <input type="checkbox" name="status" id="status" @if($productDetails->status =="1") checked @endif value="1">
                </div>
              </div>

              <div class="form-actions">
                <input type="submit" value="Edit Category" class="btn btn-success">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection