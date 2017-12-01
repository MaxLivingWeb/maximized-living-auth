<form method="post" action="{{ route('sendCode') }}">
    {{ csrf_field() }}
    <div class="inputField">
        <label for="username">Email address</label>
        <input type="email" name="username" id="username" placeholder="Email" value="{{ old('username') }}" required>
    </div>
    <div class="inputField">
        <button type="submit" class="button button-primary button-wide">Send Code</button>
    </div>
</form>
