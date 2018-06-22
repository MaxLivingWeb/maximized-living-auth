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
    public function enterVerificationCode(Request $request)
    {
        $verificationCode = session()->get('forgotPasswordVerificationCode');
        if (!empty($request->input('verificationCode'))) {
            $verificationCode = $request->input('verificationCode');
        }

        return view('forgot-password', [
            'username' => session()->get('forgotPasswordUsername'),
            'verificationCode' => $verificationCode
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
            $cognito->updateUserAttribute(
                'custom:verificationState',
                'ForgotPassword',
                $username
            );
        }
        catch(AwsException $e) {
            if ($e->getAwsErrorCode() === 'LimitExceededException') {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'Attempt limit exceeded while attempting to change forgotten password. Please try again later.'
                    ]);
            }
        }

        session()->put('forgotPasswordUsername', $username);

        return redirect()->route('forgotPassword.enterVerificationCode');
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

        $username = $request->input('username');
        $password = $request->input('password');
        $verificationCode = $request->input('verificationCode');

        $cognito = new CognitoHelper();
        try {
            $cognito->updateForgottenPassword($username, $password, $verificationCode);
            $cognito->updateUserAttribute('custom:verificationState', 'Verified', $username);
        }
        catch(AwsException $e) {
            $validVerificationCode = (
                $e->getAwsErrorCode() !== 'ExpiredCodeException'
                && $e->getAwsErrorCode() !== 'CodeMismatchException'
            );

            return redirect()->back()
                ->with([
                    'forgotPasswordUsername' => $username,
                    'forgotPasswordVerificationCode' => $validVerificationCode ? $verificationCode : null
                ])
                ->withErrors([
                    $e->getAwsErrorMessage()
                ]);
        }

        // clear this, now that password has been updated
        session()->forget('forgotPasswordUsername');
        session()->forget('forgotPasswordVerificationCode');

        // Login!
        return redirect()->route('login')->with('messages', [__('auth.verificationForgotPasswordSuccess')]);
    }
}
