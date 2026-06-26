<?php

namespace App\Http\Controllers\Web;

use App\Services\AttendanceMethodSettingsService;
use App\Services\AttendanceService;
use App\Services\ProfileFaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfileController extends WebController
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly AttendanceMethodSettingsService $attendanceMethods,
        private readonly ProfileFaceService $profileFaceService,
    ) {}

    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load(['employee.faces', 'employee.branch']);

        $employee = $user->employee;

        return view('profile.edit', [
            'user' => $user,
            'employee' => $employee,
            'canEnrollFace' => $this->profileFaceService->canEnrollFace($user),
            'needsFaceEnrollment' => $this->profileFaceService->needsEnrollment($user),
            'faceCount' => $this->profileFaceService->registeredFaceCount($user),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_profile_photo' => ['nullable', 'boolean'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if ($request->boolean('remove_profile_photo') && ! $request->hasFile('profile_photo')) {
            $this->deleteProfilePhoto($user->profile_photo_path);
            $user->profile_photo_path = null;
        }

        if ($request->hasFile('profile_photo')) {
            $this->deleteProfilePhoto($user->profile_photo_path);
            $extension = strtolower($request->file('profile_photo')->getClientOriginalExtension() ?: 'jpg');
            $user->profile_photo_path = $request->file('profile_photo')->storeAs(
                'profiles',
                'user-'.$user->id.'.'.$extension,
                'public'
            );
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        if ($user->employee) {
            $user->employee->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        }

        return redirect()
            ->route('profile.edit')
            ->with('success', __('pages.profile.saved'));
    }

    public function storeFace(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if ($employee === null) {
            abort(403, 'Akun tidak terhubung ke data pegawai.');
        }

        if (! $this->attendanceMethods->photoEnabled()) {
            return back()->with('error', __('pages.profile.face_method_disabled'));
        }

        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
            'face_descriptor' => ['required', 'json'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $descriptor = json_decode($data['face_descriptor'], true);

        if (! is_array($descriptor) || count($descriptor) < 64) {
            return back()
                ->with('error', __('pages.profile.face_invalid'))
                ->withFragment('face-enrollment');
        }

        $this->attendanceService->enrollFace(
            employee: $employee,
            faceDescriptor: $descriptor,
            photo: $request->file('photo'),
            isPrimary: $request->boolean('is_primary', true),
        );

        return redirect()
            ->route('profile.edit')
            ->with('success', __('pages.profile.face_enrolled'))
            ->withFragment('face-enrollment');
    }

    public function photo(Request $request): BinaryFileResponse|Response
    {
        $user = $request->user();
        $path = $user->profile_photo_path;

        if ($path === null || $path === '' || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('public')->path($path),
            ['Cache-Control' => 'private, max-age=3600']
        );
    }

    private function deleteProfilePhoto(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
