<?php

namespace App\Http\Controllers;

use App\Helpers\CognitoHelper;
use Illuminate\Http\Request;
use Aws\Exception\AwsException;

class ForgotPasswordController extends Controller
{
    public function index(Request $request)
    {
        return view('send-code');
    }

    public function sendCode(Request $request)
    {
        $username = $request->input('username');

        //TODO: Validate

        $cognito = new CognitoHelper();
        try {
            $result = $cognito->sendPasswordCode($username);
        }
        catch(AwsException $e) {
            return redirect()->back()->withErrors([$e->getAwsErrorMessage()]);
        }

        session()->put('forgotPasswordUsername', $username);

        return view('forgot-password');
    }

    public function updatePassword(Request $request)
    {
        $username = session()->get('forgotPasswordUsername');
        $password = $request->input('password');
        $confirmPassword = $request->input('confirmPassword');
        $verificationCode = $request->input('verificationCode');

        //TODO: Validate

        $cognito = new CognitoHelper();
        try {
            $result = $cognito->updatePassword($username, $password, $verificationCode);
        }
        catch(AwsException $e) {
            return view('forgot-password')->withErrors([$e->getAwsErrorMessage()]);
        }

        return redirect()->route('home');
    }
}
