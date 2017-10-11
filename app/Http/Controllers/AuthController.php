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

        //random number, suggestions is a integer at least 256 bits. Use it to calculate A
        $randoma = $srp->generateRandomSmallA();
        $srpA = $srp->calculateA($randoma);

        $result = $this->client->initiateAuth([
            'AuthFlow' => 'USER_SRP_AUTH',
            'AuthParameters' => [
                'USERNAME'      => $username,
                'SRP_A'         => gmp_strval($srpA)
                //'SECRET_HASH'   => $secrethash
            ],
            'ClientId'       => env('AWS_COGNITO_APP_CLIENT_ID')
        ]);

        $challengeParams = $result->get('ChallengeParameters');
        $dateNow = gmdate('D M d H:i:s \U\T\C Y');
        //$dateNow = 'Wed Oct 11 15:04:00 UTC 2017';
        $username = $challengeParams['USER_ID_FOR_SRP'];

        $hkdf = $srp->getPasswordAuthenticationKey($username, $password, $challengeParams['SRP_B'], $challengeParams['SALT']);


        //$byte_poolname = unpack('C*', $srp->getPoolName());
        //$byte_date = unpack('C*', $dateNow);
        //$byte_username = unpack('C*', $username);

        //$byte_secretBlock = unpack('C*', base64_decode($challengeParams['SECRET_BLOCK']));

        //$byte_secretBlock = unpack('C*', base64_decode('iFYZdtVB2dRQkRpoTfXKhWEj+D2N/OMAYn6yT8CrgAYEDzUutYjzLqip+o5E7tTk6TOwIQmCpy53PqF6aS8I9juPqrNG24kkgfR60V99K1Liz+1DpO6gKZtmV23DogbXTdOQmKjehnFCqaZKBukFcKRxzJo3Zk/CxBA9SEGkKZq2u//uEwiPRyz6gxAAobVOdo1yVUtcOuSTE5x6nUEb93hs+2uJydlCwdPrTYjHsplVS/JGbl1RHezX0taGGY8tGAIGFpvgQnobOxnVQGEQYlsqt0OC1nr0aZ9R/eq12w7vJuLJn1/9oEPMNx6goLN9u9wSH8f4vfGGU6wJ99ntpdlGDkFqrrR71aviEpZcUDg5VUgzQ8eqiICJ2839/Taeycp8x8Cxza6n/qeVicJR3LltsBEdQGul0oeDImS2E2o5fzL8KDMOA1xMZ7UpuI8GHEhV+1+0p5AF4jWYM2DGMaZCp36/lHKf7tMfzMDvamq2c0C6BoZTx6ohvqhGbfyRenwhssjpzzzYpR8DKQsZM0TalFdIfvdulthtPn71a96JuibugWK2uC+lZYW0zKL6WsB6evfs5Z1vdC4Vim0w3YcQ6gOMdmICA5fvIPwp74v8f9MKMezHko63Njl45r/Voin++haG1kjaNhrH1Nk33YeQfwWYa7uEe7b4hPtWWc3nW5LBM3JD0h9AV3To5ij96sUe6gxZproCZmmgbkNyxnmjZ8/4XS83+pWsH9twidlQnWi3oaBWD8MnkgOfTHkFeZ9vqu8hZsU+O75tUW9AJBr44iEUEXRiuB/9DkiKrgHxrCChvHJFW83DoYlwxhLQ9w8il7BLcGZIJJYdtbR+P4NwDBGCuKDdtCoND+tzS4zFuxAM6wy9JCq6nuYnVY6GGmwCaoUXXp8mpFmQqedGIJC4dng877r9DbF64bzjsvtQlhu4p43YaY8GbM0c8vp68LvurbVnusrDpXMgDlo7Tx2tyCZIF4rcVqKpSDsgDAnvShCUrsZzA0xtCfs4GqDpXdsA2vNRP8W9S9wLSv6n1MHWISMOWM0LKZ7HVvVrE9WLkYT7YNo+vPx5WlAQ47lIMf4dF1w/bBt7gTumIQSkCe22vS3Q7wJyitsMQ9lVjE2YyNpDAnYAO2wBnh5+LBC7sqdr5x7C8YjOKmO0rE41rJaQk5M+BaiJinwBszpmNalnpYpyWym8dwWkScL28CM8cn1IBCR4vdGYoko1SN+n0F8eaaqonVPdRF58+S1M6pXnBSZ+RpeiSt6wPPc2gRu9JNjETFd73Isl/x5R1nmBE+zE3y/ivQpB4PkVQbLstsE4GHjI5Ej07KEvGHUWZ5bxyaGrpf5C6fKtMJh0rzgmPdPqBUVFpA1FjmdkagDa67lx5HKtNH2+zOySIRQulnbMA9WRxYIruURE0c3H9Idi2ikEqNdk75Ooc7Ejb2glYds/EQH/812oFlwqfreiXeNYcXPhGXSjWPp3EWL3s2MgwaVPGHLYy8Ofn3R9+YmNWT8pIRmm5ySVXvn2EIyyD4XoUUt5aSLoiLeCx5rI6gD/NCY2Fl8PYU7gk237Vf2vDz2QPQc8SzTFNnUbeU+Ta1BmYbJyX+uW/M9Nxb9rsy4/cd+19JxPI0ykzfWmAxmjdgM9AYhOaG8SPKVEn1x2/t5xkJCnCEaaZdl/00VCIgnoqy/q8X/e9rlxuEt6IdK8X3MR'));

        //$content = array_merge($byte_poolname, $byte_username, $byte_secretBlock, $byte_date);

        $decodedSecret = base64_decode($challengeParams['SECRET_BLOCK']);

        $content = $srp->getPoolName() . $username . $decodedSecret . $dateNow;

        //$string = implode(array_map("chr", array(119,69,67,228,250,37,100,137,140,220,67,39,56,15,162,191)));


        //dd(base64_encode(  hash_hmac('sha256', $test, $string, true) ));

        $signatureString = base64_encode(hash_hmac('sha256', $content, $hkdf, true));



        //TEMP FOR DEBUGGING
        echo '<br><br>';
        echo 'signatureString: ' . $signatureString;
        echo '<br><br>';
        echo 'this.largeAValue = new BigInteger("' . gmp_strval($srpA) . '");';
        echo '<br><br>';
        echo 'challengeParameters.SALT = "' . $challengeParams['SALT'] . '";';
        echo '<br><br>';
        echo 'challengeParameters.SECRET_BLOCK = "' . $challengeParams['SECRET_BLOCK'] . '";';
        echo '<br><br>';
        echo 'challengeParameters.SRP_B = "' . $challengeParams['SRP_B'] . '";';
        echo '<br><br>';
        echo 'dateNow = "' . $dateNow . '";';
        //die();

        dump([
            'ChallengeName' => $result->get('ChallengeName'),
            'ChallengeResponses' => [
                'PASSWORD_CLAIM_SIGNATURE' => $signatureString,
                'PASSWORD_CLAIM_SECRET_BLOCK' => $challengeParams['SECRET_BLOCK'],
                'TIMESTAMP' => $dateNow,
                'USERNAME' => $username
                //'SECRET_HASH' => $srp->getSecretHash($username)
            ],
            'ClientId' => env('AWS_COGNITO_APP_CLIENT_ID')
        ]);

        try {
            $result2 = $this->client->respondToAuthChallenge([
                'ChallengeName' => $result->get('ChallengeName'),
                'ChallengeResponses' => [
                    'PASSWORD_CLAIM_SIGNATURE' => $signatureString,
                    'PASSWORD_CLAIM_SECRET_BLOCK' => $challengeParams['SECRET_BLOCK'],
                    'TIMESTAMP' => $dateNow,
                    'USERNAME' => $username
                    //'SECRET_HASH' => $srp->getSecretHash($username)
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
