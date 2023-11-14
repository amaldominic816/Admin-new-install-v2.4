<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\OrderPayment;
use Stripe\Product;
use App\Models\Cart;
use App\Models\Item;
use App\Models\Zone;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Store;
use App\Models\Coupon;
use App\Models\Refund;
use App\Mail\PlaceOrder;
use App\Models\DMVehicle;
use App\Mail\RefundRequest;
use App\Models\OrderDetail;
use App\Models\ItemCampaign;
use App\Models\RefundReason;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\ParcelCategory;
use App\Models\BusinessSetting;
use App\Models\OfflinePayments;
use App\CentralLogics\OrderLogic;
use App\Models\OrderCancelReason;
use App\CentralLogics\CouponLogic;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\ProductLogic;
use App\Mail\OrderVerificationMail;
use App\CentralLogics\CustomerLogic;
use App\Http\Controllers\Controller;
use App\Models\OfflinePaymentMethod;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;

class OrderController extends Controller
{
    public function track_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
            'contact_number' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $order = Order::with(['store', 'delivery_man.rating', 'parcel_category', 'refund','payments'])->withCount('details')->where(['id' => $request['order_id'], 'user_id' => $user_id])
        ->when(!$request->user, function ($query) use ($request) {
            return $query->whereJsonContains('delivery_address->contact_person_number', $request['contact_number']);
        })
        ->Notpos()->first();
        if ($order) {
            $order['store'] = $order['store'] ? Helpers::store_data_formatting($order['store']) : $order['store'];
            $order['delivery_address'] = $order['delivery_address'] ? json_decode($order['delivery_address']) : $order['delivery_address'];
            $order['delivery_man'] = $order['delivery_man'] ? Helpers::deliverymen_data_formatting([$order['delivery_man']]) : $order['delivery_man'];
            $order['refund_cancellation_note'] = $order['refund'] ? $order['refund']['admin_note'] : null;
            $order['refund_customer_note'] = $order['refund'] ? $order['refund']['customer_note'] : null;
            $order['min_delivery_time'] =  $order->store?(int) explode('-',$order->store?->delivery_time)[0] ?? 0:0;
            $order['max_delivery_time'] =  $order->store?(int) explode('-',$order->store?->delivery_time)[1] ?? 0:0;
            $order['offline_payment'] =  isset($order->offline_payments) ? Helpers::offline_payment_formater($order->offline_payments) : null;

            unset($order['offline_payments']);
            unset($order['details']);
        } else {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')]
                ]
            ], 404);
        }
        return response()->json($order, 200);
    }

    public function place_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_amount' => 'required',
            'payment_method' => 'required|in:cash_on_delivery,digital_payment,wallet,offline_payment',
            'order_type' => 'required|in:take_away,delivery,parcel',
            'store_id' => 'required_unless:order_type,parcel',
            'distance' => 'required_unless:order_type,take_away',
            'address' => 'required_unless:order_type,take_away',
            'longitude' => 'required_unless:order_type,take_away',
            'latitude' => 'required_unless:order_type,take_away',
            'parcel_category_id' => 'required_if:order_type,parcel',
            'receiver_details' => 'required_if:order_type,parcel',
            'charge_payer' => 'required_if:order_type,parcel|in:sender,receiver',
            'dm_tips' => 'nullable|numeric',
            'guest_id' => $request->user ? 'nullable' : 'required',
            'contact_person_name' => $request->user ? 'nullable' : 'required',
            'contact_person_number' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $coupon = null;
        $coupon_created_by = null;
        $delivery_charge = null;
        $schedule_at = $request->schedule_at ? \Carbon\Carbon::parse($request->schedule_at) : now();
        $store = null;
        $free_delivery_by = null;
        $distance_data = $request->distance;
        $increased=0;

        if($request['order_type'] == 'delivery' && !Helpers::get_business_settings('home_delivery_status')){
            return response()->json([
                'errors' => [
                    ['code' => 'order_type', 'message' => translate('messages.home_delivery_is_not_active')]
                ]
            ], 403);
        }

        if($request['order_type'] == 'take_away' && !Helpers::get_business_settings('takeaway_status')){
            return response()->json([
                'errors' => [
                    ['code' => 'order_type', 'message' => translate('messages.take_away_is_not_active')]
                ]
            ], 403);
        }

        if($request->partial_payment && !Helpers::get_business_settings('partial_payment_status')){
            return response()->json([
                'errors' => [
                    ['code' => 'order_method', 'message' => translate('messages.partial_payment_is_not_active')]
                ]
            ], 403);
        }




        if ($request->payment_method == 'offline_payment' &&  Helpers::get_mail_status('offline_payment_status') == 0) {
            return response()->json([
                'errors' => [
                    ['code' => 'offline_payment_status', 'message' => translate('messages.offline_payment_for_the_order_not_available_at_this_time')]
                ]
            ], 403);
        }

        $data =  DMVehicle::active()->where(function ($query) use ($distance_data) {
            $query->where('starting_coverage_area', '<=', $distance_data)->where('maximum_coverage_area', '>=', $distance_data)
            ->orWhere(function ($query) use ($distance_data) {
                $query->where('starting_coverage_area', '>=', $distance_data);
            });
        })
            ->orderBy('starting_coverage_area')->first();
        // if(!$data){

        //     $data=DMVehicle::active()->latest()->first();
        // }

        $extra_charges = (float) (isset($data) ? $data->extra_charges  : 0);
        $vehicle_id = (isset($data) ? $data->id  : null);


        $zone = null;
        if ($request->latitude && $request->longitude) {
            $point = new Point($request->latitude, $request->longitude);
            $zone_id = isset($store) ? [$store->zone_id] : json_decode($request->header('zoneId'), true);
            $zone = Zone::where('id', $zone_id)->whereContains('coordinates', new Point($request->latitude, $request->longitude, POINT_SRID))->first();
            if (!$zone) {
                $errors = [];
                array_push($errors, ['code' => 'coordinates', 'message' => translate('messages.out_of_coverage')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
        }

        if( $zone && $zone->increased_delivery_fee_status == 1){
            $increased=$zone->increased_delivery_fee ?? 0;
        }

        if ($request->order_type !== 'parcel') {
            if ($request->schedule_at && $schedule_at < now()) {
                return response()->json([
                    'errors' => [
                        ['code' => 'order_time', 'message' => translate('messages.you_can_not_schedule_a_order_in_past')]
                    ]
                ], 406);
            }
            $store = Store::with('discount')->selectRaw('*, IF(((select count(*) from `store_schedule` where `stores`.`id` = `store_schedule`.`store_id` and `store_schedule`.`day` = ' . $schedule_at->format('w') . ' and `store_schedule`.`opening_time` < "' . $schedule_at->format('H:i:s') . '" and `store_schedule`.`closing_time` >"' . $schedule_at->format('H:i:s') . '") > 0), true, false) as open')->where('id', $request->store_id)->first();

            if (!$store) {
                return response()->json([
                    'errors' => [
                        ['code' => 'order_time', 'message' => translate('messages.store_not_found')]
                    ]
                ], 404);
            }

            if ($request->schedule_at && !$store->schedule_order) {
                return response()->json([
                    'errors' => [
                        ['code' => 'schedule_at', 'message' => translate('messages.schedule_order_not_available')]
                    ]
                ], 406);
            }

            if ($store->open == false) {
                return response()->json([
                    'errors' => [
                        ['code' => 'order_time', 'message' => translate('messages.store_is_closed_at_order_time')]
                    ]
                ], 406);
            }

            if ($request['coupon_code']) {
                $coupon = Coupon::active()->where(['code' => $request['coupon_code']])->first();
                if (isset($coupon)) {

                    if($request->is_guest){
                        $staus = CouponLogic::is_valid_for_guest($coupon, $request['store_id']);
                    }else{
                        $staus = CouponLogic::is_valide($coupon, $request->user->id, $request['store_id']);
                    }

                    if ($staus == 407) {
                        return response()->json([
                            'errors' => [
                                ['code' => 'coupon', 'message' => translate('messages.coupon_expire')]
                            ]
                        ], 407);
                    } else if ($staus == 408) {
                        return response()->json([
                            'errors' => [
                                ['code' => 'coupon', 'message' => translate('messages.You_are_not_eligible_for_this_coupon')]
                            ]
                        ], 403);
                    } else if ($staus == 406) {
                        return response()->json([
                            'errors' => [
                                ['code' => 'coupon', 'message' => translate('messages.coupon_usage_limit_over')]
                            ]
                        ], 406);
                    } else if ($staus == 404) {
                        return response()->json([
                            'errors' => [
                                ['code' => 'coupon', 'message' => translate('messages.not_found')]
                            ]
                        ], 404);
                    }

                    $coupon_created_by = $coupon->created_by;
                    if ($coupon->coupon_type == 'free_delivery') {
                        $delivery_charge = 0;
                        $free_delivery_by =  $coupon_created_by;
                        $coupon_created_by = null;
                    }
                } else {
                    return response()->json([
                        'errors' => [
                            ['code' => 'coupon', 'message' => translate('messages.not_found')]
                        ]
                    ], 404);
                }
            }

            $module_wise_delivery_charge = $store->zone->modules()->where('modules.id', $request->header('moduleId'))->first();
            if ($module_wise_delivery_charge) {
                $per_km_shipping_charge = $module_wise_delivery_charge->pivot->per_km_shipping_charge;
                $minimum_shipping_charge = $module_wise_delivery_charge->pivot->minimum_shipping_charge;
                $maximum_shipping_charge = $module_wise_delivery_charge->pivot->maximum_shipping_charge;
            } else {
                $per_km_shipping_charge = (float)BusinessSetting::where(['key' => 'per_km_shipping_charge'])->first()->value;
                $minimum_shipping_charge = (float)BusinessSetting::where(['key' => 'minimum_shipping_charge'])->first()->value;
            }


            if ($request['order_type'] != 'take_away' && !$store->free_delivery &&  !isset($delivery_charge) &&  $store->self_delivery_system == 1) {
                $per_km_shipping_charge = $store->per_km_shipping_charge;
                $minimum_shipping_charge = $store->minimum_shipping_charge;
                $maximum_shipping_charge = $store->maximum_shipping_charge;
                $extra_charges = 0;
                $vehicle_id = null;
                $increased=0;
            }

            if ($store->free_delivery || $free_delivery_by == 'vendor') {
                $per_km_shipping_charge = $store->per_km_shipping_charge;
                $minimum_shipping_charge = $store->minimum_shipping_charge;
                $maximum_shipping_charge = $store->maximum_shipping_charge;
                $extra_charges = 0;
                $increased=0;
            }

            $original_delivery_charge = (($request->distance * $per_km_shipping_charge) > $minimum_shipping_charge) ? $request->distance * $per_km_shipping_charge  : $minimum_shipping_charge;

            if ($request['order_type'] == 'take_away') {
                $per_km_shipping_charge = 0;
                $minimum_shipping_charge = 0;
                $maximum_shipping_charge = 0;
                $extra_charges = 0;
                $distance_data = 0;
                $vehicle_id = null;
                $original_delivery_charge = 0;
                $increased=0;
            }

            if ($maximum_shipping_charge  >= $minimum_shipping_charge  && $original_delivery_charge >  $maximum_shipping_charge) {
                $original_delivery_charge = $maximum_shipping_charge;
            } else {
                $original_delivery_charge = $original_delivery_charge;
            }

            if (!isset($delivery_charge)) {
                $delivery_charge = ($request->distance * $per_km_shipping_charge > $minimum_shipping_charge) ? $request->distance * $per_km_shipping_charge : $minimum_shipping_charge;
                if ($maximum_shipping_charge  >= $minimum_shipping_charge  && $delivery_charge >  $maximum_shipping_charge) {
                    $delivery_charge = $maximum_shipping_charge;
                } else {
                    $delivery_charge = $delivery_charge;
                }
            }
            $original_delivery_charge = $original_delivery_charge + $extra_charges;
            $delivery_charge = $delivery_charge + $extra_charges;




        } else {
            $parcel_category = ParcelCategory::findOrFail($request->parcel_category_id);
            if (isset($parcel_category) && isset($parcel_category->parcel_minimum_shipping_charge)) {
                $per_km_shipping_charge = $parcel_category->parcel_per_km_shipping_charge;
                $minimum_shipping_charge = $parcel_category->parcel_minimum_shipping_charge;
            } else {
                $per_km_shipping_charge = (float)BusinessSetting::where(['key' => 'parcel_per_km_shipping_charge'])->first()->value;
                $minimum_shipping_charge = (float)BusinessSetting::where(['key' => 'parcel_minimum_shipping_charge'])->first()->value;
            }

            $original_delivery_charge = (($request->distance * $per_km_shipping_charge )> $minimum_shipping_charge) ? ($request->distance * $per_km_shipping_charge )+ $extra_charges : ($minimum_shipping_charge + $extra_charges);
        }


        if($increased > 0 ){
            if($delivery_charge > 0){
                $increased_fee = ($delivery_charge * $increased) / 100;
                $delivery_charge = $delivery_charge + $increased_fee;
            }
            if($original_delivery_charge > 0){
                $increased_fee = ($original_delivery_charge * $increased) / 100;
                $original_delivery_charge = $original_delivery_charge + $increased_fee;
            }
        }

        $address = [
            'contact_person_name' => $request->contact_person_name ? $request->contact_person_name : ($request->user?$request->user->f_name . ' ' . $request->user->f_name:''),
            'contact_person_number' => $request->contact_person_number ? str_replace('+', '', $request->contact_person_number) : ($request->user?$request->user->phone:''),
            'contact_person_email' => $request->contact_person_email ? $request->contact_person_email : ($request->user?$request->user->email:''),
            'address_type' => $request->address_type ? $request->address_type : 'Delivery',
            'address' => $request->address??'',
            'floor' => $request->floor??'',
            'road' => $request->road??'',
            'house' => $request->house??'',
            'longitude' => (string)$request->longitude,
            'latitude' => (string)$request->latitude,
        ];

        $total_addon_price = 0;
        $product_price = 0;
        $store_discount_amount = 0;
        $flash_sale_vendor_discount_amount = 0;
        $flash_sale_admin_discount_amount = 0;
        $store_discount_amount = 0;
        $product_data = [];

        $order_details = [];
        $order = new Order();
        $order->id = 100000 + Order::count() + 1;
        if (Order::find($order->id)) {
            $order->id = Order::orderBy('id', 'desc')->first()->id + 1;
        }


        $order_status ='pending';
        if(($request->partial_payment && $request->payment_method != 'offline_payment') || $request->payment_method == 'wallet' ){
            $order_status ='confirmed';
        }

        $order->user_id = $request->user ? $request->user->id : $request['guest_id'];
        $order->order_amount = $request['order_amount'];
        $order->payment_status = ($request->partial_payment ? 'partially_paid' : ($request['payment_method'] == 'wallet' ? 'paid' : 'unpaid'));
        $order->order_status = $order_status;
        $order->coupon_code = $request['coupon_code'];
        $order->payment_method = $request->partial_payment? 'partial_payment' :$request->payment_method;
        $order->transaction_reference = null;
        $order->order_note = $request['order_note'];
        $order->unavailable_item_note = $request['unavailable_item_note'];
        $order->delivery_instruction = $request['delivery_instruction'];
        $order->order_type = $request['order_type'];
        $order->store_id = $request['store_id'];
        $order->delivery_charge = round($delivery_charge, config('round_up_to_digit')) ?? 0;
        $order->original_delivery_charge = round($original_delivery_charge, config('round_up_to_digit'));
        $order->delivery_address = json_encode($address);
        $order->schedule_at = $schedule_at;
        $order->scheduled = $request->schedule_at ? 1 : 0;
        $order->cutlery = $request->cutlery ? 1 : 0;
        $order->is_guest = $request->user ? 0 : 1;
        $order->otp = rand(1000, 9999);
        $order->zone_id = isset($zone) ? $zone->id : end(json_decode($request->header('zoneId'), true));
        $order->module_id = $request->header('moduleId');
        $order->parcel_category_id = $request->parcel_category_id;
        $order->receiver_details = json_decode($request->receiver_details);

        if ($order_status == 'confirmed') {
            $order->confirmed = now();
        }
        $order->dm_vehicle_id = $vehicle_id;
        $order->pending = now();
        $order->order_attachment = $request->has('order_attachment') ? Helpers::upload('order/', 'png', $request->file('order_attachment')) : null;
        $order->distance = $request->distance;
        $order->created_at = now();
        $order->updated_at = now();
        $order->charge_payer = $request->charge_payer;

        //Added DM TIPS
        $dm_tips_manage_status = BusinessSetting::where('key', 'dm_tips_status')->first()->value;
        if ($dm_tips_manage_status == 1) {
            $order->dm_tips = $request->dm_tips ?? 0;
        } else {
            $order->dm_tips = 0;
        }

        //Added service charge
        $additional_charge_status = BusinessSetting::where('key', 'additional_charge_status')->first()->value;
        $additional_charge = BusinessSetting::where('key', 'additional_charge')->first()->value;
        if ($additional_charge_status == 1) {
            $order->additional_charge = $additional_charge ?? 0;
        } else {
            $order->additional_charge = 0;
        }

        $carts = Cart::where('user_id', $order->user_id)->where('is_guest',$order->is_guest)->where('module_id',$request->header('moduleId'))
        ->when(isset($request->is_buy_now) && $request->is_buy_now == 1 && $request->cart_id, function ($query) use ($request) {
            return $query->where('id',$request->cart_id);
        })
        ->get()->map(function ($data) {
            $data->add_on_ids = json_decode($data->add_on_ids,true);
            $data->add_on_qtys = json_decode($data->add_on_qtys,true);
            $data->variation = json_decode($data->variation,true);
			return $data;
		});

        if(isset($request->is_buy_now) && $request->is_buy_now == 1){
            $carts = json_decode($request['cart'], true);
        }

        if ($request->order_type !== 'parcel') {
            foreach ($carts as $c) {
                if ($c['item_type'] === 'App\Models\ItemCampaign' || $c['item_type'] === 'AppModelsItemCampaign') {
                    $product = ItemCampaign::with('module')->active()->find($c['item_id']);
                    if ($product) {
                        if ($product->module->module_type == 'food' && $product->food_variations) {
                            $product_variations = json_decode($product->food_variations, true);
                            $variations = [];
                            if (count($product_variations)) {
                                $variation_data = Helpers::get_varient($product_variations, $c['variation']);
                                $price = $product['price'] + $variation_data['price'];
                                $variations = $variation_data['variations'];
                            } else {
                                $price = $product['price'];
                            }
                            $product->tax = $store->tax;
                            $product = Helpers::product_data_formatting($product, false, false, app()->getLocale());
                            $addon_data = Helpers::calculate_addon_price(\App\Models\AddOn::whereIn('id', $c['add_on_ids'])->get(), $c['add_on_qtys']);

                            $or_d = [
                                'item_id' => null,
                                'item_campaign_id' => $c['item_id'],
                                'item_details' => json_encode($product),
                                'quantity' => $c['quantity'],
                                'price' => round($price, config('round_up_to_digit')),
                                'tax_amount' => Helpers::tax_calculate($product, $price),
                                'discount_on_item' => Helpers::product_discount_calculate($product, $price, $store)['discount_amount'],
                                'discount_type' => 'discount_on_product',
                                'variant' => json_encode($c['variant']),
                                'variation' => json_encode($variations),
                                'add_ons' => json_encode($addon_data['addons']),
                                'total_add_on_price' => $addon_data['total_add_on_price'],
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                            $order_details[] = $or_d;
                            $total_addon_price += $or_d['total_add_on_price'];
                            $product_price += $price * $or_d['quantity'];
                            $store_discount_amount += $or_d['discount_on_item'] * $or_d['quantity'];
                        } else {
                            if (count(json_decode($product['variations'], true)) > 0) {
                                $variant_data = Helpers::variation_price($product, json_encode($c['variation']));
                                $price = $variant_data['price'];
                                $stock = $variant_data['stock'];
                            } else {
                                $price = $product['price'];
                                $stock = $product->stock;
                            }
                            if (config('module.' . $product->module->module_type)['stock']) {
                                if ($c['quantity'] > $stock) {
                                    return response()->json([
                                        'errors' => [
                                            ['code' => 'campaign', 'message' => translate('messages.product_out_of_stock_warning', ['item' => $product->title])]
                                        ]
                                    ], 406);
                                }

                                $product_data[] = [
                                    'item' => clone $product,
                                    'quantity' => $c['quantity'],
                                    'variant' => count($c['variation']) > 0 ? $c['variation'][0]['type'] : null
                                ];
                            }

                            $product->tax = $store->tax;
                            $product = Helpers::product_data_formatting($product, false, false, app()->getLocale());
                            $addon_data = Helpers::calculate_addon_price(\App\Models\AddOn::whereIn('id', $c['add_on_ids'])->get(), $c['add_on_qtys']);
                            $or_d = [
                                'item_id' => null,
                                'item_campaign_id' => $c['item_id'],
                                'item_details' => json_encode($product),
                                'quantity' => $c['quantity'],
                                'price' => $price,
                                'tax_amount' => Helpers::tax_calculate($product, $price),
                                'discount_on_item' => Helpers::product_discount_calculate($product, $price, $store)['discount_amount'],
                                'discount_type' => 'discount_on_product',
                                'variant' => json_encode($c['variant']),
                                'variation' => json_encode($c['variation']),
                                'add_ons' => json_encode($addon_data['addons']),
                                'total_add_on_price' => $addon_data['total_add_on_price'],
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                            $order_details[] = $or_d;
                            $total_addon_price += $or_d['total_add_on_price'];
                            $product_price += $price * $or_d['quantity'];
                            $store_discount_amount += $or_d['discount_on_item'] * $or_d['quantity'];
                        }
                    } else {
                        return response()->json([
                            'errors' => [
                                ['code' => 'campaign', 'message' => translate('messages.product_unavailable_warning')]
                            ]
                        ], 404);
                    }
                } else {
                    $product = Item::with('module')->active()->find($c['item_id']);
                    if ($product) {
                        if($product->maximum_cart_quantity && ($c['quantity'] > $product->maximum_cart_quantity)){
                            return response()->json([
                                'errors' => [
                                    ['code' => 'quantity', 'message' => translate('messages.maximum_cart_quantity_limit_over')]
                                ]
                            ], 406);
                        }
                        if ($product->module->module_type == 'food' && $product->food_variations) {
                            // if (count(json_decode($product['variations'], true)) > 0) {
                            //     $price = Helpers::variation_price($product, json_encode($c['variation']));
                            // } else {
                            $product_variations = json_decode($product->food_variations, true);
                            $variations = [];
                            if (count($product_variations)) {
                                $variation_data = Helpers::get_varient($product_variations, $c['variation']);
                                // $price = Helpers::variation_price($product, json_encode($c['variation']));
                                $price = $product['price'] + $variation_data['price'];
                                $variations =  $variation_data['variations'];
                            } else {
                                $price = $product['price'];
                            }
                            $product->tax = $store->tax;
                            $product = Helpers::product_data_formatting($product, false, false, app()->getLocale());
                            $addon_data = Helpers::calculate_addon_price(\App\Models\AddOn::whereIn('id', $c['add_on_ids'])->get(), $c['add_on_qtys']);
                            $product_discount = Helpers::product_discount_calculate($product, $price, $store);
                            $or_d = [
                                'item_id' => $c['item_id'],
                                'item_campaign_id' => null,
                                'item_details' => json_encode($product),
                                'quantity' => $c['quantity'],
                                'price' => round($price, config('round_up_to_digit')),
                                'tax_amount' => round(Helpers::tax_calculate($product, $price), config('round_up_to_digit')),
                                'discount_on_item' => $product_discount['discount_amount'],
                                'discount_type' => $product_discount['discount_type'],
                                'variant' => json_encode($c['variant']),
                                'variation' => json_encode($variations),

                                // 'variation' => json_encode($c['variation']),
                                'add_ons' => json_encode($addon_data['addons']),
                                'total_add_on_price' => round($addon_data['total_add_on_price'], config('round_up_to_digit')),
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                            $total_addon_price += $or_d['total_add_on_price'];
                            $product_price += $price * $or_d['quantity'];
                            $store_discount_amount += $or_d['discount_type']!='flash_sale'?$or_d['discount_on_item'] * $or_d['quantity']:0;
                            $flash_sale_admin_discount_amount += $or_d['discount_type']=='flash_sale'?$product_discount['admin_discount_amount'] * $or_d['quantity']:0;
                            $flash_sale_vendor_discount_amount += $or_d['discount_type']=='flash_sale'?$product_discount['vendor_discount_amount'] * $or_d['quantity']:0;
                            $order_details[] = $or_d;
                        } else {

                            if (count(json_decode($product['variations'], true)) > 0 && count($c['variation'])>0) {
                                $variant_data = Helpers::variation_price($product, json_encode($c['variation']));
                                $price = $variant_data['price'];
                                $stock = $variant_data['stock'];
                            } else {
                                $price = $product['price'];
                                $stock = $product->stock;
                            }

                            if (config('module.' . $product->module->module_type)['stock']) {
                                if ($c['quantity'] > $stock) {
                                    return response()->json([
                                        'errors' => [
                                            ['code' => 'campaign', 'message' => translate('messages.product_out_of_stock_warning', ['item' => $product->name])]
                                        ]
                                    ], 406);
                                }

                                $product_data[] = [
                                    'item' => clone $product,
                                    'quantity' => $c['quantity'],
                                    'variant' => count($c['variation']) > 0 ? $c['variation'][0]['type'] : null
                                ];
                            }

                            $product->tax = $store->tax;
                            $product = Helpers::product_data_formatting($product, false, false, app()->getLocale());
                            $addon_data = Helpers::calculate_addon_price(\App\Models\AddOn::whereIn('id', $c['add_on_ids'])->get(), $c['add_on_qtys']);
                            $product_discount = Helpers::product_discount_calculate($product, $price, $store);
                            $or_d = [
                                'item_id' => $c['item_id'],
                                'item_campaign_id' => null,
                                'item_details' => json_encode($product),
                                'quantity' => $c['quantity'],
                                'price' => round($price, config('round_up_to_digit')),
                                'tax_amount' => round(Helpers::tax_calculate($product, $price), config('round_up_to_digit')),
                                'discount_on_item' => $product_discount['discount_amount'],
                                'discount_type' => $product_discount['discount_type'],
                                'variant' => json_encode($c['variant']),
                                'variation' => json_encode($c['variation']),
                                'add_ons' => json_encode($addon_data['addons']),
                                'total_add_on_price' => round($addon_data['total_add_on_price'], config('round_up_to_digit')),
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                            $total_addon_price += $or_d['total_add_on_price'];
                            $product_price += $price * $or_d['quantity'];
                            $store_discount_amount += $or_d['discount_type']!='flash_sale'?$or_d['discount_on_item'] * $or_d['quantity']:0;
                            $flash_sale_admin_discount_amount += $or_d['discount_type']=='flash_sale'?$product_discount['admin_discount_amount'] * $or_d['quantity']:0;
                            $flash_sale_vendor_discount_amount += $or_d['discount_type']=='flash_sale'?$product_discount['vendor_discount_amount'] * $or_d['quantity']:0;
                            $order_details[] = $or_d;
                        }
                    } else {
                        return response()->json([
                            'errors' => [
                                ['code' => 'item', 'message' => translate('messages.product_unavailable_warning')]
                            ]
                        ], 404);
                    }
                }
            }
            $order->discount_on_product_by = 'vendor';
            $store_discount = Helpers::get_store_discount($store);
            if (isset($store_discount)) {
                $order->discount_on_product_by = 'admin';
                if ($product_price + $total_addon_price < $store_discount['min_purchase']) {
                    $store_discount_amount = 0;
                }

                if ($store_discount['max_discount'] != 0 && $store_discount_amount > $store_discount['max_discount']) {
                    $store_discount_amount = $store_discount['max_discount'];
                }
            }
            $coupon_discount_amount = $coupon ? CouponLogic::get_discount($coupon, $product_price + $total_addon_price - $store_discount_amount - $flash_sale_admin_discount_amount - $flash_sale_vendor_discount_amount) : 0;
            $total_price = $product_price + $total_addon_price - $store_discount_amount - $flash_sale_admin_discount_amount - $flash_sale_vendor_discount_amount  - $coupon_discount_amount;

            $tax = ($store->tax > 0) ? $store->tax : 0;
            $order->tax_status = 'excluded';

            $tax_included = BusinessSetting::where(['key' => 'tax_included'])->first() ?  BusinessSetting::where(['key' => 'tax_included'])->first()->value : 0;
            if ($tax_included ==  1) {
                $order->tax_status = 'included';
            }

            $total_tax_amount = Helpers::product_tax($total_price, $tax, $order->tax_status == 'included');

            $tax_a = $order->tax_status == 'included' ? 0 : $total_tax_amount;

            if ($store->minimum_order > $product_price + $total_addon_price) {
                return response()->json([
                    'errors' => [
                        ['code' => 'order_time', 'message' => translate('messages.you_need_to_order_at_least'). $store->minimum_order . ' ' . Helpers::currency_code()]
                    ]
                ], 406);
            }

            $free_delivery_over = BusinessSetting::where('key', 'free_delivery_over')->first()->value;
            if (isset($free_delivery_over)) {
                if ($free_delivery_over <= $product_price + $total_addon_price - $coupon_discount_amount - $store_discount_amount - $flash_sale_admin_discount_amount - $flash_sale_vendor_discount_amount) {
                    $order->delivery_charge = 0;
                    $free_delivery_by = 'admin';
                }
            }

            if ($store->free_delivery) {
                $order->delivery_charge = 0;
                $free_delivery_by = 'vendor';
            }

            if ($coupon) {
                if ($coupon->coupon_type == 'free_delivery') {
                    if ($coupon->min_purchase <= $product_price + $total_addon_price - $store_discount_amount - $flash_sale_admin_discount_amount - $flash_sale_vendor_discount_amount) {
                        $order->delivery_charge = 0;
                        $free_delivery_by = $coupon->created_by;
                    }
                }
                $coupon->increment('total_uses');
            }
            $order->coupon_created_by = $coupon_created_by;
            $order->coupon_discount_amount = round($coupon_discount_amount, config('round_up_to_digit'));
            $order->coupon_discount_title = $coupon ? $coupon->title : '';

            $order->store_discount_amount = round($store_discount_amount, config('round_up_to_digit'));
            $order->tax_percentage = $tax;
            $order->total_tax_amount = round($total_tax_amount, config('round_up_to_digit'));
            $order->order_amount = round($total_price + $tax_a + $order->delivery_charge, config('round_up_to_digit'));
            $order->free_delivery_by = $free_delivery_by;
        } else {
            $point = new Point(json_decode($request->receiver_details, true)['latitude'], json_decode($request->receiver_details, true)['longitude']);
            $zone_id =  json_decode($request->receiver_details, true)['zone_id'];
            $zone = Zone::where('id', $zone_id)->whereContains('coordinates', new Point(json_decode($request->receiver_details, true)['latitude'], json_decode($request->receiver_details, true)['longitude'], POINT_SRID))->first();
            if (!$zone) {
                $errors = [];
                array_push($errors, ['code' => 'receiver_details', 'message' => translate('messages.out_of_coverage')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
            $order->delivery_charge = round($original_delivery_charge, config('round_up_to_digit')) ?? 0;
            $order->original_delivery_charge = round($original_delivery_charge, config('round_up_to_digit'));
            $order->order_amount = round($order->delivery_charge, config('round_up_to_digit'));
        }
        $order->flash_admin_discount_amount = round($flash_sale_admin_discount_amount, config('round_up_to_digit'));
        $order->flash_store_discount_amount = round($flash_sale_vendor_discount_amount, config('round_up_to_digit'));

        //DM TIPS
        $order->order_amount = $order->order_amount + $order->dm_tips + $order->additional_charge;
        if ($request->payment_method == 'wallet' && $request->user->wallet_balance < $order->order_amount) {
            return response()->json([
                'errors' => [
                    ['code' => 'order_amount', 'message' => translate('messages.insufficient_balance')]
                ]
            ], 203);
        }
        if ($request->partial_payment && $request->user->wallet_balance > $order->order_amount) {
            return response()->json([
                'errors' => [
                    ['code' => 'partial_payment', 'message' => translate('messages.order_amount_must_be_greater_than_wallet_amount')]
                ]
            ], 203);
        }
        if (isset($module_wise_delivery_charge) && $request->payment_method == 'cash_on_delivery' && $module_wise_delivery_charge->pivot->maximum_cod_order_amount && $order->order_amount > $module_wise_delivery_charge->pivot->maximum_cod_order_amount) {
            return response()->json([
                'errors' => [
                    ['code' => 'order_amount', 'message' => translate('messages.amount_crossed_maximum_cod_order_amount')]
                ]
            ], 203);
        }

        try {
            DB::beginTransaction();
            $order->save();
            if ($request->order_type !== 'parcel') {
                foreach ($order_details as $key => $item) {
                    $order_details[$key]['order_id'] = $order->id;

                    if($store_discount_amount <= 0 ){
                        $order_details[$key]['discount_on_item'] = 0;
                    }
                }
                OrderDetail::insert($order_details);
                if (count($product_data) > 0) {
                    foreach ($product_data as $item) {
                        ProductLogic::update_stock($item['item'], $item['quantity'], $item['variant'])->save();
                        ProductLogic::update_flash_stock($item['item'], $item['quantity'])?->save();
                    }
                }
                $store->increment('total_order');
            }
            if(!isset($request->is_buy_now) || (isset($request->is_buy_now) && $request->is_buy_now == 0 )){
                foreach ($carts as $cart) {
                    $cart->delete();
                }
            }
            if($request->user){
                $customer = $request->user;
                $customer->zone_id = $order->zone_id;
                $customer->save();
                if ($request->payment_method == 'wallet') CustomerLogic::create_wallet_transaction($order->user_id, $order->order_amount, 'order_place', $order->id);

                if ($request->partial_payment) {
                    if ($request->user->wallet_balance<=0) {
                        return response()->json([
                            'errors' => [
                                ['code' => 'order_amount', 'message' => translate('messages.insufficient_balance_for_partial_amount')]
                            ]
                        ], 203);
                    }
                    $p_amount = min($request->user->wallet_balance, $order->order_amount);
                    $unpaid_amount = $order->order_amount - $p_amount;
                    $order->partially_paid_amount = $p_amount;
                    $order->save();
                    CustomerLogic::create_wallet_transaction($order->user_id, $p_amount, 'partial_payment', $order->id);
                    OrderLogic::create_order_payment(order_id:$order->id, amount:$p_amount, payment_status:'paid', payment_method:'wallet');
                    OrderLogic::create_order_payment(order_id:$order->id, amount:$unpaid_amount, payment_status:'unpaid', payment_method:$request->payment_method);
                }
            }
            DB::commit();


            $payments = $order->payments()->where('payment_method','cash_on_delivery')->exists();
                if(!in_array($order->payment_method, ['digital_payment', 'partial_payment', 'offline_payment'])  || $payments){
                    Helpers::send_order_notification($order);
                }

            $order_mail_status = Helpers::get_mail_status('place_order_mail_status_user');
            $order_verification_mail_status = Helpers::get_mail_status('order_verification_mail_status_user');
            //PlaceOrderMail
            try {
                if ($order->order_status == 'pending' && config('mail.status') && $order_mail_status == '1' && $request->user) {
                    Mail::to($request->user->email)->send(new PlaceOrder($order->id));
                }
                if ($order->order_status == 'pending' && config('order_delivery_verification') == 1 && $order_verification_mail_status == '1' && $request->user) {
                    Mail::to($request->user->email)->send(new OrderVerificationMail($order->otp,$request->user->f_name));
                }
                if ($order->is_guest == 1 && $order->order_status == 'pending' && config('mail.status') && $order_mail_status == '1' && isset($request->contact_person_email)) {
                    Mail::to($request->contact_person_email)->send(new PlaceOrder($order->id));
                }
                if ($order->is_guest == 1 && $order->order_status == 'pending' && config('order_delivery_verification') == 1 && $order_verification_mail_status == '1' && isset($request->contact_person_email)) {
                    Mail::to($request->contact_person_email)->send(new OrderVerificationMail($order->otp,$request->contact_person_name));
                }
            } catch (\Exception $ex) {
                info($ex->getMessage());
            }
            //PlaceOrderMail end
            return response()->json([
                'message' => translate('messages.order_placed_successfully'),
                'order_id' => $order->id,
                'total_ammount' => $order->order_amount
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e], 403);
        }

        return response()->json([
            'errors' => [
                ['code' => 'order_time', 'message' => translate('messages.failed_to_place_order')]
            ]
        ], 403);
    }
    public function prescription_place_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required_unless:order_type,parcel',
            'order_attachment' => 'required',
            'distance' => 'required_unless:order_type,take_away',
            'address' => 'required_unless:order_type,take_away',
            'longitude' => 'required_unless:order_type,take_away',
            'latitude' => 'required_unless:order_type,take_away',
            'guest_id' => $request->user ? 'nullable' : 'required',
            'contact_person_name' => $request->user ? 'nullable' : 'required',
            'contact_person_number' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $coupon = null;
        $coupon_created_by = null;
        $delivery_charge = null;
        $schedule_at = $request->schedule_at ? \Carbon\Carbon::parse($request->schedule_at) : now();
        $store = null;
        $free_delivery_by = null;
        $distance_data = $request->distance;
        $increased=0;

        if($request['order_type'] == 'delivery' && !Helpers::get_business_settings('home_delivery_status')){
            return response()->json([
                'errors' => [
                    ['code' => 'order_type', 'message' => translate('messages.home_delivery_is_not_active')]
                ]
            ], 403);
        }
        $zone = null;
        if ($request->latitude && $request->longitude) {
            $point = new Point($request->latitude, $request->longitude);
            $zone_id = isset($store) ? [$store->zone_id] : json_decode($request->header('zoneId'), true);
            $zone = Zone::where('id', $zone_id)->whereContains('coordinates', new Point($request->latitude, $request->longitude, POINT_SRID))->first();

            if (!$zone) {
                $errors = [];
                array_push($errors, ['code' => 'coordinates', 'message' => translate('messages.out_of_coverage')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
        }

        if($zone && $zone->increased_delivery_fee_status == 1){
            $increased=$zone->increased_delivery_fee ?? 0;
        }

        $data =  DMVehicle::active()->where(function ($query) use ($distance_data) {
            $query->where('starting_coverage_area', '<=', $distance_data)->where('maximum_coverage_area', '>=', $distance_data)
            ->orWhere(function ($query) use ($distance_data) {
                $query->where('starting_coverage_area', '>=', $distance_data);
            });
        })
            ->orderBy('starting_coverage_area')->first();

        $extra_charges = (float) (isset($data) ? $data->extra_charges  : 0);
        $vehicle_id = (isset($data) ? $data->id  : null);
        if ($request->schedule_at && $schedule_at < now()) {
            return response()->json([
                'errors' => [
                    ['code' => 'order_time', 'message' => translate('messages.you_can_not_schedule_a_order_in_past')]
                ]
            ], 406);
        }
        $store = Store::with('discount')->selectRaw('*, IF(((select count(*) from `store_schedule` where `stores`.`id` = `store_schedule`.`store_id` and `store_schedule`.`day` = ' . $schedule_at->format('w') . ' and `store_schedule`.`opening_time` < "' . $schedule_at->format('H:i:s') . '" and `store_schedule`.`closing_time` >"' . $schedule_at->format('H:i:s') . '") > 0), true, false) as open')->where('id', $request->store_id)->first();

        if (!$store) {
            return response()->json([
                'errors' => [
                    ['code' => 'order_time', 'message' => translate('messages.store_not_found')]
                ]
            ], 404);
        }

        if ($request->schedule_at && !$store->schedule_order) {
            return response()->json([
                'errors' => [
                    ['code' => 'schedule_at', 'message' => translate('messages.schedule_order_not_available')]
                ]
            ], 406);
        }

        if ($store->open == false) {
            return response()->json([
                'errors' => [
                    ['code' => 'order_time', 'message' => translate('messages.store_is_closed_at_order_time')]
                ]
            ], 406);
        }

        if ($request['coupon_code']) {
            $coupon = Coupon::active()->where(['code' => $request['coupon_code']])->first();
            if (isset($coupon)) {
                if($request->is_guest){
                    $staus = CouponLogic::is_valid_for_guest($coupon, $request['store_id']);
                }else{
                    $staus = CouponLogic::is_valide($coupon, $request->user->id, $request['store_id']);
                }
                if ($staus == 407) {
                    return response()->json([
                        'errors' => [
                            ['code' => 'coupon', 'message' => translate('messages.coupon_expire')]
                        ]
                    ], 407);
                } else if ($staus == 406) {
                    return response()->json([
                        'errors' => [
                            ['code' => 'coupon', 'message' => translate('messages.coupon_usage_limit_over')]
                        ]
                    ], 406);
                } else if ($staus == 404) {
                    return response()->json([
                        'errors' => [
                            ['code' => 'coupon', 'message' => translate('messages.not_found')]
                        ]
                    ], 404);
                }

                $coupon_created_by = $coupon->created_by;
                if ($coupon->coupon_type == 'free_delivery') {
                    $delivery_charge = 0;
                    $free_delivery_by =  $coupon_created_by;
                    $coupon_created_by = null;
                }
            } else {
                return response()->json([
                    'errors' => [
                        ['code' => 'coupon', 'message' => translate('messages.not_found')]
                    ]
                ], 404);
            }
        }

        $settings =  BusinessSetting::where('key', 'cash_on_delivery')->first();
        $cod = json_decode($settings?->value, true);
        if(isset($cod['status']) &&  $cod['status'] != 1 && $store->zone->cash_on_delivery!=1){
            return response()->json([
                'errors' => [
                    ['code' => 'order_time', 'message' => translate('messages.Cash_on_delivery_is_not_active')]
                ]
            ], 403);

        }

        $module_wise_delivery_charge = $store->zone->modules()->where('modules.id', $request->header('moduleId'))->first();
        if ($module_wise_delivery_charge) {
            $per_km_shipping_charge = $module_wise_delivery_charge->pivot->per_km_shipping_charge;
            $minimum_shipping_charge = $module_wise_delivery_charge->pivot->minimum_shipping_charge;
            $maximum_shipping_charge = $module_wise_delivery_charge->pivot->maximum_shipping_charge;
        } else {
            $per_km_shipping_charge = (float)BusinessSetting::where(['key' => 'per_km_shipping_charge'])->first()->value;
            $minimum_shipping_charge = (float)BusinessSetting::where(['key' => 'minimum_shipping_charge'])->first()->value;
        }

        if ($request['order_type'] != 'take_away' && !$store->free_delivery &&  !isset($delivery_charge) &&  $store->self_delivery_system == 1) {
            $per_km_shipping_charge = $store->per_km_shipping_charge;
            $minimum_shipping_charge = $store->minimum_shipping_charge;
            $maximum_shipping_charge = $store->maximum_shipping_charge;
            $extra_charges = 0;
            $vehicle_id = null;
            $increased=0;
        }

        if ($store->free_delivery || $free_delivery_by == 'vendor') {
            $per_km_shipping_charge = $store->per_km_shipping_charge;
            $minimum_shipping_charge = $store->minimum_shipping_charge;
            $maximum_shipping_charge = $store->maximum_shipping_charge;
            $extra_charges = 0;
            $vehicle_id = null;
            $increased=0;
        }

        $original_delivery_charge = ($request->distance * $per_km_shipping_charge > $minimum_shipping_charge) ? $request->distance * $per_km_shipping_charge  : $minimum_shipping_charge;

        if ($request['order_type'] == 'take_away') {
            $per_km_shipping_charge = 0;
            $minimum_shipping_charge = 0;
            $maximum_shipping_charge = 0;
            $extra_charges = 0;
            $distance_data = 0;
            $vehicle_id = null;
            $original_delivery_charge = 0;
            $increased=0;
        }

        if ($maximum_shipping_charge  >= $minimum_shipping_charge  && $original_delivery_charge >  $maximum_shipping_charge) {
            $original_delivery_charge = $maximum_shipping_charge;
        } else {
            $original_delivery_charge = $original_delivery_charge;
        }

        if (!isset($delivery_charge)) {
            $delivery_charge = ($request->distance * $per_km_shipping_charge > $minimum_shipping_charge) ? $request->distance * $per_km_shipping_charge : $minimum_shipping_charge;
            if ($maximum_shipping_charge  >= $minimum_shipping_charge  && $delivery_charge >  $maximum_shipping_charge) {
                $delivery_charge = $maximum_shipping_charge;
            } else {
                $delivery_charge = $delivery_charge;
            }
        }
        $original_delivery_charge = $original_delivery_charge + $extra_charges;
        $delivery_charge = $delivery_charge + $extra_charges;


        if($increased > 0 ){
            if($delivery_charge > 0){
                $increased_fee = ($delivery_charge * $increased) / 100;
                $delivery_charge = $delivery_charge + $increased_fee;
            }
            if($original_delivery_charge > 0){
                $increased_fee = ($original_delivery_charge * $increased) / 100;
                $original_delivery_charge = $original_delivery_charge + $increased_fee;
            }
        }

        $address = [
            'contact_person_name' => $request->contact_person_name ? $request->contact_person_name : $request->user->f_name . ' ' . $request->user->f_name,
            'contact_person_number' => $request->contact_person_number ? $request->contact_person_number : $request->user->phone,
            'address_type' => $request->address_type ? $request->address_type : 'Delivery',
            'address' => $request->address??'',
            'floor' => $request->floor??'',
            'road' => $request->road??'',
            'house' => $request->house??'',
            'longitude' => (string)$request->longitude,
            'latitude' => (string)$request->latitude,
        ];

        $img_names = [];
        $images = [];
        if (!empty($request->file('order_attachment'))) {
            foreach ($request->order_attachment as $img) {
                $image_name = Helpers::upload('order/', 'png', $img);
                array_push($img_names, $image_name);
            }
            $images = $img_names;
        } else {
            $images = null;
        }

        $total_addon_price = 0;
        $product_price = 0;
        $store_discount_amount = 0;
        $order = new Order();
        $order->id = 100000 + Order::count() + 1;
        if (Order::find($order->id)) {
            $order->id = Order::orderBy('id', 'desc')->first()->id + 1;
        }
        $order->user_id = $request->user ? $request->user->id : $request['guest_id'];
        $order->payment_status = 'unpaid';
        $order->order_status = 'pending';
        $order->coupon_code = $request['coupon_code'];
        $order->payment_method = 'cash_on_delivery';
        $order->transaction_reference = null;
        $order->order_note = $request['order_note'];
        $order->order_type = 'delivery';
        $order->store_id = $request['store_id'];
        $order->delivery_charge = round($delivery_charge, config('round_up_to_digit')) ?? 0;
        $order->original_delivery_charge = round($original_delivery_charge, config('round_up_to_digit'));
        $order->delivery_address = json_encode($address);
        $order->schedule_at = $schedule_at;
        $order->scheduled = $request->schedule_at ? 1 : 0;
        $order->otp = rand(1000, 9999);
        $order->zone_id = isset($zone) ? $zone->id : end(json_decode($request->header('zoneId'), true));
        $order->module_id = $request->header('moduleId');
        $order->pending = now();
        $order->order_attachment = json_encode($images);
        $order->distance = $request->distance;
        $order->delivery_instruction = $request['delivery_instruction'];
        $order->dm_vehicle_id = $vehicle_id;
        $order->prescription_order = 1;
        $order->is_guest = $request->user ? 0 : 1;
        $order->created_at = now();
        $order->updated_at = now();

        //Added service charge
        $additional_charge_status = BusinessSetting::where('key', 'additional_charge_status')->first()->value;
        $additional_charge = BusinessSetting::where('key', 'additional_charge')->first()->value;
        if ($additional_charge_status == 1) {
            $order->additional_charge = $additional_charge ?? 0;
        } else {
            $order->additional_charge = 0;
        }

        //Added DM TIPS
        $dm_tips_manage_status = BusinessSetting::where('key', 'dm_tips_status')->first()->value;
        if ($dm_tips_manage_status == 1) {
            $order->dm_tips = $request->dm_tips ?? 0;
        } else {
            $order->dm_tips = 0;
        }
        $order->discount_on_product_by = 'vendor';
        $store_discount = Helpers::get_store_discount($store);
        if (isset($store_discount)) {
            $order->discount_on_product_by = 'admin';
            if ($product_price + $total_addon_price < $store_discount['min_purchase']) {
                $store_discount_amount = 0;
            }

            if ($store_discount['max_discount'] != 0 && $store_discount_amount > $store_discount['max_discount']) {
                $store_discount_amount = $store_discount['max_discount'];
            }
        }
        $coupon_discount_amount = $coupon ? CouponLogic::get_discount($coupon, $product_price + $total_addon_price - $store_discount_amount) : 0;
        $total_price = $product_price + $total_addon_price - $store_discount_amount - $coupon_discount_amount;

        $tax = ($store->tax > 0) ? $store->tax : 0;
        $order->tax_status = 'excluded';

        $tax_included = BusinessSetting::where(['key' => 'tax_included'])->first() ?  BusinessSetting::where(['key' => 'tax_included'])->first()->value : 0;
        if ($tax_included ==  1) {
            $order->tax_status = 'included';
        }

        $total_tax_amount = Helpers::product_tax($total_price, $tax, $order->tax_status == 'included');

        $tax_a = $order->tax_status == 'included' ? 0 : $total_tax_amount;

        $free_delivery_over = BusinessSetting::where('key', 'free_delivery_over')->first()->value;
        if (isset($free_delivery_over)) {
            if ($free_delivery_over <= $product_price + $total_addon_price - $coupon_discount_amount - $store_discount_amount) {
                $order->delivery_charge = 0;
                $free_delivery_by = 'admin';
            }
        }

        if ($store->free_delivery) {
            $order->delivery_charge = 0;
            $free_delivery_by = 'vendor';
        }

        if ($coupon) {
            if ($coupon->coupon_type == 'free_delivery') {
                if ($coupon->min_purchase <= $product_price + $total_addon_price - $store_discount_amount) {
                    $order->delivery_charge = 0;
                    $free_delivery_by = $coupon->created_by;
                }
            }
            $coupon->increment('total_uses');
        }
        $order->coupon_created_by = $coupon_created_by;

        $order->coupon_discount_amount = round($coupon_discount_amount, config('round_up_to_digit'));
        $order->coupon_discount_title = $coupon ? $coupon->title : '';

        $order->store_discount_amount = round($store_discount_amount, config('round_up_to_digit'));
        $order->tax_percentage = $tax;
        $order->total_tax_amount = round($total_tax_amount, config('round_up_to_digit'));
        $order->order_amount = round($total_price + $tax_a + $order->delivery_charge, config('round_up_to_digit'));
        $order->free_delivery_by = $free_delivery_by;
        $order->order_amount = $order->order_amount + $order->dm_tips + $order->additional_charge;

        try {
            DB::beginTransaction();
            $order->save();
            $store->increment('total_order');
            $customer = $request->user;
            if($customer){
                $customer->zone_id = $order->zone_id;
                $customer->save();
            }
            DB::commit();
            if($order->payment_method != 'digital_payment'){
                Helpers::send_order_notification($order);
            }
            $mail_status = Helpers::get_mail_status('place_order_mail_status_user');
            //PlaceOrderMail
            try {
                if ($order->order_status == 'pending' && config('mail.status') && $mail_status == '1' && $request->user) {
                    Mail::to($request->user->email)->send(new PlaceOrder($order->id));
                }
            } catch (\Exception $ex) {
                info($ex->getMessage());
            }
            //PlaceOrderMail end
            return response()->json([
                'message' => translate('messages.order_placed_successfully'),
                'order_id' => $order->id
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([$e], 403);
        }

        return response()->json([
            'errors' => [
                ['code' => 'order_time', 'message' => translate('messages.failed_to_place_order')]
            ]
        ], 403);
    }

    public function get_order_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];

        $paginator = Order::with(['store', 'delivery_man.rating', 'parcel_category', 'refund:order_id,admin_note,customer_note'])->withCount('details')->where(['user_id' => $user_id])->whereIn('order_status', ['delivered', 'canceled', 'refund_requested', 'refund_request_canceled', 'refunded', 'failed'])->Notpos()->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);
        $orders = array_map(function ($data) {
            $data['delivery_address'] = $data['delivery_address'] ? json_decode($data['delivery_address']) : $data['delivery_address'];
            $data['store'] = $data['store'] ? Helpers::store_data_formatting($data['store']) : $data['store'];
            $data['delivery_man'] = $data['delivery_man'] ? Helpers::deliverymen_data_formatting([$data['delivery_man']]) : $data['delivery_man'];
            $data['refund_cancellation_note'] = $data['refund'] ? $data['refund']['admin_note'] : null;
            $data['refund_customer_note'] = $data['refund'] ? $data['refund']['customer_note'] : null;
            return $data;
        }, $paginator->items());
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'orders' => $orders
        ];
        return response()->json($data, 200);
    }


    public function get_running_orders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];

        $paginator = Order::with(['store', 'delivery_man.rating', 'parcel_category'])->withCount('details')->where(['user_id' => $user_id])->whereNotIn('order_status', ['delivered', 'canceled', 'refund_requested', 'refund_request_canceled', 'refunded', 'failed'])->Notpos()->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);

        $orders = array_map(function ($data) {
            $data['delivery_address'] = $data['delivery_address'] ? json_decode($data['delivery_address']) : $data['delivery_address'];
            $data['store'] = $data['store'] ? Helpers::store_data_formatting($data['store']) : $data['store'];
            $data['delivery_man'] = $data['delivery_man'] ? Helpers::deliverymen_data_formatting([$data['delivery_man']]) : $data['delivery_man'];
            return $data;
        }, $paginator->items());
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'orders' => $orders
        ];
        return response()->json($data, 200);
    }

    public function get_order_details(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];

        $order = Order::with('details', 'offline_payments','parcel_category')->where('user_id', $user_id)->find($request->order_id);

        $details = isset($order->details) ? $order->details : null;
        if ($details != null && $details->count() > 0) {
            $details = Helpers::order_details_data_formatting($details);
            // $details['store'] = $order['store'] ? Helpers::store_data_formatting($order['store']) : $order['store'];
            // $details['delivery_man'] = $order['delivery_man'] ? Helpers::deliverymen_data_formatting([$order['delivery_man']]) : $order['delivery_man'];
            return response()->json($details, 200);
        } else if ($order->order_type == 'parcel' || $order->prescription_order == 1) {
            // $order['store'] = $order['store'] ? Helpers::store_data_formatting($order['store']) : $order['store'];
            // $order['delivery_man'] = $order['delivery_man'] ? Helpers::deliverymen_data_formatting([$order['delivery_man']]) : $order['delivery_man'];
            $order->delivery_address = json_decode($order->delivery_address, true);
            if ($order->prescription_order && $order->order_attachment) {
                $order->order_attachment = json_decode($order->order_attachment, true);
            }
            return response()->json(($order), 200);
        }

        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('messages.not_found')]
            ]
        ], 404);
    }

    public function cancel_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|max:255',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];

        $order = Order::where(['user_id' => $user_id, 'id' => $request['order_id']])->Notpos()->first();
        if (!$order) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')]
                ]
            ], 403);
        } else if ($order->order_status == 'pending' || $order->order_status == 'failed'|| $order->order_status == 'canceled') {
            if (config('module.' . $order->module->module_type)['stock']) {
                foreach ($order->details as $detail) {
                    $variant = json_decode($detail['variation'], true);
                    $item = $detail->item;
                    if ($detail->campaign) {
                        $item = $detail->campaign;
                    }
                    ProductLogic::update_stock($item, -$detail->quantity, count($variant) ? $variant[0]['type'] : null)->save();
                }
            }
            $order->order_status = 'canceled';
            $order->canceled = now();
            $order->cancellation_reason = $request->reason;
            $order->canceled_by = 'customer';
            $order->save();
            Helpers::send_order_notification($order);
            return response()->json(['message' => translate('messages.order_canceled_successfully')], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('messages.you_can_not_cancel_after_confirm')]
            ]
        ], 403);
    }

    public function refund_request(Request $request)
    {
        if (BusinessSetting::where(['key' => 'refund_active_status'])->first()->value == false) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('You can not request for a refund')]
                ]
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'customer_reason' => 'required|string|max:254',
            'refund_method' => 'nullable|string|max:100',
            'customer_note' => 'nullable|string|max:65535',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order = Order::where(['user_id' => $request->user->id, 'id' => $request['order_id']])->Notpos()->first();
        if (!$order) {
            return response()->json([
                'errors' => [
                    ['code' => 'order', 'message' => translate('messages.not_found')]
                ]
            ], 404);
        } else if ($order->order_status == 'delivered' && $order->payment_status == 'paid') {

            $id_img_names = [];
            if (!empty($request->file('image'))) {
                foreach ($request->image as $img) {
                    $image = Helpers::upload('refund/', 'png', $img);
                    array_push($id_img_names, $image);
                }
                $image = json_encode($id_img_names);
            } else {
                $image = json_encode([]);
            }
            $refund_amount = round($order->order_amount - $order->delivery_charge - $order->dm_tips, config('round_up_to_digit'));
            $refund = new Refund();
            $refund->order_id = $order->id;
            $refund->user_id = $order->user_id;
            $refund->order_status = $order->order_status;
            $refund->refund_status = 'pending';
            $refund->refund_method = $request->refund_method ?? 'wallet';
            $refund->customer_reason = $request->customer_reason;
            $refund->customer_note = $request->customer_note;
            $refund->refund_amount = $refund_amount;
            $refund->image = $image;

            $order->order_status = 'refund_requested';
            $order->refund_requested = now();
            DB::beginTransaction();
            $refund->save();
            $order->save();
            DB::commit();
            $admin = Admin::where('role_id',1)->first();
            $mail_status = Helpers::get_mail_status('refund_request_mail_status_admin');
            try {
                if (config('mail.status') && $admin['email'] && $mail_status == '1') {
                    Mail::to($admin['email'])->send(new RefundRequest($order->id));
                }
            } catch (\Exception $ex) {
                info($ex->getMessage());
            }
            return response()->json(['message' => translate('messages.refund_request_placed_successfully')], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('Something went wrong')]
            ]
        ], 403);
    }

    public function update_payment_method(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $config = Helpers::get_business_settings('cash_on_delivery');
        if ($config['status'] == 0) {
            return response()->json([
                'errors' => [
                    ['code' => 'cod', 'message' => translate('messages.Cash on delivery order not available at this time')]
                ]
            ], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $order = Order::where(['user_id' => $user_id, 'id' => $request['order_id']])->Notpos()->first();
        if ($order) {
            if($order->payment_method != 'partial_payment') {
                Order::where(['user_id' => $user_id, 'id' => $request['order_id']])->update([
                    'payment_method' => 'cash_on_delivery', 'order_status' => 'pending', 'pending' => now()
                ]);
            }else{
                Order::where(['user_id' => $user_id, 'id' => $request['order_id']])->update([
                    'order_status' => 'pending', 'pending' => now()
                ]);
                $payment = OrderPayment::where('payment_status','unpaid')->where('order_id',$request['order_id'])->first();
                if($payment){
                    $payment->payment_method = 'cash_on_delivery';
                }
                $payment->save();
            }

            $order = Order::where(['user_id' => $user_id, 'id' => $request['order_id']])->Notpos()->first();

            try {
                Helpers::send_order_notification($order);
            } catch (\Exception $e) {
                info($e->getMessage());
            }
            return response()->json(['message' => translate('messages.payment_method_updated_successfully')], 200);
        }
        return response()->json([
            'errors' => [
                ['code' => 'order', 'message' => translate('messages.not_found')]
            ]
        ], 404);
    }

    public function refund_reasons()
    {
        $refund_reasons = RefundReason::where('status', 1)->get();
        return response()->json([
            'refund_reasons' => $refund_reasons
        ], 200);
    }

    public function cancellation_reason(Request $request)
    {
        $limit = $request->query('limit', 25);
        $offset = $request->query('offset', 25);

        $reasons = OrderCancelReason::where('status', 1)->when($request->type, function ($query) use ($request) {
            $query->where('user_type', $request->type);
        })->paginate($limit, ['*'], 'page', $offset);

        $data = [
            'total_size' => $reasons->total(),
            'limit' => $limit,
            'offset' => $offset,
            'data' => $reasons->items()
        ];
        return response()->json($data, 200);
    }

    public function most_tips()
    {
        $data = Order::whereNot('dm_tips',0)->get()->mode('dm_tips');
        $data = ($data && (count($data)>0))?$data[0]:null;
        return response()->json([
            'most_tips_amount' => $data
        ], 200);
    }


    public function offline_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'method_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $config = Helpers::get_mail_status('offline_payment_status');
        if ($config == 0) {
            return response()->json([
                'errors' => [
                    ['code' => 'offline_payment_status', 'message' => translate('messages.offline_payment_for_the_order_not_available_at_this_time')]
                ]
            ], 403);
        }
        $order = Order::findOrFail($request->order_id);

        $offline_payment_info = [];
        $method = OfflinePaymentMethod::where(['id'=>$request->method_id,'status'=>1])->first();
        try{
            if(isset($method))
            {
                $fields = array_column($method->method_informations, 'customer_input');
                $values = $request->all();

                $offline_payment_info['method_id'] = $request->method_id;
                $offline_payment_info['method_name'] = $method->method_name;
                foreach ($fields as $field) {
                    if(key_exists($field, $values)) {
                        $offline_payment_info[$field] = $values[$field];
                    }
                }
            }

            // $order->payment_status= 'paid';

            $OfflinePayments= OfflinePayments::firstOrNew(['order_id' => $order->id]);
            $OfflinePayments->payment_info =json_encode($offline_payment_info);
            $OfflinePayments->customer_note = $request->customer_note;
            $OfflinePayments->method_fields = json_encode($method?->method_fields);
            DB::beginTransaction();
            $OfflinePayments->save();
            $order->save();
            DB::commit();




                $data = [
                    'title' => translate('messages.order_push_title'),
                    'description' => translate('messages.new_order_push_description'),
                    'order_id' => $order->id,
                    'image' => '',
                    'module_id' => $order->module_id,
                    'order_type' => $order->order_type,
                    'zone_id' => $order->zone_id,
                    'type' => 'new_order',
                ];
                Helpers::send_push_notif_to_topic($data, 'admin_message', 'order_request', url('/').'/admin/order/list/all');

            return response()->json([
                'payment' => 'success'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([ 'payment' => $e->getMessage()], 403);
        }
    }


    public function update_offline_payment_info(Request $request){
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $order= Order::where('id' , $request->order_id)->firstOrfail();

        $info= OfflinePayments::where('order_id' , $request->order_id)->firstOrfail();
            $old_data =   json_decode($info->payment_info , true) ;
            $method_id= data_get($old_data,'method_id',null);
                $offline_payment_info = [];
                $method = OfflinePaymentMethod::where('id', $method_id)->first();
                if(isset($method))
                {
                    $fields = array_column($method->method_informations, 'customer_input');
                    $values = $request->all();

                    $offline_payment_info['method_id'] =$method->id;
                    $offline_payment_info['method_name'] = $method->method_name;
                    foreach ($fields as $field) {
                        if(key_exists($field, $values)) {
                            $offline_payment_info[$field] = $values[$field];
                        }
                    }
                }

            $info->customer_note = $request->customer_note ?? $info->customer_note;
            $info->payment_info =json_encode($offline_payment_info);
            $info->status = 'pending';
            $info->save();

            Helpers::send_order_notification($order);

        return response()->json([ 'payment' => 'Payment_Info_Updated_successfully' ], 200);
    }



    public function order_again(Request $request){
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $longitude= $request->header('longitude') ?? 0;
        $latitude= $request->header('latitude') ?? 0;

        $zone_id= json_decode($request->header('zoneId'), true);
        $data = Store::withOpen($longitude,$latitude)->
        wherehas('orders' ,function($q) use($request){
            $q->where('user_id',$request->user()->id)->latest();
        })
        ->where('module_id' , $request->header('moduleId'))
        ->withcount('items')
        ->with(['items_for_reorder'])
        ->Active()
        ->whereIn('zone_id', $zone_id)
        ->take(20)

        ->orderBy('open', 'desc')
        ->get()
		->map(function ($data) {
			$data->items = $data->items_for_reorder->take(5);
            unset($data->items_for_reorder);
			return $data;
		});

        return response()->json(Helpers::store_data_formatting($data, true), 200);
    }
}
