<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class VerificationRequestHelper
{

    /**
     * Enter Verification code that was sent to Email Address to confirm account status
     * @param Request $request
     * @return $this|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public static function index(Request $request)
    {
        // Verify account for Registration
        if (session()->has('verifyUsername') && $request->has('verificationCode')) {
            //We have both username and verificationCode, automatically verify the user & redirect accordingly
            return self::confirm(
                session()->get('verifyUsername'),
                $request->input('verificationCode')
            );
        }

        return view('verify', [
            'askForEmail' => !session()->has('verifyUsername'),
            'verificationCode' => $request->input('verificationCode')
        ]);
    }

    /**
     * Submit Verification Code to complete confirmation process for account
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public static function submit(Request $request)
    {
        $fields = [
            'verificationCode' => 'required'
        ];

        $username = session()->get('verifyUsername');

        if(!session()->has('verifyUsername')) {
            $fields['email'] = 'required';
            $username = $request->input('email');
        }

        $request->validate($fields);

        return self::confirm($username, $request->input('verificationCode'));
    }

    /**
     * Confirm verification code that was sent through Cognito
     * @param $username
     * @param $verificationCode
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    private static function confirm($username, $verificationCode)
    {
        try {
            $cognito = new CognitoHelper();

            // Get user and check their current verification state
            $cognitoUser = $cognito->getUser($username);
            $verificationState = $cognito->getUserAttributeValue('custom:verificationState', $cognitoUser['UserAttributes']);

            // User is currently in the process of verifying their forgotton password, so continue to that view to continue the verification process
            if ($verificationState === 'ForgotPassword') {
                return redirect()->route('forgotPassword.enterVerificationCode')->with([
                    'forgotPasswordUsername' => $username,
                    'forgotPasswordVerificationCode' => $verificationCode
                ]);
            }

            // User is currently in the process of verifying their account registration
            if ($verificationState === 'Registration') {
                $cognito->confirmSignup($username, $verificationCode);
                $cognito->updateUserAttribute('custom:verificationState', 'Verified', $username);
                session()->forget('verifyUsername');
                return redirect()->route('login')->with('messages', [__('auth.emailVerified')]);
            }
        }
        catch(AwsException $e) {
            return redirect()->route('verification.index')->withErrors($e->getAwsErrorMessage());
        }
    }

}
