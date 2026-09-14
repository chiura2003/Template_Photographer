<?php

namespace App\Http\Controllers;

use App\Enums\AlbumType;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageMail;
use App\Models\Album;
use App\Models\Contact;
use App\Models\Photo;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PublicController extends Controller
{
    public function homepage()
    {
        if (! Schema::hasTable('photos') || ! Schema::hasTable('albums')) {
            return view('homepage', [
                'homepagePhotos' => collect(),
            ]);
        }

        $homepagePhotos = Photo::query()
            ->with('album')
            ->where('is_homepage', true)
            ->where('is_published', true)
            ->whereHas('album', fn ($query) => $query->where('is_published', true))
            ->orderBy('homepage_order')
            ->orderBy('id')
            ->get();

        return view('homepage', [
            'homepagePhotos' => $homepagePhotos,
        ]);
    }

    public function work()
    {
        return view('work');
    }

    public function personal()
    {
        return view('personal');
    }

    public function workAlbum(Album $album)
    {
        return $this->album($album, AlbumType::Work, 'album-work');
    }

    public function personalAlbum(Album $album)
    {
        return $this->album($album, AlbumType::Personal, 'album-personal');
    }

    public function about()
    {
        $setting = Schema::hasTable('settings')
            ? Setting::query()->first()
            : null;
        $hasProfileImage = $setting?->profile_image
            && Storage::disk('private')->exists($setting->profile_image);

        return view('about', compact('setting', 'hasProfileImage'));
    }

    public function aboutProfileImage()
    {
        abort_unless(Schema::hasTable('settings'), 404);

        $setting = Setting::query()->first();

        abort_unless($setting?->profile_image && Storage::disk('private')->exists($setting->profile_image), 404);

        return Storage::disk('private')->response($setting->profile_image);
    }

    public function contact()
    {
        $setting = Schema::hasTable('settings')
            ? Setting::query()->first()
            : null;

        return view('contact', compact('setting'));
    }

    public function contactSubmit(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['website'])) {
            return back()->with('status', 'success-message');
        }

        Contact::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
        ]);

        Mail::to(config('services.contact.recipient'))
            ->send(new ContactMessageMail(
                name: $data['name'],
                email: $data['email'],
                messageBody: $data['message'],
            ));

        return back()->with('status', 'success-message');
    }

    private function album(Album $album, AlbumType $type, string $view)
    {
        abort_unless($album->is_published && $album->type === $type, 404);

        $album->load([
            'photos' => fn ($query) => $query
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->orderBy('id'),
        ]);

        return view($view, compact('album'));
    }
}
