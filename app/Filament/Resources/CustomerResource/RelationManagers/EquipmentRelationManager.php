<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentRelationManager extends RelationManager
{
    protected static string $relationship = 'equipment';

    protected static ?string $title = 'Equipamentos';

    protected static ?string $modelLabel = 'Equipamento';

    protected static ?string $pluralModelLabel = 'Equipamentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id')
                    ->label('Equipamento')
                    ->options(function () {
                        return \App\Models\Equipment::all()->pluck('serial_number', 'id');
                    })
                    ->required()
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('serial_number')
            ->columns([
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Número de Série')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Marca')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_stock' => 'Em Estoque',
                        'with_technician' => 'Com Técnico',
                        'with_customer' => 'Com Cliente',
                        'defective' => 'Com Defeito',
                        'maintenance' => 'Em Manutenção',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'defective' => 'danger',
                        'maintenance' => 'warning',
                        'in_stock' => 'success',
                        'with_technician' => 'info',
                        'with_customer' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Telefone'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'in_stock' => 'Em Estoque',
                        'with_technician' => 'Com Técnico',
                        'with_customer' => 'Com Cliente',
                        'defective' => 'Com Defeito',
                        'maintenance' => 'Em Manutenção',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Vincular Equipamento')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Desvincular'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Desvincular Selecionados'),
                ]),
            ]);
    }
}
