<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends WebController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'in:id,en'],
        ]);

        session(['locale' => $data['locale']]);

        return back();
    }
}
