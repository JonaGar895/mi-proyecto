<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    public const TYPE_SUPER_ADMIN = 'super_admin';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class);
    }

    /**
     * Obtiene (o crea) la cuenta separada del super administrador.
     *
     * Esta cuenta es distinta del saldo personal del usuario (users.balance):
     * aquí se acumula el dinero de las obras vendidas.
     */
    public static function superAdmin(): self
    {
        $superAdmin = User::query()
            ->where('id', 1)
            ->where('is_admin', true)
            ->first()
            ?? User::query()->where('is_admin', true)->orderBy('id')->first();

        $account = static::firstOrCreate(
            ['type' => self::TYPE_SUPER_ADMIN],
            [
                'user_id' => $superAdmin?->id,
                'name' => 'Cuenta del Super Administrador',
                'balance' => 0,
            ],
        );

        // Si la cuenta se creó antes de que existiera el super administrador,
        // la vinculamos en cuanto esté disponible.
        if ($account->user_id === null && $superAdmin !== null) {
            $account->update(['user_id' => $superAdmin->id]);
        }

        return $account;
    }
}
