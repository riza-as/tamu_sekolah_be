<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subdistrict_visitors', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('subdistrict_code');
            $table->string('fullname');
            $table->string('address');
            $table->string('photo_visitor');
            $table->integer('visitor_type_id');
            $table->integer('objective_id');
            $table->string('information')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subdistrict_visitors');
    }
};
