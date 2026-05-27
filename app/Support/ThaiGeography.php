<?php

namespace App\Support;

/**
 * Thai geography lookup (province → district → subdistrict → postcode).
 *
 * Backed by a compact dataset at database/data/thai/thai-geo.json
 * (derived from the kongvut/thai-province-data dataset).
 * Shape:
 *   provinces:    {id:name}
 *   districts:    {provinceId:{districtId:name}}
 *   subdistricts: {districtId:{subdistrictId:name}}   ← added after running thai:update-geo
 *   zips:         {subdistrictId:zip}                 ← keyed by subdistrict when subdistricts present
 *                 {districtId:zip}                    ← keyed by district in legacy format
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

            static::$data += ['provinces' => [], 'districts' => [], 'subdistricts' => [], 'zips' => []];
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

    /** @return array<string,string> [subdistrictId => name] for the given district */
    public static function subdistricts(string|int|null $districtId): array
    {
        if (! $districtId) {
            return [];
        }

        return static::data()['subdistricts'][(string) $districtId] ?? [];
    }

    /** Returns true if subdistrict data is loaded in the geo file */
    public static function hasSubdistricts(): bool
    {
        return ! empty(static::data()['subdistricts']);
    }

    public static function zipForDistrict(string|int|null $districtId): ?string
    {
        if (! $districtId) {
            return null;
        }

        return static::data()['zips'][(string) $districtId] ?? null;
    }

    public static function zipForSubdistrict(string|int|null $subdistrictId): ?string
    {
        if (! $subdistrictId) {
            return null;
        }

        return static::data()['zips'][(string) $subdistrictId] ?? null;
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

    public static function subdistrictName(string|int|null $districtId, string|int|null $subdistrictId): ?string
    {
        if (! $districtId || ! $subdistrictId) {
            return null;
        }

        return static::data()['subdistricts'][(string) $districtId][(string) $subdistrictId] ?? null;
    }
}
