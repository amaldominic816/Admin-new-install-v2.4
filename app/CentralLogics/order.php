<?php

namespace App\CentralLogics;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\AdminWallet;
use App\Models\BusinessSetting;
use App\Models\StoreWallet;
use App\Models\DeliveryManWallet;
use App\CentralLogics\CustomerLogic;
use App\Models\OrderPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderLogic
{
    public static function gen_unique_id()
    {
        return rand(1000, 9999) . '-' . Str::random(5) . '-' . time();
    }

    public static function track_order($order_id)
    {
        return Helpers::order_data_formatting(Order::with(['details', 'delivery_man.rating'])->where(['id' => $order_id])->first(), false);
    }

    public static function updated_order_calculation($order)
    {
        return true;
    }
    public static function create_transaction($order, $received_by=false, $status = null)
    {
        $type = $order->order_type;
        $dm_tips_manage_status = BusinessSetting::where('key', 'dm_tips_status')->first()->value;
        $admin_subsidy = 0;
        $amount_admin = 0;
        $store_d_amount = 0;
        $admin_coupon_discount_subsidy =0;
        $store_subsidy =0;
        $store_coupon_discount_subsidy =0;
        $store_discount_amount=0;
        $flash_admin_discount_amount=0;
        $flash_store_discount_amount=0;
        $comission_on_store_amount=0;

        // free delivery by admin
        if($order->free_delivery_by == 'admin')
        {
            $admin_subsidy = $order->original_delivery_charge;
            Helpers::expenseCreate(amount:$order->original_delivery_charge,type:'free_delivery',datetime:now(),created_by:$order->free_delivery_by,order_id:$order->id);
        }
        // free delivery by store
        if($order->free_delivery_by == 'vendor')
        {
            $store_subsidy = $order->original_delivery_charge;
            Helpers::expenseCreate(amount:$order->original_delivery_charge,type:'free_delivery',datetime:now(),created_by:$order->free_delivery_by,order_id:$order->id,store_id:$order->store->id);
        }
        // coupon discount by Admin
        if($order->coupon_created_by == 'admin')
        {
            $admin_coupon_discount_subsidy = $order->coupon_discount_amount;
            Helpers::expenseCreate(amount:$admin_coupon_discount_subsidy,type:'coupon_discount',datetime:now(),created_by:$order->coupon_created_by,order_id:$order->id);
        }
        // coupon discount by store
        if($order->coupon_created_by == 'vendor')
        {
            $store_coupon_discount_subsidy = $order->coupon_discount_amount;
            Helpers::expenseCreate(amount:$store_coupon_discount_subsidy,type:'coupon_discount',datetime:now(),created_by:$order->coupon_created_by, order_id:$order->id,store_id:$order->store->id);
        }

        if($type=='parcel')
        {
            $comission = \App\Models\BusinessSetting::where('key','parcel_commission_dm')->first();
            $dm_tips = $dm_tips_manage_status ? $order->dm_tips : 0;
            $comission = isset($comission) ? $comission->value : 0;
            $order_amount = $order->order_amount - $dm_tips - $order->additional_charge;
            $dm_commission = $comission?($order_amount/ 100) * $comission:0;
            $comission_amount = $order_amount - $dm_commission;
        }
        else
        {
            $comission = isset($order->store->comission) == null?\App\Models\BusinessSetting::where('key','admin_commission')->first()->value:$order->store->comission;
            $dm_tips = $dm_tips_manage_status ? $order->dm_tips : 0;
            // $order_amount = $order->order_amount - $order->delivery_charge - $order->total_tax_amount - $dm_tips;

            if($order->store_discount_amount > 0  && $order->discount_on_product_by == 'vendor')
            {
                $amount_admin = $comission?($order->store_discount_amount/ 100) * $comission:0;
                $store_d_amount=  $order->store_discount_amount- $amount_admin;
                Helpers::expenseCreate(amount:$store_d_amount,type:'discount_on_product',datetime:now(),created_by:'vendor',order_id:$order->id,store_id:$order->store->id);
                Helpers::expenseCreate(amount:$amount_admin,type:'discount_on_product',datetime:now(),created_by:'admin',order_id:$order->id);
            }

            if($order->store_discount_amount > 0  && $order->discount_on_product_by == 'admin')
            {
                $store_discount_amount=$order->store_discount_amount;
                Helpers::expenseCreate(amount:$store_discount_amount,type:'discount_on_product',datetime:now(),created_by:'admin',order_id:$order->id);
            }

            if($order->flash_admin_discount_amount > 0)
            {
                $flash_admin_discount_amount=$order->flash_admin_discount_amount;
                Helpers::expenseCreate(amount:$flash_admin_discount_amount,type:'flash_sale_discount',datetime:now(),created_by:'admin',order_id:$order->id);
            }

            if($order->flash_store_discount_amount > 0)
            {
                $flash_store_discount_amount=$order->flash_store_discount_amount;
                Helpers::expenseCreate(amount:$flash_store_discount_amount,type:'flash_sale_discount',datetime:now(),created_by:'vendor',order_id:$order->id,store_id:$order->store->id);
            }


            $order_amount = $order->order_amount - $order->additional_charge - $order->delivery_charge - $order->total_tax_amount - $dm_tips + $flash_admin_discount_amount + $order->coupon_discount_amount + $store_discount_amount + $flash_store_discount_amount;
            // comission in delivery charge
            $delivery_charge_comission = BusinessSetting::where('key', 'delivery_charge_comission')->first();
            $delivery_charge_comission_percentage = $delivery_charge_comission ? $delivery_charge_comission->value : 0;
            $comission_on_delivery = $delivery_charge_comission_percentage * ( $order->original_delivery_charge / 100 );

            if($order->store->self_delivery_system)
            {
                $comission_on_actual_delivery_fee = 0;
            }else{

                $comission_on_actual_delivery_fee = ($order->delivery_charge > 0) ? $comission_on_delivery : 0;
            }

            //final comission
            $comission_on_store_amount = ($comission?($order_amount/ 100) * $comission:0);
            $comission_amount = $comission_on_store_amount + $comission_on_actual_delivery_fee;
            $dm_commission = $order->original_delivery_charge - $comission_on_actual_delivery_fee;

            if($order->free_delivery_by == 'admin')
            {
                if($order->store->self_delivery_system)
                {
                    $comission_on_actual_delivery_fee = 0;
                }else{

                    $comission_on_actual_delivery_fee = ($order->original_delivery_charge > 0) ? $comission_on_delivery : 0;
                }

                //final comission
                $comission_on_store_amount = ($comission?($order_amount/ 100) * $comission:0);
                $comission_amount = $comission_on_store_amount + $comission_on_actual_delivery_fee;
                $dm_commission = $order->original_delivery_charge - $comission_on_actual_delivery_fee;
            }
        }
        $store_amount =$order_amount + $order->total_tax_amount - $comission_on_store_amount - $store_coupon_discount_subsidy - $flash_store_discount_amount;
        try{
            OrderTransaction::insert([
                'vendor_id' =>$type=='parcel'?null:$order->store->vendor->id,
                'delivery_man_id'=>$order->delivery_man_id,
                'order_id' =>$order->id,
                'order_amount'=>$order->order_amount,
                'store_amount'=>$type=='parcel' ? 0 : $store_amount,
                // 'store_amount'=>$type=='parcel' ? 0 : $order_amount + $order->total_tax_amount - $comission_on_store_amount,
                'admin_commission'=>$comission_amount + $order->additional_charge - $admin_subsidy - $admin_coupon_discount_subsidy,
                'delivery_charge'=>$order->delivery_charge,
                'original_delivery_charge'=>$dm_commission,
                'tax'=>$order->total_tax_amount,
                'received_by'=> $received_by?$received_by:'admin',
                'zone_id'=>$order->zone_id,
                'module_id'=>$order->module_id,
                'admin_expense'=>$admin_subsidy + $admin_coupon_discount_subsidy + $store_discount_amount + $flash_admin_discount_amount + $amount_admin,
                'store_expense'=>$store_subsidy + $store_coupon_discount_subsidy + $flash_store_discount_amount,
                'status'=> $status,
                'dm_tips'=> $dm_tips,
                'created_at' => now(),
                'updated_at' => now(),
                'delivery_fee_comission'=>isset($comission_on_actual_delivery_fee)?$comission_on_actual_delivery_fee: 0,
                'discount_amount_by_store' => $store_coupon_discount_subsidy + $store_d_amount + $store_subsidy,
                'additional_charge' => $order->additional_charge,
            ]);
            $adminWallet = AdminWallet::firstOrNew(
                ['admin_id' => Admin::where('role_id', 1)->first()->id]
            );

            $adminWallet->total_commission_earning = $adminWallet->total_commission_earning + $comission_amount + $order->additional_charge - $admin_subsidy- $admin_coupon_discount_subsidy -$store_discount_amount - $flash_admin_discount_amount;

            if($type != 'parcel')
            {
                $vendorWallet = StoreWallet::firstOrNew(
                    ['vendor_id' => $order->store->vendor->id]
                );
                if($order->store->self_delivery_system)
                {
                    $vendorWallet->total_earning = $vendorWallet->total_earning + $order->delivery_charge + $dm_tips;
                }
                else{
                    $adminWallet->delivery_charge = $adminWallet->delivery_charge+$order->delivery_charge;
                }
                // $vendorWallet->total_earning = $vendorWallet->total_earning+($order_amount + $order->total_tax_amount - $comission_on_store_amount);
                $vendorWallet->total_earning = $vendorWallet->total_earning+$store_amount;
            }
            if($order->delivery_man && ($type == 'parcel' || ($order->store && !$order->store->self_delivery_system))){
                $dmWallet = DeliveryManWallet::firstOrNew(
                    ['delivery_man_id' => $order->delivery_man_id]
                );
                if($order->delivery_man->earning == 1){
                    $dmWallet->total_earning = $dmWallet->total_earning + $dm_commission+ $dm_tips;
                }else {
                    $adminWallet->total_commission_earning = $adminWallet->total_commission_earning + $dm_commission + $dm_tips;
                }
            } else {
                $adminWallet->total_commission_earning = $adminWallet->total_commission_earning + $dm_commission + $dm_tips;
            }

            try
            {
                DB::beginTransaction();
                $unpaid_payment = OrderPayment::where('payment_status','unpaid')->where('order_id',$order->id)->first()?->payment_method;
                $unpaid_pay_method = 'digital_payment';
                if($unpaid_payment){
                    $unpaid_pay_method = $unpaid_payment;
                }
                if($received_by=='admin')
                {
                    $adminWallet->digital_received = $adminWallet->digital_received+($order->order_amount-$order->partially_paid_amount);
                }
                else if($received_by=='store' && $type != 'parcel' && ($order->payment_method == "cash_on_delivery" || $unpaid_pay_method == 'cash_on_delivery'))
                {
                    $vendorWallet->collected_cash = $vendorWallet->collected_cash+($order->order_amount-$order->partially_paid_amount);
                }
                else if($received_by==false)
                {
                    $adminWallet->manual_received = $adminWallet->manual_received+($order->order_amount-$order->partially_paid_amount);
                }
                else if($received_by=='deliveryman' && $order->delivery_man && $order->delivery_man->type == 'zone_wise')
                {
                    $dmWallet->collected_cash = $dmWallet->collected_cash+($order->order_amount-$order->partially_paid_amount);
                }

                $adminWallet->save();
                if($type != 'parcel')
                {
                    $vendorWallet->save();
                }
                if(isset($dmWallet)){
                    $dmWallet->save();
                }

                self::update_unpaid_order_payment(order_id:$order->id, payment_method:$order->payment_method);

                DB::commit();

                $ref_status = BusinessSetting::where('key','ref_earning_status')->first()->value;
                if(isset($order->customer->ref_by) && $order->customer->order_count == 0  && $ref_status == 1){
                    $ref_code_exchange_amt = BusinessSetting::where('key','ref_earning_exchange_rate')->first()->value;
                    $referar_user=User::where('id',$order->customer->ref_by)->first();
                    $refer_wallet_transaction = CustomerLogic::create_wallet_transaction($referar_user->id, $ref_code_exchange_amt, 'referrer',$order->customer->phone);
                    $mail_status = Helpers::get_mail_status('add_fund_mail_status_user');

                    try{
                        if(config('mail.status') && $mail_status == '1') {
                            Mail::to($referar_user->email)->send(new \App\Mail\AddFundToWallet($refer_wallet_transaction));
                            }
                        } catch(\Exception $ex){
                            info($ex->getMessage());
                        }
                }

                if($order->user_id) CustomerLogic::create_loyalty_point_transaction($order->user_id, $order->id, $order->order_amount, 'order_place');

            }
            catch(\Exception $e)
            {
                DB::rollBack();
                info($e->getMessage());
                return false;
            }
        }
        catch(\Exception $e){
            info($e->getMessage());
            return false;
        }

        return true;
    }

    public static function refund_before_delivered($order){
        $adminWallet = AdminWallet::firstOrNew(
            ['admin_id' => Admin::where('role_id', 1)->first()->id]
        );
        if ($order->payment_method == 'cash_on_delivery') {
            return false;
        }
        if(($order->payment_status == "paid")){

            $adminWallet->digital_received = $adminWallet->digital_received - $order->order_amount;
            $adminWallet->save();
            if (BusinessSetting::where('key', 'wallet_add_refund')->first()->value == 1) {
                CustomerLogic::create_wallet_transaction($order->user_id, $order->order_amount, 'order_refund', $order->id);
            }
        }elseif(($order->payment_status == "partially_paid")){

            $adminWallet->digital_received = $adminWallet->digital_received - $order->partially_paid_amount;
            $adminWallet->save();
            if (BusinessSetting::where('key', 'wallet_add_refund')->first()->value == 1) {
                CustomerLogic::create_wallet_transaction($order->user_id, $order->partially_paid_amount, 'order_refund', $order->id);
            }
        }
        return true;
    }

    public static function refund_order($order)
    {
        $order_transaction = $order->transaction;
        if($order_transaction == null || $order->store == null)
        {
            return false;
        }
        $received_by = $order_transaction->received_by;

        $adminWallet = AdminWallet::firstOrNew(
            ['admin_id' => Admin::where('role_id', 1)->first()->id]
        );

        $vendorWallet = StoreWallet::firstOrNew(
            ['vendor_id' => $order->store->vendor->id]
        );

        $adminWallet->total_commission_earning = $adminWallet->total_commission_earning - $order_transaction->admin_commission + $order_transaction->delivery_fee_comission;

        $vendorWallet->total_earning = $vendorWallet->total_earning - $order_transaction->store_amount;

        $refund_amount = $order->order_amount - $order->additional_charge;

        $status = 'refunded_with_delivery_charge';
        if($order->order_status == 'delivered' || $order->order_status == 'refund_requested')
        {
            $refund_amount = $order->order_amount - $order->additional_charge - $order->delivery_charge -$order->dm_tips;
            $status = 'refunded_without_delivery_charge';
        }
        else
        {
            $adminWallet->delivery_charge = $adminWallet->delivery_charge - $order_transaction->delivery_charge;
        }
        try
        {
            DB::beginTransaction();
            $partially_paid = OrderPayment::where('payment_method','cash_on_delivery')->where('order_id',$order->id)->exists() ?? false;

            if($partially_paid){
                $refund_amount = $refund_amount - $order->partially_paid_amount;
            }
            if($received_by=='admin')
            {
                if($order->delivery_man_id && $order->payment_method != "cash_on_delivery")
                {
                    $adminWallet->digital_received = $adminWallet->digital_received - $refund_amount;
                }
                else
                {
                    $adminWallet->manual_received = $adminWallet->manual_received - $refund_amount;
                }

            }
            else if($received_by=='store')
            {
                $vendorWallet->collected_cash = $vendorWallet->collected_cash - $refund_amount;
            }

            // else if($received_by=='deliveryman')
            // {
            //     $dmWallet = DeliveryManWallet::firstOrNew(
            //         ['delivery_man_id' => $order->delivery_man_id]
            //     );
            //     $dmWallet->collected_cash=$dmWallet->collected_cash - $refund_amount;
            //     $dmWallet->save();
            // }
            $order_transaction->status = $status;
            $order_transaction->save();
            $adminWallet->save();
            $vendorWallet->save();
            DB::commit();
        }
        catch(\Exception $e)
        {
            DB::rollBack();
            info($e->getMessage());
            return false;
        }
        return true;

    }

    public static function format_export_data($orders, $type='order')
    {
        $data = [];
        foreach($orders as $key=>$order)
        {

            $data[]=[
                '#'=>$key+1,
                translate('messages.order')=>$order['id'],
                translate('messages.date')=>date('d M Y',strtotime($order['created_at'])),
                translate('messages.customer')=>$order->customer?$order->customer['f_name'].' '.$order->customer['l_name']:translate('messages.invalid_customer_data'),
                translate($type=='order'?'messages.store':'messages.parcel_category')=>Str::limit($type=='order'?($order->store?$order->store->name:translate('messages.store deleted!')):($order->parcel_category?$order->parcel_category->name:translate('messages.not_found')),20,'...'),
                translate('messages.payment_status')=>$order->payment_status=='paid'?translate('messages.paid'):translate('messages.unpaid'),
                translate('messages.total')=>\App\CentralLogics\Helpers::format_currency($order['order_amount']),
                translate('messages.order_status')=>translate('messages.'. $order['order_status']),
                translate('messages.order_type')=>translate('messages.'.$order['order_type'])
            ];
        }
        return $data;
    }
    public static function format_store_order_export_data($orders)
    {
        $data = [];
        foreach($orders as $key=>$order)
        {

            $data[]=[
                '#'=>$key+1,
                translate('messages.order')=>$order['id'],
                translate('messages.date')=>date('d M Y',strtotime($order['created_at'])),
                translate('messages.customer')=>$order->customer?$order->customer['f_name'].' '.$order->customer['l_name']:translate('messages.invalid_customer_data'),
                translate('messages.payment_status')=>$order->payment_status=='paid'?translate('messages.paid'):translate('messages.unpaid'),
                translate('messages.total')=>\App\CentralLogics\Helpers::format_currency($order['order_amount']),
                translate('messages.order_status')=>translate('messages.'. $order['order_status']),
                translate('messages.order_type')=>translate('messages.'.$order['order_type']),
                translate('messages.discount_amount')=>$order['coupon_discount_amount']+$order['store_discount_amount'],
                translate('messages.total_tax_amount')=>$order['total_tax_amount'],
                translate('messages.delivery_charge')=>$order['original_delivery_charge']
            ];
        }
        return $data;
    }

    public static function format_order_report_export_data($orders)
    {
        $data = [];
        foreach($orders as $key=>$order)
        {

            $data[]=[
                '#'=>$key+1,
                translate('messages.order')=>$order['id'],
                translate('messages.store')=>$order->store?$order->store->name:translate('messages.invalid'),
                translate('messages.customer_name')=>$order->customer?$order->customer['f_name'].' '.$order->customer['l_name']:translate('messages.invalid_customer_data'),
                translate('Total Item Amount')=>\App\CentralLogics\Helpers::format_currency($order['order_amount']-$order['dm_tips']-$order['total_tax_amount']-$order['delivery_charge']+$order['coupon_discount_amount'] + $order['store_discount_amount']),
                translate('Item Discount')=>\App\CentralLogics\Helpers::format_currency($order->details->sum('discount_on_item')),
                translate('Coupon Discount')=>\App\CentralLogics\Helpers::format_currency($order['coupon_discount_amount']),
                translate('Discounted Amount')=>\App\CentralLogics\Helpers::format_currency($order['coupon_discount_amount'] + $order['store_discount_amount']),
                translate('messages.tax')=>\App\CentralLogics\Helpers::format_currency($order['total_tax_amount']),
                translate('messages.delivery_charge')=>\App\CentralLogics\Helpers::format_currency($order['original_delivery_charge']),
                translate('messages.order_amount')=>\App\CentralLogics\Helpers::format_currency($order['order_amount']),
                translate('messages.amount_received_by')=>isset($order->transaction) ? $order->transaction->received_by : translate('messages.unpaid'),
                translate('messages.payment_method')=>translate(str_replace('_', ' ', $order['payment_method'])),
                translate('messages.order_status')=>translate('messages.'. $order['order_status']),
                translate('messages.order_type')=>translate('messages.'.$order['order_type']),
            ];
        }
        return $data;
    }

    public static function create_order_payment($order_id, $amount, $payment_status, $payment_method)
    {
        $payment = new OrderPayment();
        $payment->order_id = $order_id;
        $payment->amount = $amount;
        $payment->payment_status = $payment_status;
        $payment->payment_method = $payment_method;
        if($payment->save()){
            return true;
        }

        return false;

    }

    public static function update_unpaid_order_payment($order_id,$payment_method)
    {
        $payment = OrderPayment::where('payment_status','unpaid')->where('order_id',$order_id)->first();
        if($payment){
            $payment->payment_status = 'paid';
            if($payment_method != 'partial_payment'){
                $payment->payment_method = $payment_method;
            }
            if($payment->save()){
                return true;
            }

            return false;
        }
        return true;

    }
}
