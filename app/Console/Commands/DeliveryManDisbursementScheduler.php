<?php

namespace App\Console\Commands;

use App\Models\BusinessSetting;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class DeliveryManDisbursementScheduler extends Command
{
    protected $signature = 'dm:disbursement';
    protected $description = 'DeliveryMan disbursement scheduling based on business settings';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        app('App\Http\Controllers\Admin\DeliveryManDisbursementController')->generate_disbursement();
        $this->info('DeliveryMan disbursement scheduler executed successfully.');
    }
}
