<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_owner')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->json('coordinates')->nullable();
            $table->string('address')->nullable();
            $table->string('type')->nullable();
            $table->string('logo')->nullable();
            $table->json('images')->nullable();
            $table->text('description')->nullable();
            $table->json('schedule')->nullable();
            $table->integer('aforo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};

