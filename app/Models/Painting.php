<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Painting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'year',
        'technique',
        'emoji',
        'image_url',
        'price',
        'is_public',
        'status',
        'sold_at',
        'sold_to_user_id',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'decimal:2',
            'is_public' => 'boolean',
            'sold_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function exhibitions(): BelongsToMany
    {
        return $this->belongsToMany(Exhibition::class)->withTimestamps();
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function soldTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_to_user_id');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
