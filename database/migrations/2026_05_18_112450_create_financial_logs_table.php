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
        Schema::create('financial_logs', function (Blueprint $table) {
            $table->id();

            // Relasi ke User & Toko
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();

            // Inti Transaksi
            $table->date('date');
            $table->enum('type', ['income', 'expense'])->default('expense');

            // Kategori (Bisa untuk 'Ads', 'Stock', 'Operational', 'Shipping', dll)
            $table->string('category')->index();

            // Platform (Khusus AdSpend: Shopee, TikTok, FB Ads, Google Ads)
            $table->string('platform')->nullable()->index();

            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();

            // Opsional: Untuk tracking iklan (Campaign Name / ID)
            $table->string('reference_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_logs');
    }
};
