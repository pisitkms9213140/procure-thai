<?php

namespace App\Filament\App\Resources\UomMasters\Pages;

use App\Filament\App\Resources\UomMasters\UomMasterResource;
use App\Models\UomMaster;
use App\Support\SheetReader;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class ListUomMasters extends ListRecords
{
    protected static string $resource = UomMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            $this->importAction(),
        ];
    }

    protected function importAction(): Action
    {
        return Action::make('import')
            ->label('นำเข้า Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->modalHeading('นำเข้าหน่วยนับจาก Excel')
            ->modalDescription('อัปโหลดไฟล์ .xlsx แล้วจับคู่คอลัมน์ในไฟล์กับฟิลด์ของระบบก่อนกดนำเข้า')
            ->modalSubmitActionLabel('นำเข้า')
            ->form([
                FileUpload::make('file')
                    ->label('ไฟล์ Excel (.xlsx)')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ])
                    ->storeFiles(false)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $headers = SheetReader::headersFromState($state);
                        $set('_headers', $headers);
                        // Auto-guess the mapping; user can override.
                        $set('map_code', SheetReader::guess($headers, ['uom_code', 'code', 'รหัส']));
                        $set('map_name', SheetReader::guess($headers, ['uom_name', 'name', 'ชื่อ', 'หน่วย']));
                        $set('map_sap', SheetReader::guess($headers, ['uom_entry', 'entry', 'sap']));
                    }),

                Hidden::make('_headers'),

                Select::make('map_code')
                    ->label('คอลัมน์ → รหัสหน่วยนับ (code) *')
                    ->options(fn (Get $get) => SheetReader::optionsFromHeaders($get('_headers') ?? []))
                    ->required()
                    ->visible(fn (Get $get) => filled($get('_headers'))),

                Select::make('map_name')
                    ->label('คอลัมน์ → ชื่อหน่วยนับ (name)')
                    ->options(fn (Get $get) => SheetReader::optionsFromHeaders($get('_headers') ?? []))
                    ->visible(fn (Get $get) => filled($get('_headers'))),

                Select::make('map_sap')
                    ->label('คอลัมน์ → รหัส SAP (uom_entry)')
                    ->options(fn (Get $get) => SheetReader::optionsFromHeaders($get('_headers') ?? []))
                    ->visible(fn (Get $get) => filled($get('_headers'))),
            ])
            ->action(function (array $data) {
                $file = SheetReader::fileFromState($data['file']);
                if (! $file) {
                    Notification::make()->danger()->title('ไม่พบไฟล์')->send();
                    return;
                }

                $rows    = SheetReader::toRows($file);
                $headers = array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);

                $index = function (?string $label) use ($headers): ?int {
                    if ($label === null) {
                        return null;
                    }
                    $i = array_search($label, $headers, true);
                    return $i === false ? null : $i;
                };

                $codeI = $index($data['map_code'] ?? null);
                $nameI = $index($data['map_name'] ?? null);
                $sapI  = $index($data['map_sap'] ?? null);

                $imported = 0;
                $skipped  = 0;

                foreach (array_slice($rows, 1) as $row) {
                    $code = $codeI !== null ? trim((string) ($row[$codeI] ?? '')) : '';
                    if ($code === '') {
                        $skipped++;
                        continue;
                    }

                    UomMaster::updateOrCreate(
                        ['code' => mb_strtoupper($code)],
                        [
                            'name'      => $nameI !== null ? (string) ($row[$nameI] ?? $code) : $code,
                            'sap_code'  => $sapI !== null ? (string) ($row[$sapI] ?? '') ?: null : null,
                            'is_active' => true,
                        ]
                    );
                    $imported++;
                }

                Notification::make()
                    ->success()
                    ->title("นำเข้าสำเร็จ {$imported} รายการ")
                    ->body($skipped ? "ข้าม {$skipped} แถวที่ไม่มีรหัส" : null)
                    ->send();
            });
    }
}
