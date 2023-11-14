<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Models\Tag;
use App\Models\Item;
use App\Models\Review;
use App\Models\Category;
use App\Scopes\StoreScope;
use App\Models\TempProduct;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use App\Models\PharmacyItemDetails;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{

    public function store(Request $request)
    {
        if(!$request->vendor->stores[0]->item_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'image' => 'required',
            'price' => 'required|numeric|min:0.01',
            'discount' => 'required|numeric|min:0',
            'translations'=>'required',
        ], [
            'category_id.required' => translate('messages.category_required'),
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }

        $data = json_decode($request->translations, true);

        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', translate('messages.Name and description in english is required'));
        }

        if ($request['price'] <= $dis || count($data) < 1 || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 402);
        }

        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if(isset($tags)){
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => $value]
                );
                $tag->save();
                array_push($tag_ids,$tag->id);
            }
        }

        $item = new Item;
        $item->name = $data[0]['value'];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }
        $item->category_id = $request->sub_category_id?$request->sub_category_id:$request->category_id;
        $item->category_ids = json_encode($category);
        $item->description = $data[1]['value'];

        $choice_options = [];
        if ($request->has('choice')) {
            foreach (json_decode($request->choice_no) as $key => $no) {
                $str = 'choice_options_' . $no;
                if ($request[$str][0] == null) {
                    $validator->getMessageBag()->add('name', translate('messages.attribute_choice_option_value_can_not_be_null'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $i['name'] = 'choice_' . $no;
                $i['title'] = json_decode($request->choice)[$key];
                $i['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', json_decode($request[$str]))));
                array_push($choice_options, $i);
            }
        }
        $item->choice_options = json_encode($choice_options);
        $variations = [];
        $options = [];
        if ($request->has('choice_no')) {
            foreach (json_decode($request->choice_no) as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('|', json_decode($request[$name]));
                array_push($options, explode(',', $my_str));
            }
        }
        //Generates the combinations of customer choice options
        $combinations = Helpers::combinations($options);
        if (count($combinations[0]) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $k => $i) {
                    if ($k > 0) {
                        $str .= '-' . str_replace(' ', '', $i);
                    } else {
                        $str .= str_replace(' ', '', $i);
                    }
                }
                $i = [];
                $i['type'] = $str;
                $i['price'] = abs($request['price_' . str_replace('.', '_', $str)]);
                $i['stock'] = abs($request['stock_' . str_replace('.', '_', $str)]);
                array_push($variations, $i);
            }
        }


        $images = [];

        if($request->item_id && $request?->product_gellary == 1 ){
            $item_data= Item::withoutGlobalScope(StoreScope::class)->select(['image','images'])->findOrfail($request->item_id);

            if(!$request->has('image')){
                $oldPath = storage_path("app/public/product/{$item_data->image}");
                $newFileName =\Carbon\Carbon::now()->toDateString() . "-" . uniqid() . ".png" ;
                $newPath = storage_path("app/public/product/{$newFileName}");
                if (File::exists($oldPath)) {
                    File::copy($oldPath, $newPath);
                }
            }

            $uniqueValues = array_diff($item_data->images, explode(",", $request->removedImageKeys));

            foreach($uniqueValues as$key=> $value){
                $oldPath = storage_path("app/public/product/{$value}");
                $newFileName =\Carbon\Carbon::now()->toDateString() . "-" . uniqid() . ".png" ;
                $newPath = storage_path("app/public/product/{$newFileName}");
                if (File::exists($oldPath)) {
                    File::copy($oldPath, $newPath);
                }
                $images[]=$newFileName;
            }
        }



        if (!empty($request->file('item_images'))) {
            foreach ($request->item_images as $img) {
                $image_name = Helpers::upload('product/', 'png', $img);
                $images[]=$image_name;
            }
        }

        //combinations end
        $item->variations = json_encode($variations);

        $food_variations = [];
        if(isset($request->options))
        {
            foreach(json_decode($request->options, true) as $option)
            {
                $temp_variation['name']= $option['name'];
                $temp_variation['type']= $option['type'];
                $temp_variation['min']= $option['min'] ?? 0;
                $temp_variation['max']= $option['max'] ?? 0;
                $temp_variation['required']= $option['required']??'off';
                $temp_value = [];
                foreach($option['values'] as $value)
                {
                    if(isset($value['label'])){
                        $temp_option['label'] = $value['label'];
                    }
                    $temp_option['optionPrice'] = $value['optionPrice'];
                    array_push($temp_value,$temp_option);
                }
                $temp_variation['values']= $temp_value;
                array_push($food_variations,$temp_variation);
            }
        }
        //combinations end
        $item->food_variations = json_encode($food_variations);
        $item->price = $request->price;
        $item->image =  $request->has('image') ? Helpers::upload('product/', 'png', $request->file('image')) : $newFileName ?? null;
        $item->available_time_starts = $request->available_time_starts;
        $item->available_time_ends = $request->available_time_ends;
        $item->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        $item->discount_type = $request->discount_type;
        $item->maximum_cart_quantity = $request->maximum_cart_quantity;
        $item->attributes = $request->has('attribute_id') ? $request->attribute_id : json_encode([]);
        $item->add_ons = $request->has('addon_ids') ? json_encode(explode(',',$request->addon_ids)) : json_encode([]);
        $item->store_id = $request['vendor']->stores[0]->id;
        $item->veg = $request->veg;
        $item->module_id = $request['vendor']->stores[0]->module_id;
        $item->stock= $request->current_stock;
        $item->images = $images;
        $item->unit_id = $request->unit;
        $item->organic = $request->organic??0;
        $item->save();
        $item->tags()->sync($tag_ids);


        if ($request['vendor']->stores[0]->module->module_type == 'pharmacy') {
            $item_details = new PharmacyItemDetails();
            $item_details->item_id = $item->id;
            $item_details->common_condition_id = $request->condition_id;
            $item_details->is_basic = $request->basic ?? 0;
            $item_details->save();
        }

        foreach ($data as $key=>$i) {
            $data[$key]['translationable_type'] = 'App\Models\Item';
            $data[$key]['translationable_id'] = $item->id;
        }
        Translation::insert($data);

        $product_approval_datas = \App\Models\BusinessSetting::where('key', 'product_approval_datas')->first()?->value ?? '';
        $product_approval_datas =json_decode($product_approval_datas , true);
        if (Helpers::get_mail_status('product_approval') && data_get($product_approval_datas,'Add_new_product',null) == 1) {
            $this->store_temp_data($item, $request,$tag_ids);
            $item->is_approved = 0;
            $item->save();
            return response()->json(['success' => translate('messages.The_product_will_be_published_once_it_receives_approval_from_the_admin.')], 200);

        }


        return response()->json(['message'=>translate('messages.product_added_successfully')], 200);
    }

    public function status(Request $request)
    {
        if(!$request->vendor->stores[0]->item_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $product = Item::find($request->id);
        $product->status = $request->status;
        $product->save();

        return response()->json(['message' => translate('messages.product_status_updated')], 200);
    }

    public function get_item($id)
    {
        try {
            $item = Item::withoutGlobalScope('translate')->with('tags')->where('id',$id)
            ->first();
            $item = Helpers::product_data_formatting_translate($item, false, false, app()->getLocale());
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
            ], 404);
        }
    }

    public function update(Request $request)
    {
        if(!$request->vendor->stores[0]->item_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'category_id' => 'required',
            'price' => 'required|numeric|min:0.01',
            'discount' => 'required|numeric|min:0',

        ], [
            'category_id.required' => translate('messages.category_required'),
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }
        $data = json_decode($request->translations, true);

        if (count($data) < 1) {
            $validator->getMessageBag()->add('translations', translate('messages.Name and description in english is required'));
        }

        if ($request['price'] <= $dis || count($data) < 1 || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 402);
        }
        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if(isset($tags)){
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => $value]
                );
                $tag->save();
                array_push($tag_ids,$tag->id);
            }
        }

        $p = Item::findOrFail($request->id);

        $p->name = $data[0]['value'];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }

        $p->category_id = $request->sub_category_id?$request->sub_category_id:$request->category_id;
        $p->category_ids = json_encode($category);
        $p->description = $data[1]['value'];

        $choice_options = [];
        if ($request->has('choice')) {
            foreach (json_decode($request->choice_no) as $key => $no) {
                $str = 'choice_options_' . $no;
                if (json_decode($request[$str])[0] == null) {
                    $validator->getMessageBag()->add('name', translate('messages.attribute_choice_option_value_can_not_be_null'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $item['name'] = 'choice_' . $no;
                $item['title'] = json_decode($request->choice)[$key];
                $item['options'] = explode(',', implode('|', preg_replace('/\s+/', ' ', json_decode($request[$str]))));
                array_push($choice_options, $item);
            }
        }
        $p->choice_options = json_encode($choice_options);
        $variations = [];
        $options = [];
        if ($request->has('choice_no')) {
            foreach (json_decode($request->choice_no) as $key => $no) {
                $name = 'choice_options_' . $no;
                $my_str = implode('|', json_decode($request[$name]));
                array_push($options, explode(',', $my_str));
            }
        }
        //Generates the combinations of customer choice options
        $combinations = Helpers::combinations($options);
        if (count($combinations[0]) > 0) {
            foreach ($combinations as $key => $combination) {
                $str = '';
                foreach ($combination as $k => $i) {
                    if ($k > 0) {
                        $str .= '-' . str_replace(' ', '', $i);
                    } else {
                        $str .= str_replace(' ', '', $i);
                    }
                }
                $i = [];
                $i['type'] = $str;
                $i['price'] = abs($request['price_' . str_replace('.', '_', $str)]);
                $i['stock'] = abs($request['stock_' . str_replace('.', '_', $str)]);
                array_push($variations, $i);
            }
        }
        //combinations end

        $images = $p['images'];

        foreach ($p['images'] as $img) {
            if (!in_array($img, json_decode($request->images, true))) {
                if(Storage::disk('public')->exists('product/' . $img))
                {
                    Storage::disk('public')->delete('product/' . $img);
                }
                $key = array_search($img, $images);
                unset($images[$key]);
            }
        }
        if ($request->has('item_images')){
            foreach ($request->item_images as $img) {
                $image = Helpers::upload('product/', 'png', $img);
                array_push($images, $image);
            }
        }




        $food_variations = [];
        if(isset($request->options))
        {
            foreach(json_decode($request->options,true) as $key=>$option)
            {
                $temp_variation['name']= $option['name'];
                $temp_variation['type']= $option['type'];
                $temp_variation['min']= $option['min'] ?? 0;
                $temp_variation['max']= $option['max'] ?? 0;
                $temp_variation['required']= $option['required']??'off';
                $temp_value = [];
                foreach($option['values'] as $value)
                {
                    if(isset($value['label'])){
                        $temp_option['label'] = $value['label'];
                    }
                    $temp_option['optionPrice'] = $value['optionPrice'];
                    array_push($temp_value,$temp_option);
                }
                $temp_variation['values']= $temp_value;
                array_push($food_variations,$temp_variation);
            }
        }

        // $variation_changed = false;
        // if(strcmp($p->food_variations,json_encode($food_variations)) !== 0 || strcmp($p->variations,json_encode($variations)) !== 0){
        //     $variation_changed = true;
        // }



        $variation_changed = false;
        if((($p->food_variations != null && $food_variations != '[]' ) && strcmp($p->food_variations,json_encode($food_variations)) !== 0 )|| (
            ($p->variations != null && $variations != '[]' ) && strcmp($p->variations,json_encode($variations)) !== 0)){
            $variation_changed = true;
        }




        $old_price =$p->price;


        $p->variations = json_encode($variations);
        $p->food_variations = json_encode($food_variations);
        $p->price = $request->price;
        $p->image = $request->has('image') ? Helpers::update('product/', $p->image, 'png', $request->file('image')) : $p->image;
        $p->available_time_starts = $request->available_time_starts;
        $p->available_time_ends = $request->available_time_ends;
        $p->discount = $request->discount_type == 'amount' ? $request->discount : $request->discount;
        $p->discount_type = $request->discount_type;
        $p->maximum_cart_quantity = $request->maximum_cart_quantity;
        $p->attributes = $request->has('attribute_id') ? $request->attribute_id : json_encode([]);
        $p->add_ons = $request->has('addon_ids') ? json_encode(explode(',',$request->addon_ids)) : json_encode([]);
        $p->stock= $request->current_stock??0;
        $p->veg = $request->veg??0;
        $p->images = array_values($images);
        $p->unit_id = $request->unit;
        $p->organic = $request->organic??0;



        $product_approval_datas = \App\Models\BusinessSetting::where('key', 'product_approval_datas')->first()?->value ?? '';
        $product_approval_datas =json_decode($product_approval_datas , true);


        if (Helpers::get_mail_status('product_approval') && ((data_get($product_approval_datas,'Update_anything_in_product_details',null) == 1) || (data_get($product_approval_datas,'Update_product_price',null) == 1 && $old_price !=  $request->price) || ( data_get($product_approval_datas,'Update_product_variation',null) == 1 &&  $variation_changed)) )  {

            $this->store_temp_data($p, $request,$tag_ids, true);
            return response()->json(['product_approval' => translate('your_product_added_for_approval')], 200);
        }


        if($request['vendor']->stores[0]->module->module_type == 'pharmacy'){
            DB::table('pharmacy_item_details')
                ->updateOrInsert(
                    ['item_id' => $p->id],
                    [
                        'common_condition_id' => $request->condition_id,
                        'is_basic' => $request->basic ?? 0,
                    ]
                );
        }

        $p->save();
        $p->tags()->sync($tag_ids);

        foreach ($data as $key=>$item) {
            Translation::updateOrInsert(
                ['translationable_type' => 'App\Models\Item',
                    'translationable_id' => $p->id,
                    'locale' => $item['locale'],
                    'key' => $item['key']],
                ['value' => $item['value']]
            );
        }

        return response()->json(['message'=>translate('messages.product_updated_successfully')], 200);
    }

    public function delete(Request $request)
    {
        if(!$request->vendor->stores[0]->item_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }


        if($request?->temp_product){
            $product = TempProduct::findOrFail($request->id);
        }
        else{
            $product = Item::findOrFail($request->id);
            $product?->temp_product?->translations()?->delete();
            $product?->temp_product()?->delete();
        }


        if($product->image)
        {
            if (Storage::disk('public')->exists('product/' . $product['image'])) {
                Storage::disk('public')->delete('product/' . $product['image']);
            }
        }
        $product->translations()->delete();
        $product->delete();

        return response()->json(['message'=>translate('messages.product_deleted_successfully')], 200);
    }

    public function search(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $key = explode(' ', $request['name']);

        $products = Item::active()
        ->with(['rating'])
        ->where('store_id', $request['vendor']->stores[0]->id)
        ->when($request->category_id, function($query)use($request){
            $query->whereHas('category',function($q)use($request){
                return $q->whereId($request->category_id)->orWhere('parent_id', $request->category_id);
            });
        })
        ->when($request->store_id, function($query) use($request){
            return $query->where('store_id', $request->store_id);
        })
        ->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
            $q->orWhereHas('tags',function($query)use($key){
                $query->where(function($q)use($key){
                    foreach ($key as $value) {
                        $q->where('tag', 'like', "%{$value}%");
                    };
                });
            });
        })
        ->limit(50)
        ->get();

        $data = Helpers::product_data_formatting($products, true, false, app()->getLocale());
        return response()->json($data, 200);
    }

    public function reviews(Request $request)
    {
        $id = $request['vendor']->stores[0]->id;;

        $reviews = Review::with(['customer', 'item'])
        ->whereHas('item', function($query)use($id){
            return $query->where('store_id', $id);
        })
        ->latest()->get();

        $storage = [];
        foreach ($reviews as $item) {
            $item['attachment'] = json_decode($item['attachment']);
            $item['item_name'] = null;
            $item['item_image'] = null;
            $item['customer_name'] = null;
            if($item->item)
            {
                $item['item_name'] = $item->item->name;
                $item['item_image'] = $item->item->image;
                if(count($item->item->translations)>0)
                {
                    $translate = array_column($item->item->translations->toArray(), 'value', 'key');
                    $item['item_name'] = $translate['name'];
                }
            }

            if($item->customer)
            {
                $item['customer_name'] = $item->customer->f_name.' '.$item->customer->l_name;
            }

            unset($item['item']);
            unset($item['customer']);
            array_push($storage, $item);
        }

        return response()->json($storage, 200);
    }


    public function organic(Request $request)
    {
        if(!$request->vendor->stores[0]->item_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'organic' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $product = Item::find($request->id);
        $product->organic = $request->organic??0;
        $product->save();

        return response()->json(['message' => translate('messages.product_organic_status_updated')], 200);

    }


    public function recommended(Request $request)
    {
        if(!$request->vendor->stores[0]->item_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ],403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $product = Item::find($request->id);
        $product->recommended = $request->status;
        $product->save();

        return response()->json(['message' => translate('messages.product_recommended_status_updated')], 200);

    }

    public function store_temp_data($data, $request,$tag_ids , $update =null)
    {
        $item = TempProduct::firstOrNew(
            ['item_id' => $data->id]
        );

        $translated_data = json_decode($request->translations, true);

        $item->name = $translated_data[0]['value'];
        $item->description = $translated_data[1]['value'];
        $item->image = $data->image;
        $item->images = $data->images;

        $item->store_id = $data->store_id;
        $item->module_id = $data->module_id;
        $item->unit_id = $data->unit_id;
        $item->item_id = $data->id;

        $item->category_id = $data->category_id;
        $item->category_ids = $data->category_ids;
        $item->slug = $data->slug;

        $item->choice_options = $data->choice_options;
        $item->food_variations = $data->food_variations;
        $item->variations = $data->variations;
        $item->add_ons = $data->add_ons;
        $item->attributes = $data->attributes;

        $item->price = $data->price;
        $item->discount = $data->discount;
        $item->discount_type = $data->discount_type;
        $item->tag_ids =json_encode($tag_ids);

        $item->available_time_starts = $data->available_time_starts;
        $item->available_time_ends = $data->available_time_ends;
        $item->maximum_cart_quantity = $data->maximum_cart_quantity;
        $item->veg = $data->veg ?? 0;
        $item->organic = $data->organic ?? 0;
        $item->stock =  $data->stock ?? 0;
        $item->common_condition_id =  $request->condition_id ?? 0;
        $item->basic =  $request->basic ?? 0;


        if($update){
            $item->is_rejected = 0;
        }

        $item->save();
        if($request['vendor']->stores[0]->module->module_type == 'pharmacy'){
            DB::table('pharmacy_item_details')
                ->updateOrInsert(
                    ['temp_product_id' => $item->id],
                    [
                        'common_condition_id' => $request->condition_id,
                        'is_basic' => $request->basic ?? 0,
                        'item_id' => null
                    ]
                );
        }
        // Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'TempProduct', data_id: $item->id, data_value: $item->name);
        // Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'TempProduct', data_id: $item->id, data_value: $item->description);


        foreach ($translated_data as $key=>$translated) {
            Translation::updateOrInsert(
                ['translationable_type' => 'App\Models\TempProduct',
                    'translationable_id' => $item->id,
                    'locale' => $translated['locale'],
                    'key' => $translated['key']],
                ['value' => $translated['value']]
            );
        }

        return true;
    }


    public function pending_item_list(Request $request)
    {
        $key = explode(' ', $request['name']);
        $limit = $request['limite']??25;
        $offset = $request['offset']??1;

        $sub_category_id = $request->sub_category_id ?? 'all';
        $category_id = $request->category_id ??  'all';
        $type = $request->type  ??  'all';
        $status = $request->status  ??  'all';

        $items = TempProduct::
        when(is_numeric($category_id), function($query)use($category_id){
            return $query->whereHas('category',function($q)use($category_id){
                return $q->whereId($category_id)->orWhere('parent_id', $category_id);
            });
        })
        ->where('store_id',$request['vendor']->stores[0]->id)
        ->when(isset($key), function($q) use($key){
            $q->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->where('name', 'like', "%{$value}%");
                }
            });
        })
        ->when(is_numeric($sub_category_id), function ($query) use ($sub_category_id) {
            return $query->where('category_id', $sub_category_id);
        })
        ->when($status == 'pending' , function($query){
            $query->where('is_rejected',0);
        })
        ->when($status == 'rejected' , function($query){
            $query->where('is_rejected',1);
        })

        ->type($type)->latest()->select(['id','name' ,'image','price','is_rejected','item_id','category_ids'])->paginate($limit, ['*'], 'page', $offset);
        $storage = [];
        $categories = [];
        foreach($items->items() as $item){
            foreach (json_decode($item['category_ids']) as $value) {
                $category_name = Category::where('id',$value->id)->pluck('name');
                $categories[] = ['id' => (string)$value->id, 'position' => $value->position, 'name'=>data_get($category_name,'0','NA')];
            }
            $item['category_ids'] = $categories;
            array_push($storage, $item);
        }

        $data = [
            'total_size' => $items->total(),
            'limit' => $limit,
            'offset' => $offset,
            'items' =>  $storage,
        ];

        return response()->json($data,200);
    }

    public function requested_item_view($id){

        $product=TempProduct::withoutGlobalScope('translate')->with(['translations','store','unit'])->findOrFail($id);
        $product=  Helpers::product_data_formatting($product, false, false, app()->getLocale() , true);
        return response()->json($product,200);

    }

}
