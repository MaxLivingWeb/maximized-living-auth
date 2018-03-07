@extends('layouts.master')

@section('content')
    <section class="heroAlternative heroAlternative-padded centerAlign border-faintGrey">
        <div class="container">
            <div class="heroContent">
                <h1 class="heroHeadline">Verify Account</h1>
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
                    <h2>Verify Email</h2>

                    @if (!$askForEmail)
                        <p>We've emailed you a verification code.</p>
                    @endif

                    @include('components/forms/form-alert', ['errors' => $errors->all()])

                    @include('components/forms/verify-form', [
                        'askForEmail' => $askForEmail,
                        'verificationCode'  => $verificationCode
                    ])
                </div>
            </div>
            <div class="right bubbleBgContainer">
                <img src="../images/BubbleBg-Right.png"/>
            </div>
        </div>
    </section>
@endsection
