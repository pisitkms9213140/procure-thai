<?php

namespace App\Filament\App\Widgets;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProcurementStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $openPoAmount = PurchaseOrder::whereIn('status', ['sent', 'acknowledged', 'partial'])
            ->sum('total_amount');

        $pendingInvoices = Invoice::whereIn('status', ['pending', 'under_review'])->count();

        $overdueInvoices = Invoice::whereNotIn('status', ['paid', 'rejected'])
            ->where('due_date', '<', now())
            ->count();

        return [
            Stat::make('ซัพพลายเออร์ทั้งหมด', Supplier::where('status', 'active')->count())
                ->description('รายที่ active')
                ->icon('heroicon-o-building-office-2')
                ->color('success'),

            Stat::make('PO ที่เปิดอยู่', PurchaseOrder::whereIn('status', ['sent', 'acknowledged', 'partial'])->count())
                ->description('มูลค่า ' . number_format($openPoAmount, 2) . ' บาท')
                ->icon('heroicon-o-document-text')
                ->color('info'),

            Stat::make('ใบแจ้งหนี้รอตรวจสอบ', $pendingInvoices)
                ->description($overdueInvoices > 0 ? "เกินกำหนด {$overdueInvoices} ใบ" : 'ไม่มีที่เกินกำหนด')
                ->icon('heroicon-o-banknotes')
                ->color($overdueInvoices > 0 ? 'danger' : 'warning'),
        ];
    }
}
