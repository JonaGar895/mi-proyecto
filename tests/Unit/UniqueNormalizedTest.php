<?php

namespace Tests\Unit;

use App\Rules\UniqueNormalized;
use PHPUnit\Framework\TestCase;

class UniqueNormalizedTest extends TestCase
{
    public function test_values_are_normalized_before_comparing_them(): void
    {
        $this->assertSame(
            'noche estrellada',
            UniqueNormalized::normalize('  NOCHE   Estrellada '),
        );

        $this->assertSame(
            'maria artista',
            UniqueNormalized::normalize('María Artista'),
        );

        $this->assertSame('', UniqueNormalized::normalize(null));
    }
}
