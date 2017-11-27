<?php

namespace App\Http\Controllers;

use App\Helpers\CognitoHelper;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        //TODO: Get client from request (which will change the front end). This identifies which location the client came from (store, public site, content portal, etc)
        $client = 'client_store';
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
