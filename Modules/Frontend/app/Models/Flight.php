<?php

namespace Modules\Frontend\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flight extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'flights';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'airline_name',
        'airline_logo',
        'flight_number',
        'departure_airport',
        'arrival_airport',
        'departure_at',
        'return_departure_airport',
        'return_arrival_airport',
        'return_at',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'departure_at' => 'datetime',
        'return_at'    => 'datetime',
        'sort_order'   => 'integer',
        'is_active'    => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * Full public URL of the stored airline logo (available in JSON responses).
     */
    protected $appends = ['airline_logo_url'];

    /**
     * Get the user who created this flight.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user who last updated this flight.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Get the full public URL of the stored airline logo.
     */
    public function getAirlineLogoUrlAttribute(): ?string
    {
        return $this->airline_logo
            ? asset('storage/' . $this->airline_logo)
            : null;
    }
}
