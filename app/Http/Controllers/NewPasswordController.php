<?php

namespace App\Http\Controllers;

use App\Helpers\CognitoHelper;
use Illuminate\Http\Request;
use Aws\Exception\AwsException;

class NewPasswordController extends Controller
{
    public function index(Request $request)
    {
        return view(session()->get('client') . '/new-password');
    }

    public function updatePassword(Request $request)
    {
        $username = session()->get('username');
        $password = $request->input('password');
        $confirmPassword = $request->input('confirmPassword');

        //TODO: Validate

        $cognito = new CognitoHelper();
        try {
            $result = $cognito->newPassword($username, $password);
        }
        catch(AwsException $e) {
            return redirect()->back()->withErrors([$e->getAwsErrorMessage()]);
        }

        return $cognito->checkForChallenge($result);
    }
}
