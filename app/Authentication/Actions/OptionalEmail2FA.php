<?php

namespace App\Authentication\Actions;

use CodeIgniter\Shield\Authentication\Actions\Email2FA;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;

class OptionalEmail2FA extends Email2FA
{
    /**
     * Only create the 2FA identity (which triggers the OTP flow) when the
     * user has opted in. If they haven't, delete any stale identity so that
     * Shield's setAuthAction() finds nothing and logs them in directly.
     */
    public function createIdentity(User $user): string
    {
        if (! (bool) ($user->twofa_enabled ?? false)) {
            model(UserIdentityModel::class)->deleteIdentitiesByType($user, $this->getType());

            return '';
        }

        return parent::createIdentity($user);
    }
}
