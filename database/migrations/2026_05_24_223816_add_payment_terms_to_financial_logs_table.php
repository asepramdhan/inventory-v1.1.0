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
        Schema::table('financial_logs', function (Blueprint $table) {
            // Menentukan metode pembayaran
            $table->enum('payment_method', ['cash', 'weekly_term', 'monthly_term'])->default('cash')->after('type');

            // Status pembayaran (Lunas atau Masih Berutang)
            $table->enum('payment_status', ['paid', 'unpaid'])->default('paid')->after('payment_method');

            // Kapan utang barang ini harus dibayar
            $table->date('due_date')->nullable()->after('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_logs', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'due_date']);
        });
    }
};
