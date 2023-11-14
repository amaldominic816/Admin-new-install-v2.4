<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FlashSale extends Model
{
    use HasFactory;
    protected $casts = [
        'is_publish' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getTitleAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'title') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function products()
    {
        return $this->hasMany(FlashSaleItem::class,'flash_sale_id','id');
    }

    public function activeProducts()
    {
        return $this->hasMany(FlashSaleItem::class,'flash_sale_id','id')->where('status',1)->whereHas('item.store',function($query){
            $query->active();
        });
    }

    public function getStartTimeAttribute($value)
    {
        return $value?date('H:i',strtotime($value)):$value;
    }

    public function getEndTimeAttribute($value)
    {
        return $value?date('H:i',strtotime($value)):$value;
    }

    public function scopeActive($query)
    {
        return $query->where('is_publish', 1);
    }
    public function scopeModule($query, $module_id)
    {
        return $query->where('module_id', $module_id);
    }
    public function scopeRunning($query)
    {
        return $query->where('start_date','<=',date('Y-m-d H:i:s'))->where('end_date','>=',date('Y-m-d H:i:s'));
    }


    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', app()->getLocale());
            }]);
        });
    }
}
