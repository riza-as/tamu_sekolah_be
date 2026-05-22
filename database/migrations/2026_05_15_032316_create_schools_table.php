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
            $table->bigIncrements('id');
            $table->string('name', 40);
            $table->text('address')->nullable();
            $table->bigInteger('village_code')->nullable()->index();
            $table->unsignedBigInteger('level_id')->nullable()->index();
            $table->unsignedBigInteger('status_id')->nullable()->index();
            $table->string('school_code', 20)->nullable();
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
