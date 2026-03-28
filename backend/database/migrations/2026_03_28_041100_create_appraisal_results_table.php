<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appraisal_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->constrained('appraisals')->cascadeOnDelete();
            $table->string('metric_key');
            $table->string('metric_value');
            $table->timestamps();
            $table->index('appraisal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisal_results');
    }
};
