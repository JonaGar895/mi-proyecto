<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('paintings', function (Blueprint $table) {
            $table->enum('status', ['available', 'reserved', 'sold'])->default('available')->after('is_public');
            $table->timestamp('sold_at')->nullable()->after('status');
            $table->foreignId('sold_to_user_id')->nullable()->constrained('users')->nullOnDelete()->after('sold_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paintings', function (Blueprint $table) {
            $table->dropForeign(['sold_to_user_id']);
            $table->dropColumn(['status', 'sold_at', 'sold_to_user_id']);
        });
    }
};
