@extends('client_store/layouts.page')

@section('content')
    <div class="container">
        <div class="col-md-6 col-md-push-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Forgot Password</h3>
                </div>
                <div class="panel-body">
                    @if($errors->any())
                        @include('components/forms/form-alert', ['errors' => $errors->all()])
                    @endif

                    @include('components/forms/send-code-form')
                </div>
            </div>
        </div>
    </div>
@endsection
