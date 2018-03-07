<?php

namespace App\Http\Controllers;

use App\Helpers\CognitoHelper;
use Illuminate\Http\Request;
use Aws\Exception\AwsException;

class ForgotPasswordController extends Controller
{
    /**
     * Forgot your Password? Fill out the form to send a verification code to your email
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('send-code');
    }

    /**
     * Verification Code sent for reseting password... fill out the form to confirm password update
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function checkVerificationCode(Request $request)
    {
        return view('forgot-password', [
            'verificationCode' => $request->input('verificationCode')
        ]);
    }

    /**
     * Forgot your Password? Form was filled out, and verification code was sent to your email
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendVerificationCode(Request $request)
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

        return redirect()->route('forgotPassword.checkVerificationCode');
    }

    /**
     * Verification Code was entered, and new password was set
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
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

        session()->forget('forgotPasswordUsername');

        return redirect()->route('login');
    }
}
