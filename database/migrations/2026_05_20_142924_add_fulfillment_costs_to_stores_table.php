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
        Schema::table('stores', function (Blueprint $table) {
            // Biaya tetap per pesanan (misal: 1250)
            $table->decimal('processing_fee', 10, 2)->default(1250)->after('admin_fee');

            // Cadangan biaya lain (persentase atau nominal)
            $table->decimal('extra_fee', 10, 2)->default(0)->after('processing_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['processing_fee', 'extra_fee']);
        });
    }
};
