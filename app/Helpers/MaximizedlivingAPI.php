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

    public function getPermissions()
    {
        $result = $this->client->get('permissions');

        return json_decode($result->getBody()->getContents());
    }

    public function getUserPermissions($permissionSettings = null)
    {
        if ($permissionSettings === null) {
            return;
        }

        $permissions = $this->getPermissions();

        $userPermissions = explode(',', $permissionSettings);

        return collect($permissions)
            ->mapWithKeys(function ($permission) use ($userPermissions) {
                return [
                    $permission->key => collect($userPermissions)->contains($permission->key)
                ];
            })
            ->all();
    }

    public function getUserAffiliate($userId)
    {
        $result = $this->client->get('user/' . $userId . '/affiliate');

        return json_decode($result->getBody()->getContents());
    }
}
