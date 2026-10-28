<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'stripe_token',
        'is_admin',
        'is_vendor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_vendor' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isVendor(): bool
    {
        return (bool) $this->is_vendor || $this->isAdmin();
    }

    public function canSell(): bool
    {
        return (bool) $this->is_vendor || $this->isAdmin();
    }

    public function paintings(): HasMany
    {
        return $this->hasMany(Painting::class);
    }

    public function favoritePaintings(): BelongsToMany
    {
        return $this->belongsToMany(Painting::class, 'favorites')->withTimestamps();
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function publicationComments(): HasMany
    {
        return $this->hasMany(PublicationComment::class);
    }

    public function inquiriesSent(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'buyer_id');
    }

    public function inquiriesReceived(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'seller_id');
    }

    public function soldPaintings(): HasMany
    {
        return $this->hasMany(Painting::class, 'sold_to_user_id');
    }
}
