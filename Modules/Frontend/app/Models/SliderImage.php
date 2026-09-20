<?php

namespace Modules\Frontend\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SliderImage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'slider_images';

    protected $fillable = [
        'image',
        'sort_order',
        'is_active',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Full public URL of the stored image (available in JSON responses).
     */
    protected $appends = ['image_url'];

    /**
     * Get the user who created this slider image.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user who last updated this slider image.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Get the full public URL of the stored slider image.
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/'.$this->image);
    }
}
