<div class="form-group">
    <label class="input-label" for="email">{{ trans('auth.email') }} {{ !empty($optional) ? "(". trans('public.optional') .")" : '' }}:</label>
    <input name="email" type="email" autocomplete="email" class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email') }}" id="email" aria-describedby="emailHelp"
           @if(empty($optional) && empty($authToggleBranch)) required @endif>

    @error('email')
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>
