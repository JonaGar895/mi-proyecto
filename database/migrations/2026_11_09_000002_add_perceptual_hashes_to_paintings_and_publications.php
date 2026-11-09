<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paintings', function (Blueprint $table) {
            $table->string('image_phash', 16)->nullable()->after('image_hash');
            $table->string('image_dhash', 16)->nullable()->after('image_phash');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->string('image_phash', 16)->nullable()->after('image_hash');
            $table->string('image_dhash', 16)->nullable()->after('image_phash');
        });
    }

    public function down(): void
    {
        Schema::table('paintings', function (Blueprint $table) {
            $table->dropColumn(['image_phash', 'image_dhash']);
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumn(['image_phash', 'image_dhash']);
        });
    }
};
