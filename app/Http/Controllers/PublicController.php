<?php

namespace App\Http\Controllers;

use App\Enums\AlbumType;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageMail;
use App\Models\Album;
use App\Models\Contact;
use App\Models\Photo;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PublicController extends Controller
{
    public function homepage()
    {
        $setting = Setting::current();

        if (! Schema::hasTable('photos') || ! Schema::hasTable('albums')) {
            return view('homepage', [
                'homepagePhotos' => collect(),
                'setting' => $setting,
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
            'setting' => $setting,
        ]);
    }

    public function work()
    {
        return view('work', [
            'setting' => Setting::current(),
        ]);
    }

    public function personal()
    {
        return view('personal', [
            'setting' => Setting::current(),
        ]);
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
        $setting = Setting::current();
        $hasProfileImage = $setting?->profile_image
            && Storage::disk('private')->exists($setting->profile_image);

        return view('about', compact('setting', 'hasProfileImage'));
    }

    public function aboutProfileImage(Request $request)
    {
        abort_unless(Setting::tableExists(), 404);

        $setting = Setting::current();

        abort_unless($setting?->profile_image && Storage::disk('private')->exists($setting->profile_image), 404);

        $disk = Storage::disk('private');

        $response = $disk->response($setting->profile_image);
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        $response->setLastModified(new \DateTimeImmutable('@'.$disk->lastModified($setting->profile_image)));

        if ($response->isNotModified($request)) {
            return new Response(null, Response::HTTP_NOT_MODIFIED, [
                'Cache-Control' => 'public, max-age=3600',
                'Last-Modified' => $response->headers->get('Last-Modified'),
            ]);
        }

        return $response;
    }

    public function contact()
    {
        $setting = Setting::current();

        return view('contact', compact('setting'));
    }

    public function contactSubmit(ContactRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['website'])) {
            return $this->contactFinish($request, true);
        }

        Contact::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
        ]);

        $recipient = Setting::current()?->email;

        try {
            Mail::to($recipient ?: config('services.contact.recipient'))
                ->send(new ContactMessageMail(
                    name: $data['name'],
                    email: $data['email'],
                    messageBody: $data['message'],
                ));
        } catch (\Throwable $exception) {
            report($exception);

            return $this->contactFinish($request, false);
        }

        return $this->contactFinish($request, true);
    }

    private function contactFinish(Request $request, bool $ok): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return $ok
                ? response()->json(['status' => 'success-message'])
                : response()->json(['message' => 'Invio non riuscito. Riprova più tardi.'], 500);
        }

        return $ok
            ? back()->with('status', 'success-message')
            : back()->withInput()->withErrors(['message' => 'Invio non riuscito. Riprova più tardi.']);
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
