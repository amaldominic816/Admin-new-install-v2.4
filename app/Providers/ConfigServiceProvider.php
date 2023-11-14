<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Module;
use App\Models\Setting;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
Carbon::setWeekStartsAt(Carbon::MONDAY);
Carbon::setWeekEndsAt(Carbon::SUNDAY);
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $mode = env('APP_MODE');
        try {
            $data = BusinessSetting::where(['key' => 'mail_config'])->first();
            $emailServices = json_decode($data['value'], true);
            if ($emailServices) {
                $config = array(
                    'status' => (Boolean)(isset($emailServices['status'])?$emailServices['status']:1),
                    'driver' => $emailServices['driver'],
                    'host' => $emailServices['host'],
                    'port' => $emailServices['port'],
                    'username' => $emailServices['username'],
                    'password' => $emailServices['password'],
                    'encryption' => $emailServices['encryption'],
                    'from' => array('address' => $emailServices['email_id'], 'name' => $emailServices['name']),
                    'sendmail' => '/usr/sbin/sendmail -bs',
                    'pretend' => false,
                );
                Config::set('mail', $config);
            }

            $gateway=
            [ 'paytm',
            'razor_pay',
            'flutterwave',
            'paypal',
            'ssl_commerz',
            'paystack' ];

            $data= Setting::whereIn('key_name',$gateway)->pluck('live_values','key_name')->toArray();
            if (isset($data['paystack'])) {
                $config = array(
                    'publicKey' => env('PAYSTACK_PUBLIC_KEY',data_get($data,'paystack.public_key',null)),
                    'secretKey' => env('PAYSTACK_SECRET_KEY', data_get($data,'paystack.secret_key',null)),
                    'paymentUrl' => env('PAYSTACK_PAYMENT_URL',data_get($data,'paystack.callback_url',null)),
                    'merchantEmail' => env('MERCHANT_EMAIL', data_get($data,'paystack.merchant_email',null)),
                );
                Config::set('paystack', $config);
            }


            if (data_get($data,'ssl_commerz',null)) {
                if ( data_get($data,'ssl_commerz.mode',null) == 'live') {
                    $url = "https://securepay.sslcommerz.com";
                    $host = false;
                } else {
                    $url = "https://sandbox.sslcommerz.com";
                    $host = true;
                }
                $config = array(
                    'projectPath' => env('PROJECT_PATH'),
                    'apiDomain' => env("API_DOMAIN_URL", $url),
                    'apiCredentials' => [
                        'store_id' => data_get($data,'ssl_commerz.store_id',null),
                        'store_password' => data_get($data,'ssl_commerz.store_password',null),
                    ],
                    'apiUrl' => [
                        'make_payment' => "/gwprocess/v4/api.php",
                        'transaction_status' => "/validator/api/merchantTransIDvalidationAPI.php",
                        'order_validate' => "/validator/api/validationserverAPI.php",
                        'refund_payment' => "/validator/api/merchantTransIDvalidationAPI.php",
                        'refund_status' => "/validator/api/merchantTransIDvalidationAPI.php",
                    ],
                    'connect_from_localhost' => env("IS_LOCALHOST", $host), // For Sandbox, use "true", For Live, use "false"
                    'success_url' => '/success',
                    'failed_url' => '/fail',
                    'cancel_url' => '/cancel',
                    'ipn_url' => '/ipn',
                );
                Config::set('sslcommerz', $config);
            }

            if (data_get($data,'paypal',null)) {
                if (data_get($data,'paypal.mode',null) == 'live') {
                    $paypal_mode = "live";
                } else {
                    $paypal_mode = "sandbox";
                }
                $config = array(
                    'client_id' => data_get($data,'paypal.client_id',null), // values : (local | production)
                    'secret' => data_get($data,'paypal.client_secret',null),
                    'settings' => array(
                        'mode' => env('PAYPAL_MODE', $paypal_mode), //live||sandbox
                        'http.ConnectionTimeOut' => 30,
                        'log.LogEnabled' => true,
                        'log.FileName' => storage_path() . '/logs/paypal.log',
                        'log.LogLevel' => 'ERROR'
                    ),
                );
                Config::set('paypal', $config);
            }

            if (data_get($data,'flutterwave',null)) {
                $config = array(
                    'publicKey' => env('FLW_PUBLIC_KEY',data_get($data,'flutterwave.public_key',null)), // values : (local | production)
                    'secretKey' => env('FLW_SECRET_KEY', data_get($data,'flutterwave.secret_key',null)),
                    'secretHash' => env('FLW_SECRET_HASH', data_get($data,'flutterwave.hash',null)),
                );
                Config::set('flutterwave', $config);
            }

            if (data_get($data,'razor_pay',null)) {
                $config = array(
                    'razor_key' => env('RAZOR_KEY', data_get($data,'razor_pay.api_key',null)),
                    'razor_secret' => env('RAZOR_SECRET', data_get($data,'razor_pay.api_secret',null))
                );
                Config::set('razor', $config);
            }

            if (data_get($data,'paytm',null)) {
                $PAYTM_STATUS_QUERY_NEW_URL='https://securegw-stage.paytm.in/merchant-status/getTxnStatus';
                $PAYTM_TXN_URL='https://securegw-stage.paytm.in/theia/processTransaction';
                if (data_get($data,'paytm.mode',null) == 'live') {
                    $PAYTM_STATUS_QUERY_NEW_URL='https://securegw.paytm.in/merchant-status/getTxnStatus';
                    $PAYTM_TXN_URL='https://securegw.paytm.in/theia/processTransaction';
                }
                $config = array(
                    'PAYTM_ENVIRONMENT' => ($mode=='live')?'PROD':'TEST',
                    'PAYTM_MERCHANT_KEY' => env('PAYTM_MERCHANT_KEY',data_get($data,'paytm.merchant_key',null)),
                    'PAYTM_MERCHANT_MID' => env('PAYTM_MERCHANT_MID', data_get($data,'paytm.merchant_id',null)),
                    'PAYTM_MERCHANT_WEBSITE' => env('PAYTM_MERCHANT_WEBSITE', data_get($data,'paytm.merchant_website_link',null)),
                    'PAYTM_REFUND_URL' => env('PAYTM_REFUND_URL', data_get($data,'paytm.paytm_refund_url',null)),
                    'PAYTM_STATUS_QUERY_URL' => env('PAYTM_STATUS_QUERY_URL', $PAYTM_STATUS_QUERY_NEW_URL),
                    'PAYTM_STATUS_QUERY_NEW_URL' => env('PAYTM_STATUS_QUERY_NEW_URL', $PAYTM_STATUS_QUERY_NEW_URL),
                    'PAYTM_TXN_URL' => env('PAYTM_TXN_URL', $PAYTM_TXN_URL),
                );

                Config::set('config_paytm', $config);
            }

            $odv = BusinessSetting::where(['key' => 'order_delivery_verification'])->first();
            if ($odv) {
                Config::set('order_delivery_verification', $odv->value);
            } else {
                Config::set('order_delivery_verification', 0);
            }

            $pagination = BusinessSetting::where(['key' => 'default_pagination'])->first();
            if ($pagination) {
                Config::set('default_pagination', $pagination->value);
            } else {
                Config::set('default_pagination', 25);
            }

            $round_up_to_digit = BusinessSetting::where(['key' => 'digit_after_decimal_point'])->first();
            if ($round_up_to_digit) {
                Config::set('round_up_to_digit', $round_up_to_digit->value);
            } else {
                Config::set('round_up_to_digit', 2);
            }

            $dm_maximum_orders = BusinessSetting::where(['key' => 'dm_maximum_orders'])->first();
            if ($dm_maximum_orders) {
                Config::set('dm_maximum_orders', $dm_maximum_orders->value);
            } else {
                Config::set('dm_maximum_orders', 1);
            }

            $order_confirmation_model = BusinessSetting::where(['key' => 'order_confirmation_model'])->first();
            if ($order_confirmation_model) {
                Config::set('order_confirmation_model', $order_confirmation_model->value);
            } else {
                Config::set('order_confirmation_model', 'deliveryman');
            }

            $timezone = BusinessSetting::where(['key' => 'timezone'])->first();
            if ($timezone) {
                Config::set('timezone', $timezone->value);
                date_default_timezone_set($timezone->value);
            }

            $timeformat = BusinessSetting::where(['key' => 'timeformat'])->first();
            if ($timeformat && $timeformat->value == '12') {
                Config::set('timeformat', 'h:i:a');
            }
            else{
                Config::set('timeformat', 'H:i');
            }

            $canceled_by_store = BusinessSetting::where(['key' => 'canceled_by_store'])->first();
            if ($canceled_by_store) {
                Config::set('canceled_by_store', (boolean)$canceled_by_store->value);
            }

            $canceled_by_deliveryman = BusinessSetting::where(['key' => 'canceled_by_deliveryman'])->first();
            if ($canceled_by_deliveryman) {
                Config::set('canceled_by_deliveryman', (boolean)$canceled_by_deliveryman->value);
            }

            $toggle_veg_non_veg = (boolean)BusinessSetting::where(['key' => 'toggle_veg_non_veg'])->first()->value;
            if($toggle_veg_non_veg)
            {
                Config::set('toggle_veg_non_veg', $toggle_veg_non_veg);
            }
            else{
                Config::set('toggle_veg_non_veg', false);
            }
      
        } catch (\Exception $ex) {

        }
    }
}
