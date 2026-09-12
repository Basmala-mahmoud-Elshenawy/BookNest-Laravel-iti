@if ($errors->any())
    <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
@endif
<div class="form-group">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
</div>
<div class="form-group">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description ?? '') }}</textarea>
</div>
