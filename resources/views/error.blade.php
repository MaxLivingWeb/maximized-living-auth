@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="col-md-6 col-md-push-3">
            <div class="panel panel-primary">
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
@endsection
