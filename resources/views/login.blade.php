@extends('layouts.master')

@section('content')
    <section class="heroAlternative heroAlternative-padded centerAlign border-faintGrey">
        <div class="container">
            <div class="heroContent">
                <h1 class="heroHeadline">My Account</h1>
            </div>
        </div>
    </section>
    <section class="userFormSection">
        <div class="bubbleCardContainer">
            <div class="left bubbleBgContainer">
                <img src="../images/BubbleBg-Left.png"/>
            </div>
            <div class="container">
                <div class="userFormCard card">
                    <h2>Login</h2>
                    <p>If you have an account, please login below</p>

                    @if($errors->any())
                        @include('components/forms/form-alert', ['errors' => $errors->all()])
                    @endif

                    @include('components/forms/login-form')
                </div>
            </div>
            <div class="right bubbleBgContainer">
                <img src="../images/BubbleBg-Right.png"/>
            </div>
        </div>
    </section>
@endsection
