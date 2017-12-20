<form method="post" action="{{ route('registerSubmit') }}">
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
        <label for="password_confirmation">Confirm Password*</label>
        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm Password" required/>
    </div>
    <div class="inputField">
        <label for="address1">Address *</label>
        <input type="text" name="address1" id="address1" placeholder="Address" value="{{ old('address1') }}" required/>
    </div>
    <div class="inputField">
        <label for="address2">Unit</label>
        <input type="text" name="address2" id="address2" placeholder="Unit" value="{{ old('address2') }}" />
    </div>
    <div class="inputField">
        <label for="zip">Zip/Postal Code *</label>
        <input type="text" name="zip" id="zip" placeholder="Zip/Postal Code" value="{{ old('zip') }}" required/>
    </div>
    <div class="inputField">
        <label for="country">Country *</label>
        <select name="country" id="country" required>
            @foreach(__('states') as $key => $value)
                <option value="{{ $key }}" {{ old('country') === $key ? 'selected' : ''}}>{{ $key }}</option>
            @endforeach
        </select>
    </div>
    <div class="inputField">
        <label for="state">State/Province *</label>
        <select name="state" id="state" required>
            @foreach(__('states')[old('country')] ?? collect(__('states'))->first() as $key => $value)
                <option value="{{ $key }}" {{ old('state') === $key ? 'selected' : ''}}>{{ $value }}</option>
            @endforeach
        </select>
    </div>
    <div class="inputField">
        <label for="city">City *</label>
        <input type="text" name="city" id="city" placeholder="City" value="{{ old('city') }}" required/>
    </div>
    <div class="inputField">
        <button type="submit" class="button button-primary button-wide">Register</button>
    </div>
</form>
