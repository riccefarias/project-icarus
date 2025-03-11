<?php

namespace App\Filament\Resources\EquipmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VehiclesRelationManager extends RelationManager
{
    protected static string $relationship = 'vehicles';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Section::make('Informações do Veículo')
                    ->schema([
                        Forms\Components\TextInput::make('license_plate')
                            ->label('Placa')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('model')
                            ->label('Modelo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('brand')
                            ->label('Marca')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('year')
                            ->label('Ano')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('color')
                            ->label('Cor')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('chassis')
                            ->label('Chassi')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('active')
                            ->label('Ativo')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Observações')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('license_plate')
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Placa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Marca')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('active')
                    ->label('Status')
                    ->options([
                        '1' => 'Ativo',
                        '0' => 'Inativo',
                    ]),
                Tables\Filters\TrashedFilter::make(),
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
