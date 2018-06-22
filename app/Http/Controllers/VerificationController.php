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

        // Default Verification (ie - Registration)
        if (session()->has('verifyUsername') && $request->has('verificationCode')) {
            //We have both username and verificationCode, automatically verify the user & redirect accordingly
            return $this->confirmVerificationCode(
                session()->get('verifyUsername'),
                $request->input('verificationCode')
            );
        }

        // Simply verify this confirmation code
        // Note: If user's verificationState is set to "ForgotPassword" they will be directed to a 2nd page to fill out the rest of their information... otherwise verification for Account Registration is complete.
        return view('verify', [
            'askForEmail' => !session()->has('verifyUsername'),
            'verificationCode' => $request->input('verificationCode')
        ]);
    }

    /**
     * Submit verification code
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function submitVerificationCode(Request $request)
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

        return $this->confirmVerificationCode($username, $request->input('verificationCode'));
    }

    /**
     * Confirm verification code that was sent through Cognito
     * @param $username
     * @param $verificationCode
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    private function confirmVerificationCode($username, $verificationCode)
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
            // User is currently in the process of updating their resetted password (an admin reset it)
            elseif ($verificationState === 'AdminResetPassword') {
                return redirect()->route('newPasswordFromPasswordReset.enterVerificationCode')->with([
                    'resetPasswordUsername' => $username,
                    'resetPasswordVerificationCode' => $verificationCode
                ]);
            }
            // User is currently in the process of verifying their account registration
            else {
                $cognito->confirmSignup($username, $verificationCode);
                $cognito->updateUserAttribute('custom:verificationState', 'Verified', $username);
                session()->forget('verifyUsername');
                return redirect()->route('login')->with('messages', [__('auth.verificationRegistrationSuccess')]);
            }
        }
        catch(AwsException $e) {
            return redirect()->route('verification.index')->withErrors($e->getAwsErrorMessage());
        }
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

        // Get user and check their current verification state
        $cognitoUser = $cognito->getUser($username);
        $verificationState = $cognito->getUserAttributeValue('custom:verificationState', $cognitoUser['UserAttributes']);

        try {
            // User is currently in the process of verifying their forgotton password, so continue to that view to continue the verification process
            if ($verificationState === 'ForgotPassword') {
                $cognito->sendPasswordCode($username);
            }
            // User is currently in the process of verifying their account registration
            else {
                $cognito->resendConfirmationCode($username);
            }
        }
        catch(AwsException $e) {
            if ($e->getAwsErrorCode() === 'LimitExceededException') {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'Attempt limit exceeded while attempting to resend verification code. Please try again later.'
                    ]);
            }
        }

        return redirect()->route('verification.index');
    }

}
