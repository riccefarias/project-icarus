<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SystemInfoCard extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected function getStats(): array
    {
        $version = $this->getProjectVersion();
        
        return [
            Stat::make('Versão do Sistema', $version)
                ->description('Icarus Fleet Management')
                ->descriptionIcon('heroicon-m-document')
                ->color('primary'),
        ];
    }
    
    protected function getProjectVersion(): string
    {
        $versionFile = base_path('version.txt');
        
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        
        return '0.1.0';
    }
}