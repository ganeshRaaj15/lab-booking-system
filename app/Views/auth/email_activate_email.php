<?php

echo view('emails/transactional', [
    'preheader' => 'Finish setting up your FKMP Smart Lab account.',
    'eyebrow' => 'Account Activation',
    'heading' => 'Verify your email address',
    'greeting' => 'Hello ' . ($user->username ?? 'there') . ',',
    'lead' => 'Enter this activation code to finish setting up your FKMP Smart Lab account.',
    'code' => (string) ($code ?? ''),
    'codeLabel' => 'Activation Code',
    'footerNote' => 'If you did not create this account, you can ignore this email.',
]);
