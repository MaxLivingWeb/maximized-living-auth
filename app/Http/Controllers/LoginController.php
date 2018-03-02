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
        if (!empty($request->input('redirect_path'))) {
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
        $request->validate([
            'username' => 'required|email',
            'password' => 'required'
        ]);

        $username = strtolower($request->input('username'));
        $password = $request->input('password');

        $cognito = new CognitoHelper();
        try {
            $result = $cognito->login($username, $password);
        }
        catch(AwsException $e) {
            return redirect()->back()->withInput()->withErrors([$e->getAwsErrorMessage()]);
        }

        //get have a result, save the username
        session()->put('username', $username);

        return $cognito->checkForChallenge($result);
    }
}
