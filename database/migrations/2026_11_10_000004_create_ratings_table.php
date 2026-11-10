<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Valoraciones de obras: cada usuario puede puntuar una obra una única
     * vez con una nota del 1 al 5.
     */
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('painting_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->comment('Valoración de 1 a 5');
            $table->timestamps();

            $table->unique(['user_id', 'painting_id']);
            $table->index(['painting_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
