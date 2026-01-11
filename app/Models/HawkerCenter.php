<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HawkerCenter extends Model
{
    /** @use HasFactory<\Database\Factories\HawkerCenterFactory> */
    use HasFactory;

    /**
     * @return HasMany<Closure, $this>
     */
    public function closures(): HasMany
    {
        return $this->hasMany(Closure::class);
    }
}
