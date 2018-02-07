<?php

namespace App\Helpers;

use App\Helpers\AuthenticatedUserHelper;
use Aws\Sdk;
use Illuminate\Routing\Redirector;
use Jose\Loader;

class CognitoHelper
{
    private $client;
    private $srp;

    function __construct()
    {
        $sharedConfig = [
            'region'  => 'us-east-2',
            'version' => '2016-04-18'
        ];

        $sdk = new Sdk($sharedConfig);

        $this->client = $sdk->createCognitoIdentityProvider();

        $this->srp = new SRPHelper();
    }

    /**
     * Validates the callback URL against the URLs configured in the AWS console
     *
     * @param string $url
     * @return bool
     */
    public function checkCallbackUrl($url)
    {
        //TODO: Enable when https is setup on all sites
        //if(env('APP_ENV') !== 'production') {
            return true;
        //}

        $result = $this->client->describeUserPoolClient([
            'ClientId' => env('AWS_COGNITO_APP_CLIENT_ID'),
            'UserPoolId' => env('AWS_COGNITO_USER_POOL_ID')
        ]);

        $pool = $result->get('UserPoolClient');
        if(!isset($pool['CallbackURLs'])) {
            return false;
        }

        return in_array($url, $pool['CallbackURLs']);
    }

    /**
     * Checks the response from AWS for a challenge. If a challenge is given redirect the user so we can respond to the challenge.
     * If no challenge is given the user is authenticated and can be redirected back to their application
     *
     * @param \Aws\Result AuthenticationResultType $result
     * @return Redirector
     */
    public function checkForChallenge($result)
    {
        $challengeName = $result->get('ChallengeName');
        if($challengeName === null) {
            //No challenge, verify user is authenticated
            if($result->get('AuthenticationResult') !== null) {
                //user has been authenticated, redirect back to application
                return $this->redirectAuthenticatedUser($result->get('AuthenticationResult'));
            }
            return redirect()->route('home');
        }
        else if($challengeName === 'NEW_PASSWORD_REQUIRED') {
            session()->put('cognitoSession', $result->get('Session'));
            return redirect()->route('newPassword');
        }
    }

    /**
     * Attempts to authenticate a user. Initiates authentication with AWS and responds to a PASSWORD_VERIFIER challenge
     *
     * @param string $username
     * @param string $password
     * @return \Aws\Result AuthenticationResultType
     */
    public function login($username, $password)
    {
        $username = strtolower($username);
        $secrethash = $this->srp->getSecretHash($username);
        $srpA = $this->srp->calculateA();

        $initResult = $this->client->initiateAuth([
            'AuthFlow' => 'USER_SRP_AUTH',
            'AuthParameters' => [
                'USERNAME' => $username,
                'SRP_A' => $this->srp->toHex($srpA),
                'SECRET_HASH' => $secrethash
            ],
            'ClientId' => env('AWS_COGNITO_APP_CLIENT_ID')
        ]);

        $challengeParams = $initResult->get('ChallengeParameters');
        $dateNow = gmdate('D M j H:i:s \U\T\C Y');

        $username = $challengeParams['USER_ID_FOR_SRP'];

        $hkdf = $this->srp->getPasswordAuthenticationKey($username, $password, $challengeParams['SRP_B'], $challengeParams['SALT']);

        $content = $this->srp->getPoolName() . $username . base64_decode($challengeParams['SECRET_BLOCK']) . $dateNow;

        $signatureString = base64_encode(hash_hmac('sha256', $content, $hkdf, true));

        $result = $this->client->respondToAuthChallenge([
            'ChallengeName' => $initResult->get('ChallengeName'),
            'ChallengeResponses' => [
                'PASSWORD_CLAIM_SIGNATURE' => $signatureString,
                'PASSWORD_CLAIM_SECRET_BLOCK' => $challengeParams['SECRET_BLOCK'],
                'TIMESTAMP' => $dateNow,
                'USERNAME' => $username,
                'SECRET_HASH' => $this->srp->getSecretHash($username)
            ],
            'ClientId' => env('AWS_COGNITO_APP_CLIENT_ID')
        ]);

        return $result;
    }

    /**
     * Attempts to update a users temporary password. Uses the users authenticated session to update their password
     *
     * @param string $username
     * @param string $password
     * @return \Aws\Result AuthenticationResultType
     */
    public function newPassword($username, $password)
    {
        $username = strtolower($username);
        $result = $this->client->respondToAuthChallenge([
            'ChallengeName' => 'NEW_PASSWORD_REQUIRED',
            'ChallengeResponses' => [
                'NEW_PASSWORD' => $password,
                'USERNAME' => $username,
                'SECRET_HASH' => $this->srp->getSecretHash($username)
            ],
            'ClientId' => env('AWS_COGNITO_APP_CLIENT_ID'),
            'Session' => session()->get('cognitoSession')
        ]);

        return $result;
    }

    /**
     * Sends the user a password reset verification code via email or sms
     *
     * @param string $username
     * @return \Aws\Result CodeDeliveryDetails
     */
    public function sendPasswordCode($username)
    {
        $username = strtolower($username);
        $result = $this->client->forgotPassword([
            'ClientId' => env('AWS_COGNITO_APP_CLIENT_ID'),
            'SecretHash' => $this->srp->getSecretHash($username),
            'Username' => $username,
        ]);

        return $result;
    }

    /**
     * Attempts to update a users temporary password. Uses the users authenticated session to update their password
     *
     * @param string $username
     * @param string $password
     * @param string $verificationCode
     * @return \Aws\Result
     */
    public function updatePassword($username, $password, $verificationCode)
    {
        $username = strtolower($username);
        $result = $this->client->confirmForgotPassword([
            'ClientId' => env('AWS_COGNITO_APP_CLIENT_ID'),
            'ConfirmationCode' => $verificationCode,
            'Password' => $password,
            'SecretHash' => $this->srp->getSecretHash($username),
            'Username' => $username,
        ]);

        return $result;
    }

    /**
     * Registers a new account for the user
     *
     * @param string $username
     * @param string $password
     * @param string $shopifyId
     * @return \Aws\Result
     */
    public function signup($username, $password, $shopifyId)
    {
        $username = strtolower($username);
        $result = $this->client->signUp([
            'ClientId' => env('AWS_COGNITO_APP_CLIENT_ID'),
            'Password' => $password,
            'SecretHash' => $this->srp->getSecretHash($username),
            'Username' => $username,
            'UserAttributes' => [
                [
                    'Name' => 'custom:shopifyId',
                    'Value' => $shopifyId
                ]
            ]
        ]);

        return $result;
    }

    /**
     * Confirms the users email/signup
     *
     * @param string $username
     * @param string $verificationCode
     * @return \Aws\Result
     */
    public function confirmSignup($username, $verificationCode)
    {
        $username = strtolower($username);
        $result = $this->client->confirmSignUp([
            'ClientId' => env('AWS_COGNITO_APP_CLIENT_ID'),
            'ConfirmationCode' => $verificationCode,
            'SecretHash' => $this->srp->getSecretHash($username),
            'Username' => $username
        ]);

        return $result;
    }

    /**
     * Redirects a user back to their callback url along with their IdToken and RefreshToken from AWS
     *
     * @param \Aws\Result AuthenticationResultType $authenticationResults
     * @return Redirector
     */
    private function redirectAuthenticatedUser($authenticationResults)
    {
        session()->forget('cognitoSession');

        //TODO: Refresh token has been removed. We need to find an alternative way to send the refresh token without exceeding the URL length limit
        //'refreshToken=' . $authenticationResults['RefreshToken']
        $params = [
            'idToken' => $authenticationResults['IdToken']
        ];

        if (session()->has('redirect_path')
            && preg_match('/[a-z]/i', session('redirect_path'))
        ) {
            $params['redirect_path'] = session('redirect_path');
            session()->forget('redirect_path');
        }

        $user = $this->validateAuthenticatedUserByToken($authenticationResults['IdToken']);

        // No `redirect_uri` is set, so check User's permissions and redirect where they should be
        if (!session()->has('redirect_uri')) {
            if ($user->is_admin) {
                return redirect(env('MAXLIVING_ADMIN_URL') . $this->url_query($params));
            }
            if ($user->is_affiliate) {
                $params['redirect_path'] = 'account';
                return redirect(env('MAXLIVING_ADMIN_URL') . $this->url_query($params));
            }
            $params['redirect_path'] = 'account';
            return redirect(env('MAXLIVING_STORE_URL') . $this->url_query($params));
        }

        // Make sure User's redirect_uri is accessible based on their permissions
        if (session()->get('redirect_uri') == env('MAXLIVING_ADMIN_URL') && !$user->is_admin && !$user->is_affiliate) {
            $params['redirect_path'] = 'account';
            return redirect(env('MAXLIVING_STORE_URL') . $this->url_query($params));
        }

        return redirect(session()->get('redirect_uri') . $this->url_query($params));
    }

    private function validateAuthenticatedUserByToken($token)
    {
        $loader = new Loader();
        $user = $loader->load($token)->getPayload();

        // Tidy up user data to sendback
        return (object)[
            'email' => $user['email'],
            'permissions' => AuthenticatedUserHelper::getUserPermissions($user),
            'is_admin' => AuthenticatedUserHelper::checkIfAdmin($user),
            'is_affiliate' => AuthenticatedUserHelper::checkIfAffiliate($user)
        ];
    }

    private function url_query($params)
    {
        return '?' . http_build_query($params, '', '&');
    }

}
