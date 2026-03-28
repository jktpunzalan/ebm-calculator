<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('individualizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_id')->constrained('study_library')->cascadeOnDelete();
            $table->text('patient_notes')->nullable();
            $table->decimal('baseline_risk_rc', 8, 4);
            $table->decimal('relative_risk_rr', 8, 4);
            $table->decimal('risk_on_treatment_rt', 8, 4);
            $table->decimal('arr', 8, 4);
            $table->decimal('nnt_nnh', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('individualizations');
    }
};
