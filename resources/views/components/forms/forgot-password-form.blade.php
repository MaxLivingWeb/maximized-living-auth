<form method="post" action="{{ route('updatePassword') }}">
    {{ csrf_field() }}
    <div class="inputField">
        <label for="password">Verification Code</label>
        <input value="{{ old('verificationCode') }}" type="text" name="verificationCode" id="verificationCode" placeholder="Verification Code" required>
    </div>
    <div class="inputField">
        <label for="password">New Password</label>
        <input type="password" name="password" id="password" placeholder="Password" required>
    </div>
    <div class="inputField">
        <label for="password">Confirm New Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Password" required>
    </div>
    <div class="inputField">
        <button type="submit" class="button button-primary button-wide">Submit</button>
    </div>
</form>
