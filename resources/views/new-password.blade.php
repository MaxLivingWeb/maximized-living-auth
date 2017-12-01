@extends('layouts.master')

@section('content')
    <section class="heroAlternative heroAlternative-padded centerAlign border-faintGrey">
        <div class="container">
            <div class="heroContent">
                <h1 class="heroHeadline">New Password</h1>
            </div>
        </div>
    </section>
    <section class="welcomeSection">
        <div class="bubbleCardContainer">
            <div class="left bubbleBgContainer">
                <img src="../images/BubbleBg-Left.png"/>
            </div>
            <div class="container">
                <div class="welcomeCard card">
                    @if($errors->any())
                        @include('components/forms/form-alert', ['errors' => $errors->all()])
                    @endif

                    @include('components/forms/new-password-form')
                </div>
            </div>
            <div class="right bubbleBgContainer">
                <img src="../images/BubbleBg-Right.png"/>
            </div>
        </div>
    </section>
@endsection
