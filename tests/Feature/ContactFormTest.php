<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_displays_setting_data(): void
    {
        Setting::create([
            'photographer_name' => 'Fio Gallery',
            'phone' => '+39 346 301 9079',
            'instagram_url' => 'https://instagram.com/frntms',
            'vimeo' => 'https://vimeo.com/fio-gallery',
        ]);

        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('+39 346 301 9079');
        $response->assertSee('https://instagram.com/frntms');
        $response->assertSee('https://vimeo.com/fio-gallery');
        $response->assertSee('VIMEO');
    }

    public function test_contact_submit_stores_message_and_fails_without_valid_data(): void
    {
        $response = $this->post(route('contact.submit'), [
            'name' => '',
            'email' => 'not-an-email',
            'message' => 'short',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'message']);
        $this->assertCount(0, Contact::query()->get());
    }

    public function test_contact_submit_stores_valid_message(): void
    {
        $response = $this->post(route('contact.submit'), [
            'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
            'message' => 'Ciao Tommaso, vorrei prenotare un servizio fotografico.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'success-message');

        $this->assertDatabaseHas('contacts', [
            'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
            'status' => 'new',
        ]);
    }

    public function test_contact_submit_accepts_empty_website_honeypot_field(): void
    {
        $response = $this->post(route('contact.submit'), [
            'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
            'message' => 'Ciao Tommaso, vorrei prenotare un servizio fotografico.',
            'website' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'success-message');

        $this->assertDatabaseHas('contacts', [
            'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
        ]);
    }

    public function test_contact_submit_ignores_honeypot_spam(): void
    {
        $response = $this->post(route('contact.submit'), [
            'name' => 'Bot',
            'email' => 'bot@spam.com',
            'message' => 'Questo messaggio viene da un bot.',
            'website' => 'http://spam.example.com',
        ]);

        $response->assertSessionHas('status', 'success-message');
        $this->assertCount(0, Contact::query()->get());
    }
}