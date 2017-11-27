@extends('layouts.master')

@section('content')
    <section class="page-header text-center">
        <div class="container">
            <h1>An Error Occurred</h1>
        </div>
    </section>
    <section>
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-push-3">
                    <div class="panel panel-primary panel-shadow panel-pushdown">
                        <div class="panel-heading">
                            <h3 class="panel-title">Error</h3>
                        </div>
                        <div class="panel-body">
                            @if(isset($error))
                                <h4>{{ $error }}</h4>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
