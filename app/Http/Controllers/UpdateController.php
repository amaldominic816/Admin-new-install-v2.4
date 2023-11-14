<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\DataSetting;

ini_set('max_execution_time', 180);

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Traits\ActivationClass;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class UpdateController extends Controller
{
    use ActivationClass;

    public function update_software_index()
    {
        return view('update.update-software');
    }

    public function update_software(Request $request)
    {
        if (env('SOFTWARE_VERSION') == '1.0') {
            $filesystem = new Filesystem;
            $filesystem->cleanDirectory('database/migrations');
        }

        Helpers::setEnvironmentValue('BUYER_USERNAME', $request['username']);
        Helpers::setEnvironmentValue('PURCHASE_CODE', $request['purchase_key']);
        Helpers::setEnvironmentValue('APP_MODE', 'live');
        Helpers::setEnvironmentValue('SOFTWARE_VERSION', '2.4.0');
        Helpers::setEnvironmentValue('REACT_APP_KEY', '45370351');
        Helpers::setEnvironmentValue('APP_NAME', '6amMart' . time());

        // $data = Helpers::requestSender();
        // if (!$data['active']) {
        if (!$this->actch()) {
            return redirect(base64_decode('aHR0cHM6Ly82YW10ZWNoLmNvbS9zb2Z0d2FyZS1hY3RpdmF0aW9u'));
        }

        Artisan::call('migrate', ['--force' => true]);
        $previousRouteServiceProvier = base_path('app/Providers/RouteServiceProvider.php');
        $newRouteServiceProvier = base_path('app/Providers/RouteServiceProvider.txt');
        copy($newRouteServiceProvier, $previousRouteServiceProvier);
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Helpers::insert_business_settings_key("mobile_app_section_heading" , "Download the App for Enjoy Best Restaurant Test");
        Helpers::insert_business_settings_key("mobile_app_section_text" , "Default Text Mobile App Section");
        Helpers::insert_business_settings_key("feature_section_description" , "Feature section description");
        Helpers::insert_business_settings_key("Feature section description", json_encode([
            "app_url_android_status" => "0",
            "app_url_android" => "https://play.google.com",
            "app_url_ios_status" => "0",
            "app_url_ios" => "https://www.apple.com/app-store",
            "web_app_url_status" => "0",
            "web_app_url" => "https://6ammart-web.6amtech.com/"
        ]));

        //version 1.5.0
        Helpers::insert_business_settings_key("wallet_status" , "0");
        Helpers::insert_business_settings_key("loyalty_point_status" , "0");
        Helpers::insert_business_settings_key("ref_earning_status" , "0");
        Helpers::insert_business_settings_key("wallet_add_refund" , "0");
        Helpers::insert_business_settings_key("loyalty_point_exchange_rate" , "0");
        Helpers::insert_business_settings_key("ref_earning_exchange_rate" , "0");
        Helpers::insert_business_settings_key("loyalty_point_item_purchase_point" , "0");
        Helpers::insert_business_settings_key("loyalty_point_minimum_point" , "0");
        Helpers::insert_business_settings_key("dm_tips_status" , "0");
        Helpers::insert_business_settings_key('tax_included', '0');
        Helpers::insert_business_settings_key('refund_active_status', '1');
        Helpers::insert_business_settings_key('social_login','[{"login_medium":"google","client_id":"","client_secret":"","status":"0"},{"login_medium":"facebook","client_id":"","client_secret":"","status":""}]');
        Helpers::insert_business_settings_key('system_language','[{"id":1,"direction":"ltr","code":"en","status":1,"default":true}]');
        Helpers::insert_business_settings_key('language','["en"]');
        //version 2.0.1
        // Helpers::insert_business_settings_key('otp_interval_time', '30');
        // Helpers::insert_business_settings_key('max_otp_hit', '5');

        Helpers::insert_business_settings_key("home_delivery_status" , "1");
        Helpers::insert_business_settings_key("takeaway_status" , "1");

        $data_settings = file_get_contents('database/partial/data_settings.sql');
        $email_tempaltes = file_get_contents('database/partial/email_tempaltes.sql');

        if( DataSetting::count() < 1){
            DB::statement($data_settings);
        }
        if( EmailTemplate::count() < 1){
            DB::statement($email_tempaltes);
        }

        //version 2.2.0
        Helpers::insert_data_settings_key('admin_login_url', 'login_admin' ,'admin');
        Helpers::insert_data_settings_key('admin_employee_login_url', 'login_admin_employee' ,'admin-employee');
        Helpers::insert_data_settings_key('store_login_url', 'login_store' ,'store');
        Helpers::insert_data_settings_key('store_employee_login_url', 'login_store_employee' ,'store-employee');

        try {
            if (!Schema::hasTable('addon_settings')) {
                $sql = file_get_contents('database/partial/addon_settings.sql');
                DB::unprepared($sql);
                $this->set_data();
                $this->set_sms_data();

            }

            if (env('SOFTWARE_VERSION') == '2.4') {

                $this->set_sms_data();
                $this->update_table();
            }


                if (!Schema::hasTable('payment_requests')) {
                    $sql = file_get_contents('database/partial/payment_requests.sql');
                DB::unprepared($sql);
                }


                $storesToUpdate = Store::whereNull('slug')->get(['id','name','slug']);
                foreach ($storesToUpdate as $store) {
                    $slug = Str::slug($store->name);
                    $store->slug = $store->slug? $store->slug :"{$slug}{$store->id}";
                    $store->save();
                }

            } catch (\Exception $exception) {
            Toastr::error('Database import failed! try again');
            return back();
        }

        $landing = BusinessSetting::where('key', 'landing_page')->exists();
        if(!$landing){
            Helpers::insert_business_settings_key('landing_page','1');
            Helpers::insert_business_settings_key('landing_integration_type','none');
        }

        $data = DataSetting::where('type', 'login_admin')->pluck('value')->first();
        return redirect('/login/'.$data);
    }

    private function set_data(){
        try{
            $gateway= ['ssl_commerz_payment',
            'razor_pay',
            'paypal',
            'stripe',
            'senang_pay',
            'paystack',
            'flutterwave',
            'mercadopago',
            'paymob_accept',
            'liqpay',
            'paytm',
            'bkash',
            'paytabs' ];

            $data= BusinessSetting::whereIn('key',$gateway)->pluck('value','key')->toArray();


            foreach($data as $key => $value){

            $gateway=$key;
            if($key == 'ssl_commerz_payment' ){
                $gateway='ssl_commerz';
            }

            $decoded_value= json_decode($value , true);
            $data= ['gateway' => $gateway ,
                'mode' =>  isset($decoded_value['status']) == 1  ?  'live': 'test'
                ];

                if ($gateway == 'ssl_commerz') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'store_id' => $decoded_value['store_id'],
                        'store_password' => $decoded_value['store_password'],
                    ];
                } elseif ($gateway == 'paypal') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'client_id' => $decoded_value['paypal_client_id'],
                        'client_secret' => $decoded_value['paypal_secret'],
                    ];
                } elseif ($gateway == 'stripe') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'api_key' => $decoded_value['api_key'],
                        'published_key' => $decoded_value['published_key'],
                    ];
                } elseif ($gateway == 'razor_pay') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'api_key' => $decoded_value['razor_key'],
                        'api_secret' => $decoded_value['razor_secret'],
                    ];
                } elseif ($gateway == 'senang_pay') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'callback_url' => null,
                        'secret_key' => $decoded_value['secret_key'],
                        'merchant_id' => $decoded_value['merchant_id'],
                    ];
                } elseif ($gateway == 'paytabs') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'profile_id' => $decoded_value['profile_id'],
                        'server_key' => $decoded_value['server_key'],
                        'base_url' => $decoded_value['base_url'],
                    ];
                } elseif ($gateway == 'paystack') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'callback_url' => $decoded_value['paymentUrl'],
                        'public_key' => $decoded_value['publicKey'],
                        'secret_key' => $decoded_value['secretKey'],
                        'merchant_email' => $decoded_value['merchantEmail'],
                    ];
                } elseif ($gateway == 'paymob_accept') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'callback_url' => null,
                        'api_key' => $decoded_value['api_key'],
                        'iframe_id' => $decoded_value['iframe_id'],
                        'integration_id' => $decoded_value['integration_id'],
                        'hmac' => $decoded_value['hmac'],
                    ];
                } elseif ($gateway == 'mercadopago') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'access_token' => $decoded_value['access_token'],
                        'public_key' => $decoded_value['public_key'],
                    ];
                } elseif ($gateway == 'liqpay') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'private_key' => $decoded_value['public_key'],
                        'public_key' => $decoded_value['private_key'],
                    ];
                } elseif ($gateway == 'flutterwave') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'secret_key' => $decoded_value['secret_key'],
                        'public_key' => $decoded_value['public_key'],
                        'hash' => $decoded_value['hash'],
                    ];
                } elseif ($gateway == 'paytm') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'merchant_key' => $decoded_value['paytm_merchant_key'],
                        'merchant_id' => $decoded_value['paytm_merchant_mid'],
                        'merchant_website_link' => $decoded_value['paytm_merchant_website'],
                    ];
                } elseif ($gateway == 'bkash') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'app_key' => $decoded_value['api_key'],
                        'app_secret' => $decoded_value['api_secret'],
                        'username' => $decoded_value['username'],
                        'password' => $decoded_value['password'],
                    ];
                }

            $credentials= json_encode(array_merge($data, $additional_data));

            $payment_additional_data=['gateway_title' => ucfirst(str_replace('_',' ',$gateway)),
                                    'gateway_image' => null];

            DB::table('addon_settings')->updateOrInsert(['key_name' => $gateway, 'settings_type' => 'payment_config'], [
            'key_name' => $gateway,
            'live_values' => $credentials,
            'test_values' => $credentials,
            'settings_type' => 'payment_config',
            'mode' => isset($decoded_value['status']) == 1  ?  'live': 'test',
            'is_active' => isset($decoded_value['status']) == 1  ?  1: 0 ,
            'additional_data' => json_encode($payment_additional_data),
            ]);
            }
        } catch (\Exception $exception) {
            Toastr::error('Database import failed! try again');
            return true;
            }
        return true;
    }

    private function set_sms_data(){
        try{
            $sms_gateway= ['twilio_sms',
            'nexmo_sms',
            'msg91_sms',
            '2factor_sms'];

            $data= BusinessSetting::whereIn('key',$sms_gateway)->pluck('value','key')->toArray();
            foreach($data as $key => $value){
                    $decoded_value= json_decode($value , true);

                    if ($key == 'twilio_sms') {
                        $sms_gateway='twilio';
                        $additional_data = [
                            'status' => data_get($decoded_value,'status',null),
                            'sid' => data_get($decoded_value,'sid',null),
                            'messaging_service_sid' =>  data_get($decoded_value,'messaging_service_id',null),
                            'token' => data_get($decoded_value,'token',null),
                            'from' =>data_get($decoded_value,'from',null),
                            'otp_template' => data_get($decoded_value,'otp_template',null),
                        ];
                    } elseif ($key == 'nexmo_sms') {
                        $sms_gateway='nexmo';
                        $additional_data = [
                            'status' => data_get($decoded_value,'status',null),
                            'api_key' => data_get($decoded_value,'api_key',null),
                            'api_secret' =>  data_get($decoded_value,'api_secret',null),
                            'token' => data_get($decoded_value,'token',null),
                            'from' =>  data_get($decoded_value,'from',null),
                            'otp_template' =>  data_get($decoded_value,'otp_template',null),
                        ];
                    } elseif ($key == '2factor_sms') {
                        $sms_gateway='2factor';
                        $additional_data = [
                            'status' => data_get($decoded_value,'status',null),
                            'api_key' => data_get($decoded_value,'api_key',null),
                        ];
                    } elseif ($key == 'msg91_sms') {
                        $sms_gateway='msg91';
                        $additional_data = [
                            'status' => data_get($decoded_value,'status',null),
                            'template_id' =>  data_get($decoded_value,'template_id',null),
                            'auth_key' =>  data_get($decoded_value,'authkey',null),
                        ];
                    }
                    $data= ['gateway' => $sms_gateway ,
                    'mode' =>  isset($decoded_value['status']) == 1  ?  'live': 'test'
                ];
                    $credentials= json_encode(array_merge($data, $additional_data));

                    DB::table('addon_settings')->updateOrInsert(['key_name' => $sms_gateway, 'settings_type' => 'sms_config'], [
                        'key_name' => $sms_gateway,
                        'live_values' => $credentials,
                        'test_values' => $credentials,
                        'settings_type' => 'sms_config',
                        'mode' => isset($decoded_value['status']) == 1  ?  'live': 'test',
                        'is_active' => isset($decoded_value['status']) == 1  ?  1: 0 ,
                    ]);
                }
            } catch (\Exception $exception) {
                Toastr::error('Database import failed! try again');
                return true;
                }
            return true;
       }


private function update_table(){


    $gateways = [
        'viva_wallet' => [
            'status' => 0,
            'client_id' => null,
            'client_secret' => null,
            'source_code' => null,
        ],
        'paradox' => [
            'status' => 0,
            'api_key' => null,
            'sender_id' => null,
        ],
    ];


    foreach ($gateways as $key => $conf) {
        $data = [
            'gateway' => $key,
            'mode' => 'test',
        ];
        $credentials = json_encode(array_merge($data, $conf));

        $settings = $key == 'paradox' ? 'sms_config' : 'payment_config';

        DB::table('addon_settings')->updateOrInsert(['key_name' => $key, 'settings_type' => $settings], [
            'key_name' => $key,
            'live_values' => $credentials,
            'test_values' => $credentials,
            'settings_type' => $settings,
            'mode' => 'test',
            'is_active' => 0,
        ]);
    }
}

}
