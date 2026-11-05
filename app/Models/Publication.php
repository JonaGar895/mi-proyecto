<?php

namespace App\Models;

use App\Services\ImageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'painting_id',
        'image_path',
        'image_hash',
        'title',
        'summary',
        'content',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Publication $publication): void {
            if (! $publication->isDirty('image_path')) {
                return;
            }

            $previous = $publication->getOriginal('image_path');

            if ($previous && $previous !== $publication->image_path) {
                ImageService::delete($previous);
            }
        });

        static::deleting(function (Publication $publication): void {
            ImageService::delete($publication->image_path);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function painting(): BelongsTo
    {
        return $this->belongsTo(Painting::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PublicationComment::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
