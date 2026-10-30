<?php

namespace App\Support;

use App\Models\SiteSetting;
use NumberFormatter;

class CurrencyFormatter
{
    public function code(): string
    {
        return strtoupper(SiteSetting::getValue('currency_code', 'EUR'));
    }

    public function symbol(): string
    {
        return SiteSetting::getValue('currency_symbol', '€');
    }

    public function fractionDigits(?string $currencyCode = null): int
    {
        return in_array(strtoupper($currencyCode ?? $this->code()), ['XOF', 'XAF', 'JPY', 'KRW', 'CLP', 'VND'], true) ? 0 : 2;
    }

    public function format(int|float|string $amount, ?string $currencyCode = null, ?string $currencySymbol = null): string
    {
        $locale = app()->getLocale() === 'en' ? 'en_US' : 'fr_FR';
        $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $this->fractionDigits($currencyCode));
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $this->fractionDigits($currencyCode));
        $formatted = $formatter->format((float) $amount);

        if ($formatted === false) {
            throw new \RuntimeException('Impossible de formater le montant.');
        }

        $symbol = $currencySymbol ?? $this->symbol();
        $prefixSymbols = ['$', '£', '¥'];
        $separator = in_array($symbol, $prefixSymbols, true) ? '' : "\u{00A0}";

        return in_array($symbol, $prefixSymbols, true)
            ? $symbol.$formatted
            : $formatted.$separator.$symbol;
    }

    public function minorUnits(int|float|string $amount): int
    {
        return (int) round((float) $amount * (10 ** $this->fractionDigits()));
    }
}
