<?php

namespace App\Helpers;

use App\Helpers\CognitoHelper;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;

class VerificationHelper
{
    public static function confirmVerificationCode($username, $verificationCode)
    {
        try {
            $cognito = new CognitoHelper();
            $cognito->confirmSignup($username, $verificationCode);

            session()->forget('verifyUsername');

            return redirect()->route('login')->with('messages', [__('auth.emailVerified')]);
        }
        catch(AwsException $e) {
            return redirect()->back()->withErrors([__('auth.failedToVerify')]);
        }
    }
}
