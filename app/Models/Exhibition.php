<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\CarbonInterface;

/**
 * @property-read CarbonInterface|null $starts_at
 * @property-read CarbonInterface|null $ends_at
 */
class Exhibition extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paintings(): BelongsToMany
    {
        return $this->belongsToMany(Painting::class)->withTimestamps();
    }

    /**
     * Estado de la exposición según sus fechas: futura, activa o finalizada.
     */
    public function state(): string
    {
        $today = today();

        if ($this->starts_at && $today->lt($this->starts_at)) {
            return 'upcoming';
        }

        if ($this->ends_at && $today->gt($this->ends_at)) {
            return 'finished';
        }

        return 'active';
    }

    /** El usuario indicado puede editar o eliminar la exposición. */
    public function canManage(User $user): bool
    {
        return $user->isAdmin() || ($this->user_id !== null && $this->user_id === $user->id);
    }
}
