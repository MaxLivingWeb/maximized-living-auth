<?php

namespace App\Http\Controllers;

use App\Helpers\CognitoHelper;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        // Pass any additional params to session
        if (!empty($request->input('redirect_path'))
            && (
                $request->input('redirect_path') !== '/login'
                || $request->input('redirect_path') !== 'login'
            )
        ) {
            session()->put('redirect_path', $request->input('redirect_path'));
        }

        // If no params were passed in url, forget these session variables just in case (so that autoredirects may take place)
        if (empty($request->input('redirect_uri'))) {
            session()->forget('redirect_uri');
        }
        if (empty($request->input('redirect_path'))) {
            session()->forget('redirect_path');
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $cognito = new CognitoHelper();

        $request->validate([
            'username' => 'required|email',
            'password' => 'required'
        ]);

        $username = strtolower($request->input('username'));
        $password = $request->input('password');

        try {
            $result = $cognito->login($username, $password);
        }
        catch(AwsException $e) {
            // User is not allowed to proceed. Display error message to reach out to ML Support
            if ($e->getAwsErrorCode() === 'NotAuthorizedException' && $e->getAwsErrorMessage() === 'User is disabled') {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'Your account has been suspended.<br><br>You can log in by creating a new account. Please reach out to MaxLiving support if you have questions.<br><br>Tel: <a href="tel:3219392040">(321) 939-2040</a><br>Email: <a href="mailto:websupport@maxliving.com">websupport@maxliving.com</a>'
                    ]);
            }

            // Admin Reset User's Password, will need to enter submitted Verification Code & create a new password!
            if ($e->getAwsErrorCode() === 'PasswordResetRequiredException') {
                session()->put('resetPasswordUsername', $username);
                return redirect(route('newPasswordFromPasswordReset.enterVerificationCode'));
            }

            // User is not Verified, redirect back to verification page
            if ($e->getAwsErrorCode() === 'UserNotConfirmedException') {
                session()->put('verifyUsername', $username);
                return redirect(route('verification.index'));
            }

            return redirect()->back()->withInput()->withErrors([$e->getAwsErrorMessage()]);
        }

        //get have a result, save the username
        session()->put('username', $username);

        return $cognito->checkForChallenge($result);
    }
}
