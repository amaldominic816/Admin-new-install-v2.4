<?php

namespace App\Models;

use App\Scopes\ZoneScope;
use App\Scopes\StoreScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TempProduct extends Model
{
    use HasFactory;
    protected $casts = [
        'tax' => 'float',
        'price' => 'float',
        'status' => 'integer',
        'discount' => 'float',
        'avg_rating' => 'float',
        'set_menu' => 'integer',
        'category_id' => 'integer',
        'store_id' => 'integer',
        'reviews_count' => 'integer',
        'recommended' => 'integer',
        'maximum_cart_quantity' => 'integer',
        'organic' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'veg'=>'integer',
        'images'=>'array',
        'module_id'=>'integer',
        'item_id'=>'integer',
        'is_rejected'=>'integer',
        'stock'=>'integer',
    ];
    protected $guarded = ['id'];

    public function scopeModule($query, $module_id)
    {
        return $query->where('module_id', $module_id);
    }
    public function scopeApproved($query)
    {
        return $query;
    }
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }
    public function item(){
        return $this->belongsTo(Item::class,'item_id');
    }
    public function common_condition(){
        return $this->belongsTo(CommonCondition::class,'common_condition_id');
    }

    public function pharmacy_item_details()
    {
        return $this->hasOne(PharmacyItemDetails::class, 'temp_product_id');
    }

    public function scopeType($query, $type)
    {
        if($type == 'veg')
        {
            return $query->where('veg', true);
        }
        else if($type == 'non_veg')
        {
            return $query->where('veg', false);
        }

        return $query;
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class,'unit_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class,'module_id');
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    protected static function booted()
    {
        if(auth('vendor')->check() || auth('vendor_employee')->check())
        {
            static::addGlobalScope(new StoreScope);
        }
        static::addGlobalScope(new ZoneScope);

    }
}
