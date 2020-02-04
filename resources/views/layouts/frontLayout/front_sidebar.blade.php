  <div class="left-sidebar">
    <h2>Category</h2>
    <div class="panel-group category-products" id="accordian"><!--category-productsr-->
        <div class="panel panel-default">
            <!-- <-?php echo $categories_menu -->
            @foreach($categories as $cat)
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordian" href="#{{$cat->id}}">
                        <span class="badge pull-right"><i class="fa fa-plus"></i></span>
                        {{$cat->name}}
                    </a>
                </h4>
            </div>

            <div id="{{$cat->id}}" class="panel-collapse collapse">
                <div class="panel-body">
                    <ul>
                        @foreach($cat->categories as $subcat)
                        @if($subcat->status == "1")
                        <li><a href="{{'/products/'.$subcat->url}}" >{{$subcat->name}}</a></li>
                        @endif
                        @endforeach
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div><!--/category-products-->     

    <div class="price-range"><!--price-range-->
        <h2>Price Range</h2>
        <div class="well text-center">
                <input type="text" class="span2" value="" data-slider-min="0" data-slider-max="500000" data-slider-step="5" data-slider-value="[250,450]" id="sl2" ><br />
                <b class="pull-left">Rp 0</b> <b class="pull-right">Rp 500.000</b>
        </div>
    </div><!--/price-range-->

    <div class="shipping text-center"><!--shipping-->
        <img src="{{ asset('images/frontend_images/home/shipping.jpg') }}" alt="" />
    </div><!--/shipping-->

 </div>