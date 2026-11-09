<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refuerza a nivel de base de datos los registros que no deben repetirse:
     * una exposición no puede tener el mismo título y una solicitud de compra
     * no puede generar dos ingresos en la cuenta del super administrador.
     */
    public function up(): void
    {
        Schema::table('exhibitions', function (Blueprint $table) {
            $table->unique('title');
        });

        Schema::table('account_transactions', function (Blueprint $table) {
            $table->unique('inquiry_id');
        });
    }

    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropUnique(['inquiry_id']);
        });

        Schema::table('exhibitions', function (Blueprint $table) {
            $table->dropUnique(['title']);
        });
    }
};
