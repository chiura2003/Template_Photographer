<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['email', 'facebook_url', 'website_url']);
            $table->string('vimeo')->nullable()->after('instagram_url');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('email');
            $table->string('facebook_url')->nullable();
            $table->string('website_url')->nullable();
            $table->dropColumn('vimeo');
        });
    }
};
