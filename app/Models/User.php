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
        'balance',
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
            'balance' => 'decimal:2',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->id === 1 && (bool) $this->is_admin;
    }

    public function isAdmin(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return (bool) $this->is_admin;
    }

    public function canApproveSales(): bool
    {
        return $this->isSuperAdmin() || (bool) $this->is_admin;
    }

    public function isVendor(): bool
    {
        return (bool) $this->is_vendor || $this->isAdmin();
    }

    public function canSell(): bool
    {
        return (bool) $this->is_vendor || $this->isAdmin();
    }

    public function canAfford(Painting $painting): bool
    {
        if (! $painting->price) {
            return true;
        }

        return $this->balance >= $painting->price;
    }

    public function canBuy(Painting $painting): array
    {
        if ($painting->user_id === $this->id) {
            return ['can_buy' => false, 'reason' => 'No puedes comprar tu propia obra.'];
        }

        if ($painting->status === 'sold') {
            return ['can_buy' => false, 'reason' => 'Esta obra ya ha sido vendida.'];
        }

        if (! $painting->is_public && ! $this->isAdmin()) {
            return ['can_buy' => false, 'reason' => 'Esta obra no está disponible para la compra.'];
        }

        if (! $this->canAfford($painting)) {
            return ['can_buy' => false, 'reason' => 'Saldo insuficiente para comprar esta obra.'];
        }

        if (Inquiry::where('painting_id', $painting->id)->where('buyer_id', $this->id)->exists()) {
            return ['can_buy' => false, 'reason' => 'Ya has enviado una solicitud de compra para esta obra.'];
        }

        return ['can_buy' => true, 'reason' => null];
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
