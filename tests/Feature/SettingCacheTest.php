<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_current_caches_a_plain_array_and_returns_a_model(): void
    {
        Setting::create([
            'photographer_name' => 'Fio Gallery',
            'email' => 'hello@example.com',
        ]);

        $setting = Setting::current();

        $this->assertInstanceOf(Setting::class, $setting);
        $this->assertSame('Fio Gallery', $setting->photographer_name);

        $cached = Cache::get(Setting::CACHE_KEY);

        $this->assertIsArray($cached);
        $this->assertSame('Fio Gallery', $cached['photographer_name']);
    }

    public function test_current_hydrates_a_model_from_a_warm_cache(): void
    {
        Setting::create(['photographer_name' => 'Fio Gallery']);

        Cache::forever(Setting::CACHE_KEY, ['id' => 1, 'photographer_name' => 'From Cache']);

        $setting = Setting::current();

        $this->assertInstanceOf(Setting::class, $setting);
        $this->assertSame('From Cache', $setting->photographer_name);
    }
}
