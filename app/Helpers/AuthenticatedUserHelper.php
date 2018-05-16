<?php

namespace App\Helpers;

class AuthenticatedUserHelper
{

    /**
     * Get Authenticated User Permissions
     * @param $user
     * @return array
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
     * @return array|null
     */
    public static function getUserAffiliateData($user)
    {
        $maxlivingAPI = new MaximizedLivingAPI();

        return $maxlivingAPI->getUserAffiliate($user['cognito:username']) ?? null;
    }

    /**
     * Determines what type of user the current user is
     * @param \stdClass $user
     * @param \stdClass $affiliate
     * @return string
     */
    public static function getCurrentUserType($user, $affiliate): string
    {
        if (self::checkIfAdmin($user)) {
            return 'Admin';
        }

        if (!empty($affiliate)) {
            // user has a Client commission group
            if(preg_match("/^Client\s-\s.+$/", $affiliate->commission->description)) {
                return 'Client';
            }

            // user is a Wholesaler
            if($affiliate->wholesaler === true) {
                return 'Wholesaler';
            }
        }

        return 'Customer';
    }

    /**
     * Check if User is admin based on the provided permissions array
     * @param $user
     * @return bool
     */
    private static function checkIfAdmin($user)
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

}
