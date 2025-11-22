<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="row g-3">
        <div class="col-12">
            <label for="update_password_current_password" class="form-label">{{ __('Current Password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('updatePassword.current_password') is-invalid @enderror" autocomplete="current-password">
            @if($errors->updatePassword->has('current_password'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
            @endif
        </div>

        <div class="col-md-6">
            <label for="update_password_password" class="form-label">{{ __('New Password') }}</label>
            <input id="update_password_password" name="password" type="password" class="form-control @error('updatePassword.password') is-invalid @enderror" autocomplete="new-password">
            @if($errors->updatePassword->has('password'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
            @endif
        </div>

        <div class="col-md-6">
            <label for="update_password_password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('updatePassword.password_confirmation') is-invalid @enderror" autocomplete="new-password">
            @if($errors->updatePassword->has('password_confirmation'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-shield-lock"></i> {{ __('Save') }}</button>
            @if (session('status') === 'password-updated')
                <span class="ms-2 text-success">{{ __('Saved.') }}</span>
            @endif
        </div>
    </div>
</form>
