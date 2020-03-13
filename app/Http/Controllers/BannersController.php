<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Image;
use App\Banner;

class BannersController extends Controller
{
    public function addBanner(Request $request) 
    {
        if($request->isMethod('post'))
        {
            $data = $request->all();
            $banner = new Banner;
          if($request->hasFile('image'))
          {
            $files = $request->file('image');
            // upload image after resize 
            $extension = $files->getClientOriginalExtension();
            $filename = rand(111,99999).'.'.$extension;
            $banner_patch = 'images/backend_images/banners/'.$filename;
            Image::make($files)->resize(1140, 340)->save($banner_patch);
            $banner->image = $filename;
             
          }
            $banner->title = $data['title'];
            $banner->link = $data['link'];
            $banner->status = $data['status'] ?? 0;
            $banner->save();

          return redirect('/admin/add-banner')->with('flash_message_success','Banner Image Has Ben Added Successfully');
        }
        return view('admin.banners.add_banner');
    }


    public function editBanner(Request $request, $id = null)
    {
        if($request->isMethod('post'))
        {
            $data = $request->all();
            if($request->hasFile('image'))
          {
            $files = $request->file('image');
            // upload image after resize 
            $extension = $files->getClientOriginalExtension();
            $filename = rand(111,99999).'.'.$extension;
            $banner_patch = 'images/backend_images/banners/'.$filename;
            Image::make($files)->resize(1140, 340)->save($banner_patch);
          }

          Banner::where('id',$id)->update([
              'image' => $filename ?? $data['current_image'],
              'title' => $data['title'] ?? "",
              'link' => $data['link'] ?? "",
              'status' => $data['status'] ?? "0",
          ]);

          return redirect()->back()->with('flash_message_success','Banner Image Has Ben Updated Successfully !');
        }

        $bannerDetails = Banner::where('id',$id)->first();
 
        return view('admin.banners.edit_banner')->with(\compact('bannerDetails'));
    }


    public function deleteBanner($id = null)
    {
      $banner = DB::table('banners')->where('id',$id)->first();
      $banner_patch = 'images/backend_images/banners/';
      // Delete large image if not exist folder
      if(\file_exists($banner_patch.$banner->image))
      {
          \unlink($banner_patch.$banner->image);
      }

        Banner::where('id',$id)->delete();
        return redirect()->back()->with('flash_message_success','Banner Image Has Ben Deleted !');
    }


    public function viewBanners()
    {
        $banners = Banner::orderBy('created_at','DESC')->get();

        return \view('admin.banners.view_banner')->with(\compact('banners'));
    }




}
