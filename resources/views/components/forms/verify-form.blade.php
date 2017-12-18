<form method="post" action="{{ route('verifySubmit') }}">
    {{ csrf_field() }}
    @if($askForEmail)
    <div class="inputField">
        <label for="password">Email Address</label>
        <input type="text" name="email" id="email" placeholder="Email Address" required>
    </div>
    @endif
    @if(!isset($code))
    <div class="inputField">
        <label for="password">Verification Code</label>
        <input type="text" name="verificationCode" id="verificationCode" placeholder="Verification Code" required>
    </div>
    @else
        <input type="hidden" value="{{ $code }}" name="verificationCode" id="verificationCode">
    @endif
    <div class="inputField">
        <button type="submit" class="button button-primary button-wide">Submit</button>
    </div>
</form>
