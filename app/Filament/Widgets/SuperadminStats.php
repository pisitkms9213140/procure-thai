<?php

namespace App\Filament\Widgets;

use App\Models\Tenant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
//use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

//class SuperadminStats extends StatsOverviewWidget
class SuperadminStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            // 1. นับจำนวนบริษัทลูกค้าทั้งหมด
            Stat::make('บริษัททั้งหมด (Total Tenants)', Tenant::count())
                ->description('ลูกค้าในระบบทั้งหมด')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            // 2. นับเฉพาะลูกค้าที่ Active อยู่
            Stat::make('กำลังใช้งาน (Active)', Tenant::where('status', 'active')->count())
                ->description('ลูกค้าที่ใช้งานปกติ')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            // 3. (จำลอง) นับจำนวนลูกค้าที่เพิ่งอัปโหลด Master Data ล่าสุด
            // *ในอนาคตเราอาจจะมีฟิลด์ last_import_at เพื่อเช็คว่าบริษัทไหนไม่อัปเดตข้อมูลนานแล้ว
            Stat::make('รอดำเนินการ / หมดอายุ', Tenant::whereIn('status', ['pending', 'overdue'])->count())
                ->description('ลูกค้าที่ต้องติดตาม')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),
        ];
    }
}
