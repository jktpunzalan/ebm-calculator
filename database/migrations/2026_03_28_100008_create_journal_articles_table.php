<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('authors');
            $table->text('abstract')->nullable();
            $table->string('doi')->nullable();
            $table->tinyInteger('volume')->unsigned()->nullable();
            $table->tinyInteger('issue')->unsigned()->nullable();
            $table->smallInteger('year')->unsigned();
            $table->string('pdf_path')->nullable();
            $table->date('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_articles');
    }
};
