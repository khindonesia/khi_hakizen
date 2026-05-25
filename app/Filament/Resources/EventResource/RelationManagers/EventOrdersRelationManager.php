<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Data Order Event';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->label('Status Pendaftaran')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\Select::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'free' => 'Free',
                        'expired' => 'Expired',
                    ])
                    ->required()
                    ->default('free'),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah Bayar')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->default(0),
                Forms\Components\TextInput::make('external_id')
                    ->label('External ID (Xendit)')
                    ->placeholder('e.g. EVT-...'),
                Forms\Components\TextInput::make('invoice_id')
                    ->label('Invoice ID (Xendit)'),
                Forms\Components\TextInput::make('payment_url')
                    ->label('Payment Link (Xendit)')
                    ->url(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pembeli')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.payment_status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'free' => 'info',
                        'pending' => 'warning',
                        'expired' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.amount')
                    ->label('Total Bayar')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.external_id')
                    ->label('External ID')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pivot.invoice_id')
                    ->label('Invoice ID')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label('Tanggal Order')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'free' => 'Free',
                        'expired' => 'Expired',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => 
                        empty($data['value']) ? $query : $query->where('event_user.payment_status', $data['value'])
                    ),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Tambah Order Manual')
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('status')
                            ->label('Status Pendaftaran')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'free' => 'Free',
                                'expired' => 'Expired',
                            ])
                            ->default('free')
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Bayar')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit Order'),
                Tables\Actions\DetachAction::make()->label('Batalkan Order'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
