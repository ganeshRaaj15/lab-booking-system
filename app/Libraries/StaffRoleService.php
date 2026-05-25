<?php

namespace App\Libraries;

use CodeIgniter\Shield\Entities\User;

class StaffRoleService
{
    public const DEFAULT_STAFF_EMAIL_DOMAIN = '@uthm.edu.my';

    public function resolveStaffEmailDomain(?string $configuredDomain = null): string
    {
        $domain = $configuredDomain;

        if ($domain === null) {
            $domain = setting('system.staff_email_domain');
            $domain = is_string($domain) ? $domain : '';
        }

        $normalized = $this->normalizeDomain($domain);

        return $normalized !== '' ? $normalized : self::DEFAULT_STAFF_EMAIL_DOMAIN;
    }

    public function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));

        if ($domain === '') {
            return '';
        }

        return '@' . ltrim($domain, '@');
    }

    public function emailMatchesStaffDomain(?string $email, ?string $domain = null): bool
    {
        $email = strtolower(trim((string) $email));

        if ($email === '') {
            return false;
        }

        return str_ends_with($email, $this->resolveStaffEmailDomain($domain));
    }

    public function syncStaffAccess(User $user): bool
    {
        $email = strtolower(trim((string) $user->email));

        if (! $this->emailMatchesStaffDomain($email)) {
            return false;
        }

        // Student domain takes priority: if the email also matches the student domain,
        // leave role assignment to StudentRoleService and skip staff assignment.
        $studentDomain = (new StudentRoleService())->resolveStudentEmailDomain();
        if (str_ends_with($email, $studentDomain)) {
            return false;
        }

        $currentGroups = array_values(array_unique(array_map(
            static fn(string $group): string => strtolower($group),
            $user->getGroups() ?? []
        )));

        $targetGroups = array_values(array_filter(
            $currentGroups,
            static fn(string $group): bool => $group !== 'external'
        ));

        if (! in_array('staff', $targetGroups, true)) {
            $targetGroups[] = 'staff';
        }

        $currentComparison = $currentGroups;
        $targetComparison  = $targetGroups;
        sort($currentComparison);
        sort($targetComparison);

        if ($currentComparison === $targetComparison) {
            return false;
        }

        $user->syncGroups(...$targetGroups);

        return true;
    }
}
