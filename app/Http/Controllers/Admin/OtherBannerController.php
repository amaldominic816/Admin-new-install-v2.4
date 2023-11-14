<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ModuleWiseBanner;
use App\Models\ModuleWiseWhyChoose;
use App\Models\Translation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Config;

class OtherBannerController extends Controller
{
    function promotional_index()
    {
        $module_type = Config::get('module.current_module_type');
        return view("admin-views.other-banners.{$module_type}-index");
    }
    function promotional_why_choose()
    {
        $module_id = Config::get('module.current_module_id');
        $banners = ModuleWiseWhyChoose::where('module_id',$module_id)->get();
        return view("admin-views.other-banners.parcel-why-choose",compact('banners'));
    }
    function promotional_video()
    {
        $module_type = Config::get('module.current_module_type');
        return view("admin-views.other-banners.parcel-video");
    }

    function promotional_store(Request $request)
    {
        $request->validate([
            'image' => 'required|max:2048',
        ]);

        $module_id = Config::get('module.current_module_id');
        $module_type = Config::get('module.current_module_type');

        if($module_type == 'parcel'){

            ModuleWiseBanner::insert(
                [
                    'module_id' => $module_id,
                    'key' => $request->key,
                    'type' => 'promotional_banner',
                    'value' => Helpers::upload('promotional_banner/', 'png', $request->file('image'))
                ]
            );

            Toastr::success(translate('messages.banner_setup_updated'));
            return back();
        }

        ModuleWiseBanner::updateOrInsert(
            [
                'module_id' => $module_id,
                'key' => $request->key,
                'type' => 'promotional_banner',
            ],
            ['value' => Helpers::upload('promotional_banner/', 'png', $request->file('image'))]
        );

        Toastr::success(translate('messages.banner_setup_updated'));
        return back();
    }

    function promotional_edit($id)
    {
        $banner = ModuleWiseBanner::find($id);
        return view("admin-views.other-banners.parcel-promotional-edit", compact('banner'));
    }

    function promotional_update(Request $request,$id)
    {
        $banner = ModuleWiseBanner::find($id);
        $banner->value = $request->has('image') ? Helpers::update('promotional_banner/', $banner->value, 'png', $request->file('image')) : $banner->value;
        $banner->save();

        Toastr::success(translate('messages.banner_updated'));
        return redirect()->route('admin.promotional-banner.add-new');
    }

    public function promotional_status(Request $request)
    {
        if (env('APP_MODE') == 'demo' && $request->id == 1) {
            Toastr::warning('Sorry!You can not inactive this banner!');
            return back();
        }
        $banner = ModuleWiseBanner::findOrFail($request->id);
        $banner->status = $request->status;
        $banner->save();
        Toastr::success(translate('messages.banner_status_updated'));
        return back();
    }

    public function promotional_destroy(ModuleWiseBanner $banner)
    {
        if (env('APP_MODE') == 'demo' && $banner->id == 1) {
            Toastr::warning(translate('messages.you_can_not_delete_this_banner_please_add_a_new_banner_to_delete'));
            return back();
        }
        $banner->delete();
        Toastr::success(translate('messages.banner_deleted_successfully'));
        return back();
    }

    function why_choose_store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'short_description' => 'required',
            'image' => 'required',
        ]);
        if($request->title[array_search('default', $request->lang)] == ''){
            Toastr::error(translate('default_data_is_required'));
            return back();
        }
        $module_id = Config::get('module.current_module_id');
        $banner = new ModuleWiseWhyChoose();
        $banner->title = $request->title[array_search('default', $request->lang)];
        $banner->short_description = $request->short_description[array_search('default', $request->lang)];
        $banner->image = Helpers::upload('why_choose/', 'png', $request->file('image'));
        $banner->module_id = $module_id;
        $banner->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'ModuleWiseWhyChoose', data_id: $banner->id, data_value: $banner->title);
        Helpers::add_or_update_translations(request: $request, key_data: 'short_description', name_field: 'short_description', model_name: 'ModuleWiseWhyChoose', data_id: $banner->id, data_value: $banner->short_description);

        Toastr::success(translate('messages.banner_added_successfully'));
        return back();
    }

    function why_choose_edit($id)
    {
        $banner = ModuleWiseWhyChoose::withoutGlobalScope('translate')->find($id);
        return view("admin-views.other-banners.parcel-why-choose-edit", compact('banner'));
    }

    function why_choose_update(Request $request,$id)
    {
        $banner = ModuleWiseWhyChoose::find($id);
        $banner->title = $request->title[array_search('default', $request->lang)];
        $banner->short_description = $request->short_description[array_search('default', $request->lang)];
        $banner->image = $request->has('image') ? Helpers::update('why_choose/', $banner->image, 'png', $request->file('image')) : $banner->image;
        $banner->save();

        Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'ModuleWiseWhyChoose', data_id: $banner->id, data_value: $banner->title);
        Helpers::add_or_update_translations(request: $request, key_data: 'short_description', name_field: 'short_description', model_name: 'ModuleWiseWhyChoose', data_id: $banner->id, data_value: $banner->short_description);

        Toastr::success(translate('messages.banner_updated'));
        return redirect()->route('admin.promotional-banner.add-why-choose');
    }

    public function why_choose_status(Request $request)
    {
        if (env('APP_MODE') == 'demo' && $request->id == 1) {
            Toastr::warning('Sorry!You can not inactive this banner!');
            return back();
        }
        $banner = ModuleWiseWhyChoose::findOrFail($request->id);
        $banner->status = $request->status;
        $banner->save();
        Toastr::success(translate('messages.banner_status_updated'));
        return back();
    }

    public function why_choose_destroy(ModuleWiseWhyChoose $banner)
    {
        if (env('APP_MODE') == 'demo' && $banner->id == 1) {
            Toastr::warning(translate('messages.you_can_not_delete_this_banner_please_add_a_new_banner_to_delete'));
            return back();
        }
        $banner->delete();
        Toastr::success(translate('messages.banner_deleted_successfully'));
        return back();
    }

    function video_content_store(Request $request)
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));
            return back();
        }

        $request->validate([
            'content1_title.0' => 'required',
            'content1_subtitle.0' => 'required',
            'content2_title.0' => 'required',
            'content2_subtitle.0' => 'required',
            'content3_title.0' => 'required',
            'content3_subtitle.0' => 'required',
        ], [
            'content1_title.0.required' => translate('messages.default_content1_title_is_required'),
            'content1_subtitle.0.required' => translate('messages.condefault_tent1_subtitle_is_required'),
            'content2_title.0.required' => translate('messages.default_content2_title_is_required'),
            'content2_subtitle.0.required' => translate('messages.condefault_tent2_subtitle_is_required'),
            'content3_title.0.required' => translate('messages.default_content3_title_is_required'),
            'content3_subtitle.0.required' => translate('messages.condefault_tent3_subtitle_is_required'),
        ]);

        $module_id = Config::get('module.current_module_id');

        $content1_title = ModuleWiseBanner::firstOrNew([
            'module_id' => $module_id,
            'type' => 'video_banner_content',
            'key' => 'content1_title',
        ]);

        $content1_title->value = $request->content1_title[array_search('default', $request->lang)];
        $content1_title->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'content1_title', name_field: 'content1_title', model_name: 'ModuleWiseBanner', data_id: $content1_title->id, data_value: $content1_title->value);

        $content1_subtitle = ModuleWiseBanner::firstOrNew([
            'module_id' => $module_id,
            'type' => 'video_banner_content',
            'key' => 'content1_subtitle',
        ]);

        $content1_subtitle->value = $request->content1_subtitle[array_search('default', $request->lang)];
        $content1_subtitle->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'content1_subtitle', name_field: 'content1_subtitle', model_name: 'ModuleWiseBanner', data_id: $content1_subtitle->id, data_value: $content1_subtitle->value);

        $content2_title = ModuleWiseBanner::firstOrNew([
            'module_id' => $module_id,
            'type' => 'video_banner_content',
            'key' => 'content2_title',
        ]);

        $content2_title->value = $request->content2_title[array_search('default', $request->lang)];
        $content2_title->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'content2_title', name_field: 'content2_title', model_name: 'ModuleWiseBanner', data_id: $content2_title->id, data_value: $content2_title->value);

        $content2_subtitle = ModuleWiseBanner::firstOrNew([
            'module_id' => $module_id,
            'type' => 'video_banner_content',
            'key' => 'content2_subtitle',
        ]);

        $content2_subtitle->value = $request->content2_subtitle[array_search('default', $request->lang)];
        $content2_subtitle->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'content2_subtitle', name_field: 'content2_subtitle', model_name: 'ModuleWiseBanner', data_id: $content2_subtitle->id, data_value: $content2_subtitle->value);

        $content3_title = ModuleWiseBanner::firstOrNew([
            'module_id' => $module_id,
            'type' => 'video_banner_content',
            'key' => 'content3_title',
        ]);

        $content3_title->value = $request->content3_title[array_search('default', $request->lang)];
        $content3_title->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'content3_title', name_field: 'content3_title', model_name: 'ModuleWiseBanner', data_id: $content3_title->id, data_value: $content3_title->value);

        $content3_subtitle = ModuleWiseBanner::firstOrNew([
            'module_id' => $module_id,
            'type' => 'video_banner_content',
            'key' => 'content3_subtitle',
        ]);

        $content3_subtitle->value = $request->content3_subtitle[array_search('default', $request->lang)];
        $content3_subtitle->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'content3_subtitle', name_field: 'content3_subtitle', model_name: 'ModuleWiseBanner', data_id: $content3_subtitle->id, data_value: $content3_subtitle->value);

        Toastr::success(translate('messages.video_/_image_content_setup_updated'));
        return back();
    }
    function video_image_store(Request $request)
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));
            return back();
        }

        $request->validate([
            'section_title' => 'required',
            'banner_type' => 'required',
            'banner_video' => 'required_if:banner_type,video'
        ]);

        $module_id = Config::get('module.current_module_id');

        $section_title = ModuleWiseBanner::firstOrNew([
            'module_id' => $module_id,
            'type' => 'video_banner_content',
            'key' => 'section_title',
        ]);

        $section_title->value = $request->section_title[array_search('default', $request->lang)];
        $section_title->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'section_title', name_field: 'section_title', model_name: 'ModuleWiseBanner', data_id: $section_title->id, data_value: $section_title->value);

        $banner_type = ModuleWiseBanner::firstOrNew([
            'module_id' => $module_id,
            'type' => 'video_banner_content',
            'key' => 'banner_type',
        ]);

        $banner_type->value = $request->banner_type;
        $banner_type->save();

        $banner_video = ModuleWiseBanner::firstOrNew([
            'module_id' => $module_id,
            'type' => 'video_banner_content',
            'key' => 'banner_video',
        ]);

        $banner_video->value = $request->banner_video??$banner_video->value;
        $banner_video->save();

        $banner_image = ModuleWiseBanner::firstOrNew([
            'module_id' => $module_id,
            'type' => 'video_banner_content',
            'key' => 'banner_image',
        ]);

        $banner_image->value = $request->has('banner_image') ? Helpers::update('promotional_banner/', $banner_image->value, 'png', $request->file('banner_image')) :$banner_image->value;
        $banner_image->save();

        Toastr::success(translate('messages.video_/_image_content_setup_updated'));
        return back();
    }
}
