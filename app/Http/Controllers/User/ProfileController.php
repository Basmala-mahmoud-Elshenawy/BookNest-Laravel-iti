<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('profile');
        $categories = Category::orderBy('name')->get();

        return view('user.profile', compact('user', 'categories'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
            'interests' => ['nullable', 'string', 'max:1000'],
            'favorite_topics' => ['nullable', 'string', 'max:1000'],
            'skills' => ['nullable', 'string', 'max:1000'],
            'learning_goals' => ['nullable', 'string', 'max:1000'],
            'preferred_category_ids' => ['nullable', 'array'],
            'preferred_category_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $user = $request->user();
        $user->update(['name' => $validated['name'], 'email' => $validated['email']]);

        $user->profile()->updateOrCreate(['user_id' => $user->id], [
            'interests' => $this->splitToArray($validated['interests'] ?? ''),
            'favorite_topics' => $this->splitToArray($validated['favorite_topics'] ?? ''),
            'skills' => $this->splitToArray($validated['skills'] ?? ''),
            'learning_goals' => $this->splitToArray($validated['learning_goals'] ?? ''),
            'preferred_category_ids' => $validated['preferred_category_ids'] ?? [],
        ]);

        return back()->with('status', 'Profile updated. Your recommendations have been refreshed.');
    }

    private function splitToArray(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->values()
            ->all();
    }
}
