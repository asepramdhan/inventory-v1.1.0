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
        Schema::table('transactions', function (Blueprint $table) {
            // Nomor Pesanan (bisa unik per toko/platform)
            $table->string('order_number')->nullable()->after('store_id');

            // Status Pesanan dengan default 'diproses'
            $table->string('status')->default('diproses')->after('total_price');

            // Catatan tambahan (opsional tapi sangat berguna)
            $table->text('notes')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'status', 'notes']);
        });
    }
};
