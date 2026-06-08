<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserKycResource\Pages;
use App\Filament\Resources\UserKycResource\RelationManagers;
use App\Models\UserKyc;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserKycResource extends Resource
{
    protected static ?string $model = UserKyc::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Nama User')->searchable(),
                Tables\Columns\TextColumn::make('nik')->label('NIK')->copyable()->searchable(query: function (Builder $query, string $search): Builder {
                    // Jika yang diketik admin adalah angka (potensial NIK asli)
                    if (is_numeric($search)) {
                        // Ubah ketikan admin jadi hash pakai custom salt kita
                        $searchHash = hash_hmac('sha256', $search, config('app.kyc.salt'));

                        // Belokkan pencariannya ke kolom nik_hash
                        return $query->where('nik_hash', $searchHash);
                    }

                    // Jika bukan angka, kembalikan query kosong agar tidak error
                    return $query->whereRaw('1 = 0');
                }),
                Tables\Columns\TextColumn::make('user.kyc_status')
                    ->label('Status KYC')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    // 1. ACTION APPROVE & UPGRADE ROLE
                    Action::make('approve_kyc')
                        ->label('Approve & Upgrade')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->visible(fn($record) => $record->user->kyc_status !== 'approved')
                        ->action(function ($record) {
                            $user = $record->user;

                            $user->update(['kyc_status' => 'approved']);
                            $user->syncRoles(['Member']); // Otomatis hapus role Basic, ganti ke Member
                            $record->update(['rejection_reason' => null]);
                        })
                        ->successNotificationTitle('KYC Disetujui, User menjadi Member!'),

                    // 2. ACTION REJECT KYC
                    Action::make('reject_kyc')
                        ->label('Reject KYC')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->form([
                            Textarea::make('rejection_reason')->label('Alasan Penolakan')->required(),
                        ])
                        ->visible(fn($record) => $record->user->kyc_status === 'pending')
                        ->action(function ($record, array $data) {
                            $user = $record->user;

                            $user->update(['kyc_status' => 'rejected']);
                            $user->syncRoles(['Basic']); // Memastikan/mengembalikan user ke role Basic
                            $record->update(['rejection_reason' => $data['rejection_reason']]);
                        })
                        ->successNotificationTitle('KYC Ditolak, User tetap Basic.'),
                ])->label('Aksi KYC')->icon('heroicon-m-ellipsis-vertical'),
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
            'index' => Pages\ListUserKycs::route('/'),
            'create' => Pages\CreateUserKyc::route('/create'),
            'edit' => Pages\EditUserKyc::route('/{record}/edit'),
        ];
    }
}
