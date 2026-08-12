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
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();

            // Identitas dasar kurir
            $table->string('code', 32)->nullable()->unique()->comment('Kode unik internal kurir, mis. KRR0001');
            $table->string('name', 120);

            // Kontak
            $table->string('phone', 32)->nullable();
            $table->string('email', 120)->nullable();
            $table->text('address')->nullable();

            // Data operasional kurir
            $table->string('vehicle_type', 32)->nullable()->comment('motor | mobil | van | truck | etc');
            $table->string('vehicle_plate', 32)->nullable();

            // Level 1-5 (1 = junior, 5 = senior)
            $table->unsignedTinyInteger('level')->default(1);

            // Status aktif kerja
            $table->string('status', 16)->default('active')->comment('active | inactive | suspended');

            // Tanggal mulai aktif jadi kurir (bisa beda dengan created_at)
            $table->timestamp('joined_at')->nullable();

            $table->timestamps();

            // Indexes untuk query yang sering dipakai
            $table->index('name');
            $table->index('level');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
