<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class YoutubeVideo extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'youtube_videos';

    protected $fillable = [
        'title',
        'slug',
        'youtube_url',
        'youtube_video_id',
        'thumbnail',
        'description',
        'sort_order',
        'is_active',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'published_at' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * Get the user who created this video.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user who last updated this video.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}