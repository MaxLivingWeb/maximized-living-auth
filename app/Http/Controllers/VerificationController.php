<?php

namespace App\Http\Controllers;

use App\Helpers\VerificationRequestHelper;
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

        // Simply verify this confirmation code
        // Note: If user's verificationState is set to "ForgotPassword" they will be directed to a 2nd page to fill out the rest of their information... otherwise verification for Account Registration is complete.
        return VerificationRequestHelper::index($request);
    }

    /**
     * Submit verification code
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request)
    {
        return VerificationRequestHelper::submit($request);
    }

    /**
     * Request a new Verification Code
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function requestVerificationCode()
    {
        return view('request-new-code', [
            'title' => session()->get('resendVerificationCodeTitle'),
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

        // Get user and check their current verification state
        $cognitoUser = $cognito->getUser($username);
        $verificationState = $cognito->getUserAttributeValue('custom:verificationState', $cognitoUser['UserAttributes']);

        try {
            // User is currently in the process of verifying their forgotton password, so continue to that view to continue the verification process
            if ($verificationState === 'ForgotPassword') {
                $cognito->sendPasswordCode($username);
            }

            // User is currently in the process of verifying their account registration
            if ($verificationState === 'Registration') {
                $cognito->resendConfirmationCode($username);
            }
        }
        catch(AwsException $e) {
            dd($e);
        }

        return redirect()->route('verification.index');
    }

}
