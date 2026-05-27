<?php

namespace App\Support;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Storage;

/**
 * Builds a Filament "Import Excel" header action with a column-mapping step:
 * upload .xlsx -> auto-read header row -> map each system field to a file
 * column (auto-guessed, required ones block submit) -> import on confirm.
 *
 * The mapping dropdown options are read directly from the uploaded file, so
 * they stay consistent at render AND validation time (no hidden state to lose).
 */
class MappedImportAction
{
    /**
     * @param  string  $heading  Modal heading.
     * @param  array<int,array{key:string,label:string,required?:bool,guess?:array<int,string>}>  $fields
     * @param  callable(array<string,mixed>):bool  $persist  Receives [fieldKey => cellValue] for one row;
     *                                                        return true if imported, false if skipped.
     */
    public static function make(string $heading, array $fields, callable $persist): Action
    {
        $form = [
            FileUpload::make('file')
                ->label('ไฟล์ Excel (.xlsx)')
                // Linux finfo frequently reports .xlsx as application/zip (xlsx is
                // a zip), so accept those variants too or server-side mime
                // validation rejects valid files.
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
                ->afterStateUpdated(function ($state, Set $set) use ($fields) {
                    try {
                        $headers = SheetReader::headersFromState($state);
                    } catch (\Throwable $e) {
                        Notification::make()->danger()->title('อ่านไฟล์ไม่ได้')->body($e->getMessage())->send();
                        $headers = [];
                    }

                    foreach ($fields as $f) {
                        $set('map_' . $f['key'], $headers ? SheetReader::guess($headers, $f['guess'] ?? [$f['key']]) : null);
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

        return Action::make('import')
            ->label('นำเข้า Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->modalHeading($heading)
            ->modalDescription('อัปโหลดไฟล์ .xlsx แล้วจับคู่คอลัมน์ในไฟล์กับฟิลด์ของระบบก่อนกดนำเข้า')
            ->modalSubmitActionLabel('นำเข้า')
            ->form($form)
            ->action(function (array $data) use ($fields, $persist) {
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

                $imported   = 0;
                $skipped    = 0;
                $failed     = 0;
                $firstError = null;

                foreach (array_slice($rows, 1) as $row) {
                    $values = [];
                    foreach ($cols as $key => $idx) {
                        $values[$key] = $idx !== null ? ($row[$idx] ?? null) : null;
                    }

                    // Isolate each row so one bad record (invalid enum, duplicate
                    // unique key, etc.) doesn't abort the whole import.
                    try {
                        if ($persist($values)) {
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
}
