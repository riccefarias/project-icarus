<?php

namespace App\Filament\Resources\EquipmentResource\Pages;

use App\Filament\Resources\EquipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEquipment extends EditRecord
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('qrcode')
                ->label('QR Code')
                ->icon('heroicon-o-qr-code')
                ->url(fn () => route('equipment.qrcode', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('showQrCode')
                ->label('Ver QR Code')
                ->icon('heroicon-o-eye')
                ->modalHeading(fn (): string => "QR Code: {$this->record->serial_number}")
                ->modalContent(fn () => view('equipment.qr-modal', ['equipment' => $this->record]))
                ->modalSubmitAction(false)
                ->modalCancelAction(false),
            Actions\DeleteAction::make(),
        ];
    }
}
