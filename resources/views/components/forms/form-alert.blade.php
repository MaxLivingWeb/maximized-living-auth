@if (count($errors) > 0)
    <div class="formErrors">
        @foreach($errors as $error)
            <div class="alert alert-danger">
                <p>{!! $error !!}</p>
            </div>
        @endforeach
    </div>
@endif

@if (isset($messages))
    <div class="formErrors">
        @foreach($messages as $message)
            <div class="alert alert-success">
                <p>{!! $message !!}</p>
            </div>
        @endforeach
    </div>
@endif
