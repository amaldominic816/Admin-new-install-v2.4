<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSaleItem extends Model
{
    use HasFactory;

    protected $casts = [
        'flash_sale_id' => 'integer',
        'item_id' => 'integer',
        'status' => 'integer',
        'stock' => 'integer',
        'sold' => 'integer',
        'available_stock' => 'integer',
        'price' => 'double',
        'discounted_price' => 'double',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)->whereHas('item.store',function($query){
            $query->active();
        });
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_stock','>', 0);
    }
}
