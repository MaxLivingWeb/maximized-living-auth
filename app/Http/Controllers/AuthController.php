<?php

namespace App\Http\Controllers;

use App\Helpers\SRPHelper;
use Aws\Exception\AwsException;
use Aws\Sdk;

class AuthController extends Controller
{
    private $client;

    function __construct()
    {
        $sharedConfig = [
            'region'  => 'us-east-2',
            'version' => '2016-04-18'
        ];

        $sdk = new Sdk($sharedConfig);

        $this->client = $sdk->createCognitoIdentityProvider();
    }

    public function index()
    {
        // TEMP
        $username = 'j.deridder@arcane.ws';
        $password = 'Tesser4514!';


        $srp = new SRPHelper();
        $secrethash = $srp->getSecretHash($username);
        $srpA = $srp->calculateA();

        $result = $this->client->initiateAuth([
            'AuthFlow' => 'USER_SRP_AUTH',
            'AuthParameters' => [
                'USERNAME'      => $username,
                'SRP_A'         => $srp->toHex($srpA),
                'SECRET_HASH'   => $secrethash
            ],
            'ClientId'       => env('AWS_COGNITO_APP_CLIENT_ID')
        ]);

        $challengeParams = $result->get('ChallengeParameters');
        $dateNow = gmdate('D M d H:i:s \U\T\C Y');
        $username = $challengeParams['USER_ID_FOR_SRP'];

        $hkdf = $srp->getPasswordAuthenticationKey($username, $password, $challengeParams['SRP_B'], $challengeParams['SALT']);

        $decodedSecret = base64_decode($challengeParams['SECRET_BLOCK']);

        $content = $srp->getPoolName() . $username . $decodedSecret . $dateNow;

        $signatureString = base64_encode(hash_hmac('sha256', $content, $hkdf, true));

        try {
            $result2 = $this->client->respondToAuthChallenge([
                'ChallengeName' => $result->get('ChallengeName'),
                'ChallengeResponses' => [
                    'PASSWORD_CLAIM_SIGNATURE' => $signatureString,
                    'PASSWORD_CLAIM_SECRET_BLOCK' => $challengeParams['SECRET_BLOCK'],
                    'TIMESTAMP' => $dateNow,
                    'USERNAME' => $username,
                    'SECRET_HASH' => $srp->getSecretHash($username)
                ],
                'ClientId' => env('AWS_COGNITO_APP_CLIENT_ID')
            ]);

            dd($result2);
        }
        catch(AwsException $e)
        {
            dd($e);
        }



    }
}
