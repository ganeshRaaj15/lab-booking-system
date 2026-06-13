<?php

namespace App\Libraries;

use App\Models\SettingsModel;
use Config\App as AppConfig;

class WhatsAppConfiguration
{
    private const DEFAULT_PUBLIC_BASE_URL = 'https://slams.cloud';

    private SettingsModel $settings;

    public function __construct(?SettingsModel $settings = null)
    {
        $this->settings = $settings ?? new SettingsModel();
    }

    public function diagnostics(): array
    {
        return [
            'enabled' => $this->enabled(),
            'callback_url' => $this->callbackUrl(),
            'verify_token' => $this->verifyToken(),
            'has_access_token' => $this->settingValue('whatsapp_access_token') !== null,
            'phone_number_id' => $this->settingValue('whatsapp_phone_number_id') ?? '',
            'business_account_id' => $this->settingValue('whatsapp_business_account_id') ?? '',
            'public_base_url' => $this->publicBaseUrl(),
        ];
    }

    public function enabled(): bool
    {
        return filter_var($this->settingValue('whatsapp_enabled') ?? '0', FILTER_VALIDATE_BOOL);
    }

    public function verifyToken(): string
    {
        return $this->settingValue('whatsapp_verify_token') ?? '';
    }

    public function callbackUrl(): string
    {
        return rtrim($this->publicBaseUrl(), '/') . '/webhooks/whatsapp';
    }

    public function publicBaseUrl(): string
    {
        $candidates = [
            $this->settingValue('whatsapp_public_base_url'),
            env('app.publicSiteUrl'),
            (string) config(AppConfig::class)->baseURL,
            self::DEFAULT_PUBLIC_BASE_URL,
        ];

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeBaseUrl($candidate);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return self::DEFAULT_PUBLIC_BASE_URL;
    }

    private function settingValue(string $key): ?string
    {
        $value = $this->settings->get('system', $key);
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function normalizeBaseUrl($candidate): ?string
    {
        if (! is_string($candidate)) {
            return null;
        }

        $candidate = trim($candidate);
        if ($candidate === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $candidate)) {
            $candidate = 'https://' . ltrim($candidate, '/');
        }

        $parts = parse_url($candidate);
        if (! is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $host = strtolower((string) $parts['host']);
        if (in_array($host, ['localhost', '127.0.0.1'], true) || str_ends_with($host, '.test')) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $scheme = in_array($scheme, ['http', 'https'], true) ? $scheme : 'https';

        $url = $scheme . '://' . $host;
        if (! empty($parts['port'])) {
            $url .= ':' . (int) $parts['port'];
        }

        if (! empty($parts['path']) && $parts['path'] !== '/') {
            $url .= '/' . trim((string) $parts['path'], '/');
        }

        return rtrim($url, '/');
    }
}
