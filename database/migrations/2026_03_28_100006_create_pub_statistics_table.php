<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pub_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('shares')->default(0);
            $table->unsignedBigInteger('pdf_downloads')->default(0);
            $table->unsignedBigInteger('saves')->default(0);
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pub_statistics');
    }
};
