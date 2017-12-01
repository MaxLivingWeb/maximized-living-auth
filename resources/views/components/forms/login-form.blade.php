<form method="post" action="{{ route('submitLogin') }}">
    {{ csrf_field() }}
    <div class="inputField">
        <label for="username">Email *</label>
        <input type="email" name="username" id="username" placeholder="Email" value="{{ old('username') }}" required/>
    </div>
    <div class="inputField">
        <label for="password">Password *</label>
        <input type="password" name="password" id="password" placeholder="Password" required/>
    </div>
    <div class="inputField">
        <a href="{{ route('forgotPassword') }}">Forgot Password?</a>
    </div>
    <div class="inputField">
        <button type="submit" class="button button-primary button-wide">Login</button>
    </div>
</form>
