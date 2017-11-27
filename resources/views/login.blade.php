@extends('layouts.master')

@section('content')
    <section class="page-header text-center">
        <div class="container">
            <h1>My Account</h1>
        </div>
    </section>
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-push-3">
                    <div class="panel panel-primary panel-shadow panel-pushdown">
                        <div class="panel-heading">
                            <h2 class="panel-title">Login</h2>
                        </div>
                        <div class="panel-body">
                            @if($errors->any())
                                @include('components/forms/form-alert', ['errors' => $errors->all()])
                            @endif

                            @include('components/forms/login-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
