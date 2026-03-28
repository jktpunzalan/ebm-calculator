<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('study_library', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appraisal_id')->nullable()->constrained('appraisals')->nullOnDelete();
            $table->string('title');
            $table->string('study_type');
            $table->string('validity_label');
            $table->string('key_result_label');
            $table->string('key_result_value');
            $table->boolean('is_starred')->default(false);
            $table->timestamp('saved_at');
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_library');
    }
};
