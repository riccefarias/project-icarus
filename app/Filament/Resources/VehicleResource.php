<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Integrations\IntegrationsManager;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Veículos';

    protected static ?string $modelLabel = 'Veículo';

    protected static ?string $pluralModelLabel = 'Veículos';

    protected static ?string $navigationGroup = 'Gestão';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

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

                Forms\Components\Section::make('Informações do Rastreador')
                    ->schema([
                        Forms\Components\Select::make('equipment_id')
                            ->label('Equipamento')
                            ->relationship('equipment', 'serial_number')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->createOptionForm([
                                Forms\Components\Section::make('Informações do Equipamento')
                                    ->schema([
                                        Forms\Components\TextInput::make('serial_number')
                                            ->label('Número de Série')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(),
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
                                            ->default('with_customer')
                                            ->required(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Informações do SIM')
                                    ->schema([
                                        Forms\Components\TextInput::make('imei')
                                            ->label('IMEI')
                                            ->maxLength(255)
                                            ->unique(),
                                        Forms\Components\TextInput::make('phone_number')
                                            ->label('Número de Telefone')
                                            ->tel()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('chip_provider')
                                            ->label('Operadora')
                                            ->maxLength(255),
                                    ])->columns(3),
                            ]),
                        Forms\Components\TextInput::make('traccar_id')
                            ->label('ID no Traccar')
                            ->disabled()
                            ->helperText('Este campo é gerenciado automaticamente pelo sistema de integração com o Traccar'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Placa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Marca')
                    ->searchable(),
                Tables\Columns\TextColumn::make('equipment.serial_number')
                    ->label('Equipamento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
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
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('active')
                    ->label('Status')
                    ->options([
                        '1' => 'Ativo',
                        '0' => 'Inativo',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->after(function (Vehicle $record) {
                            $integrationsManager = app(IntegrationsManager::class);
                            $integrationsManager->loadIntegrations();

                            $activeIntegration = $integrationsManager->getActiveIntegration();
                            if ($activeIntegration && $activeIntegration->isEnabled()) {
                                $activeIntegration->syncVehicle($record);
                            }
                        }),
                    Tables\Actions\Action::make('sync_traccar')
                        ->label('Sincronizar com Traccar')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function (Vehicle $record) {
                            $integrationsManager = app(IntegrationsManager::class);
                            $integrationsManager->loadIntegrations();

                            $activeIntegration = $integrationsManager->getActiveIntegration();
                            if ($activeIntegration && $activeIntegration->isEnabled()) {
                                $traccarId = $activeIntegration->syncVehicle($record);

                                if ($traccarId) {
                                    return Tables\Actions\Action::makeModalMessage()
                                        ->success()
                                        ->title('Veículo sincronizado')
                                        ->body('O veículo foi sincronizado com sucesso com o Traccar.');
                                }

                                return Tables\Actions\Action::makeModalMessage()
                                    ->danger()
                                    ->title('Erro na sincronização')
                                    ->body('Não foi possível sincronizar o veículo com o Traccar. Verifique os logs para mais detalhes.');
                            }

                            return Tables\Actions\Action::makeModalMessage()
                                ->danger()
                                ->title('Nenhuma integração ativa')
                                ->body('Não há integração ativa configurada. Por favor, configure uma integração primeiro.');
                        }),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('sync_traccar_bulk')
                        ->label('Sincronizar com Traccar')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $integrationsManager = app(IntegrationsManager::class);
                            $integrationsManager->loadIntegrations();

                            $activeIntegration = $integrationsManager->getActiveIntegration();
                            if ($activeIntegration && $activeIntegration->isEnabled()) {
                                $successCount = 0;
                                $failCount = 0;

                                foreach ($records as $record) {
                                    $traccarId = $activeIntegration->syncVehicle($record);
                                    if ($traccarId) {
                                        $successCount++;
                                    } else {
                                        $failCount++;
                                    }
                                }

                                if ($failCount === 0) {
                                    return Tables\Actions\BulkAction::makeModalMessage()
                                        ->success()
                                        ->title('Veículos sincronizados')
                                        ->body("Todos os {$successCount} veículos foram sincronizados com sucesso.");
                                }

                                return Tables\Actions\BulkAction::makeModalMessage()
                                    ->warning()
                                    ->title('Sincronização parcial')
                                    ->body("{$successCount} veículos sincronizados com sucesso e {$failCount} falhas.");
                            }

                            return Tables\Actions\BulkAction::makeModalMessage()
                                ->danger()
                                ->title('Nenhuma integração ativa')
                                ->body('Não há integração ativa configurada. Por favor, configure uma integração primeiro.');
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EquipmentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
