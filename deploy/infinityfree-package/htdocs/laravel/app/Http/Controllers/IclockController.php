<?php

namespace App\Http\Controllers;

use App\Services\ZktecoAdmsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IclockController extends Controller
{
    public function __construct(
        private readonly ZktecoAdmsService $admsService,
    ) {}

    public function cdata(Request $request): Response
    {
        $content = $request->isMethod('POST')
            ? $this->admsService->handleCdataPost($request)
            : $this->admsService->handleCdataGet($request);

        return response($content, 200)->header('Content-Type', 'text/plain');
    }

    public function getRequest(Request $request): Response
    {
        return response($this->admsService->handleGetRequest($request), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function deviceCmd(Request $request): Response
    {
        return response($this->admsService->handleDeviceCmd($request), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function registry(Request $request): Response
    {
        return response($this->admsService->handleRegistry($request), 200)
            ->header('Content-Type', 'text/plain');
    }
}
