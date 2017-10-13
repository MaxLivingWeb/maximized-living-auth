@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-md-6 col-md-push-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">New Password</h3>
                </div>
                <div class="panel-body">
                    @if(isset($message) && isset($hasErrors))
                        @component(
                            'components/form-alert',
                            [
                                'message' => $message,
                                'hasErrors' => $hasErrors
                            ]
                        )@endcomponent
                    @endif

                    @component(
                        'components/new-password-form'
                    )@endcomponent
                </div>
            </div>
        </div>
    </div>
@endsection