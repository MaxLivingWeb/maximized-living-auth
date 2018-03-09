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
    public function resetUserPassword($username)
    {
        $username = strtolower($username);
        $result = $this->client->adminResetUserPassword([
            'Username' => $username,
            'UserPoolId' => env('AWS_COGNITO_USER_POOL_ID')
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
    public function updateForgottenPassword($username, $password, $verificationCode)
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
     * Retrieves a single user from Cognito.
     *
     * @param string $id The Cognito ID of the user to retrieve.
     * @return mixed
     */
    public function getUser($id)
    {
        try {
            return $this->client->adminGetUser([
                'UserPoolId' => env('AWS_COGNITO_USER_POOL_ID'),
                'Username' => $id
            ]);
        }
        catch(AwsException $e) {
            if($e->getStatusCode() !== 400) { // user not found
                abort(
                    $e->getStatusCode(),
                    $e->getAwsErrorMessage()
                );
            }

            return;
        }
    }

    /**
     * Get Cognito User Attribute by passing the Attribute keyname and array of attributes
     * @param $key
     * @param $attributes
     */
    public function getUserAttributeValue($key, $attributes)
    {
        if (empty($attributes)) {
            return;
        }

        return collect($attributes)
            ->where('Name', $key)
            ->first()['Value'];
    }

    /**
     * Update Cognito User attribute
     * @param $key
     * @param $value
     * @param $username (email)
     * @return \Aws\Result
     */
    public function updateUserAttribute($key, $value, $username)
    {
        return $this->client->adminUpdateUserAttributes([
            'UserAttributes' => [
                [
                    'Name' => $key,
                    'Value' => $value,
                ]
            ],
            'UserPoolId' => env('AWS_COGNITO_USER_POOL_ID'),
            'Username' => $username,
        ]);
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
                ],
                [
                    'Name' => 'custom:verificationState',
                    'Value' => 'Registration'
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
     * Re-sends the user a verification code via email or sms
     *
     * @param string $username
     * @return \Aws\Result CodeDeliveryDetails
     */
    public function resendConfirmationCode($username)
    {
        $username = strtolower($username);
        $result = $this->client->resendConfirmationCode([
            'ClientId' => env('AWS_COGNITO_APP_CLIENT_ID'),
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

        // Redirect to the provided link
        if (session()->has('redirect_uri')) {
            // Make sure User's redirect_uri is accessible based on their permissions
            if (session()->get('redirect_uri') == env('MAXLIVING_ADMIN_URL') && !$user->is_admin && !$user->is_affiliate) {
                $params['redirect_path'] = 'account';
                return $this->handle_redirect(env('MAXLIVING_STORE_URL') . $this->url_query($params));
            }
            // Redirect URL is accessible, so go there
            return $this->handle_redirect(session()->get('redirect_uri') . $this->url_query($params));
        }

        // Automatically redirect to AdminPortal
        if ($user->is_admin) {
            return $this->handle_redirect(env('MAXLIVING_ADMIN_URL') . $this->url_query($params));
        }

        // Affiliate User redirects
        if ($user->is_affiliate) {
            // No permissions. Automatically redirect to AdminPortal (the "My Account" page) since permissions are empty. From here, they can click the links to end up wherever.
            if (empty($user->permissions)) {
                $params['redirect_path'] = 'account';
                return $this->handle_redirect(env('MAXLIVING_ADMIN_URL') . $this->url_query($params));
            }

            // Automatically redirect to ContentPortal
            if ($user->permissions->get('contentportal') || $user->permissions->get('contentportal:administrator')) {
                return $this->handle_redirect(env('MAXLIVING_CONTENTPORTAL_URL') . $this->url_query($params));
            }

            // Automatically redirect to Wordpress Site (if affiliate user has location website)
            $affiliateWebsiteURL = $user->affiliate['location']->vanity_website_url ?? null;
            if ($user->permissions->get('public-website')
                && filter_var($affiliateWebsiteURL, FILTER_VALIDATE_URL) !== FALSE
            ) {
                return $this->handle_redirect($affiliateWebsiteURL.'/wp-login.php'.$this->url_query($params));
            }
        }

        // Automatically redirect to the Store
        $params['redirect_path'] = 'account';
        return $this->handle_redirect(env('MAXLIVING_STORE_URL') . $this->url_query($params));
    }

    private function validateAuthenticatedUserByToken($token)
    {
        $loader = new Loader();
        $user = $loader->load($token)->getPayload();

        // Tidy up user data to sendback
        return (object)[
            'email' => $user['email'],
            'permissions' => AuthenticatedUserHelper::getUserPermissions($user),
            'affiliate' => AuthenticatedUserHelper::getUserAffiliateData($user),
            'is_admin' => AuthenticatedUserHelper::checkIfAdmin($user),
            'is_affiliate' => AuthenticatedUserHelper::checkIfAffiliate($user)
        ];
    }

    /**
     * Forget session variables (needed only for logging in) and then handle the redirect
     * @param $url
     * @return \Illuminate\Http\RedirectResponse|Redirector
     */
    private function handle_redirect($to)
    {
        session()->forget('redirect_uri');
        session()->forget('redirect_path');

        return redirect($to);
    }

    private function url_query($params)
    {
        return '?' . http_build_query($params, '', '&');
    }

}
