<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    
    protected static ?string $navigationLabel = 'Serviços';
    
    protected static ?string $modelLabel = 'Serviço';
    
    protected static ?string $pluralModelLabel = 'Serviços';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_type')
                    ->options([
                        'installation' => 'Instalação',
                        'uninstallation' => 'Desinstalação',
                        'maintenance' => 'Manutenção',
                        'repair' => 'Reparo',
                        'inspection' => 'Inspeção',
                        'other' => 'Outro',
                    ])
                    ->required()
                    ->label('Tipo de Serviço'),
                
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nome'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->label('Email'),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->label('Telefone'),
                    ])
                    ->label('Cliente')
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set) {
                        $set('vehicle_id', null);
                        $set('equipment_id', null);
                    }),
                
                Forms\Components\Select::make('vehicle_id')
                    ->relationship('vehicle', 'license_plate', function (Builder $query, Forms\Get $get) {
                        $customerId = $get('customer_id');
                        if ($customerId) {
                            $query->where('customer_id', $customerId);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Veículo'),
                
                Forms\Components\Select::make('equipment_id')
                    ->relationship('equipment', 'serial_number')
                    ->searchable()
                    ->preload()
                    ->label('Equipamento'),
                
                Forms\Components\Select::make('technician_id')
                    ->relationship('technician', 'name', fn (Builder $query) => 
                        $query->whereHas('roles', fn ($q) => $q->where('name', 'tecnico'))
                    )
                    ->searchable()
                    ->preload()
                    ->label('Técnico Responsável'),
                
                Forms\Components\DateTimePicker::make('scheduled_date')
                    ->label('Data Agendada')
                    ->required(),
                
                Forms\Components\DateTimePicker::make('completion_date')
                    ->label('Data de Conclusão')
                    ->afterOrEqual('scheduled_date'),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'scheduled' => 'Agendado',
                        'in_progress' => 'Em Andamento',
                        'completed' => 'Concluído',
                        'cancelled' => 'Cancelado',
                    ])
                    ->default('scheduled')
                    ->required()
                    ->label('Status'),
                
                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->columnSpanFull(),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Observações')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service_type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'installation' => 'Instalação',
                        'uninstallation' => 'Desinstalação',
                        'maintenance' => 'Manutenção',
                        'repair' => 'Reparo',
                        'inspection' => 'Inspeção',
                        'other' => 'Outro',
                        default => $state,
                    })
                    ->sortable()
                    ->label('Tipo'),
                    
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->label('Cliente'),
                    
                Tables\Columns\TextColumn::make('vehicle.license_plate')
                    ->searchable()
                    ->sortable()
                    ->label('Veículo'),
                    
                Tables\Columns\TextColumn::make('equipment.serial_number')
                    ->searchable()
                    ->sortable()
                    ->label('Equipamento'),
                    
                Tables\Columns\TextColumn::make('technician.name')
                    ->searchable()
                    ->sortable()
                    ->label('Técnico'),
                    
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Data Agendada'),
                    
                Tables\Columns\TextColumn::make('completion_date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Data Conclusão'),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'scheduled',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Agendado',
                        'in_progress' => 'Em Andamento',
                        'completed' => 'Concluído',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->sortable()
                    ->label('Status'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Criado em'),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Atualizado em'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_type')
                    ->options([
                        'installation' => 'Instalação',
                        'uninstallation' => 'Desinstalação',
                        'maintenance' => 'Manutenção',
                        'repair' => 'Reparo',
                        'inspection' => 'Inspeção',
                        'other' => 'Outro',
                    ])
                    ->label('Tipo de Serviço'),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Agendado',
                        'in_progress' => 'Em Andamento',
                        'completed' => 'Concluído',
                        'cancelled' => 'Cancelado',
                    ])
                    ->label('Status'),
                    
                Tables\Filters\SelectFilter::make('customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Cliente'),
                    
                Tables\Filters\SelectFilter::make('technician')
                    ->relationship('technician', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Técnico'),
                    
                Tables\Filters\Filter::make('scheduled_date')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_from')
                            ->label('Agendado a partir de'),
                        Forms\Components\DatePicker::make('scheduled_until')
                            ->label('Agendado até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['scheduled_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_date', '>=', $date),
                            )
                            ->when(
                                $data['scheduled_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['scheduled_from'] ?? null) {
                            $indicators['scheduled_from'] = 'Agendado a partir de ' . \Carbon\Carbon::parse($data['scheduled_from'])->format('d/m/Y');
                        }
                        
                        if ($data['scheduled_until'] ?? null) {
                            $indicators['scheduled_until'] = 'Agendado até ' . \Carbon\Carbon::parse($data['scheduled_until'])->format('d/m/Y');
                        }
                        
                        return $indicators;
                    }),
                    
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label('Completar')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (Service $record) => $record->status !== 'completed' && $record->status !== 'cancelled')
                    ->action(function (Service $record) {
                        $record->update([
                            'status' => 'completed',
                            'completion_date' => now(),
                        ]);
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn (Service $record) => $record->status !== 'cancelled')
                    ->requiresConfirmation()
                    ->action(function (Service $record) {
                        $record->update([
                            'status' => 'cancelled',
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('completeMultiple')
                        ->label('Completar Selecionados')
                        ->color('success')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                if ($record->status !== 'completed' && $record->status !== 'cancelled') {
                                    $record->update([
                                        'status' => 'completed',
                                        'completion_date' => now(),
                                    ]);
                                }
                            });
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
