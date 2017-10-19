<?php

namespace App\Http\Controllers;

use App\Helpers\CognitoHelper;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        //TODO: Get client from request, this will change the front end
        $client = 'clientA';
        session()->put('client', $client);

        $url = $request->input('redirect_uri');

        if($url === null) {
            //check if we have a url in session
            $url = session()->get('redirect_uri');
        }

        $cognito = new CognitoHelper();
        if(!$cognito->checkCallbackUrl($url)) {
            return view('error', ['error' => 'Invalid redirect_url']);
        }

        session()->put('redirect_uri', $url);

        return view($client . '/login');
    }

    public function login(Request $request)
    {
        $username = $request->input('username');
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
