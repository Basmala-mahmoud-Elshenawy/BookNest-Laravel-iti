@if ($errors->any())
    <div class="form-error" style="margin-bottom:16px;">
        <ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

@if(isset($book) && $book->cover_path)
    <img src="{{ $book->coverUrl() }}" alt="Current cover" style="width:100px;border-radius:8px;margin-bottom:16px;">
@endif

<div class="form-group">
    <label class="form-label">Title</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $book->title ?? '') }}" required>
</div>
<div class="form-group">
    <label class="form-label">Author(s) <span class="form-help">(comma-separated)</span></label>
    <input type="text" name="authors" class="form-control" value="{{ old('authors', isset($book) ? $book->authors->pluck('name')->implode(', ') : '') }}" required>
</div>
<div class="form-group">
    <label class="form-label">Category</label>
    <select name="category_id" class="form-control">
        <option value="">— None —</option>
        @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(old('category_id', $book->category_id ?? null) == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="4">{{ old('description', $book->description ?? '') }}</textarea>
</div>
<div class="form-group">
    <label class="form-label">ISBN</label>
    <input type="text" name="isbn" class="form-control" value="{{ old('isbn', $book->isbn ?? '') }}">
</div>
<div class="form-group">
    <label class="form-label">Published Date</label>
    <input type="date" name="published_at" class="form-control" value="{{ old('published_at', optional($book->published_at ?? null)->format('Y-m-d')) }}">
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div class="form-group">
        <label class="form-label">Total Copies</label>
        <input type="number" min="0" name="total_copies" class="form-control" value="{{ old('total_copies', $book->total_copies ?? 1) }}" required>
    </div>
    <div class="form-group">
        <label class="form-label">Available Copies</label>
        <input type="number" min="0" name="available_copies" class="form-control" value="{{ old('available_copies', $book->available_copies ?? 1) }}" required>
    </div>
</div>
<div class="form-group">
    <label class="form-label">Cover Image</label>
    <input type="file" name="cover" class="form-control" accept="image/*">
    <p class="form-help">Leave blank to keep the current cover.</p>
</div>
