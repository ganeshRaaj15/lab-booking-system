<?php

namespace App\Libraries;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;

class AccountRecoveryService
{
    /**
     * @param array{audience?: 'web'|'native'} $options
     */
    public function sendLoginLink(User $user, array $options = []): bool
    {
        if (empty($user->id)) {
            return false;
        }

        $email = $this->emailForUser($user);
        if ($email === '') {
            log_message('warning', 'Cannot send account recovery link: user {id} has no email identity.', ['id' => $user->id]);

            return false;
        }

        $token = bin2hex(random_bytes(32));

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);
        $identityModel->deleteIdentitiesByType($user, Session::ID_TYPE_MAGIC_LINK);

        $identityId = $identityModel->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => hash('sha256', $token),
            'expires' => Time::now()->addSeconds(setting('Auth.magicLinkLifetime')),
        ], true);

        if (! $this->sendEmail($user, $email, $token)) {
            if ($identityId) {
                $identityModel->delete($identityId);
            }

            return false;
        }

        return true;
    }

    private function emailForUser(User $user): string
    {
        try {
            return strtolower(trim((string) ($user->email ?? $user->getEmail() ?? '')));
        } catch (\Throwable $e) {
            log_message('error', 'Unable to read recovery email for user {id}: {message}', [
                'id'      => $user->id,
                'message' => $e->getMessage(),
            ]);

            return '';
        }
    }

    private function sendEmail(User $user, string $emailAddress, string $token): bool
    {
        helper('email');

        $email = emailer(['mailType' => 'html'])
            ->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
        $email->setTo($emailAddress);
        $email->setSubject('Secure sign-in link for FKMP Smart Lab');
        $linkOptions = $this->linkOptions($token, $options['audience'] ?? 'web');
        $email->setMessage(view(
            setting('Auth.views')['magic-link-email'],
            [
                'token'      => $token,
                'user'       => $user,
                'expiresIn'  => (int) ceil(setting('Auth.magicLinkLifetime') / MINUTE),
                'primaryUrl' => $linkOptions['primaryUrl'],
                'primaryCta' => $linkOptions['primaryCta'],
                'secondaryUrl' => $linkOptions['secondaryUrl'],
                'secondaryCta' => $linkOptions['secondaryCta'],
            ],
            ['debug' => false],
        ));

        if ($email->send(false) === false) {
            log_message('error', 'Unable to send account recovery email for user {id}: {debug}', [
                'id'    => $user->id,
                'debug' => $email->printDebugger(['headers']),
            ]);

            return false;
        }

        $email->clear();

        return true;
    }

    /**
     * @return array{
     *     primaryUrl: string,
     *     primaryCta: string,
     *     secondaryUrl: string|null,
     *     secondaryCta: string|null
     * }
     */
    private function linkOptions(string $token, string $audience): array
    {
        $encodedToken = rawurlencode($token);
        $verifyUrl = url_to('verify-magic-link') . '?token=' . $encodedToken;

        if ($audience === 'native') {
            return [
                'primaryUrl' => site_url('login/open-magic-link') . '?token=' . $encodedToken,
                'primaryCta' => 'Open in SLAMS Mobile',
                'secondaryUrl' => $verifyUrl,
                'secondaryCta' => 'Continue in browser',
            ];
        }

        return [
            'primaryUrl' => $verifyUrl,
            'primaryCta' => 'Sign in securely',
            'secondaryUrl' => null,
            'secondaryCta' => null,
        ];
    }
}
