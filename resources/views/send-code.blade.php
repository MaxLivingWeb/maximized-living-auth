@extends('layouts.master')

@section('content')
    <section class="page-header text-center">
        <div class="container">
            <h1>Forgot Password?</h1>
            <p>Proceed by assigning a new password for your account.</p>
        </div>
    </section>
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-push-3">
                    <div class="panel panel-primary panel-shadow panel-pushdown">
                        <div class="panel-heading">
                            <h3 class="panel-title">Please Enter your Email Address</h3>
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
        </div>
    </section>
@endsection
