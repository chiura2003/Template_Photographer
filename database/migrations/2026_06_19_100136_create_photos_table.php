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
    Schema::create('photos', function (Blueprint $table) {
        $table->id();

        $table->foreignId('album_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->string('title')->nullable();

        $table->text('description')->nullable();

        $table->string('filename');

        $table->string('filepath');

        $table->string('mime_type')->nullable();

        $table->unsignedBigInteger('filesize')->nullable();

        $table->integer('width')->nullable();

        $table->integer('height')->nullable();

        $table->string('alt_text')->nullable();

        $table->integer('sort_order')->default(0);

        $table->boolean('is_published')->default(true);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
