<?php

namespace App\Filament\Resources\EquipmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'history';

    protected static ?string $title = 'Histórico de Movimentação';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('new_status')
                    ->label('Novo Status')
                    ->options([
                        'in_stock' => 'Em Estoque',
                        'with_technician' => 'Com Técnico',
                        'with_customer' => 'Com Cliente',
                        'defective' => 'Com Defeito',
                        'maintenance' => 'Em Manutenção',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Observações')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable(),
                Tables\Columns\TextColumn::make('previous_status')
                    ->label('Status Anterior')
                    ->formatStateUsing(fn ($state) => $this->formatStatus($state)),
                Tables\Columns\TextColumn::make('new_status')
                    ->label('Novo Status')
                    ->formatStateUsing(fn ($state) => $this->formatStatus($state)),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Observações')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, string $model): mixed {
                        // Pega o status atual do equipamento para registrar como status anterior
                        $equipment = $this->getOwnerRecord();
                        
                        return $model::create([
                            'equipment_id' => $equipment->id,
                            'user_id' => auth()->id(),
                            'previous_status' => $equipment->status,
                            'new_status' => $data['new_status'],
                            'notes' => $data['notes'],
                        ]);
                    })
                    ->after(function () {
                        // Atualiza o status do equipamento para o novo status
                        $equipment = $this->getOwnerRecord();
                        $equipment->status = $this->getMountedActionFormModel()->new_status;
                        $equipment->save();
                    }),
            ])
            ->actions([
                // O histórico não deve ser editável ou excluível
            ])
            ->bulkActions([
                // Sem ações em massa
            ]);
    }

    protected function formatStatus(?string $status): string
    {
        $statusMap = [
            'in_stock' => 'Em Estoque',
            'with_technician' => 'Com Técnico',
            'with_customer' => 'Com Cliente',
            'defective' => 'Com Defeito',
            'maintenance' => 'Em Manutenção',
        ];

        return $statusMap[$status] ?? $status ?? 'N/A';
    }
}
