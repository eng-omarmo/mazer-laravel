<form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-none">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('patch')

    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="phone" class="form-label">{{ __('Phone') }}</label>
            <input id="phone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label for="position" class="form-label">{{ __('Position') }}</label>
            <input id="position" name="position" type="text" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', $user->position) }}" autocomplete="organization-title">
            @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="photo" class="form-label">{{ __('Photo') }}</label>
            <input id="photo" name="photo" type="file" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if($user->photo_path)
                <div class="mt-2">
                    <img src="{{ asset('storage/'.$user->photo_path) }}" alt="Profile Photo" class="rounded-circle" style="height:64px;width:64px;object-fit:cover;">
                </div>
            @endif
        </div>

        <div class="col-12">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="form-text mt-1">
                    {{ __('Your email address is unverified.') }}
                    <button form="send-verification" class="btn btn-link p-0 align-baseline">{{ __('Click here to re-send the verification email.') }}</button>
                </div>
                @if (session('status') === 'verification-link-sent')
                    <div class="text-success small mt-1">{{ __('A new verification link has been sent to your email address.') }}</div>
                @endif
            @endif
        </div>

        <div class="col-12">
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> {{ __('Save') }}</button>
            @if (session('status') === 'profile-updated')
                <span class="ms-2 text-success">{{ __('Saved.') }}</span>
            @endif
        </div>
    </div>
</form>
