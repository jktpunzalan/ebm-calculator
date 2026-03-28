<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pub_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $table->enum('event_type', ['view', 'share', 'pdf_download', 'save']);
            $table->string('ip_hash', 64);
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('publication_id');
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pub_events');
    }
};
