<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VerificationFromEmailController extends Controller
{
    /**
     * User clicked link from "Verification" email template
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index(Request $request)
    {
        $params = [];
        if ($request->input('verificationCode')) {
            $params['verificationCode'] = $request->input('verificationCode');
        }

        // Redirect to "Forgot Password Verification" page, since 'forgotPasswordUsername' was saved to the session
        if (session()->has('forgotPasswordUsername')) {
            return redirect(route('forgotPassword.checkVerificationCode').'?'.http_build_query($params));
        }

        // Redirect to "Registration Verification" page
        return redirect(route('register.checkVerificationCode').'?'.http_build_query($params));
    }
}
