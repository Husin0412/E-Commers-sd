<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Auth;
use Input;
use Date;
use Session;
use Image;
use App\Category;
use App\Product;
use App\ProductsAttribute; 
use App\ProductsImage;
use DB;
use App\Coupon;
use App\Banner;
use App\User;
use App\Country;
use App\DeliveryAddress; 
use App\Order;
use App\OrdersProduct; 
use \Illuminate\Support\Facades\Storage;
use App\Exports\productsExport;
use Excel;
// reference the Dompdf namespace
use Dompdf\Dompdf;
use PDF;
use Carbon\Carbon;

class ProductsController extends Controller
{

    public function __construct() {
        // sas
    }
    

    public function products($url=null)
    {
        // Show 404 page if category URL does not exist
        $countCategory = Category::where(['url' => $url,'status'=> 1])->count();
        if($countCategory == 0)
        {
            abort(404);
        }

        $categories = Category::with('categories')->where(['parent_id' => 0])->get();
        $categoryDetails = Category::where(['url' => $url])->first();
  
        if($categoryDetails->parent_id == 0)
        {
            // if url is main category url
            $countSubCategories = Category::where(['parent_id' => $categoryDetails->id])->count();
            if($countSubCategories == 0 ) {
                  abort(404);
            } 

            $subCategories = Category::where(['parent_id' => $categoryDetails->id])->get();
            foreach($subCategories as $key => $subcat)
            { 
                $cat_ids[] = $subcat->id;
            }
            $productAll = DB::table('products')->whereIn('products.category_id', $cat_ids)->where('products.status',1)->orderBy('products.id','desc');
            // $productAll = \json_decode(\json_encode($productAll));
            $allProductCount = Product::whereIn('category_id', $cat_ids)->where('status',1)->count();
            $breadcrumb = "<a href='/' style='color: darkorange;'>Home</a> / <a href='".$categoryDetails->url."' style='color: darkorange;' >".$categoryDetails->name."</a>";
        }else{
            // If url is sub category url
            $productAll = DB::table('products')->where(['products.category_id' => $categoryDetails->id])->where('products.status',1)->orderBy('id','desc');
            $allProductCount = Product::where(['category_id' => $categoryDetails->id])->where('status',1)->count();    
            $mainCategory = DB::table('categories')->where('id',$categoryDetails->parent_id)->first();
            $breadcrumb = "<a href='/' style='color: darkorange;'>Home</a> / <a href='".$mainCategory->url."' style='color: darkorange;' >".$mainCategory->name."</a> / <a href='".$categoryDetails->url."' style='color: darkorange;' >".$categoryDetails->name."</a>";
        }

        if(!empty($_GET['color'])) {
            $colorArray = explode( '-',$_GET['color']);
            $productAll =  $productAll->whereIn('products.product_color',$colorArray);
        }

        if(!empty($_GET['sleeve'])) {
            $sleeveArray = explode( '-',$_GET['sleeve']);
            $productAll =  $productAll->whereIn('products.sleeve',$sleeveArray);
        }

        if(!empty($_GET['pattern'])) {
            $patternArray = explode( '-',$_GET['pattern']);
            $productAll =  $productAll->whereIn('products.pattern',$patternArray);
        }

        if(!empty($_GET['size'])) {
            $sizeArray = explode( '-',$_GET['size']);
            $productAll =  $productAll->join('products_attributes','products_attributes.product_id', '=', 'products.id')
            ->select('products.*','products_attributes.product_id','products_attributes.size')
            ->groupBy('products_attributes.product_id')
            ->whereIn('products_attributes.size',$sizeArray);
        }
        // dd($productAll,$sizeArray, $allProductCount);
        $productAll = $productAll->paginate(9);
        // $productAll = json_decode(json_encode($productAll));
        // dd($productAll);
        $colorArray = DB::table('products')->select('product_color')->groupBy('product_color')->get();
        $sleeveArray = DB::table('products')->select('sleeve')->where('sleeve', '!=', '')->groupBy('sleeve')->get();
        $patternArray = DB::table('products')->select('pattern')->where('pattern', '!=', '')->groupBy('pattern')->get();
        $sizeArray = ProductsAttribute::select('size')->where('size', '!=', '')->where('size', '!=', 'tes')->groupBy('size')->get();
        $sizeArray = array_flatten(json_decode(json_encode($sizeArray),true));

        $banners = Banner::where('status', 1)->get(); 
        $billboard = DB::table('billboards')->inRandomOrder()->orderBy('id','DESC')->where('status',1)->offset(0)->limit(1)->get();
        // meta tags
        $meta_title = $categoryDetails->meta_title;
        $meta_description = $categoryDetails->meta_description;
        $meta_keyword = $categoryDetails->meta_keywords;

        return view('products.listing')->with(\compact('categoryDetails','productAll','categories','banners','billboard','allProductCount','meta_title','meta_description','meta_keyword','sizeArray','breadcrumb'));
    }


    public function filter(Request $request) {

        $data = $request->all();
        $colorUrl = "";
        if(!empty($data['colorFilter'])) {
            foreach($data['colorFilter'] as $color) {
                if(empty($colorUrl)) {
                    $colorUrl = "&color=".$color;
                } else {
                    $colorUrl .= "-".$color;
                }       
            }   
        }

        $sleeveUrl = "";
        if(!empty($data['sleeveFilter'])) {
            foreach($data['sleeveFilter'] as $slv) {
                if(empty($sleeveUrl)) {
                    $sleeveUrl = "&sleeve=".$slv;
                } else {
                    $sleeveUrl .= "-".$slv;
                }       
            }
            
        }

        $patternUrl = "";
        if(!empty($data['patternFilter'])) {
            foreach($data['patternFilter'] as $ptr) {
                if(empty($patternUrl)) {
                    $patternUrl = "&pattern=".$ptr;
                } else {
                    $patternUrl .= "-".$ptr;
                }       
            }           
        }

        $sizeUrl = "";
        if(!empty($data['sizeFilter'])) {
            foreach($data['sizeFilter'] as $siz) {
                if(empty($sizeUrl)) {
                    $sizeUrl = "&size=".$siz;
                } else {
                    $sizeUrl .= "-".$siz;
                }       
            }           
        }
        // dd($data['url']);
        $finalUrl = "products/".$data['url']."?".$colorUrl.$sleeveUrl.$patternUrl.$sizeUrl;
        return Redirect::to($finalUrl);
    }


    public function searchProducts(Request $request)
    {
        if($request->isMethod('post')) {
            $data = $request->all();

            $banners = Banner::where('status', 1)->get();
            $billboard = DB::table('billboards')->orderBy('id','DESC')->where('status',1)->offset(0)->limit(1)->get();
            $categories = Category::with('categories')->where(['parent_id' => 0])->get();
            $search_product = $data['product'];
            // $productAll = Product::where('product_name','like','%'.$search_product.'%')->orwhere('product_code',$search_product)->where('status',1)->paginate(9);
            $productCount = DB::table('products')->where( function($query) use ($search_product) {
                $query->where('product_name','like','%'.$search_product.'%')
                ->orwhere('product_code',$search_product)
                ->orwhere('description','like','%'.$search_product.'%')
                ->orwhere('product_color','like','%'.$search_product.'%');
            })->where('status',1)->count();

            $productAll = DB::table('products')->where( function($query) use ($search_product) {
                $query->where('product_name','like','%'.$search_product.'%')
                ->orwhere('product_code',$search_product)
                ->orwhere('description','like','%'.$search_product.'%')
                ->orwhere('product_color','like','%'.$search_product.'%');
            })->where('status',1)->get();

            $sizeArray = ProductsAttribute::select('size')->where('size', '!=', '')->where('size', '!=', 'tes')->groupBy('size')->get();
            $sizeArray = array_flatten(json_decode(json_encode($sizeArray),true));
            $breadcrumb = "<a href='/' style='color: darkorange;'>Home</a> / ".$search_product;

            return view('products.listing')->with(\compact('search_product','productAll','categories','banners','billboard','productCount','sizeArray','breadcrumb'));
        }
    }


    public function product($id = null)
    {
        //show 404 page if product disable
        $productsCount = Product::where(['id'=>$id, 'status'=>1])->count();
        if($productsCount == 0)
        {
            \abort(404);  
        }
        // get product detail
        $productDetails = Product::with('attributes')->where('id',$id)->first();
        
        $relatedProduct = Product::where('id', '!=' , $id )->where(['category_id'=>$productDetails->category_id])->get();
         foreach($relatedProduct->chunk(3) as $chunk)
         {
           foreach($chunk as $item)
            {
               
            }
         }
        // get all categories and subcategories
        $categories = Category::with('categories')->where(['parent_id'=>0])->get();
        $categoryDetails = Category::where(['id' => $productDetails->category_id])->first();
                
        if($categoryDetails->parent_id == 0)
        {
            $breadcrumb = "<a href='/' style='color: darkorange;'>Home</a> / <a href='/products/".$categoryDetails->url."' style='color: darkorange;' >".$categoryDetails->name."</a> / ".$productDetails->product_name;
        }else{
            $mainCategory = DB::table('categories')->where('id',$categoryDetails->parent_id)->first();
            $breadcrumb = "<a href='/' style='color: darkorange;'>Home</a> / <a href='/products/".$mainCategory->url."' style='color: darkorange;' >".$mainCategory->name."</a> / <a href='/products/".$categoryDetails->url."' style='color: darkorange;' >".$categoryDetails->name."</a> / <span style='color: cornflowerblue;' > ".$productDetails->product_name." </span>";
        }

        //Get Products Alternate Images
        $productAltImage = ProductsImage::where('product_id',$id)->get();
        // get Attribute stock
        $total_stock = ProductsAttribute::where('product_id',$id)->sum('stock');
        $billboard = DB::table('billboards')->inRandomOrder()->orderBy('id','DESC')->where('status',1)->offset(0)->limit(2)->get();
        $meta_title = $productDetails->product_name;
        $meta_description = $productDetails->description;
        $meta_keyword = $productDetails->product_name;

        return \view('products.detail')->with(\compact('productDetails','categories','productAltImage','total_stock','relatedProduct','billboard','meta_title','meta_description','meta_keyword','breadcrumb'));
    }


    public function getProductPrice(Request $request)
    {
        $data = $request->all();
        
        $proArr = \explode("-",$data['idSize']);
        $proArr = ProductsAttribute::where(['product_id' => $proArr[0], 'size' => $proArr[1]])->first();
        $getCurrencyRates = Product::getCurrencyRates($proArr->price);
        $price = Product::currencyRate($proArr->price); 
        $price = is_number($price,2);
        echo ($price."-".$proArr->price."-".$getCurrencyRates['IDR_rate']."-".$getCurrencyRates['USD_rate']."-".$getCurrencyRates['KHR_rate']."-".$getCurrencyRates['EUR_rate']);
        // echo("#");
        // echo(array("t" => $getCurrencyRates['USD_rate']));
        echo("#");
        echo($proArr->stock);

    }


    public function addtocart(Request $request)
    {
        Session::forget('CouponAmount');
        Session::forget('CouponCode');
        $data = $request->all();
        // dd($data);
        if(!empty($data['wishListButton']) && $data['wishListButton'] == "Wish List"  ) {
            // check user is login in
            if(!Auth::check()) {
                return redirect()->back()->with('flash_message_error','Please login to add product in your Wish List');
            }
            // check size is selected
            if(empty($data['size'])) {
                return redirect()->back()->with('flash_message_error', 'please select size to add product in your wish list');
            }
            // get product size
            $sizeArr = \explode("-",$data['size']);
            $product_sizes = $sizeArr[1];
            // get product price
            $proPrice = DB::table('products_attributes')->where(['product_id' => $data['product_id'], 'size' =>$product_sizes])->first();
            $product_price = $proPrice->price;
            // get user email
            $user_email = Auth::user()->email;
            // sert quantity as 1 
            $quantity = 1;
            // get current date
            $created_at = Carbon::now();

            $wishListCount = DB::table('wish_list')->where(['user_email' => $user_email, 'product_id' => $data['product_id'], 'product_color' => $data['product_color'], 'product_code' => $data['product_code'], 'size' => $product_sizes])->count();
            if($wishListCount > 0) {
                return redirect()->back()->with('flash_message_error','Product already exists in Wish List !');
            } else {
                // insert product in wishlist
                DB::table("wish_list")->insert([
                    'product_id' => $data['product_id'],
                    'product_name' => $data['product_name'],
                    'product_code' => $data['product_code'],
                    'product_color' => $data['product_color'],
                    'price' => $product_price,
                    'size' => $product_sizes,
                    'quantity' => $quantity,
                    'user_email' => $user_email,
                    'created_at' => $created_at
                ]);
                return redirect()->back()->with('flash_message_success','Product hash been added in Wish List');
            }

        } else {
            // if product added from wish list
            if(!empty($data['cartButtom']) && $data['cartButtom'] == "Add to Cart" ){
                $data['quantity'] = 1;
            }
            // check product stock is available or not
            $product_size = explode("-",$data['size']);
            $getProductStock = ProductsAttribute::where(['product_id' => $data['product_id'],'size' => $product_size["1"]])->first();
            if($getProductStock->stock < $data['quantity']) {
                return \redirect()->back()->with('flash_message_error','Required Quantity is not available !');
        }

        $session_id = Session::get('session_id');
        if(!isset($session_id))
        { 
            $session_id = \str_random(40);
            Session::put('session_id',$session_id);
        }

        $sizeArr = \explode("-",$data['size']);
        $product_sizes = $sizeArr[1];

        if(empty(Auth::check())) {
            $countProducts = DB::table('cart')->where([
                'product_id' => $data['product_id'],
                'product_color' => $data['product_color'],
                'size' => $product_sizes,
                'session_id' => $session_id
              ])->count();
    
            if($countProducts > 0)
            {
                return redirect()->back()->with('flash_message_error','Product already exists in Cart !');
            }

        } else {
            $countProducts = DB::table('cart')->where([
                'product_id' => $data['product_id'],
                'product_color' => $data['product_color'],
                'size' => $product_sizes,
                'user_email' => Auth::User()->email,
              ])->count();
    
            if($countProducts > 0)
            {
                return redirect()->back()->with('flash_message_error','Product already exists in Cart !');
            }

        } 
        if(empty($product_sizes))
        {
            return redirect()->back()->with('flash_message_error','Please select size !');
        }
        // else{
            $getSKU = ProductsAttribute::select('sku')->where(['product_id'=>$data['product_id'], 'size'=>$product_sizes ])->first();

            DB::table('cart')->insert([
                'product_id' => $data['product_id'],
                'product_name' => $data['product_name'],
                'product_code' => $getSKU->sku,
                'product_color' => $data['product_color'],
                'size' => $product_sizes,
                'price' => $data['price'],
                'quantity' => $data['quantity'],
                'user_email' => Auth::user()->email ?? "",
                'session_id' => $session_id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        // }       
            return redirect('/cart')->with('flash_message_success','Product has been added in Cart !');
        }

    }


    public function cart()
    {
        if(Auth::check())
        {
            $user_email = Auth::user()->email;
            $userCart = DB::table('cart')->where('user_email',$user_email)->get();
        }else{
            $session_id = Session::get('session_id');
            $userCart = DB::table('cart')->where('session_id',$session_id)->get();
        }

        foreach($userCart as $key => $product)
        {
            $productDetails = Product::where('id',$product->product_id)->first();
            $userCart[$key]->image = "$productDetails->image";
           
        }
 
        $meta_title ="Shopping Cart - E-commerce web";
        $meta_description = "View Shopping Cart Of E-commerce Web";
        $meta_keyword = "shopping cart, e-com Website";
        return view('products.cart')->with(\compact('userCart','meta_title','meta_description','meta_keyword'));
    }


    public function deleteCartProduct($id = null)
    {
        Session::forget('CouponAmount');
        Session::forget('CouponCode');
        
        DB::table('cart')->where('id',$id)->delete();
        return redirect('/cart')->with('flash_message_success','Product has been deleted from cart');
    }

    
    public function wishList() {
        if(Auth::check()) {
            $user_email = Auth::user()->email;
            $userWishList = DB::table('wish_list')->where('user_email',$user_email)->get();
            foreach($userWishList as $key => $product)
            {
                $productDetails = Product::where('id',$product->product_id)->first();
                $userWishList[$key]->image = "$productDetails->image";
            }
        }else {
            $userWishList = array();
        }

        $meta_title ="Wish List - E-commerce web";
        $meta_description = "View Wish List Of E-commerce Web";
        $meta_keyword = "wish list, e-com Website";
        return view('products.wish_list')->with(\compact('userWishList','meta_title','meta_description','meta_keyword'));
    }


    public function updateCartQuantity(Request $request)
    {
        Session::forget('CouponAmount');
        Session::forget('CouponCode');
        $data = $request->all();
        // return $data['upDownData'];
        if($data['upDownData'] == 0 )
        {
            $down = "1";
            DB::table('cart')->where('id',$data['idKita'])->decrement('quantity',$down);

            if(Auth::check())
            {
                $user_email = Auth::user()->email;
                $userCart = DB::table('cart')->where('user_email',$user_email)->get();
            }else{
                $session_id = Session::get('session_id');
                $userCart = DB::table('cart')->where('session_id',$session_id)->get();
            }
    
            foreach($userCart as $key => $product)
            {
                $productDetails = Product::where('id',$product->product_id)->first();
                $userCart[$key]->image = "$productDetails->image";
               
            }

            return view('products.blade_index_ajax.cartBodyFron', ['userCart' => $userCart]);

        } else if($data['upDownData'] == 1 ) {

            $getCartDetails = DB::table('cart')->where('id',$data['idKita'])->first();
            $getAttributeStock = ProductsAttribute::where('sku',$getCartDetails->product_code)->first();
            $updated_quantity = $getCartDetails->quantity+$data['upDownData'];
         
            if($getAttributeStock->stock >= $updated_quantity)
            {
                DB::table('cart')->where('id',$data['idKita'])->increment('quantity',$data['upDownData']);

                if(Auth::check())
                {
                    $user_email = Auth::user()->email;
                    $userCart = DB::table('cart')->where('user_email',$user_email)->get();
                }else{
                    $session_id = Session::get('session_id');
                    $userCart = DB::table('cart')->where('session_id',$session_id)->get();
                }
        
                foreach($userCart as $key => $product)
                {
                    $productDetails = Product::where('id',$product->product_id)->first();
                    $userCart[$key]->image = "$productDetails->image";
                   
                }
    
                return view('products.blade_index_ajax.cartBodyFron', ['userCart' => $userCart]);

            }else{

                return array('notAvailable' => 'notAvailable');    
            }
       
        }
    
    }


    public function applyCoupon(Request $request, $id = null)
    {
        Session::forget('CouponAmount');
        Session::forget('CouponCode');
        //  coupon is valid for discount // get cart total amount
        $currencyLocale = Session::get('currencyLocale');
        $session_id = Session::get('session_id');
        $data = $request->all();

        if(Auth::check())
        {
            $user_email = Auth::user()->email;
            $userCart = DB::table('cart')->where('user_email',$user_email)->get();
        }else{
            $userCart = DB::table('cart')->where('session_id',$session_id)->get();
        }

        $total_amount = 0;
        foreach($userCart as $item)
        {
            $total_amount = $total_amount + ($item->price * $item->quantity);
        }

         $couponCount = Coupon::where(['coupon_code' => $data['coupon_code']])->count();
        //  if coupon exist
         if($couponCount == 0)
         {
            $wadukteh = array(
                'total_amount' => $currencyLocale->currency_simbol.' '.is_number($total_amount,2),
                'couponNotExists' => 'couponNotExists'
            );
             return $wadukteh;
         }else{
             $couponDetails = Coupon::where('coupon_code',$data['coupon_code'])->first();
            //  if coupon active
             if($couponDetails->status == "0")
             {
                $wadukteh = array(
                    'total_amount' => $currencyLocale->currency_simbol.' '.is_number($total_amount,2),
                    'couponNotActive' => 'couponNotActive'
                );
                return $wadukteh;
             }
             
             $expiry_date = $couponDetails->expiry_date;
             $current_date = Date('Y-m-d');
            //  if coupon expiry
             if($expiry_date < $current_date)
             {
                $wadukteh = array(
                    'total_amount' => $currencyLocale->currency_simbol.' '.is_number($total_amount,2),
                    'couponExpired' => 'couponExpired'
                );
                return $wadukteh;
             }

            if($couponDetails->amount_type == "Fixed")
            {
                $couponAmount = $couponDetails->amount;
            }else{
                $couponAmount = $total_amount * ($couponDetails->amount/100);
            }
            //  add coupon code & amount in session

            Session::put('CouponAmount',$couponAmount);
            Session::put('CouponCode',$data['coupon_code']);

            $wadukteh = array(
                'subTotal' => $currencyLocale->currency_simbol.' '.is_number($total_amount,2),
                'couponDiscount' => $currencyLocale->currency_simbol.' '.is_number( Product::currencyRate($couponAmount),2),
                'grandTotal' => $currencyLocale->currency_simbol.' '.is_number($total_amount - $couponAmount,2),
                'couponCodeSuccessfullyApplied' => 'couponCodeSuccessfullyApplied'
            );
            return $wadukteh;
            // return redirect()->back()->with('flash_message_success','Coupon code successfully applied. You are availing discount !');

         }
    }


    public function checkout(Request $request)
    {
        $user_id = Auth::user()->id;
        $user_email = Auth::user()->email;
        $userDetails = DB::table('users as u')
        ->leftJoin('users_biodata as ub', 'u.id', '=', 'ub.user_id')
        ->where('u.id', $user_id)
        ->select('u.*', 'ub.user_id', 'ub.address', 'ub.city', 'ub.state', 'ub.country', 'ub.pincode', 'ub.mobile')
        ->first();
        $countries = Country::get();

        // check if shipping exist
        $shippingCount = DeliveryAddress::where('user_id',$user_id)->count();
        $shippingDetails = Array();
        if($shippingCount > 0)
        {
            $shippingDetails = DeliveryAddress::where('user_id',$user_id)->first();
        }
        // update cart table with user email
        $session_id = Session::get('session_id');

        DB::table('cart')->where(['session_id' => $session_id])->update(['user_email' => $user_email]);

        if($request->isMethod('post'))
        { 
            $data = $request->all();

            if(empty($data['billing_name']) || 
               empty($data['billing_address']) || 
               empty($data['billing_city']) || 
               empty($data['billing_state']) || 
               empty($data['billing_country']) || 
               empty($data['billing_pincode']) || 
               empty($data['billing_mobile']) || 
               empty($data['shipping_name']) ||
               empty($data['shipping_address']) ||
               empty($data['shipping_city']) ||
               empty($data['shipping_state']) ||
               empty($data['shipping_country']) ||
               empty($data['shipping_pincode']) || 
               empty($data['shipping_mobile']))
               {
                return redirect()->back()->with('flash_message_error','Please fill all field to checkout !');
               }
            //    user update
            User::where('id',$user_id)->update([ 'name' => $data['billing_name'] ]);
            $userBioCount = DB::table('users_biodata')->where('user_id', $user_id)->get()->count();

            $dataMereka = [
                'address' => $data['billing_address'],
                'city' => $data['billing_city'],
                'state' => $data['billing_state'],
                'country' => $data['billing_country'],
                'pincode' => $data['billing_pincode'],
                'mobile' => $data['billing_mobile'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if($userBioCount > 0 ) {
                DB::table('users_biodata')->where('user_id', $user_id)->update($dataMereka);
            } else {
                DB::table('users_biodata')->where('user_id', $user_id)->insert($dataMereka);
            }

            if($shippingCount > 0)
            {
                // update shipping address
                DeliveryAddress::where('user_id',$user_id)->update([
                    'name' => $data['shipping_name'],
                    'address' => $data['shipping_address'],
                    'city' => $data['shipping_city'],
                    'state' => $data['shipping_state'],
                    'country' => $data['shipping_country'],
                    'pincode' => $data['shipping_pincode'],
                    'mobile' => $data['shipping_mobile']]);
            }else{
                // Add new shipping address
                $shipping = New DeliveryAddress;
                $shipping->user_id = $user_id;
                $shipping->user_email = $user_email;
                $shipping->name = $data['shipping_name'];
                $shipping->address = $data['shipping_address'];
                $shipping->city = $data['shipping_city'];
                $shipping->state = $data['shipping_state'];
                $shipping->country = $data['shipping_country'];
                $shipping->pincode = $data['shipping_pincode'];
                $shipping->mobile = $data['shipping_mobile'];
                $shipping->save();
            }
                $pincodeCount = DB::table('pincodes')->where('pincode',$data['shipping_pincode'])->count();
                if($pincodeCount == 0 ) {
                    return \redirect()->back()->with('flash_message_error','Your location is not available for delivery. please choose another location');
                }
            return redirect()->action('ProductsController@orderReview');
            
        }
        
        $meta_title ="Checkout - E-com Website";
        return view('products.checkout')->with(\compact('userDetails','countries','shippingCount','shippingDetails','meta_title'));

    }


    public function orderReview()
    {
        $user_id = Auth::user()->id;
        $user_email = Auth::user()->email;
        $shippingDetails = DeliveryAddress::where('user_id',$user_id)->first();
        $userDetails = DB::table('users as u')
        ->leftJoin('users_biodata as ub', 'u.id', '=', 'ub.user_id')
        ->where('u.id', $user_id)
        ->select('u.*', 'ub.user_id', 'ub.address', 'ub.city', 'ub.state', 'ub.country', 'ub.pincode', 'ub.mobile')
        ->first();

        $userCart = DB::table('cart')->where('user_email',$user_email)->get();
        $total_weight = 0;
        foreach($userCart as $key => $product)
        {
            $productDetails = Product::where('id',$product->product_id)->first();
            $userCart[$key]->image = "$productDetails->image";   
            $total_weight = $total_weight + $productDetails->weight;
        }
        $codPincodeCount = DB::table('cod_pincodes')->where('pincode',$shippingDetails->pincode)->count();
        $prepaidPincodeCount = DB::table('prepaid_pincodes')->where('pincode',$shippingDetails->pincode)->count();
        //  fetch shipping charges
        $shippingCharges = Product::getShippingCharges($total_weight, $shippingDetails->country);
        Session::put("ShippingCharges",$shippingCharges);

        $meta_title ="Order Review - E-com Website";
        return view('products.order_review')->with(\compact('shippingDetails','userDetails','userCart','meta_title','codPincodeCount','prepaidPincodeCount','shippingCharges'));
    }
    
    
    public function PlaceOrder(Request $request)
    {
        if($request->isMethod('post'))
        {
            $data = $request->all();

            if(empty($data['payment_method'])) {
                $request->validate([
                    'payment_method' => 'required'
                ]);
                return back()->with('flash_message_error','Please select method payment');
            }

            $user_id = Auth::user()->id;
            $user_email = Auth::user()->email;
            // Prevent out of stock produck from ordering
            $userCart = DB::table('cart')->where('user_email',$user_email)->get();
            foreach($userCart as $cart) {
                $getAttributeCount = Product::getAttributeCount($cart->product_id, $cart->size); 
                if($getAttributeCount == 0) {
                    Product::deleteCartProduct($cart->product_id, $user_email);
                    return redirect('/cart')->with('flash_message_error','One the product is not available. Try Again !');
                }

                $product_stock = Product::getProductStock($cart->product_id, $cart->size); 
                if($product_stock == 0) {
                    Product::deleteCartProduct($cart->product_id, $user_email);
                    return redirect('/cart')->with('flash_message_error','Sold Out Product remove from cart. Try Again !');
                }

               if($cart->quantity > $product_stock) {
                    return redirect('/cart')->with('flash_message_error','Reduce Product Stock and try again.');
               }

               $product_status = Product::getProductStatus($cart->product_id);
               if($product_status == 0) {
                  Product::deleteCartProduct($cart->product_id, $user_email);
                  return redirect('/cart')->with('flash_message_error','Disable Product remove from cart. Please try again. !');
               }

               $getCategoryId = DB::table('products')->select('category_id')->where('id',$cart->product_id)->first();
               $category_status = Product::getCategoryStatus($getCategoryId->category_id);
               if($category_status == 0) {
                    Product::deleteCartProduct($cart->product_id, $user_email);
                    return redirect('/cart')->with('flash_message_error','One of the product category is disabled. Please try again. !');
               }
            }

            // get shippong address off user
            $shippingDetails = DeliveryAddress::where(['user_email' => $user_email])->first();
            $pincodeCount = DB::table('pincodes')->where('pincode',$shippingDetails->pincode)->count();

            if($pincodeCount == 0 ) {
                return \redirect()->back()->with('flash_message_error','Your location is not available for delivery. please choose another location');
            }
           /*  //  fetch shipping charges
            $shippingCharges = Product::getShippingCharges($shippingDetails->country);  */
            $grandTotal = Product::getGrandTotal();
  
            $order = new Order;
            $order->user_id = $user_id;
            $order->user_email = $user_email;
            $order->name = $shippingDetails->name;
            $order->address = $shippingDetails->address;
            $order->city = $shippingDetails->city;
            $order->state = $shippingDetails->state;
            $order->pincode = $shippingDetails->pincode;
            $order->country = $shippingDetails->country;
            $order->mobile = $shippingDetails->mobile;
            $order->coupon_code = Session::get('CouponCode') ?? "";
            $order->coupon_amount = Session::get('CouponAmount') ?? "";
            $order->order_status = "New";
            $order->payment_method = $data['payment_method'];
            $order->shipping_charges = Session::get('ShippingCharges');
            $order->grant_total = $grandTotal;
            $order->save();

            $order_id = DB::getPdo()->lastInsertId();
        
            $cartProducts = DB::table('cart')->where(['user_email' => $user_email])->get();

            foreach($cartProducts as $pro)
            {
               $cartPro = new OrdersProduct;
               $cartPro->order_id = $order_id;
               $cartPro->user_id = $user_id;
               $cartPro->product_id = $pro->product_id;
               $cartPro->product_code = $pro->product_code;
               $cartPro->product_name = $pro->product_name;
               $cartPro->product_size = $pro->size;
               $cartPro->product_color = $pro->product_color;
               $cartPro->product_price = $pro->price;
               $cartPro->product_qty = $pro->quantity;
               $cartPro->save();

              //  reduce stoct script starrt
              $getProductStock = DB::table('products_attributes')->where('sku',$pro->product_code)->first();
              $newStock = $getProductStock->stock - $pro->quantity;
              if($newStock <0) {
                  $newStock = 0;
              }
              DB::table('products_attributes')->where('sku',$pro->product_code)->update(['stock' => $newStock]);
            //   dd("Original Stock :".$getProductStock->stock, "Stock to reduce :".$pro->quantity);
            }

            Session::put('order_id',$order_id);
            Session::put('grant_total',$grandTotal);

            if($data['payment_method'] == "COD")
            {
                $productDetails = Order::with('orders')->where('id', $order_id)->first();
                // $productDetails = json_decode(json_encode($productDetails));
                $userDetails = DB::table('users as u')
                ->leftJoin('users_biodata as ub', 'u.id', '=', 'ub.user_id')
                ->where('u.id', $user_id)
                ->select('u.*', 'ub.user_id', 'ub.address', 'ub.city', 'ub.state', 'ub.country', 'ub.pincode', 'ub.mobile')
                ->first();
                // dd($userDetails);
                // for order email
                $email = $user_email;
                $messageData = [
                    'email' => $email,
                    'name' => $shippingDetails->name,
                    'order_id' => $order_id,
                    'productDetails' => $productDetails,
                    'userDetails' =>  $userDetails,
                ];
                Mail::send('emails.order',$messageData, function($message) use($email) {
                    $message->to($email)->subject('Order Placed - E-com Husin');
                });
                // end order email
                return redirect('/thanks');
            }else{
                return redirect('/paypal');
            }
            
        }
    }


    public function thanks(Request $request)
    {
        $user_email = Auth::user()->email;
        DB::table('cart')->where('user_email',$user_email)->delete();

        return view('orders.thanks');
    }

    
    public function thanksPaypal() 
    {
        return view('orders.thanks_paypal');
    }


    public function cancelPaypal() 
    {
        return view('orders.cancel_paypal');
    }


    public function paypal(Request $request)
    {
        $user_email = Auth::user()->email;
        DB::table('cart')->where('user_email',$user_email)->delete();
        
        return view('orders.paypal');
    }


    public function ipnPaypal(Request $request) {
        $data = $request->all();
        if($data['payment_status']=="Completed") {
            // wee will send email to user/admin
            // update order status to payment captured

            // get order id
            $order_id = Session::get('order_id');

            // update order
            Order::where('id',$order_id)->update(['order_status' => 'Payment Captured']);
            $productDetails = Order::with('orders')->where('id',$order_id)->first();
            $productDetails = json_decode(json_encode($productDetails));

            $user_id = $productDetails['user_id'];
            $user_email = $productDetails['user_email'];
            $name = $productDetails['name'];

            $userDetails = User::where('id',$user_id)->first();
            $userDetails = json_decode(json_encode($userDetails));

            $email = $user_email;
            $messageData = [
                'email' => $email,
                'name' => $name,
                'order_id' => $order_id,
                'productDetails' => $productDetails,
                'userDetails' => $userDetails
            ];

            Mail::send('emails.order',$messageData, function($message) use ($email) {
                $message->to($email)->subject('Order Placed - E-com Website');
            });

            DB::table('cart')->where('user_email',$user_email)->delete();
        

        }
    }
    

    public function userOrders()
    {
        $user_id = Auth::user()->id;
        $orders = Order::with('orders')->where('user_id',$user_id)->orderBy('id','DESC')->get();
        return view('orders.users_orders')->with(\compact('orders'));
    }


    public function userOrderDetails($order_id)
    {
        $user_id = Auth::user()->id;
        $orderDetails = Order::with('orders')->where('id',$order_id)->first();

        return \view('orders.users_order_details')->with(\compact('orderDetails'));
    }

     
    public function viewOrders() 
    {
        $orders = Order::with('orders')->orderBy('id','desc')->get();
        return view('admin.orders.view_orders')->with(\compact('orders'));
    }


    public function checkPincode(Request $request) {

        if($request->isMethod('post')) {
            $data = $request->all();
            $pincodeCount = DB::table('pincodes')->where('pincode',$data['pincode'])->count();
            if($pincodeCount > 0 ) {
                echo true;
            } else {
                echo false;
            }

        }
        // $data = $request->all();
        // echo "<pre>"; print_r($data); die;
    }


    public function deleteWishlistProduct($id = null) {
        DB::table('wish_list')->where('id', $id)->delete();
        return redirect()->back()->with('flash_message_success','Product has been delete from Wish List');
    }





}