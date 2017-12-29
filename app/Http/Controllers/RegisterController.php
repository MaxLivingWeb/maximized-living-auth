<?php

namespace App\Http\Controllers;

use App\Helpers\CognitoHelper;
use App\Helpers\ShopifyHelper;
use Aws\Exception\AwsException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function index(Request $request)
    {
        return view('register');
    }

    public function registerSubmit(Request $request)
    {
        $request->validate([
            'username' => 'required|email',
            'password' => 'required|min:8|confirmed|regex:/^.*(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!$#%]).*$/',
            'address1' => 'required',
            'zip'      => 'required',
            'country'  => 'required',
            'state'    => 'required',
            'city'     => 'required',
        ]);

        try {
            $country = $request->input('country');
            $state = $request->input('state');
            //Shopify doesnt consider Puerto Rico as a separate country
            if($country === 'Puerto Rico') {
                $country = 'US';
                $state = 'PR';
            }
            $customer = [
                'email' => $request->input('username'),
                'addresses' => [
                    [
                        'address1' => $request->input('address1'),
                        'address2' => $request->input('address2') ?? '',
                        'zip'      => $request->input('zip'),
                        'country'  => $country,
                        'province' => $state,
                        'city'     => $request->input('city'),
                    ]
                ]
            ];

            $shopify = new ShopifyHelper();
            $shopifyId = $shopify->getOrCreateCustomer($customer)->id;

            $cognito = new CognitoHelper();
            $cognito->signup(
                $request->input('username'),
                $request->input('password'),
                strval($shopifyId)
            );

            session()->put('verifyUsername', $request->input('username'));
        }
        catch (AwsException $e) {
            return redirect()->back()->withErrors([$e->getAwsErrorMessage()]);
        }

        return redirect()->route('verify');
    }

    public function verify(Request $request)
    {
        if(session()->has('verifyUsername') && $request->has('verificationCode')) {
            //We have both username and verificationCode, automatically verify the user
            return $this->verifyUser(
                session()->get('verifyUsername'),
                $request->input('verificationCode')
            );
        }

        return view('verify', [
            'askForEmail' => !session()->has('verifyUsername'),
            'verificationCode' => $request->input('verificationCode')
        ]);
    }

    public function verifySubmit(Request $request)
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

        return $this->verifyUser($username, $request->input('verificationCode'));
    }

    private function verifyUser($username, $verificationCode)
    {
        try {
            $cognito = new CognitoHelper();
            $cognito->confirmSignup($username, $verificationCode);
        }
        catch(AwsException $e) {
            return redirect()->back()->withErrors([__('auth.failedToVerify')]);
        }

        session()->forget('verifyUsername');

        return redirect()->route('login')->with('messages', [__('auth.emailVerified')]);
    }
}
