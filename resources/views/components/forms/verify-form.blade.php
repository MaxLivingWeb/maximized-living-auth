<form method="post" action="{{ route('register.submitVerificationCode') }}">
    {{ csrf_field() }}

    @if($askForEmail)
        <div class="inputField">
            <label for="email">Email Address</label>
            <input type="text" name="email" id="email" placeholder="Email Address" required>
        </div>
    @endif

    @if(!isset($verificationCode))
        <div class="inputField">
            <label for="verificationCode">Verification Code</label>
            <input type="text" name="verificationCode" id="verificationCode" placeholder="Verification Code" required>
        </div>
    @else
        <input type="hidden" value="{{ $verificationCode }}" name="verificationCode" id="verificationCode">
    @endif

    <div class="inputField">
        <p><small>Request a new verification token by clicking <a href="{{ route('verification.requestVerificationCode') }}">here</a>.</small></p>
    </div>

    <div class="inputField">
        <button type="submit" class="button button-primary button-wide">Submit</button>
    </div>
</form>
