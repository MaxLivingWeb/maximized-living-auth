<form method="post" action="{{ route('sendCode') }}">
    {{ csrf_field() }}
    <div class="form-group">
        <label for="username">Email address</label>
        <input type="email" class="form-control" name="username" id="username" placeholder="Email" value="{{ old('username') }}" required>
    </div>
    <button type="submit" class="btn btn-default">Send Code</button>
</form>