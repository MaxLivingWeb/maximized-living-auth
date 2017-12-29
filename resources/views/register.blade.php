@extends('layouts.master')

@section('content')
    <section class="heroAlternative heroAlternative-padded centerAlign border-faintGrey">
        <div class="container">
            <div class="heroContent">
                <h1 class="heroHeadline">Register</h1>
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
                    <h2>Register</h2>
                    <p>Enter your details below</p>

                    @include('components/forms/form-alert', ['errors' => $errors->all()])

                    @include('components/forms/register-form')
                </div>
            </div>
            <div class="right bubbleBgContainer">
                <img src="../images/BubbleBg-Right.png"/>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script type="text/javascript">
        document.addEventListener('readystatechange', () => {
            if (document.readyState === 'complete') {
                var countries = JSON.parse('@json(__('states'))');

                var country = document.getElementById('country');
                country.addEventListener('change', function(e) {
                    const stateSelect = document.getElementById('state');
                    stateSelect.innerHTML = '';
                    const states = countries[country.value]

                    for(state in states) {
                        stateSelect.add(new Option(states[state], state));
                    }
                });
            }
        });
    </script>
@endsection
