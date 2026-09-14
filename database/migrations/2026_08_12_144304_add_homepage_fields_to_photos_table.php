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
        Schema::table('photos', function (Blueprint $table) {
            $table->boolean('is_homepage')
                ->default(false)
                ->after('is_published');

            $table->unsignedInteger('homepage_order')
                ->default(0)
                ->after('is_homepage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn([
                'is_homepage',
                'homepage_order',
            ]);
        });
    }
};