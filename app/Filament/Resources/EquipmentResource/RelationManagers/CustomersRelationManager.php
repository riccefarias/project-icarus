<?php

namespace App\Filament\Resources\EquipmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CustomersRelationManager extends RelationManager
{
    protected static string $relationship = 'customers';

    protected static ?string $title = 'Clientes';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id')
                    ->label('Cliente')
                    ->options(function () {
                        return \App\Models\Customer::all()->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('document')
                    ->label('Documento')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('active')
                    ->label('Status')
                    ->options([
                        '1' => 'Ativo',
                        '0' => 'Inativo',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Vincular Cliente')
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
