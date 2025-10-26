<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fk_business')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('fk_user')->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('stars')->default(5); // 1-5
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_feedback');
    }
};

