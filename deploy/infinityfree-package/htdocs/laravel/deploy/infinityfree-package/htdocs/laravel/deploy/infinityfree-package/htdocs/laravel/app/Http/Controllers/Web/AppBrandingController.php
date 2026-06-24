<?php

namespace App\Http\Controllers\Web;

use App\Services\AppBrandingService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AppBrandingController extends WebController
{
    public function logo(AppBrandingService $branding): BinaryFileResponse|Response
    {
        $path = $branding->all()['logo_path'];

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('public')->path($path),
            ['Cache-Control' => 'public, max-age=86400']
        );
    }
}
