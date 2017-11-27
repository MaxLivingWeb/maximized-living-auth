<form method="post" action="{{ route('updatePassword') }}">
    {{ csrf_field() }}
    <div class="form-group">
        <label for="password">Verification Code</label>
        <input type="text" class="form-control" name="verificationCode" id="verificationCode" placeholder="Verification Code" required>
    </div>
    <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
    </div>
    <div class="form-group">
        <label for="password">Confirm New Password</label>
        <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" placeholder="Password" required>
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
</form>