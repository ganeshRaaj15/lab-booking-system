<?php

namespace App\Authentication\Actions;

use App\Authentication\OtpPolicy;
use CodeIgniter\Shield\Authentication\Actions\Email2FA;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;

class OptionalEmail2FA extends Email2FA
{
    /**
     * Require OTP for all non-admin password logins. Admin users can still
     * opt in explicitly with the account-level twofa_enabled preference.
     */
    public function createIdentity(User $user): string
    {
        if (! (new OtpPolicy())->requiresOtp($user)) {
            model(UserIdentityModel::class)->deleteIdentitiesByType($user, $this->getType());

            return '';
        }

        return parent::createIdentity($user);
    }
}
