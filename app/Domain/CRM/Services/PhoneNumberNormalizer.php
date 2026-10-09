<?php

namespace App\Domain\CRM\Services;

class PhoneNumberNormalizer
{
    /**
     * Normalize a phone number to standard E.164 format.
     * Supports Armenia (+374), Russia (+7), and international formats.
     */
    public static function normalize(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        // Strip whitespace, brackets, hyphens, dots
        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

        if (empty($cleaned)) {
            return null;
        }

        // Armenia local format: 0XX XXXXXX (9 digits) -> +374XXXXXXXX
        if (preg_match('/^0(\d{8})$/', $cleaned, $matches)) {
            return '+374'.$matches[1];
        }

        // Armenia without plus: 374XXXXXXXX (11 digits) -> +374XXXXXXXX
        if (preg_match('/^374(\d{8})$/', $cleaned, $matches)) {
            return '+374'.$matches[1];
        }

        // Russia local format: 8XXXXXXXXXX (11 digits) -> +7XXXXXXXXXX
        if (preg_match('/^8([3489]\d{9})$/', $cleaned, $matches)) {
            return '+7'.$matches[1];
        }

        // Russia without plus: 7XXXXXXXXXX (11 digits) -> +7XXXXXXXXXX
        if (preg_match('/^7([3489]\d{9})$/', $cleaned, $matches)) {
            return '+7'.$matches[1];
        }

        // Already has plus
        if (str_starts_with($cleaned, '+')) {
            return $cleaned;
        }

        // Default: add plus if international digits
        return '+'.$cleaned;
    }

    /**
     * Validate whether a phone number is plausible E.164.
     */
    public static function isValid(?string $phone): bool
    {
        $normalized = self::normalize($phone);
        if (! $normalized) {
            return false;
        }

        // Armenia specific: +374 followed by 8 digits
        if (str_starts_with($normalized, '+374')) {
            return (bool) preg_match('/^\+374[1-9]\d{7}$/', $normalized);
        }

        // Russia specific: +7 followed by 10 digits
        if (str_starts_with($normalized, '+7')) {
            return (bool) preg_match('/^\+7[3489]\d{9}$/', $normalized);
        }

        // General E.164: + followed by 7 to 15 digits
        return (bool) preg_match('/^\+[1-9]\d{6,14}$/', $normalized);
    }

    /**
     * Format normalized phone number for user-friendly display.
     */
    public static function formatDisplay(?string $phone): string
    {
        $normalized = self::normalize($phone);
        if (! $normalized) {
            return '';
        }

        // +374 91 123456 -> +374 (91) 12-34-56
        if (preg_match('/^\+374(\d{2})(\d{2})(\d{2})(\d{2})$/', $normalized, $m)) {
            return "+374 ({$m[1]}) {$m[2]}-{$m[3]}-{$m[4]}";
        }

        // +7 916 1234567 -> +7 (916) 123-45-67
        if (preg_match('/^\+7(\d{3})(\d{3})(\d{2})(\d{2})$/', $normalized, $m)) {
            return "+7 ({$m[1]}) {$m[2]}-{$m[3]}-{$m[4]}";
        }

        return $normalized;
    }
}
