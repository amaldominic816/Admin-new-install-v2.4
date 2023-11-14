<?php
namespace App\Http\Controllers\Admin;

use DateTime;
use App\Models\Item;
use App\Models\FlashSale;
use Illuminate\Http\Request;
use App\Models\FlashSaleItem;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Config;

class FlashSaleController extends Controller
{
    function index(Request $request)
    {
        $key = explode(' ', $request['search']);

        $flash_sales = FlashSale::where('module_id', Config::get('module.current_module_id'))->orderBy('title')
        ->when(isset($key) , function($q) use($key){
            $q->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%{$value}%");
                }
            });
        })

        ->paginate(config('default_pagination'));
        return view('admin-views.flash-sale.index', compact('flash_sales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:100',
            'title.0' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'admin_discount_percentage' => 'required|min:0.01|max:100',
            'vendor_discount_percentage' => 'required|min:0.01|max:100',
        ], [
            'title.required' => translate('messages.title is required!'),
            'title.0.required'=>translate('default_data_is_required'),
        ]);

        if(($request->admin_discount_percentage+$request->vendor_discount_percentage) != 100){
            Toastr::error(translate('messages.invalid_distribution_of_discount'));
            return back();
        }

        $start_date = $request->start_date;
        $start_date_time = new DateTime($start_date);
        $start_date = $start_date_time->format('Y-m-d H:i:s');
        $end_date = $request->end_date;
        $end_date_time = new DateTime($end_date);
        $end_date = $end_date_time->format('Y-m-d H:i:s');

        $flash_sale = new FlashSale();
        $flash_sale->title = $request->title[array_search('default', $request->lang)];
        $flash_sale->start_date = $start_date;
        $flash_sale->end_date = $end_date;
        $flash_sale->module_id = Config::get('module.current_module_id');
        $flash_sale->is_publish = 0;
        $flash_sale->admin_discount_percentage = $request->admin_discount_percentage;
        $flash_sale->vendor_discount_percentage = $request->vendor_discount_percentage;
        $flash_sale->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'FlashSale', data_id: $flash_sale->id, data_value: $flash_sale->title);
        Toastr::success(translate('messages.flash_sale_added_successfully'));
        return back();
    }

    public function edit($id)
    {
        $flash_sale = FlashSale::withoutGlobalScope('translate')->findOrFail($id);
        return view('admin-views.flash-sale.edit', compact('flash_sale'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|max:100',
            'title.0' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'admin_discount_percentage' => 'required|min:0.01|max:100',
            'vendor_discount_percentage' => 'required|min:0.01|max:100',
        ], [
            'title.required' => translate('messages.title is required!'),
            'title.0.required'=>translate('default_data_is_required'),
        ]);

        if(($request->admin_discount_percentage+$request->vendor_discount_percentage) != 100){
            Toastr::error(translate('messages.invalid_distribution_of_discount'));
            return back();
        }

        $start_date = $request->start_date;
        $start_date_time = new DateTime($start_date);
        $start_date = $start_date_time->format('Y-m-d H:i:s');
        $end_date = $request->end_date;
        $end_date_time = new DateTime($end_date);
        $end_date = $end_date_time->format('Y-m-d H:i:s');

        $flash_sale = FlashSale::findOrFail($id);
        $flash_sale->title = $request->title[array_search('default', $request->lang)];
        $flash_sale->start_date = $start_date;
        $flash_sale->end_date = $end_date;
        $flash_sale->admin_discount_percentage = $request->admin_discount_percentage;
        $flash_sale->vendor_discount_percentage = $request->vendor_discount_percentage;
        $flash_sale->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'title', name_field: 'title', model_name: 'FlashSale', data_id: $flash_sale->id, data_value: $flash_sale->title);
        Toastr::success(translate('messages.flash_sale_updated_successfully'));
        return back();
    }

    public function delete(Request $request)
    {
        $flash_sale = FlashSale::findOrFail($request->id);
        $flash_sale->products()->delete();
        $flash_sale->translations()->delete();

        $flash_sale->delete();
        Toastr::success(translate('messages.flash_sale_deleted_successfully'));
        return back();
    }

    public function publish(Request $request)
    {
        $flash_sale = FlashSale::find($request->id);
        if ($flash_sale) {
            $flash_sale->is_publish = $request->publish;
            $flash_sale->save();

            FlashSale::whereNot('id', $request->id)->where('module_id', Config::get('module.current_module_id'))->update(['is_publish' => 0]);

        }
        Toastr::success(translate('messages.flash_sale_publish_updated'));
        return back();
    }

    public function add_product(Request $request,$id)
    {
        $flash_sale = FlashSale::findOrFail($id);

        $key = explode(' ', $request['search']);

        $items = FlashSaleItem::where('flash_sale_id', $flash_sale->id)
        ->when(isset($key) , function($q) use($key){
            $q->whereHas('item', function($q) use ($key){
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            });
        })

        ->paginate(config('default_pagination'));

        return view('admin-views.flash-sale.product-index', compact('flash_sale','items'));
    }

    public function store_product(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
            'stock' => 'required|min:1',
            'discount_type' => 'required',
            'discount' => 'required_if:discount_type,percent,amount',
        ], [
            'item_id.required' => translate('messages.product is required!'),
        ]);

        $item = FlashSaleItem::where('flash_sale_id', $request->flash_sale_id)->where('item_id',$request->item_id)->first();
        if($item){
            Toastr::error(translate('messages.Item_already_exists'));
            return back();
        }

        $item = Item::find($request->item_id);

        if($request->stock>$item->stock){
            Toastr::error(translate('messages.Item_stock_exceeded'));
            return back();
        }

        $flash_sale = new FlashSaleItem();
        $flash_sale->flash_sale_id = $request->flash_sale_id;
        $flash_sale->item_id = $request->item_id;
        $flash_sale->stock = $request->stock;
        $flash_sale->available_stock = $request->stock;
        $flash_sale->discount_type = $request->discount_type;
        $flash_sale->discount = $request->discount;

        if($request->discount_type == 'current_active_discount'){
            $discount_amount = helpers::product_discount_calculate($item, $item->price, $item->store)['discount_amount'];
        }else{
            if ($request->discount_type == 'percent') {
                $discount_amount = ($item->price / 100) * $request->discount;
            } else {
                $discount_amount = $request->discount;
            }
        }

        $flash_sale->discount_amount = $discount_amount;
        if($discount_amount >= $item->price) {
            Toastr::error(translate('messages.Item_discount_amount_exceeded'));
            return back();
        }
        $flash_sale->price = $item->price-$discount_amount;

        $flash_sale->save();

        Toastr::success(translate('messages.Item_added_successfully'));
        return back();
    }

    public function delete_product(Request $request)
    {
        $flash_sale = FlashSaleItem::findOrFail($request->id);
        $flash_sale->delete();
        Toastr::success(translate('messages.item_deleted_successfully'));
        return back();
    }

    public function status_product(Request $request)
    {
        $flash_sale = FlashSaleItem::find($request->id);
        $flash_sale->status = $request->status;
        $flash_sale->save();
        Toastr::success(translate('messages.flash_sale_publish_updated'));
        return back();
    }
}
