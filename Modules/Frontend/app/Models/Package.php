<?php

namespace Modules\Frontend\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'packages';

    protected $fillable = [
        'title',
        'slug',
        'duration_days',
        'price',
        'price_label',
        'short_description',
        'description',
        'thumbnail',
        'package_type',
        'is_featured',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Full public URL of the stored thumbnail (available in JSON responses).
     */
    protected $appends = ['thumbnail_url'];

    /**
     * Get the user who created this package.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user who last updated this package.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Get the user who deleted this package.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }

    /**
     * Get the features (inclusions) of this package.
     */
    public function features(): HasMany
    {
        return $this->hasMany(PackageFeature::class, 'package_id', 'id')
            ->orderBy('sort_order');
    }

    /**
     * Get the full public URL of the stored thumbnail.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail
            ? asset('storage/'.$this->thumbnail)
            : null;
    }
}
