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
        Schema::create('school_levels', function (Blueprint $table) {
            // Kolom id sebagai BIGINT Primary Key (Auto-increment)
            $table->bigIncrements('id');
            
            // Kolom name dengan tipe VARCHAR, panjang 50, dan nullable
            $table->string('name', 50)->nullable();
            
            // Menambahkan created_at dan updated_at (opsional, tapi standar Laravel)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_levels');
    }
};