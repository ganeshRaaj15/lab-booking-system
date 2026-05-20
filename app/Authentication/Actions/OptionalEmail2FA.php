<?php

namespace App\Authentication\Actions;

use CodeIgniter\Shield\Authentication\Actions\Email2FA;
use CodeIgniter\Shield\Entities\User;

class OptionalEmail2FA extends Email2FA
{
    public function hasCompleted(User $user): bool
    {
        // If the user has not opted in to 2FA, treat the action as already done.
        if (! (bool) ($user->twofa_enabled ?? false)) {
            return true;
        }

        return parent::hasCompleted($user);
    }
}
