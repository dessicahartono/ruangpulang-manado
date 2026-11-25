<?php

namespace App\Models;

use Database\Factories\ActivityMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityMedia extends Model
{
    /** @use HasFactory<ActivityMediaFactory> */
    use HasFactory;

    public const TYPE_IMAGE = 'image';

    public const TYPE_VIDEO = 'video';

    protected $fillable = ['activity_id', 'path', 'type', 'alt', 'focus', 'position'];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function isVideo(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }
}
