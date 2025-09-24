<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Models\Member;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $member = Member::where('user_id', $user->id)->first();
        return view('profile.edit', [
            'user' => $user,
            'member' => $member,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Only allow updating name on User model; email cannot be changed here
        $validated = $request->validated();
        $user->fill(['name' => $validated['name'] ?? $user->name]);
        $user->save();

        // Update related member profile fields
        $member = Member::where('user_id', $user->id)->first();
        if ($member) {
            $data = [];
            if (array_key_exists('phone', $validated)) {
                $data['phone'] = $validated['phone'];
            }
            if (array_key_exists('address', $validated)) {
                $data['address'] = $validated['address'];
            }

            if ($request->hasFile('profile_picture')) {
                if ($member->profile_picture) {
                    Storage::disk('public')->delete($member->profile_picture);
                }
                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $data['profile_picture'] = $path;
            }

            if (!empty($data)) {
                $member->update($data);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Only Admin or Super Admin can delete an account from profile
        if (! $request->user()->hasAnyRole(['Admin','Super Admin'])) {
            abort(403, 'You are not authorized to delete this account.');
        }

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
