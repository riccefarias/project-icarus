<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Equipment;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        return [
            Stat::make('Clientes', Customer::count())
                ->description('Total de clientes cadastrados')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),
            
            Stat::make('Veículos', Vehicle::count())
                ->description('Total de veículos cadastrados')
                ->descriptionIcon('heroicon-m-truck')
                ->color('danger'),
                
            Stat::make('Equipamentos', Equipment::count())
                ->description('Total de equipamentos cadastrados')
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color('warning'),
                
            Stat::make('Usuários', User::count())
                ->description('Total de usuários do sistema')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}