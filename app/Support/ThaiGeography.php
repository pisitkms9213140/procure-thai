<?php

namespace App\Support;

/**
 * Thai geography lookup (province → district → postcode).
 *
 * Backed by a compact dataset at database/data/thai/thai-geo.json
 * (derived from the kongvut/thai-province-data dataset).
 * Shape: { provinces: {id:name}, districts: {provinceId:{id:name}}, zips: {districtId:zip} }
 */
class ThaiGeography
{
    protected static ?array $data = null;

    protected static function data(): array
    {
        if (static::$data === null) {
            $path = base_path('database/data/thai/thai-geo.json');

            static::$data = is_file($path)
                ? (json_decode(file_get_contents($path), true) ?: [])
                : [];

            static::$data += ['provinces' => [], 'districts' => [], 'zips' => []];
        }

        return static::$data;
    }

    /** @return array<string,string> [provinceId => name], sorted by name */
    public static function provinces(): array
    {
        return static::data()['provinces'];
    }

    /** @return array<string,string> [districtId => name] for the given province */
    public static function districts(string|int|null $provinceId): array
    {
        if (! $provinceId) {
            return [];
        }

        return static::data()['districts'][(string) $provinceId] ?? [];
    }

    public static function zipForDistrict(string|int|null $districtId): ?string
    {
        if (! $districtId) {
            return null;
        }

        return static::data()['zips'][(string) $districtId] ?? null;
    }

    public static function provinceName(string|int|null $provinceId): ?string
    {
        if (! $provinceId) {
            return null;
        }

        return static::data()['provinces'][(string) $provinceId] ?? null;
    }

    public static function districtName(string|int|null $provinceId, string|int|null $districtId): ?string
    {
        if (! $provinceId || ! $districtId) {
            return null;
        }

        return static::data()['districts'][(string) $provinceId][(string) $districtId] ?? null;
    }
}
