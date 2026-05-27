<?php

namespace App\Support;

use App\Imports\RawSheetImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Helpers for the column-mapping import flow: resolve a Filament FileUpload
 * state (temporary upload OR stored path) to a readable file, read its rows /
 * header labels, expose headers as dropdown options, and auto-guess which file
 * column maps to a given system field. Rows are read raw (row 0 = headers) so
 * arbitrary SAP/Thai headers and a leading "#" column survive.
 */
class SheetReader
{
    /** Per-request memos keyed by absolute path (avoid re-parsing per closure). */
    protected static array $headerCache = [];
    protected static array $rowsCache = [];

    /** Resolve a FileUpload state value to an absolute, readable file path. */
    public static function pathFromState(mixed $state): ?string
    {
        if (is_array($state)) {
            $state = reset($state) ?: null;
        }

        if ($state instanceof UploadedFile) {
            $path = $state->getRealPath();
            return ($path !== false && $path !== '') ? $path : null;
        }

        if (is_string($state) && $state !== '') {
            if (is_file($state)) {
                return $state;
            }
            $abs = Storage::disk('local')->path($state);
            return is_file($abs) ? $abs : null;
        }

        return null;
    }

    /** First worksheet as raw rows (row 0 = headers), memoised per path. */
    public static function toRows(string $path): array
    {
        if (array_key_exists($path, static::$rowsCache)) {
            return static::$rowsCache[$path];
        }

        $sheets = Excel::toArray(new RawSheetImport(), $path);

        return static::$rowsCache[$path] = ($sheets[0] ?? []);
    }

    /** Trimmed header labels from row 0 (memoised per path). */
    public static function headers(string $path): array
    {
        if (array_key_exists($path, static::$headerCache)) {
            return static::$headerCache[$path];
        }

        $rows = static::toRows($path);

        return static::$headerCache[$path] = array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);
    }

    public static function headersFromState(mixed $state): array
    {
        $path = static::pathFromState($state);

        return $path ? static::headers($path) : [];
    }

    public static function optionsFromState(mixed $state): array
    {
        return static::optionsFromHeaders(static::headersFromState($state));
    }

    /** Build [label => label] dropdown options, dropping blanks/dupes. */
    public static function optionsFromHeaders(array $headers): array
    {
        $options = [];
        foreach ($headers as $h) {
            $h = trim((string) $h);
            if ($h !== '') {
                $options[$h] = $h;
            }
        }

        return $options;
    }

    /**
     * Auto-guess the file column for a system field: the first header whose
     * normalised text contains one of the candidate needles.
     */
    public static function guess(array $headers, array $needles): ?string
    {
        foreach ($needles as $needle) {
            $needle = mb_strtolower($needle);
            foreach ($headers as $h) {
                $label = trim((string) $h);
                if ($label !== '' && str_contains(mb_strtolower($label), $needle)) {
                    return $label;
                }
            }
        }

        return null;
    }
}
