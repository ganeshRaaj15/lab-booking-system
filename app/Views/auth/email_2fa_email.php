<?php

echo view('emails/transactional', [
    'preheader' => 'Your FKMP Smart Lab verification code is ready.',
    'eyebrow' => 'Secure Sign-In',
    'heading' => 'Your authentication code',
    'greeting' => 'Hello ' . ($user->username ?? 'there') . ',',
    'lead' => 'Use this verification code to complete your sign-in. It expires in 10 minutes and should not be shared with anyone.',
    'code' => (string) ($code ?? ''),
    'codeLabel' => 'Verification Code',
    'footerNote' => 'If you did not try to sign in, you can ignore this email and consider changing your password.',
]);
