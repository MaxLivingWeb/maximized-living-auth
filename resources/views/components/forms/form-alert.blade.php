@if (count($errors) > 0)
    @foreach($errors as $error)
        <div class="alert alert-danger">
            <p>{!! $error !!}</p>
        </div>
    @endforeach
@endif

@if (isset($messages))
    @foreach($messages as $message)
        <div class="alert alert-success">
            <p>{!! $message !!}</p>
        </div>
    @endforeach
@endif
