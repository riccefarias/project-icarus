<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentRelationManager extends RelationManager
{
    protected static string $relationship = 'equipment';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Equipamento')
                    ->schema([
                        Forms\Components\TextInput::make('serial_number')
                            ->label('Número de Série')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('model')
                            ->label('Modelo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('brand')
                            ->label('Marca')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'in_stock' => 'Em Estoque',
                                'with_technician' => 'Com Técnico',
                                'with_customer' => 'Com Cliente',
                                'defective' => 'Com Defeito',
                                'maintenance' => 'Em Manutenção',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Informações do SIM')
                    ->schema([
                        Forms\Components\TextInput::make('imei')
                            ->label('IMEI')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone_number')
                            ->label('Número de Telefone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('chip_provider')
                            ->label('Operadora')
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Observações')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
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
                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'in_stock' => 'Em Estoque',
                        'with_technician' => 'Com Técnico',
                        'with_customer' => 'Com Cliente',
                        'defective' => 'Com Defeito',
                        'maintenance' => 'Em Manutenção',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('imei')
                    ->label('IMEI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Telefone'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
