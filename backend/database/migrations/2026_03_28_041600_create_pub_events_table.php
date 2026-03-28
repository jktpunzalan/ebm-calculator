<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pub_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('publications')->cascadeOnDelete();
            $table->enum('event_type', ['view','share','pdf','save']);
            $table->text('user_agent')->nullable();
            $table->string('ip_hash')->nullable();
            $table->timestamp('occurred_at');
            $table->index(['publication_id','event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pub_events');
    }
};
