<?php

namespace App\Support;

use App\Imports\RawSheetImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Helpers for the column-mapping import flow: read an uploaded .xlsx into raw
 * rows, expose its header labels as dropdown options, and auto-guess which
 * file column maps to a given system field.
 */
class SheetReader
{
    /** Normalise a Filament FileUpload state value to an UploadedFile (or null). */
    public static function fileFromState(mixed $state): ?UploadedFile
    {
        if (is_array($state)) {
            $state = reset($state) ?: null;
        }

        return $state instanceof UploadedFile ? $state : null;
    }

    /** First worksheet as raw rows (row 0 = headers). */
    public static function toRows(UploadedFile|string $file): array
    {
        $sheets = Excel::toArray(new RawSheetImport(), $file);

        return $sheets[0] ?? [];
    }

    /** Trimmed header labels from row 0. */
    public static function headers(UploadedFile|string $file): array
    {
        $rows = static::toRows($file);

        return array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);
    }

    /** Read headers directly from a Filament FileUpload state value. */
    public static function headersFromState(mixed $state): array
    {
        $file = static::fileFromState($state);

        return $file ? static::headers($file) : [];
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
     * Auto-guess the file column for a system field: return the first header
     * whose normalised text contains one of the candidate needles.
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
