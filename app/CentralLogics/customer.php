<?php

namespace App\CentralLogics;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\BusinessSetting;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\LoyaltyPointTransaction;
use App\Models\WalletBonus;

class CustomerLogic
{

    public static function create_wallet_transaction($user_id, float $amount, $transaction_type, $referance)
    {
        if (BusinessSetting::where('key', 'wallet_status')->first()->value != 1) return false;
        $user = User::find($user_id);
        $current_balance = $user->wallet_balance;

        $wallet_transaction = new WalletTransaction();
        $wallet_transaction->user_id = $user->id;
        $wallet_transaction->transaction_id = Str::uuid();
        $wallet_transaction->reference = $referance;
        $wallet_transaction->transaction_type = $transaction_type;

        $debit = 0.0;
        $credit = 0.0;
        $admin_bonus = 0.0;

        if (in_array($transaction_type, ['add_fund_by_admin', 'add_fund', 'order_refund', 'loyalty_point', 'referrer'])) {
            $credit = $amount;
            if ($transaction_type == 'add_fund') {
                $admin_bonus = self::calculate_wallet_bonus($amount);
                $wallet_transaction->admin_bonus = $admin_bonus;
            } else if ($transaction_type == 'loyalty_point') {

                $check_loyalty_point_exchange_rate = (int) BusinessSetting::where('key', 'loyalty_point_exchange_rate')->first()->value;

                if($check_loyalty_point_exchange_rate == 0){
                    
                    $credit = (int)($amount / 1);
                }
                else{
                    $credit = (int)($amount / BusinessSetting::where('key', 'loyalty_point_exchange_rate')->first()->value);
                }
            }
        } else if ($transaction_type == 'order_place') {
            $debit = $amount;
        } else if ($transaction_type == 'partial_payment') {
            $debit = $amount;
            $credit = 0.0;
        }

        $wallet_transaction->credit = $credit;
        $wallet_transaction->debit = $debit;
        $wallet_transaction->balance = $current_balance + $credit + $admin_bonus - $debit;
        $wallet_transaction->created_at = now();
        $wallet_transaction->updated_at = now();
        $user->wallet_balance = $current_balance + $credit + $admin_bonus - $debit;

        try {
            DB::beginTransaction();
            $user->save();
            $wallet_transaction->save();
            if ($admin_bonus>0) {
                Helpers::expenseCreate(amount:$admin_bonus,type:'add_fund_bonus',created_by:'admin',user_id:$user->id,datetime:now());
            }
            DB::commit();
            if (in_array($transaction_type, ['loyalty_point', 'order_place', 'add_fund_by_admin', 'referrer','partial_payment'])) return $wallet_transaction;
            return true;
        } catch (\Exception $ex) {
            info($ex->getMessage());
            DB::rollback();

            return false;
        }
        return false;
    }

    public static function create_loyalty_point_transaction($user_id, $referance, $amount, $transaction_type)
    {
        $settings = array_column(BusinessSetting::whereIn('key', ['loyalty_point_status', 'loyalty_point_exchange_rate', 'loyalty_point_item_purchase_point'])->get()->toArray(), 'value', 'key');
        if ($settings['loyalty_point_status'] != 1) {
            return true;
        }

        $credit = 0;
        $debit = 0;
        $user = User::find($user_id);

        $loyalty_point_transaction = new LoyaltyPointTransaction();
        $loyalty_point_transaction->user_id = $user->id;
        $loyalty_point_transaction->transaction_id = Str::uuid();
        $loyalty_point_transaction->reference = $referance;
        $loyalty_point_transaction->transaction_type = $transaction_type;

        if ($transaction_type == 'order_place') {
            $credit = (int)($amount * $settings['loyalty_point_item_purchase_point'] / 100);
        } else if ($transaction_type == 'point_to_wallet') {
            $debit = $amount;
        }

        $current_balance = $user->loyalty_point + $credit - $debit;
        $loyalty_point_transaction->balance = $current_balance;
        $loyalty_point_transaction->credit = $credit;
        $loyalty_point_transaction->debit = $debit;
        $loyalty_point_transaction->created_at = now();
        $loyalty_point_transaction->updated_at = now();
        $user->loyalty_point = $current_balance;

        try {
            DB::beginTransaction();
            $user->save();
            $loyalty_point_transaction->save();
            DB::commit();
            return true;
        } catch (\Exception $ex) {
            info($ex->getMessage());
            DB::rollback();

            return false;
        }
        return false;
    }

    public static function calculate_wallet_bonus($add_amount) 
    {
        $percent_bonus = WalletBonus::active()->where('bonus_type','percentage')
        ->whereDate('end_date', '>=', date('Y-m-d'))->whereDate('start_date', '<=', date('Y-m-d'))->where('minimum_add_amount','<=',$add_amount)->orderBy('bonus_amount','desc')->first();
        $amount_bonus = WalletBonus::active()->where('bonus_type','amount')
        ->whereDate('end_date', '>=', date('Y-m-d'))->whereDate('start_date', '<=', date('Y-m-d'))->where('minimum_add_amount','<=',$add_amount)->orderBy('bonus_amount','desc')->first();

        if($percent_bonus && ($add_amount>=$percent_bonus->minimum_add_amount)){
            $p_bonus = ($add_amount * $percent_bonus->bonus_amount)/100;
            $p_bonus = $p_bonus > $percent_bonus->maximum_bonus_amount ? $percent_bonus->maximum_bonus_amount : $p_bonus;
        }else{
            $p_bonus = 0;
        }

        if($amount_bonus && ($add_amount>=$amount_bonus->minimum_add_amount)){
            $a_bonus = $amount_bonus?$amount_bonus->bonus_amount: 0;
        }else{
            $a_bonus = 0;
        }

        $bonus_amount = max([$p_bonus,$a_bonus]);

        return $bonus_amount;    
    }
}
