<?php

namespace App\Helpers;

use phpseclib\Math\BigInteger;

class SRPHelper
{
    private $N;
    private $g;
    private $a;
    private $A;
    private $U;
    private $k;

    function __construct()
    {
        $this->N = gmp_init('FFFFFFFFFFFFFFFFC90FDAA22168C234C4C6628B80DC1CD129024E088A67CC74020BBEA63B139B22514A08798E3404DDEF9519B3CD3A431B302B0A6DF25F14374FE1356D6D51C245E485B576625E7EC6F44C42E9A637ED6B0BFF5CB6F406B7EDEE386BFB5A899FA5AE9F24117C4B1FE649286651ECE45B3DC2007CB8A163BF0598DA48361C55D39A69163FA8FD24CF5F83655D23DCA3AD961C62F356208552BB9ED529077096966D670C354E4ABC9804F1746C08CA18217C32905E462E36CE3BE39E772C180E86039B2783A2EC07A28FB5C55DF06F4C52C9DE2BCBF6955817183995497CEA956AE515D2261898FA051015728E5A8AAAC42DAD33170D04507A33A85521ABDF1CBA64ECFB850458DBEF0A8AEA71575D060C7DB3970F85A6E1E4C7ABF5AE8CDB0933D71E8C94E04A25619DCEE3D2261AD2EE6BF12FFA06D98A0864D87602733EC86A64521F2B18177B200CBBE117577A615D6C770988C0BAD946E208E24FA074E5AB3143DB5BFCE0FD108E4B82D120A93AD2CAFFFFFFFFFFFFFFFF', 16);
        $this->g = gmp_init(2);

        $this->k = gmp_init($this->hexHash('00' . $this->toHex($this->N) . $this->toHex($this->g)), 16);

        $this->a = $this->generateRandomSmallA();
    }

    public function getSecretHash($username)
    {
        return base64_encode(hash_hmac('sha256', $username . env('AWS_COGNITO_APP_CLIENT_ID'), env('AWS_COGNITO_APP_CLIENT_SECRET'), true));
    }

    public function generateRandomSmallA()
    {
        $randomBigInt = gmp_random_bits(1024);

        return gmp_mod($randomBigInt, $this->N);
    }

    public function calculateA()
    {
        $this->A = gmp_powm($this->g, $this->a, $this->N);

        return $this->A;
    }

    public function calculateU($A, $B)
    {
        $this->U = gmp_init($this->hexHash($this->padHex($A) . $this->padHex($B)), 16);

        return $this->U;
    }

    public function getPasswordAuthenticationKey($username, $password, $serverBValue, $salt)
    {
        $serverBValue = gmp_init($serverBValue, 16);
        $salt = gmp_init($salt, 16);

        if (gmp_mod($serverBValue, $this->N) === GMP_ROUND_ZERO) {
            throw new \Exception('B cannot be zero.');
        }

        $this->U = $this->calculateU($this->A, $serverBValue);

        if ($this->U === GMP_ROUND_ZERO) {
            throw new \Exception('U cannot be zero.');
        }

        $usernamePasswordHash = hash('sha256', $this->getPoolName() . $username . ':' . $password);

        $x = gmp_init($this->hexHash($this->padHex($salt) . $usernamePasswordHash), 16);

        $gModPowXN = gmp_powm($this->g, $x, $this->N);

        $intValue2 = gmp_sub($serverBValue, gmp_mul($this->k, $gModPowXN));

        $sValue = gmp_mod(gmp_powm($intValue2, gmp_add($this->a, gmp_mul($this->U, $x)), $this->N), $this->N);

        $hkdf = hash_hkdf('sha256', hex2bin($this->padHex($sValue)), 16, 'Caldera Derived Key', hex2bin($this->padHex($this->U)));

        return $hkdf;
    }

    public function toHex($gmp)
    {
        return bin2hex(gmp_export($gmp));
    }

    private function hexHash($hexStr)
    {
        return $this->toHex(gmp_init(hash('sha256', hex2bin($hexStr)), 16));
    }


    private function padHex($gmp)
    {
        $bigInt = new BigInteger($gmp);
        return $bigInt->toHex(true);
    }

    public function getPoolName()
    {
        $poolId = env('AWS_COGNITO_USER_POOL_ID');
        return substr($poolId, strpos($poolId, "_") + 1);
    }
}
