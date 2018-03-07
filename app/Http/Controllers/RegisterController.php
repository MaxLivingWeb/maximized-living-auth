<?php

namespace App\Http\Controllers;

use App\Helpers\CognitoHelper;
use App\Helpers\ShopifyHelper;
use App\Helpers\VerificationHelper;
use Aws\Exception\AwsException;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * View Registration Form
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('register');
    }

    /**
     * Submit Registration Data
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function submitRegistration(Request $request)
    {
        $validatedData = $request->validate([
            'firstName'    => 'required',
            'lastName'     => 'required',
            'username'     => 'required|email',
            'password'     => 'required|min:8|confirmed|regex:/^.*(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!$#%]).*$/',
            'businessName' => 'required',
            'address1'     => 'required',
            'address2'     => 'nullable',
            'zip'          => 'required',
            'country'      => 'required',
            'state'        => 'required',
            'city'         => 'required',
        ]);

        try {
            $country = $validatedData['country'];
            $state = $validatedData['state'];
            //Shopify doesnt consider Puerto Rico as a separate country
            if($country === 'Puerto Rico') {
                $country = 'US';
                $state = 'PR';
            }
            $customer = [
                'first_name' => $validatedData['firstName'],
                'last_name' => $validatedData['lastName'],
                'email' => $validatedData['username'],
                'addresses' => [
                    (object)[
                        'address1' => $validatedData['address1'],
                        'address2' => $validatedData['address2'] ?? '',
                        'zip'      => $validatedData['zip'],
                        'country'  => $country,
                        'province' => $state,
                        'city'     => $validatedData['city'],
                        'company'  => $validatedData['businessName'],
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

        return redirect()->route('register.checkVerificationCode');
    }

    /**
     * Check Verification code that was sent to Email Address to confirm account status
     * @param Request $request
     * @return $this|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function checkVerificationCode(Request $request)
    {
        // Verify account for Registration
        if (session()->has('verifyUsername') && $request->has('verificationCode')) {
            //We have both username and verificationCode, automatically verify the user & redirect accordingly
            return VerificationHelper::confirmVerificationCode(
                session()->get('verifyUsername'),
                $request->input('verificationCode')
            );
        }

        return view('verify', [
            'askForEmail' => !session()->has('verifyUsername'),
            'verificationCode' => $request->input('verificationCode')
        ]);
    }

    /**
     * Submit Verification Code to complete confirmation process for account
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

        return VerificationHelper::confirmVerificationCode($username, $request->input('verificationCode'));
    }
}
