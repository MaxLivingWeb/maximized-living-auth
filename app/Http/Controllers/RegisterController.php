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

    public function submit(Request $request)
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
        }
        catch (AwsException $e) {
            return redirect()->back()->withErrors([$e->getAwsErrorMessage()]);
        }

        return redirect()->route('login');
    }
}
