<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = [

            [
                'name' => 'Marco Rossi',
                'email' => 'marco.rossi@example.com',
                'phone' => '+39 333 1234567',
                'message' => 'Hi, I would like to receive a quote for my wedding in September.',
                'status' => 'new',
            ],

            [
                'name' => 'Laura Bianchi',
                'email' => 'laura.bianchi@example.com',
                'phone' => '+39 333 9876543',
                'message' => 'I would like to organize a family photo session.',
                'status' => 'new',
            ],

            [
                'name' => 'Hotel Paradise',
                'email' => 'info@hotelparadise.com',
                'phone' => '+34 928 000000',
                'message' => 'We are looking for a photographer to update our hotel images.',
                'status' => 'read',
            ],

        ];

        foreach ($contacts as $contact) {

            Contact::updateOrCreate(
                [
                    'email' => $contact['email'],
                ],
                $contact
            );

        }
    }
}