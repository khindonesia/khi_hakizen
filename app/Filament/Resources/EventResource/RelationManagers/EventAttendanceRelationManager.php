<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EventAttendanceRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Attendance Tiap Event';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label('Status Kehadiran / Pendaftaran')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active / Hadir',
                        'cancelled' => 'Cancelled / Batal',
                    ])
                    ->required()
                    ->default('active'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Nomor Handphone')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('pivot.status')
                    ->label('Status Kehadiran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label('Waktu Mendaftar')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Kehadiran')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active / Hadir',
                        'cancelled' => 'Cancelled / Batal',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => 
                        empty($data['value']) ? $query : $query->where('event_user.status', $data['value'])
                    ),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Tambah Kehadiran Manual')
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('status')
                            ->label('Status Kehadiran')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active / Hadir',
                                'cancelled' => 'Cancelled / Batal',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('checkIn')
                    ->label('Check-In (Hadir)')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Model $record): bool => $record->pivot->status !== 'active')
                    ->action(function (Model $record) {
                        $this->getRelationship()->updateExistingPivot($record->id, [
                            'status' => 'active',
                        ]);
                    }),
                Tables\Actions\Action::make('cancelPresence')
                    ->label('Batal')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Model $record): bool => $record->pivot->status === 'active')
                    ->action(function (Model $record) {
                        $this->getRelationship()->updateExistingPivot($record->id, [
                            'status' => 'cancelled',
                        ]);
                    }),
                Tables\Actions\EditAction::make()->label('Edit Status'),
                Tables\Actions\DetachAction::make()->label('Hapus Peserta'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
