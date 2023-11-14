<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflinePaymentMethod extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'status'=>'integer',
        'method_fields' => 'array',
        'method_informations' => 'array',
    ];
}
