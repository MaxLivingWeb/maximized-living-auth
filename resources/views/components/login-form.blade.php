<form method="post" action="{{ route('submitLogin') }}">
    {{ csrf_field() }}
    <div class="form-group">
        <label for="username">Email address</label>
        <input type="email" class="form-control" name="username" id="username" placeholder="Email" value="{{ old('username') }}" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
    </div>
    <button type="submit" class="btn btn-default">Login</button>
</form>