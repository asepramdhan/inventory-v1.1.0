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
        Schema::create('capital_mutations', function (Blueprint $table) {
            $table->id();
            // Relasi ke User pemilik data
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Relasi ke Toko (nullable, karena bayar produsen atau tarik saldo bank umum mungkin tidak terikat 1 toko khusus)
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();

            // Jenis Mutasi: withdrawal (Penarikan Saldo), supplier_payment (Bayar Produsen)
            $table->string('type');

            // Nominal Uang
            $table->decimal('amount', 15, 2);

            // Tanggal eksekusi mutasi
            $table->date('date');

            // Asal uang & Tujuan uang (Misal: dari "Shopee" ke "BCA", atau dari "Kas Inti" ke "Produsen A")
            $table->string('source')->nullable();
            $table->string('destination')->nullable();

            // Status Pembayaran (Khusus bayar produsen yang bisa tempo/utang dulu)
            // Nilai: paid (Lunas), unpaid (Belum Lunas/Utang)
            $table->string('payment_status')->default('paid');
            $table->date('due_date')->nullable(); // Jatuh tempo jika unpaid

            // Catatan Tambahan (Misal: "Bayar Nota No. 99" atau "Tarik saldo MeowMeal.id")
            $table->text('description')->nullable();

            // Nomor Referensi / Bukti Transfer jika ada
            $table->string('reference_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capital_mutations');
    }
};
