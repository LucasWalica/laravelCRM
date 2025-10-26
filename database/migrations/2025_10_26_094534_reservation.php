<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_user_client')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fk_business_service')->constrained('business_services')->cascadeOnDelete();
            $table->dateTime('time_start');
            $table->dateTime('estimated_time_end')->nullable();
            $table->enum('status', ['pending','confirmed','cancelled','completed','no_show'])->default('pending');
            $table->integer('aforo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
