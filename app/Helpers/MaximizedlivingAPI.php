<?php

namespace App\Helpers;

use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;

class MaximizedLivingAPI
{
    private $client;

    function __construct()
    {
        $this->client = new GuzzleHttp\Client(['base_uri' => env('MAXLIVING_API_URL'). '/api/']);
    }

    public function getUserAffiliate($userId)
    {
        $result = $this->client->get('user/' . $userId . '/affiliate');

        return json_decode($result->getBody()->getContents());
    }
}
