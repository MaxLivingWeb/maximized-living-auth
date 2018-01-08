<?php

namespace App\Helpers;

use Aws\Sdk;
use Illuminate\Routing\Redirector;
use GuzzleHttp;
use GuzzleHttp\Exception\ClientException;

class MaximizedLivingAPI
{
    private $client;

    function __construct()
    {
        $this->client = new GuzzleHttp\Client(['base_uri' => env('MAXLIVING_API_URL'). '/api/']);
    }

    /*
     * Permissions
     */

    public function getUserPermissions($permissionSettings = null)
    {
        if ($permissionSettings === null) {
            return false;
        }

        // Permission Values as they are saved into the Maximizedliving API
        $availablePermissions = [
            'dashboard-usermanagement',
            'dashboard-commissions',
            'dashboard-wholesaler',
            'public-website',
            'contentportal'
        ];

        $userPermissions = explode(',', $permissionSettings);

        return collect($availablePermissions)
            ->mapWithKeys(function($permission) use($userPermissions) {
                return [
                    $permission => collect($userPermissions)->contains($permission)
                ];
            })
            ->all();
    }
}
