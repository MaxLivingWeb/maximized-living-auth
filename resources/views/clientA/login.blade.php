@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-md-6 col-md-push-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Login</h3>
                </div>
                <div class="panel-body">
                    @if($errors->any())
                        @component(
                            'components/form-alert',
                            [
                                'errors' => $errors->all()
                            ]
                        )@endcomponent
                    @endif

                    @component(
                        'components/login-form'
                    )@endcomponent
                </div>
            </div>
        </div>
    </div>
@endsection