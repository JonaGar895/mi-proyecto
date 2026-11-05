<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paintings', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('image_url');
            $table->string('image_hash', 64)->nullable()->unique()->after('image_path');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('painting_id');
            $table->string('image_hash', 64)->nullable()->unique()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('paintings', function (Blueprint $table) {
            $table->dropUnique(['image_hash']);
            $table->dropColumn(['image_path', 'image_hash']);
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->dropUnique(['image_hash']);
            $table->dropColumn(['image_path', 'image_hash']);
        });
    }
};
