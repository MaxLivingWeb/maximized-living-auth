<?php

namespace App\Helpers;

class AuthenticatedUserHelper
{

    /**
     * Get Authenticated User Permissions
     * @param $user
     */
    public static function getUserPermissions($user)
    {
        if (!isset($user['custom:permissions'])) {
            return;
        }

        $maxlivingAPI = new MaximizedLivingAPI();

        return collect($maxlivingAPI->getUserPermissions($user['custom:permissions']));
    }

    /**
     * Get Affiliate Data for User
     * @param $user
     */
    public static function getUserAffiliateData($user)
    {
        $maxlivingAPI = new MaximizedLivingAPI();

        $affiliate = (array)$maxlivingAPI->getUserAffiliate($user['cognito:username']);

        return $affiliate;
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
        $affiliate = self::getUserAffiliateData($user);

        return !empty($affiliate);
    }

}
