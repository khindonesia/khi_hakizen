<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Filament\Actions\Action;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // TOMBOL EXPORT KUSTOM (SINKRONUS TANPA QUEUE)
            Action::make('exportCsv')
                ->label('Export Users')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    $users = User::all(['id', 'name', 'username', 'email', 'verified', 'created_at']);

                    $csvFileName = 'users-export-' . now()->format('Y-m-d') . '.csv';

                    $headers = [
                        "Content-type"        => "text/csv",
                        "Content-Disposition" => "attachment; filename=$csvFileName",
                        "Pragma"              => "no-cache",
                        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                        "Expires"             => "0"
                    ];

                    $callback = function () use ($users) {
                        $file = fopen('php://output', 'w');

                        // Header Kolom CSV
                        fputcsv($file, ['ID', 'Full Name', 'Username', 'Email Address', 'Is Verified', 'Joined At']);

                        // Isi Data
                        foreach ($users as $user) {
                            fputcsv($file, [
                                $user->id,
                                $user->name,
                                $user->username,
                                $user->email,
                                $user->verified ? 'Yes' : 'No',
                                $user->created_at->format('Y-m-d H:i:s'),
                            ]);
                        }

                        fclose($file);
                    };

                    return response()->stream($callback, 200, $headers);
                }),

            Actions\CreateAction::make()
                ->label('New User'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Users')
                ->badge(User::count()),

            'verified' => Tab::make('Verified')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('verified', true))
                ->badge(User::where('verified', true)->count())
                ->badgeColor('success'),

            'unverified' => Tab::make('Unverified')
                // Menggunakan kombinasi di bawah agar fleksibel membaca data false, 0, maupun null di database
                ->modifyQueryUsing(fn(Builder $query) => $query->where(function (Builder $q) {
                    $q->where('verified', false)
                        ->orWhere('verified', 0)
                        ->orWhereNull('verified');
                }))
                ->badge(User::where(function ($q) {
                    $q->where('verified', false)
                        ->orWhere('verified', 0)
                        ->orWhereNull('verified');
                })->count())
                ->badgeColor('danger'),
        ];
    }
}
