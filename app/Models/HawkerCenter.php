<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HawkerCenter extends Model
{
    /** @use HasFactory<\Database\Factories\HawkerCenterFactory> */
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'photo_url',
    ];

    /**
     * @return HasMany<Closure, $this>
     */
    public function closures(): HasMany
    {
        return $this->hasMany(Closure::class);
    }
}
