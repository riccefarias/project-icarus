<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Integrations\IntegrationsManager;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static ?string $navigationGroup = 'Gestão';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('document')
                            ->label('Documento (CPF/CNPJ)')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('active')
                            ->label('Ativo')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Endereço')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('Endereço')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')
                            ->label('Cidade')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('state')
                            ->label('Estado')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->label('CEP')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informações Adicionais')
                    ->schema([
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Pessoa de Contato')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3),
                        Forms\Components\TextInput::make('traccar_id')
                            ->label('ID no Traccar')
                            ->disabled()
                            ->helperText('Este campo é gerenciado automaticamente pelo sistema de integração com o Traccar'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
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
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('sync_traccar')
                        ->label('Sincronizar com Traccar')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function (Customer $record) {
                            $integrationsManager = app(IntegrationsManager::class);
                            $integrationsManager->loadIntegrations();

                            $activeIntegration = $integrationsManager->getActiveIntegration();
                            if ($activeIntegration && $activeIntegration->isEnabled()) {
                                $traccarId = $activeIntegration->syncCustomer($record);

                                if ($traccarId) {
                                    return Tables\Actions\Action::makeModalMessage()
                                        ->success()
                                        ->title('Cliente sincronizado')
                                        ->body('O cliente foi sincronizado com sucesso com o Traccar.');
                                }

                                return Tables\Actions\Action::makeModalMessage()
                                    ->danger()
                                    ->title('Erro na sincronização')
                                    ->body('Não foi possível sincronizar o cliente com o Traccar. Verifique os logs para mais detalhes.');
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
                                    $traccarId = $activeIntegration->syncCustomer($record);
                                    if ($traccarId) {
                                        $successCount++;
                                    } else {
                                        $failCount++;
                                    }
                                }

                                if ($failCount === 0) {
                                    return Tables\Actions\BulkAction::makeModalMessage()
                                        ->success()
                                        ->title('Clientes sincronizados')
                                        ->body("Todos os {$successCount} clientes foram sincronizados com sucesso.");
                                }

                                return Tables\Actions\BulkAction::makeModalMessage()
                                    ->warning()
                                    ->title('Sincronização parcial')
                                    ->body("{$successCount} clientes sincronizados com sucesso e {$failCount} falhas.");
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
