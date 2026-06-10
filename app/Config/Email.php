<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use Config\App as AppConfig;

class Email extends BaseConfig
{
    private const SETTING_KEYS = [
        'email_from_email',
        'email_from_name',
        'email_protocol',
        'email_mail_path',
        'email_smtp_host',
        'email_smtp_user',
        'email_smtp_pass',
        'email_smtp_port',
        'email_smtp_crypto',
        'email_smtp_helo_host',
    ];

    public string $fromEmail  = 'no-reply@fkmp-smartlab.local';
    public string $fromName   = 'FKMP Smart Lab';
    public string $recipients = '';
    public string $userAgent = 'CodeIgniter';
    public string $protocol = 'mail';
    public string $mailPath = '/usr/sbin/sendmail';
    public string $SMTPHost = '';
    public string $SMTPUser = '';
    public string $SMTPPass = '';
    public int $SMTPPort = 25;
    public int $SMTPTimeout = 5;
    public bool $SMTPKeepAlive = false;
    public string $SMTPCrypto = 'tls';
    public string $SMTPHeloHost = '';
    public bool $wordWrap = true;
    public int $wrapChars = 76;
    public string $mailType = 'html';
    public string $charset = 'UTF-8';
    public bool $validate = false;
    public int $priority = 3;
    public string $CRLF = "\r\n";
    public string $newline = "\r\n";
    public bool $BCCBatchMode = false;
    public int $BCCBatchSize = 200;
    public bool $DSN = false;

    public function __construct()
    {
        parent::__construct();

        $settings = $this->runtimeSettings();

        $this->fromEmail = $this->stringRuntimeValue('email.fromEmail', 'email_from_email', $this->resolvedFallbackFromEmail(), $settings);
        $this->fromName = $this->stringRuntimeValue('email.fromName', 'email_from_name', $this->fromName, $settings);
        $this->protocol = strtolower($this->stringRuntimeValue('email.protocol', 'email_protocol', $this->protocol, $settings));
        $this->mailPath = $this->stringRuntimeValue('email.mailPath', 'email_mail_path', $this->mailPath, $settings);
        $this->SMTPHost = $this->stringRuntimeValue('email.SMTPHost', 'email_smtp_host', $this->SMTPHost, $settings);
        $this->SMTPUser = $this->stringRuntimeValue('email.SMTPUser', 'email_smtp_user', $this->SMTPUser, $settings);
        $this->SMTPPass = $this->normalizeSmtpPassword(
            $this->runtimeSecretValue('email.SMTPPass', 'email_smtp_pass', $this->SMTPPass, $settings),
            $this->SMTPHost
        );
        $this->SMTPPort = $this->intRuntimeValue('email.SMTPPort', 'email_smtp_port', $this->SMTPPort, $settings);
        $this->SMTPCrypto = strtolower($this->stringRuntimeValue('email.SMTPCrypto', 'email_smtp_crypto', $this->SMTPCrypto, $settings));
        $this->SMTPHeloHost = $this->stringRuntimeValue('email.SMTPHeloHost', 'email_smtp_helo_host', $this->SMTPHeloHost, $settings);
        $this->mailType = trim((string) env('email.mailType', $this->mailType));
    }

    private function resolvedFallbackFromEmail(): string
    {
        $configured = trim($this->fromEmail);
        if ($configured !== '' && ! str_ends_with(strtolower($configured), '.local')) {
            return $configured;
        }

        $host = parse_url((string) config(AppConfig::class)->baseURL, PHP_URL_HOST);
        if (is_string($host) && $host !== '') {
            return 'no-reply@' . preg_replace('/^www\./i', '', strtolower($host));
        }

        return $this->fromEmail;
    }

    private function normalizeSmtpPassword(string $password, string $host): string
    {
        $password = trim($password);
        $host = strtolower(trim($host));

        if ($password === '') {
            return $password;
        }

        if (
            ($host === 'smtp.gmail.com' || $host === 'smtp.googlemail.com')
            && preg_match('/\s/', $password) === 1
        ) {
            $compact = preg_replace('/\s+/', '', $password) ?? $password;
            if (strlen($compact) === 16) {
                return $compact;
            }
        }

        return $password;
    }

    private function runtimeSettings(): array
    {
        static $cache;
        if (is_array($cache)) {
            return $cache;
        }

        $cache = [];

        try {
            $db = \Config\Database::connect();
            if (! $db->tableExists('settings')) {
                return $cache;
            }

            $rows = $db->table('settings')
                ->select('`key`, value')
                ->where('class', 'system')
                ->whereIn('key', self::SETTING_KEYS)
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $key = (string) ($row['key'] ?? '');
                if ($key === '') {
                    continue;
                }

                $cache[$key] = is_string($row['value']) ? trim($row['value']) : $row['value'];
            }
        } catch (\Throwable $e) {
            log_message('debug', 'Email runtime settings unavailable: ' . $e->getMessage());
        }

        return $cache;
    }

    private function stringRuntimeValue(string $envKey, string $settingKey, string $default, array $settings): string
    {
        $env = env($envKey, null);
        if (is_string($env) && trim($env) !== '') {
            return trim($env);
        }

        $setting = $settings[$settingKey] ?? null;
        if (is_string($setting) && trim($setting) !== '') {
            return trim($setting);
        }

        return trim($default);
    }

    private function runtimeSecretValue(string $envKey, string $settingKey, string $default, array $settings): string
    {
        $env = env($envKey, null);
        if (is_string($env) && trim($env) !== '') {
            return $env;
        }

        $setting = $settings[$settingKey] ?? null;
        if (is_string($setting) && trim($setting) !== '') {
            return $setting;
        }

        return $default;
    }

    private function intRuntimeValue(string $envKey, string $settingKey, int $default, array $settings): int
    {
        $env = env($envKey, null);
        if ($env !== null && trim((string) $env) !== '') {
            return max((int) $env, 1);
        }

        $setting = $settings[$settingKey] ?? null;
        if ($setting !== null && trim((string) $setting) !== '') {
            return max((int) $setting, 1);
        }

        return max($default, 1);
    }
}
