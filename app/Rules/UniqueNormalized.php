<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Comprueba que no exista otro registro con el mismo valor.
 *
 * La comparación normaliza el texto antes de buscar: elimina los espacios
 * sobrantes, ignora mayúsculas/minúsculas y también las tildes, de modo que
 * "Noche estrellada", "noche  estrellada" y "NOCHE ESTRELLADA" se consideran
 * el mismo valor.
 */
class UniqueNormalized implements ValidationRule
{
    /**
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $scopes  Columnas que deben coincidir (por ejemplo, user_id).
     */
    public function __construct(
        private readonly string $model,
        private readonly string $column,
        private readonly ?Model $ignore = null,
        private readonly array $scopes = [],
        private readonly string $message = 'Ya existe un registro con ese mismo valor.',
        private readonly bool $ignoreEmpty = true,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $target = self::normalize($value);

        if ($this->ignoreEmpty && $target === '') {
            return;
        }

        $query = $this->model::query()->select([$this->column]);

        foreach ($this->scopes as $scope => $scopeValue) {
            if ($scopeValue === null) {
                $query->whereNull($scope);

                continue;
            }

            $query->where($scope, $scopeValue);
        }

        $ignoreKey = $this->ignore?->getKey();

        if ($ignoreKey !== null) {
            $query->whereKeyNot($ignoreKey);
        }

        foreach ($query->cursor() as $record) {
            if (self::normalize($record->{$this->column}) === $target) {
                $fail($this->message);

                return;
            }
        }
    }

    /** Normaliza un texto para poder compararlo con tolerancia a variantes. */
    public static function normalize(mixed $value): string
    {
        $text = Str::ascii((string) $value);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return mb_strtolower(trim($text));
    }
}
