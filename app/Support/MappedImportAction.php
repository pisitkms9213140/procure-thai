<?php

namespace App\Support;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

/**
 * Builds a Filament "Import Excel" header action with a column-mapping step:
 * upload .xlsx -> auto-read header row -> map each system field to a file
 * column (auto-guessed, required ones block submit) -> import on confirm.
 *
 * Rows are read by column INDEX (see SheetReader) so arbitrary SAP/Thai
 * headers and a leading "#" column survive.
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
                ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                ->storeFiles(false)
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, Set $set) use ($fields) {
                    $headers = SheetReader::headersFromState($state);
                    $set('_headers', $headers);
                    foreach ($fields as $f) {
                        $set('map_' . $f['key'], SheetReader::guess($headers, $f['guess'] ?? [$f['key']]));
                    }
                }),

            Hidden::make('_headers'),
        ];

        foreach ($fields as $f) {
            $select = Select::make('map_' . $f['key'])
                ->label($f['label'] . (($f['required'] ?? false) ? ' *' : ''))
                ->options(fn (Get $get) => SheetReader::optionsFromHeaders($get('_headers') ?? []))
                ->visible(fn (Get $get) => filled($get('_headers')));

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
                $file = SheetReader::fileFromState($data['file']);
                if (! $file) {
                    Notification::make()->danger()->title('ไม่พบไฟล์')->send();
                    return;
                }

                $rows    = SheetReader::toRows($file);
                $headers = array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);

                // Resolve each mapped field to a column index.
                $cols = [];
                foreach ($fields as $f) {
                    $label = $data['map_' . $f['key']] ?? null;
                    $i = $label !== null ? array_search($label, $headers, true) : false;
                    $cols[$f['key']] = $i === false ? null : $i;
                }

                $imported = 0;
                $skipped  = 0;

                foreach (array_slice($rows, 1) as $row) {
                    $values = [];
                    foreach ($cols as $key => $idx) {
                        $values[$key] = $idx !== null ? ($row[$idx] ?? null) : null;
                    }

                    if ($persist($values)) {
                        $imported++;
                    } else {
                        $skipped++;
                    }
                }

                Notification::make()
                    ->success()
                    ->title("นำเข้าสำเร็จ {$imported} รายการ")
                    ->body($skipped ? "ข้าม {$skipped} แถว" : null)
                    ->send();
            });
    }
}
