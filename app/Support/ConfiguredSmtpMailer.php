<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class ConfiguredSmtpMailer
{
    public function isEnabled(): bool
    {
        return SiteSetting::getValue('mail_enabled', '0') === '1';
    }

    public function configure(): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $mailerName = $this->mailerName();
        $usesMailtrapApi = $mailerName === 'mailtrap_api';

        if ($usesMailtrapApi) {
            Config::set('mail.mailers.mailtrap_api', [
                'transport' => 'mailtrap_api',
                'token' => config('services.mailtrap.token'),
                'endpoint' => config('services.mailtrap.endpoint'),
                'category' => config('services.mailtrap.category', 'Transactional'),
            ]);
            Config::set('mail.from.address', SiteSetting::getValue('mail_from_address'));
            Config::set('mail.from.name', SiteSetting::getValue('mail_from_name', config('app.name')));
            Config::set('mail.default', $mailerName);
            app('mail.manager')->purge($mailerName);

            return true;
        }

        $encryptedPassword = SiteSetting::getValue('mail_smtp_password_encrypted');

        if ($encryptedPassword === '') {
            throw new RuntimeException('Le mot de passe SMTP n’est pas configuré.');
        }

        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'scheme' => SiteSetting::getValue('mail_smtp_encryption') === 'ssl' ? 'smtps' : 'smtp',
            'url' => null,
            'host' => SiteSetting::getValue('mail_smtp_host'),
            'port' => (int) SiteSetting::getValue('mail_smtp_port', '587'),
            'username' => SiteSetting::getValue('mail_smtp_username') ?: null,
            'password' => Crypt::decryptString($encryptedPassword),
            'timeout' => 10,
            'local_domain' => parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost',
        ]);
        Config::set('mail.from.address', SiteSetting::getValue('mail_from_address'));
        Config::set('mail.from.name', SiteSetting::getValue('mail_from_name', config('app.name')));
        Config::set('mail.default', $mailerName);
        app('mail.manager')->purge('smtp');

        return true;
    }

    public function mailerName(): string
    {
        return config('services.mailtrap.token') ? 'mailtrap_api' : 'smtp';
    }
}
