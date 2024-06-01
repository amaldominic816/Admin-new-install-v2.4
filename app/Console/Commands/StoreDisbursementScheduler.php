<?php

namespace App\Console\Commands;

use App\Models\BusinessSetting;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class StoreDisbursementScheduler extends Command
{
    protected $signature = 'store:disbursement';
    protected $description = 'Store disbursement scheduling based on business settings';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        app('App\Http\Controllers\Admin\StoreDisbursementController')->generate_disbursement();
        $this->info('Store disbursement scheduler executed successfully.');
    }
}
