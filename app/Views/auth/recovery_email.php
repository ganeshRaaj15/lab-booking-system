<?php

$defaultVerifyUrl = url_to('verify-magic-link') . '?token=' . rawurlencode((string) ($token ?? ''));

echo view('emails/transactional', [
    'preheader' => 'Use your one-time FKMP Smart Lab sign-in link.',
    'eyebrow' => 'Secure Sign-In Link',
    'heading' => 'Sign in to FKMP Smart Lab',
    'greeting' => 'Hello ' . ($user->username ?? 'there') . ',',
    'lead' => 'Use this one-time link to sign in securely. It expires in ' . (string) ($expiresIn ?? 15) . ' minutes and can only be used once.',
    'actionUrl' => $primaryUrl ?? $defaultVerifyUrl,
    'actionText' => $primaryCta ?? 'Sign in securely',
    'secondaryUrl' => $secondaryUrl ?? '',
    'secondaryText' => $secondaryCta ?? '',
    'footerNote' => 'If you did not request this link, you can ignore this email. The link will expire automatically.',
]);
