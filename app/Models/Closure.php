<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Closure extends Model
{
    /** @use HasFactory<\Database\Factories\ClosureFactory> */
    use HasFactory;

    protected $fillable = [
        'hawker_center_id',
        'type',
        'start_date',
        'end_date',
        'remarks',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<HawkerCenter, $this>
     */
    public function hawkerCenter(): BelongsTo
    {
        return $this->belongsTo(HawkerCenter::class);
    }

    /**
     * @param  Builder<Closure>  $query
     * @return Builder<Closure>
     */
    public function scopeUpcoming(Builder $query, int $days = 30): Builder
    {
        $today = now()->startOfDay();
        $endDate = now()->addDays($days)->endOfDay();

        return $query->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $today);
    }
}
