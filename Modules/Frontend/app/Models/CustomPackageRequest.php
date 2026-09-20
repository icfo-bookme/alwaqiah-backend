<?php

namespace Modules\Frontend\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomPackageRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'custom_package_requests';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_QUOTED = 'quoted';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'airline_id',
        'name',
        'phone',
        'email',
        'travel_date',
        'makkah_hotel',
        'madinah_hotel',
        'preferred_transport',
        'adults',
        'children',
        'male',
        'female',
        'food_preference',
        'additional_note',
        'status',
        'quoted_price',
        'admin_note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'quoted_price' => 'decimal:2',
    ];

    public static function statuses(): array
    {
        return [
            'pending' => 'Pending',
            'contacted' => 'Contacted',
            'processing' => 'Processing',
            'quoted' => 'Quoted',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
        ];
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class, 'airline_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
