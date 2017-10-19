<form method="post" action="{{ route('newPassword') }}">
    {{ csrf_field() }}
    <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
    </div>
    <div class="form-group">
        <label for="password">Confirm New Password</label>
        <input type="password" class="form-control" name="confrimPassword" id="confrimPassword" placeholder="Password" required>
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>