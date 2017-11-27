@extends('layouts.master')

@section('content')
    <section class="page-header text-center">
        <div class="container">
            <h1>Password Reset</h1>
            <p>Enter the code which was sent to your Email</p>
        </div>
    </section>
    <section>
        <div class="contianer">
            <div class="row">
                <div class="col-md-6 col-md-push-3">
                    <div class="panel panel-primary panel-shadow panel-pushdown">
                        <div class="panel-heading">
                            <h3 class="panel-title">Verification Needed</h3>
                        </div>
                        <div class="panel-body">
                            @if($errors->any())
                                @include('components/forms/form-alert', ['errors' => $errors->all()])
                            @endif

                            @include('components/forms/forgot-password-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
