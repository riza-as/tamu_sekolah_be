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
        Schema::create('schools', function (Blueprint $table) {
            // Menggunakan bigIncrements karena di gambar tipe datanya BIGINT dan Primary Key
            $table->bigIncrements('id'); 
            
            // Kolom name dengan length 255
            $table->string('name', 255);
            
            // Kolom address menggunakan tipe TEXT dan nullable (sesuai centang Allow Null)
            $table->text('address')->nullable();
            
            // Kolom foreign key/relasi menggunakan BIGINT
            $table->unsignedBigInteger('village_code')->nullable();
            $table->unsignedBigInteger('level_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedBigInteger('school_code')->nullable();
            
            // Kolom created_at dan updated_at menggunakan TIMESTAMP
            // Di Laravel, method timestamps() otomatis membuat keduanya dan nullable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};