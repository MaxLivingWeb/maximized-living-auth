@extends('layouts.master')

@section('content')
    <section class="heroAlternative heroAlternative-padded centerAlign border-faintGrey">
        <div class="container">
            <div class="heroContent">
                <h1 class="heroHeadline">New Password</h1>
                @if (empty($verificationCode))
                    <p>Enter the verification code that was sent to your email address to change your password.</p>
                @else
                    <p>Verification code received!<br>Enter your new password below to change your password.</p>
                @endif
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
                    <div>
                        <p class="alert alert-warning">
                            Your password has been reset, and you have to change your password in order to log in again.
                        </p>
                    </div>
                    <br/>

                    @if($errors->any())
                        @include('components/forms/form-alert', ['errors' => $errors->all()])
                    @endif

                    @include('components/forms/forgot-password-form', [
                        'username' => $username,
                        'verificationCode' => $verificationCode
                    ])
                </div>
            </div>
            <div class="right bubbleBgContainer">
                <img src="../images/BubbleBg-Right.png"/>
            </div>
        </div>
    </section>
@endsection
