<?php
/**
 * Pomocné funkce pro veřejné pohledy SAFE (formátování termínu apod.).
 */

declare(strict_types=1);

/** Zkratka pro bezpečný výpis do HTML (escape). */
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

/** Český název měsíce ve 2. pádě (pro "8. listopadu 2025"). */
function cz_month(int $monthNumber): string
{
    $months = [
        1 => 'ledna', 2 => 'února', 3 => 'března', 4 => 'dubna',
        5 => 'května', 6 => 'června', 7 => 'července', 8 => 'srpna',
        9 => 'září', 10 => 'října', 11 => 'listopadu', 12 => 'prosince',
    ];
    return $months[$monthNumber] ?? '';
}

/** Anglický název měsíce (pro "November 8, 2025"). */
function en_month(int $monthNumber): string
{
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];
    return $months[$monthNumber] ?? '';
}

/**
 * Přeloží název kategorie ocenění do angličtiny.
 * Většina kategorií je univerzální (U11, U13s, EXTRA, UMP...), přeloží se
 * jen ty české: MUŽI -> MEN, ŽENY -> WOMEN.
 */
function safe_translate_category(string $category, string $lang = 'cs'): string
{
    if ($lang !== 'en') {
        return $category;
    }
    $map = [
        'MUŽI' => 'MEN',
        'ŽENY' => 'WOMEN',
    ];
    return $map[$category] ?? $category;
}

/**
 * Zformátuje termín akce do čitelné podoby v daném jazyce.
 * Vrací např. "8. listopadu 2025 od 18:00" / "November 8, 2025 from 6:00 PM".
 * Když termín není nastaven, vrátí null.
 */
function safe_format_termin(?string $termin, string $lang = 'cs'): ?string
{
    if (empty($termin)) {
        return null;
    }
    try {
        $dt = new DateTime($termin);
    } catch (\Exception $e) {
        return null;
    }
    $day  = (int)$dt->format('j');
    $mon  = (int)$dt->format('n');
    $year = $dt->format('Y');

    if ($lang === 'en') {
        $time = $dt->format('g:i A');
        return en_month($mon) . " $day, $year from $time";
    }
    $time = $dt->format('H:i');
    return "$day. " . cz_month($mon) . " $year od $time";
}
