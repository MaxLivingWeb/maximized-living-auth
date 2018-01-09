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
        $maxlivingAPI = new MaximizedLivingAPI();

        return collect($maxlivingAPI->getUserPermissions($user['custom:permissions']));
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
