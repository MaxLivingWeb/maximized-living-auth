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

    public function verifyCode(Request $request)
    {
        return view('forgot-password');
    }

    public function sendCode(Request $request)
    {
        $request->validate([
            'username' => 'required|email'
        ]);

        $username = $request->input('username');

        $cognito = new CognitoHelper();
        try {
            $cognito->sendPasswordCode($username);
        }
        catch(AwsException $e) {
        }

        session()->put('forgotPasswordUsername', $username);

        return redirect()->route('forgotPassword.verifyCode');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password'         => 'required|min:8|confirmed|regex:/^.*(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!$#%]).*$/',
            'verificationCode' => 'required'
        ]);

        $username = session()->get('forgotPasswordUsername');
        $password = $request->input('password');
        $verificationCode = $request->input('verificationCode');


        $cognito = new CognitoHelper();
        try {
            $cognito->updatePassword($username, $password, $verificationCode);
        }
        catch(AwsException $e) {
            return view('forgot-password')->withErrors([$e->getAwsErrorMessage()]);
        }

        return redirect()->route('login');
    }
}
