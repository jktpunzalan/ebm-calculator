<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('authors');
            $table->enum('type', [
                'therapy', 'diagnosis', 'harm', 'prognosis',
                'systematic_review', 'economic_evaluation', 'cpg', 'screening', 'other',
            ]);
            $table->json('audience_tags')->nullable();
            $table->string('html_file_path');
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
