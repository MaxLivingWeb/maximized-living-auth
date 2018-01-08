<?php

namespace App\Helpers;

class AuthenticatedUserHelper
{

    /**
     * Get Authenticated User Permissions
     * @param $user | Cognito User Object
     * @return \Illuminate\Support\Collection|void
     */
    public static function getUserPermissions($user)
    {
        if (!isset($user['custom:permissions'])) {
            return;
        }

        // Permission Values as they are saved into the Maximizedliving API
        $availablePermissions = [
            'dashboard-usermanagement',
            'dashboard-commissions',
            'dashboard-wholesaler',
            'public-website',
            'contentportal'
        ];

        $userPermissions = explode(',', $user['custom:permissions']);

        return collect($availablePermissions)
            ->mapWithKeys(function($permission) use($userPermissions) {
                return [
                    $permission => collect($userPermissions)->contains($permission)
                ];
            });
    }

    /**
     * Check if User is admin based on the provided permissions array
     * @param $user
     * @return bool
     */
    public static function checkIfAdmin($user)
    {
        $permissions = self::getUserPermissions($user);

        if (is_null($permissions)) {
            return false;
        }

        return ($permissions->get('dashboard-usermanagement')
            && $permissions->get('dashboard-commissions')
            && $permissions->get('dashboard-wholesaler')
        );
    }

    /**
     * Check if User is an affiliate by checking `cognito:username` user data
     * @param $user
     * @return bool
     */
    public static function checkIfAffiliate($user)
    {
        $maxlivingAPI = new MaximizedLivingAPI();

        $affiliate = (array)$maxlivingAPI->getUserAffiliate($user['cognito:username']);

        return !empty($affiliate);
    }

}
