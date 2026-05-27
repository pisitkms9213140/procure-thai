<?php

namespace App\Support;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Storage;

/**
 * Builds a Filament "Import Excel" header action with a column-mapping step:
 * upload .xlsx -> auto-read header row -> map each system field to a file
 * column (auto-guessed, required ones block submit) -> optional category
 * filters (code prefix / group) -> import on confirm.
 */
class MappedImportAction
{
    /**
     * @param  string  $heading  Modal heading.
     * @param  array<int,array{key:string,label:string,required?:bool,guess?:array<int,string>}>  $fields
     * @param  callable(array<string,mixed>,array<string,mixed>):bool  $persist  Receives
     *         [fieldKey => mappedCellValue] and the full raw row [header => cellValue]; return
     *         true if imported, false if skipped.
     * @param  string|null  $prefixFromKey  Field key whose value's prefix (before the first "-")
     *                                       is offered as selectable categories.
     * @param  string|null  $groupFromKey   Field key whose distinct values are offered as
     *                                       selectable groups.
     */
    public static function make(
        string $heading,
        array $fields,
        callable $persist,
        ?string $prefixFromKey = null,
        ?string $groupFromKey = null,
        string $name = 'import',
        string $label = 'นำเข้า Excel',
    ): Action {
        $form = [
            FileUpload::make('file')
                ->label('ไฟล์ Excel (.xlsx)')
                // Linux finfo frequently reports .xlsx as application/zip.
                ->acceptedFileTypes([
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'application/zip',
                    'application/octet-stream',
                ])
                ->disk('local')
                ->directory('imports-tmp')
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, Set $set) use ($fields, $prefixFromKey, $groupFromKey) {
                    try {
                        $headers = SheetReader::headersFromState($state);
                    } catch (\Throwable $e) {
                        Notification::make()->danger()->title('อ่านไฟล์ไม่ได้')->body($e->getMessage())->send();
                        $headers = [];
                    }

                    $guesses = [];
                    foreach ($fields as $f) {
                        $guesses[$f['key']] = $headers ? SheetReader::guess($headers, $f['guess'] ?? [$f['key']]) : null;
                        $set('map_' . $f['key'], $guesses[$f['key']]);
                    }

                    // Build category filter options (read rows once).
                    $rows = $headers ? SheetReader::toRows(SheetReader::pathFromState($state) ?? '') : [];

                    if ($prefixFromKey) {
                        $idx = $guesses[$prefixFromKey] !== null ? array_search($guesses[$prefixFromKey], $headers, true) : false;
                        $prefixes = $idx !== false ? static::distinctPrefixes($rows, $idx) : [];
                        $set('_prefixes', $prefixes);
                        $set('filter_prefixes', array_keys($prefixes)); // default: all ticked
                    }

                    if ($groupFromKey) {
                        $idx = $guesses[$groupFromKey] !== null ? array_search($guesses[$groupFromKey], $headers, true) : false;
                        $groups = $idx !== false ? static::distinctValues($rows, $idx) : [];
                        $set('_groups', $groups);
                        $set('filter_groups', array_keys($groups)); // default: all
                    }
                }),
        ];

        foreach ($fields as $f) {
            $select = Select::make('map_' . $f['key'])
                ->label($f['label'] . (($f['required'] ?? false) ? ' *' : ''))
                ->options(fn (Get $get) => SheetReader::optionsFromState($get('file')))
                ->visible(fn (Get $get) => filled($get('file')));

            if ($f['required'] ?? false) {
                $select->required();
            }

            $form[] = $select;
        }

        if ($prefixFromKey) {
            $form[] = Hidden::make('_prefixes');
            $form[] = CheckboxList::make('filter_prefixes')
                ->label('นำเข้าเฉพาะหมวด (รหัสขึ้นต้นด้วย)')
                ->helperText('เลือกหมวดที่ต้องการนำเข้า — ติ๊กไว้ทั้งหมด = นำเข้าทุกหมวด')
                ->options(fn (Get $get) => $get('_prefixes') ?? [])
                ->columns(4)
                ->visible(fn (Get $get) => filled($get('_prefixes')));
        }

        if ($groupFromKey) {
            $form[] = Hidden::make('_groups');
            $form[] = Select::make('filter_groups')
                ->label('นำเข้าเฉพาะกลุ่มสินค้า')
                ->multiple()
                ->options(fn (Get $get) => $get('_groups') ?? [])
                ->visible(fn (Get $get) => filled($get('_groups')));
        }

        return Action::make($name)
            ->label($label)
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->visible(fn () => ! (auth()->user()?->isVendor() ?? false))
            ->modalHeading($heading)
            ->modalDescription('อัปโหลดไฟล์ .xlsx แล้วจับคู่คอลัมน์ในไฟล์กับฟิลด์ของระบบก่อนกดนำเข้า')
            ->modalSubmitActionLabel('นำเข้า')
            ->form($form)
            ->action(function (array $data) use ($fields, $persist, $prefixFromKey, $groupFromKey) {
                $path = SheetReader::pathFromState($data['file']);
                if (! $path) {
                    Notification::make()->danger()->title('ไม่พบไฟล์')->send();
                    return;
                }

                try {
                    $rows = SheetReader::toRows($path);
                } catch (\Throwable $e) {
                    Notification::make()->danger()->title('อ่านไฟล์ไม่ได้')->body($e->getMessage())->send();
                    return;
                }

                if (count($rows) < 2) {
                    Notification::make()->warning()->title('ไม่พบข้อมูลในไฟล์')->send();
                    return;
                }

                $headers = array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);

                $cols = [];
                foreach ($fields as $f) {
                    $label = $data['map_' . $f['key']] ?? null;
                    $i = $label !== null ? array_search($label, $headers, true) : false;
                    $cols[$f['key']] = $i === false ? null : $i;
                }

                // Category filter setup.
                $prefixIdx   = $prefixFromKey ? ($cols[$prefixFromKey] ?? null) : null;
                $groupIdx    = $groupFromKey ? ($cols[$groupFromKey] ?? null) : null;
                $selPrefixes = $data['filter_prefixes'] ?? [];
                $selGroups   = $data['filter_groups'] ?? [];

                $imported   = 0;
                $skipped    = 0;
                $filtered   = 0;
                $failed     = 0;
                $firstError = null;

                foreach (array_slice($rows, 1) as $row) {
                    // Apply category filters (empty selection = no filter).
                    if ($prefixIdx !== null && ! empty($selPrefixes)) {
                        $code = trim((string) ($row[$prefixIdx] ?? ''));
                        $prefix = $code !== '' ? mb_strtoupper(strtok($code, '-')) : '';
                        if (! in_array($prefix, $selPrefixes, true)) {
                            $filtered++;
                            continue;
                        }
                    }
                    if ($groupIdx !== null && ! empty($selGroups)) {
                        $group = trim((string) ($row[$groupIdx] ?? ''));
                        if (! in_array($group, $selGroups, true)) {
                            $filtered++;
                            continue;
                        }
                    }

                    $values = [];
                    foreach ($cols as $key => $idx) {
                        $values[$key] = $idx !== null ? ($row[$idx] ?? null) : null;
                    }

                    $raw = [];
                    foreach ($headers as $i => $label) {
                        if ($label !== '') {
                            $raw[$label] = $row[$i] ?? null;
                        }
                    }

                    // Isolate each row so one bad record doesn't abort the import.
                    try {
                        if ($persist($values, $raw)) {
                            $imported++;
                        } else {
                            $skipped++;
                        }
                    } catch (\Throwable $e) {
                        $failed++;
                        $firstError ??= $e->getMessage();
                    }
                }

                // Remove the temporary uploaded file.
                $stored = is_array($data['file']) ? reset($data['file']) : $data['file'];
                if (is_string($stored) && $stored !== '') {
                    try {
                        Storage::disk('local')->delete($stored);
                    } catch (\Throwable) {
                        // ignore cleanup failure
                    }
                }

                $notes = [];
                if ($filtered) {
                    $notes[] = "กรองออก {$filtered} แถว";
                }
                if ($skipped) {
                    $notes[] = "ข้าม {$skipped} แถว";
                }
                if ($failed) {
                    $notes[] = "ผิดพลาด {$failed} แถว" . ($firstError ? " ({$firstError})" : '');
                }

                Notification::make()
                    ->{$failed ? 'warning' : 'success'}()
                    ->title("นำเข้าสำเร็จ {$imported} รายการ")
                    ->body($notes ? implode(' · ', $notes) : null)
                    ->send();
            });
    }

    /** Distinct uppercase prefixes (text before the first "-") in a column. */
    protected static function distinctPrefixes(array $rows, int $idx): array
    {
        $out = [];
        foreach (array_slice($rows, 1) as $row) {
            $code = trim((string) ($row[$idx] ?? ''));
            if ($code === '') {
                continue;
            }
            $prefix = mb_strtoupper(strtok($code, '-'));
            if ($prefix !== '') {
                $out[$prefix] = $prefix;
            }
        }
        ksort($out);

        return $out;
    }

    /** Distinct trimmed values in a column. */
    protected static function distinctValues(array $rows, int $idx): array
    {
        $out = [];
        foreach (array_slice($rows, 1) as $row) {
            $val = trim((string) ($row[$idx] ?? ''));
            if ($val !== '') {
                $out[$val] = $val;
            }
        }
        asort($out);

        return $out;
    }
}
