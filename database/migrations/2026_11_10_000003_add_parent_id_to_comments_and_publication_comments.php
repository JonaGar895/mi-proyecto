<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite responder a un comentario existente manteniendo un único
     * nivel de profundidad: las respuestas no pueden tener respuestas.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('painting_id')
                ->constrained('comments')
                ->cascadeOnDelete();
        });

        Schema::table('publication_comments', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('publication_id')
                ->constrained('publication_comments')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });

        Schema::table('publication_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
