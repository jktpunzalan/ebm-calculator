<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('study_type');
            $table->string('title');
            $table->string('authors')->nullable();
            $table->smallInteger('year')->nullable();
            $table->json('checklist');
            $table->text('notes')->nullable();
            $table->tinyInteger('validity_score')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisals');
    }
};
