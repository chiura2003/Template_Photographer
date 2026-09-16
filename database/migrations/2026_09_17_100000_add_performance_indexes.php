<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->index(['is_homepage', 'is_published', 'homepage_order'], 'photos_homepage_lookup');
            $table->index(['album_id', 'sort_order'], 'photos_album_sort');
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->index(['type', 'is_published', 'year', 'sort_order'], 'albums_public_lookup');
        });

        Cache::forget(Setting::HAS_TABLE_KEY);
        Cache::forget(Setting::CACHE_KEY);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropIndex('photos_homepage_lookup');
            $table->dropIndex('photos_album_sort');
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->dropIndex('albums_public_lookup');
        });

        Cache::forget(Setting::HAS_TABLE_KEY);
        Cache::forget(Setting::CACHE_KEY);
    }
};