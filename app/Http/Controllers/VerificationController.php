<?php

namespace App\Http\Controllers;

use App\Helpers\CognitoHelper;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * User clicked link from "Verification" email template
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index(Request $request)
    {
        $params = $request->query();
        $query = !empty($params) ? '?'.http_build_query($params) : '';

        // Redirect to "Forgot Password Verification" page, since 'forgotPasswordUsername' was saved to the session
        if (session()->has('forgotPasswordUsername')) {
            return redirect(route('forgotPassword.enterVerificationCode').$query);
        }

        // Redirect to "Registration Verification" page
        return redirect(route('register.enterVerificationCode').$query);
    }

    /**
     * Enter Verification code that was sent to Email Address to confirm account status
     * @param Request $request
     * @return $this|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function enterVerificationCode(Request $request)
    {
        return view('verify', [
            'askForEmail' => !session()->has('verifyUsername'),
            'verificationCode' => $request->input('verificationCode')
        ]);
    }

    /**
     * Request a new Verification Code
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function requestVerificationCode()
    {
        return view('request-new-code', [
            'username' => session()->get('verifyUsername')
        ]);
    }

    /**
     * Resend a new verification code, now have to validate this new one
     * @param Request $request
     * @return $this
     */
    public function resendVerificationCode(Request $request)
    {
        $cognito = new CognitoHelper();

        $request->validate([
            'username' => 'required|email'
        ]);

        $username = $request->input('username');

        try {
            $cognito->resendConfirmationCode($username);
        }
        catch(AwsException $e) {
        }

        return redirect()->route('verification.index');
    }

}
