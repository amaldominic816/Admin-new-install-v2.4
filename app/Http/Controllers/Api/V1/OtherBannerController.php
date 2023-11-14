<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleWiseBanner;
use App\Models\ModuleWiseWhyChoose;
use Illuminate\Http\Request;

class OtherBannerController extends Controller
{
    public function get_banners(Request $request)
    {
        $module_id= $request->header('moduleId');

        $module = Module::find($module_id);

        $banners=ModuleWiseBanner::Active()->where('module_id', $module_id)->where('type','promotional_banner')->get();

        $bannerData = [];

        if($module->module_type == 'parcel'){
            $bannerData['banners'] = $banners;
        }else{
            foreach ($banners as $banner) {
                $key = $banner->key;
                $value = $banner->value;
                $bannerData[$key] = $value;
            }
        }

        $data =  [
            'promotional_banner_url' => asset('storage/app/public/promotional_banner')
        ];

        $data = array_merge($data, $bannerData);
        
        return response()->json($data, 200);

    }

    public function get_video_content(Request $request)
    {
        $module_id= $request->header('moduleId');

        $banners=ModuleWiseBanner::Active()->where('module_id', $module_id)->where('type','video_banner_content')->whereIn('key', ['section_title','banner_type','banner_video','banner_image'])->get();

        $bannerData = [];

        foreach ($banners as $banner) {
            $key = $banner->key;
            $value = $banner->value;
            $bannerData[$key] = $value;
        }

        $banner_contents=ModuleWiseBanner::Active()->where('module_id', $module_id)->where('type','video_banner_content')->whereIn('key', ['content1_title','content1_subtitle','content2_title','content2_subtitle','content3_title','content3_subtitle'])->get();

        $data =  [
            'promotional_banner_url' => asset('storage/app/public/promotional_banner'),
            'banner_contents' => $banner_contents
        ];
        $data = array_merge($data, $bannerData);
        return response()->json($data, 200);

    }

    public function get_why_choose(Request $request)
    {
        $module_id= $request->header('moduleId');

        $banners=ModuleWiseWhyChoose::Active()->where('module_id', $module_id)->get();

        $data =  [
            'why_choose_url' => asset('storage/app/public/why_choose'),
            'banners' => $banners
        ];
        return response()->json($data, 200);

    }
}
