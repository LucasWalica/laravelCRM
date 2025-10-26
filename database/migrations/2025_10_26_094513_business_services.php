<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_business')->constrained('businesses')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('time_estimation')->nullable(); // en minutos
            $table->integer('aforo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_services');
    }
};
