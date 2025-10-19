<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('name', 50);
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('release_year')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->integer('duration')->nullable();
            $table->string('cover_image', 255)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('full_path', 255)->nullable();
            $table->string('status', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
