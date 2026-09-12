@if ($errors->any())
    <div class="form-error" style="margin-bottom:16px;">{{ $errors->first() }}</div>
@endif
<div class="form-group">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
</div>
<div class="form-group">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
</div>
<div class="form-group">
    <label class="form-label">Role</label>
    <select name="role" class="form-control">
        <option value="user" @selected(old('role', $user->role ?? 'user') === 'user')>User</option>
        <option value="admin" @selected(old('role', $user->role ?? '') === 'admin')>Admin</option>
    </select>
</div>
<div class="form-group">
    <label class="form-label">Password @if(isset($user))<span class="form-help">(leave blank to keep current)</span>@endif</label>
    <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
</div>
