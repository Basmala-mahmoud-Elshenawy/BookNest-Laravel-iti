@if($errors->any())<div class="form-error"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="form-group"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name',$author->name??'') }}" required></div>
<div class="form-group"><label class="form-label">Bio</label><textarea class="form-control" name="bio" rows="6">{{ old('bio',$author->bio??'') }}</textarea></div>
