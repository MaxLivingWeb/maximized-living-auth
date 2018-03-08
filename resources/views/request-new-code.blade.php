@extends('layouts.master')

@section('content')
    <section class="heroAlternative heroAlternative-padded centerAlign border-faintGrey">
        <div class="container">
            <div class="heroContent">
                <h1 class="heroHeadline">Resend Verification Code?</h1>
                <p>Enter your account email to receive a new token.</p>
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
                    <h2>Enter your Email Address</h2>

                    @if($errors->any())
                        @include('components/forms/form-alert', ['errors' => $errors->all()])
                    @endif

                    <form method="post" action="{{ route('verification.resendVerificationCode') }}">
                        {{ csrf_field() }}
                        <div class="inputField">
                            <label for="username">Email address</label>
                            <input type="email" name="username" id="username" placeholder="Email" value="{{ $username ?? old('username') }}" required>
                        </div>
                        <div class="inputField">
                            <p><small>Go back to verification page <a href="{{ route('verification.index') }}">here</a>.</small></p>
                        </div>
                        <div class="inputField">
                            <button type="submit" class="button button-primary button-wide">Send Code</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="right bubbleBgContainer">
                <img src="../images/BubbleBg-Right.png"/>
            </div>
        </div>
    </section>
@endsection
