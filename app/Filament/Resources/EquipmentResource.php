<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentResource\Pages;
use App\Filament\Resources\EquipmentResource\RelationManagers;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationGroup = 'Gestão';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $navigationLabel = 'Equipamentos';
    
    protected static ?string $modelLabel = 'Equipamento';
    
    protected static ?string $pluralModelLabel = 'Equipamentos';

    public static function form(Form $form): Form
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
                                'maintenance' => 'Em Manutenção'
                            ])
                            ->required(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Informações do SIM')
                    ->schema([
                        Forms\Components\TextInput::make('imei')
                            ->label('IMEI')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('traccar_id')
                            ->label('ID no Traccar')
                            ->disabled()
                            ->helperText('Este campo é gerenciado automaticamente pelo sistema de integração'),
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

    public static function table(Table $table): Table
    {
        return $table
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
                        'maintenance' => 'Em Manutenção'
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('imei')
                    ->label('IMEI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('traccar_id')
                    ->label('ID no Traccar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Telefone'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'in_stock' => 'Em Estoque',
                        'with_technician' => 'Com Técnico',
                        'with_customer' => 'Com Cliente',
                        'defective' => 'Com Defeito',
                        'maintenance' => 'Em Manutenção'
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VehiclesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }
}
