<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_library', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appraisal_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('study_type');
            $table->enum('validity_label', ['high', 'moderate', 'low'])->nullable();
            $table->string('key_result_label')->nullable();
            $table->string('key_result_value')->nullable();
            $table->boolean('is_starred')->default(false);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_library');
    }
};
