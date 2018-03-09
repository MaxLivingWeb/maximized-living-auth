<form method="post" action="{{ route('forgotPassword.updatePassword') }}">
    {{ csrf_field() }}

    @if (empty($username))
        <div class="inputField">
            <label for="password">Email Address</label>
            <input value="{{ old('username') }}" type="text" name="username" id="username" placeholder="Email Address" required>
        </div>
    @else
        <input type="hidden" name="username" value="{{ $username }}" />
    @endif

    @if (empty($verificationCode))
        <div class="inputField">
            <label for="password">Verification Code</label>
            <input value="{{ old('verificationCode') }}" type="text" name="verificationCode" id="verificationCode" placeholder="Verification Code" required>
        </div>
    @else
        <input type="hidden" name="verificationCode" value="{{ $verificationCode }}" />
    @endif

    <div class="inputField">
        <label for="password">New Password</label>
        <input type="password" name="password" id="password" placeholder="Password" required>
    </div>
    <div class="inputField">
        <label for="password">Confirm New Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Password" required>
    </div>

    <div class="inputField">
        <p><small>Request a new verification token by clicking <a href="{{ route('verification.requestVerificationCode') }}">here</a>.</small></p>
    </div>

    <div class="inputField">
        <button type="submit" class="button button-primary button-wide">Submit</button>
    </div>
</form>
