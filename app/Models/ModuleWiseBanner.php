<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ModuleWiseBanner extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => 'integer',
    ];

    protected $fillable = ['module_id', 'key', 'type', 'value'];

    public function scopeModule($query, $module_id)
    {
        return $query->where('module_id', $module_id);
    }

    public function module()
    {
        return $this->belongsTo(Module::class,'module_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }


    public function getValueAttribute($value){
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == $this->key) {
                    return $translation['value'];
                }
            }
        }
        return $value;
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
