<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['id' => 1],
            [
                'photographer_name' => 'Demo Photographer',

                'phone' => '+34 600 123 456',

                'bio' => 'Professional photographer specialized in portraits, weddings and landscape photography.',

                'profile_image' => 'photos/demo/profile.webp',

                'instagram_url' => 'https://instagram.com/demo.photographer',

                'vimeo' => 'https://vimeo.com/demo.photographer',
            ]
        );
    }
}