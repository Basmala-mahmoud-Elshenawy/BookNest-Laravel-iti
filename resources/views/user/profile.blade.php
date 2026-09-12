@extends('layouts.app')
@section('title', 'Your Profile — BookNest')
@section('content')
<section class="section">
    <div class="container" style="max-width:720px;">
        <h1>Your Profile</h1>
        <p class="book-author">Tell us what you like — this powers your match percentages and AI recommendations.</p>

        <form method="POST" action="{{ route('profile.update') }}" class="card card-pad" style="margin-top:24px;">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Interests <span class="form-help">(comma-separated)</span></label>
                <input type="text" name="interests" class="form-control" value="{{ old('interests', implode(', ', $user->profile->interests ?? [])) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Favorite Topics</label>
                <input type="text" name="favorite_topics" class="form-control" value="{{ old('favorite_topics', implode(', ', $user->profile->favorite_topics ?? [])) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Skills</label>
                <input type="text" name="skills" class="form-control" value="{{ old('skills', implode(', ', $user->profile->skills ?? [])) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Learning Goals</label>
                <input type="text" name="learning_goals" class="form-control" value="{{ old('learning_goals', implode(', ', $user->profile->learning_goals ?? [])) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Preferred Categories</label>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    @foreach($categories as $category)
                        @php $preferred = $user->profile->preferred_category_ids ?? []; @endphp
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.85rem;background:var(--color-bg);padding:8px 12px;border-radius:999px;">
                            <input type="checkbox" name="preferred_category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, $preferred))>
                            {{ $category->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>
</section>
@endsection
