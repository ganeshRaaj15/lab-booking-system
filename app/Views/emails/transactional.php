<?php

helper('url');

$heading = trim((string) ($heading ?? 'FKMP Smart Lab'));
$preheader = trim((string) ($preheader ?? $heading));
$eyebrow = trim((string) ($eyebrow ?? 'FKMP Smart Lab'));
$greeting = trim((string) ($greeting ?? ''));
$lead = trim((string) ($lead ?? ''));
$code = trim((string) ($code ?? ''));
$codeLabel = trim((string) ($codeLabel ?? 'Verification Code'));
$actionUrl = trim((string) ($actionUrl ?? ''));
$actionText = trim((string) ($actionText ?? ''));
$secondaryUrl = trim((string) ($secondaryUrl ?? ''));
$secondaryText = trim((string) ($secondaryText ?? ''));
$footerNote = trim((string) ($footerNote ?? ''));
$logoUrl = base_url('images/logo.png');

$paragraphs = array_values(array_filter(array_map(
    static fn($paragraph): string => trim((string) $paragraph),
    is_array($paragraphs ?? null) ? $paragraphs : []
), static fn(string $paragraph): bool => $paragraph !== ''));

$detailsText = trim((string) ($details ?? ''));
$bodyParagraphs = [];

foreach ($paragraphs as $paragraph) {
    if ($detailsText === '' && preg_match('/\R/', $paragraph) === 1) {
        $detailsText = $paragraph;
        continue;
    }

    $bodyParagraphs[] = $paragraph;
}

$detailLines = [];
if ($detailsText !== '') {
    $detailLines = array_values(array_filter(array_map(
        static fn($line): string => trim((string) $line),
        preg_split('/\R+/', $detailsText) ?: []
    ), static fn(string $line): bool => $line !== ''));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <title><?= esc($heading) ?></title>
</head>
<body style="margin:0;padding:0;background-color:#edf6f4;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;">
        <?= esc($preheader) ?>
    </div>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;border-collapse:collapse;background-color:#edf6f4;">
        <tr>
            <td align="center" style="padding:28px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:680px;width:100%;border-collapse:separate;">
                    <tr>
                        <td style="padding:0 0 14px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">
                                <tr>
                                    <td style="padding:0 0 10px 0;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                            <tr>
                                                <td valign="middle" style="padding-right:12px;">
                                                    <img src="<?= esc($logoUrl) ?>" alt="SLAMS" width="52" height="52" style="display:block;width:52px;height:52px;border:0;outline:none;text-decoration:none;border-radius:14px;">
                                                </td>
                                                <td valign="middle">
                                                    <div style="font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1.4;color:#147d75;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;">FKMP UTHM</div>
                                                    <div style="font-family:Arial,Helvetica,sans-serif;font-size:22px;line-height:1.2;color:#10211f;font-weight:700;">Smart Lab Management System</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#0f766e;background-image:linear-gradient(135deg,#0f766e 0%,#115e59 55%,#0b3f3c 100%);border-radius:28px 28px 0 0;padding:18px 28px 0 28px;">
                            <div style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.4;color:#d7f4f0;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">
                                <?= esc($eyebrow) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff;border:1px solid #d8ebe8;border-top:none;border-radius:0 0 28px 28px;padding:30px 28px 26px 28px;">
                            <h1 style="margin:0 0 14px 0;font-family:Arial,Helvetica,sans-serif;font-size:32px;line-height:1.12;color:#10211f;font-weight:800;">
                                <?= esc($heading) ?>
                            </h1>

                            <?php if ($greeting !== ''): ?>
                                <p style="margin:0 0 12px 0;font-family:Arial,Helvetica,sans-serif;font-size:16px;line-height:1.7;color:#1f3633;">
                                    <?= nl2br(esc($greeting)) ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($lead !== ''): ?>
                                <p style="margin:0 0 18px 0;font-family:Arial,Helvetica,sans-serif;font-size:17px;line-height:1.7;color:#45615e;">
                                    <?= nl2br(esc($lead)) ?>
                                </p>
                            <?php endif; ?>

                            <?php foreach ($bodyParagraphs as $paragraph): ?>
                                <p style="margin:0 0 14px 0;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.75;color:#314846;">
                                    <?= nl2br(esc($paragraph)) ?>
                                </p>
                            <?php endforeach; ?>

                            <?php if ($code !== ''): ?>
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:24px 0 20px 0;border-collapse:collapse;">
                                    <tr>
                                        <td style="background-color:#f2fbf9;border:1px solid #b9e3dc;border-radius:22px;padding:20px;text-align:center;">
                                            <div style="font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.4;color:#147d75;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;margin-bottom:10px;">
                                                <?= esc($codeLabel) ?>
                                            </div>
                                            <div style="font-family:Arial,Helvetica,sans-serif;font-size:34px;line-height:1.1;color:#0f172a;font-weight:800;letter-spacing:0.28em;">
                                                <?= esc($code) ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?>

                            <?php if ($detailLines !== []): ?>
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:22px 0 18px 0;border-collapse:collapse;">
                                    <tr>
                                        <td style="background-color:#f8fcfb;border:1px solid #d8ebe8;border-radius:22px;padding:18px 18px 8px 18px;">
                                            <?php foreach ($detailLines as $line): ?>
                                                <?php [$label, $value] = array_pad(explode(':', $line, 2), 2, ''); ?>
                                                <div style="margin:0 0 10px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#27403d;">
                                                    <?php if (trim($value) !== ''): ?>
                                                        <span style="color:#5d7773;font-weight:700;display:inline-block;min-width:104px;"><?= esc(trim($label)) ?>:</span>
                                                        <span><?= esc(trim($value)) ?></span>
                                                    <?php else: ?>
                                                        <span><?= esc($line) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?>

                            <?php if ($actionUrl !== '' && $actionText !== ''): ?>
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0 0 0;border-collapse:collapse;">
                                    <tr>
                                        <td align="center" style="border-radius:14px;background-color:#0f766e;">
                                            <a href="<?= esc($actionUrl) ?>" style="display:inline-block;padding:14px 22px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.2;color:#ffffff;font-weight:700;text-decoration:none;border-radius:14px;">
                                                <?= esc($actionText) ?>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?>

                            <?php if ($secondaryUrl !== '' && $secondaryText !== ''): ?>
                                <p style="margin:14px 0 0 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.7;color:#45615e;">
                                    <a href="<?= esc($secondaryUrl) ?>" style="color:#0f766e;text-decoration:underline;font-weight:700;"><?= esc($secondaryText) ?></a>
                                </p>
                            <?php endif; ?>

                            <?php if ($footerNote !== ''): ?>
                                <p style="margin:22px 0 0 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.7;color:#6a7f7b;">
                                    <?= nl2br(esc($footerNote)) ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 8px 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#6a7f7b;text-align:center;">
                            FKMP Smart Lab Management System<br>
                            This is an automated message. Please do not reply directly to this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
