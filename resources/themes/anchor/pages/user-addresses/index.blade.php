<?php
    use App\Models\UserAddress;
    use Filament\Forms\Concerns\InteractsWithForms;
    use Filament\Forms\Contracts\HasForms;
    use Filament\Tables;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Columns\IconColumn;
    use Filament\Tables\Actions\Action;
    use Filament\Tables\Actions\ActionGroup;
    use Filament\Tables\Table;
    use Filament\Tables\Filters\Filter;
    use Filament\Tables\Enums\FiltersLayout;
    use Filament\Notifications\Notification;
    use Livewire\Volt\Component;
    use function Laravel\Folio\{middleware, name};
    middleware('auth');
    name('user-addresses');
    new class extends Component implements HasForms, Tables\Contracts\HasTable
    {
        use InteractsWithForms, Tables\Concerns\InteractsWithTable;
        
        public ?array $data = [];
        
        public function setPrimaryAddress(UserAddress $record): void
        {
            $record->setPrimary();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Address set as primary successfully!',
            ]);
        }
        
        public function deleteAddress(UserAddress $record): void
        {
            try {
                // Cek apakah ini alamat utama dan ada alamat lain
                if ($record->is_primary && UserAddress::where('user_id', auth()->id())->count() > 1) {
                    // Temukan alamat lain dan jadikan primary
                    $newPrimary = UserAddress::where('user_id', auth()->id())
                        ->where('id', '!=', $record->id)
                        ->first();
                        
                    if ($newPrimary) {
                        $newPrimary->setPrimary();
                    }
                }
                
                // Hapus alamat
                $record->delete();
                
                // Kirim notifikasi
                Notification::make()
                    ->success()
                    ->title('Address deleted successfully')
                    ->send();
                
            } catch (\Exception $e) {
                // Tangkap error
                Notification::make()
                    ->danger()
                    ->title('Error deleting address')
                    ->body($e->getMessage())
                    ->send();
            }
        }
        
        public function table(Table $table): Table
        {
            return $table
                ->query(UserAddress::query()->where('user_id', auth()->id()))
                ->columns([
                    IconColumn::make('is_primary')
                        ->boolean()
                        ->label('Primary')
                        ->color('warning')
                        ->size('lg')
                        ->sortable(),
                    TextColumn::make('address_line')
                        ->label('Address')
                        ->description(fn (UserAddress $record): string => 
                            "{$record->city}, {$record->state} {$record->postal_code}")
                        ->searchable()
                        ->sortable()
                        ->weight('medium'),
                    TextColumn::make('country')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('address_type')
                        ->badge()
                        ->color(fn (string $state): string => 
                            match ($state) {
                                'Home' => 'success',
                                'Office' => 'info',
                                'Other' => 'gray',
                                default => 'gray',
                            })
                        ->searchable(),
                    TextColumn::make('phone_number')
                        ->icon('heroicon-o-phone')
                        ->searchable()
                        ->toggleable(),
                ])
                ->filters([
                    Filter::make('address_type')
                        ->label('Filter by Address Type')
                        ->form([
                            \Filament\Forms\Components\Select::make('address_type')
                                ->options([
                                    'Home' => 'Home',
                                    'Office' => 'Office',
                                    'Other' => 'Other',
                                ])
                                ->placeholder('All Types')
                                ->multiple(),
                        ])
                        ->query(function ($query, array $data) {
                            if (!empty($data['address_type'])) {
                                $query->whereIn('address_type', $data['address_type']);
                            }
                        }),
                    Filter::make('is_primary')
                        ->label('Primary Address')
                        ->toggle()
                        ->query(fn ($query) => $query->where('is_primary', true)),
                ])
                ->filtersLayout(FiltersLayout::AboveContent)
                ->actions([
                    Action::make('set_primary')
                        ->label('Set as Primary')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->hidden(fn (UserAddress $record): bool => $record->is_primary)
                        ->action(fn (UserAddress $record) => $this->setPrimaryAddress($record)),
                    ActionGroup::make([
                        Tables\Actions\EditAction::make()
                            ->url(fn (UserAddress $record): string => "/user-addresses/{$record->id}/edit")
                            ->icon('heroicon-o-pencil-square'),
                        Tables\Actions\DeleteAction::make()
                            ->icon('heroicon-o-trash')
                            ->requiresConfirmation()
                            ->modalHeading('Delete Address')
                            ->modalDescription('Are you sure you want to delete this address? This action cannot be undone.')
                            ->modalSubmitActionLabel('Yes, delete address')
                            ->modalIcon('heroicon-o-exclamation-triangle')
                            ->hidden(fn (UserAddress $record): bool => 
                                $record->is_primary && UserAddress::where('user_id', auth()->id())->count() <= 1)
                            ->action(fn (UserAddress $record) => $this->deleteAddress($record)),
                    ]),
                ])
                ->defaultSort('is_primary', 'desc')
                ->striped()
                ->paginated([10, 25, 50, 100])
                ->emptyStateHeading('No addresses found')
                ->emptyStateDescription('You haven\'t added any addresses yet. Click the button below to add your first address.')
                ->emptyStateIcon('heroicon-o-map-pin')
                ->emptyStateActions([
                    Tables\Actions\Action::make('create')
                        ->label('Add New Address')
                        ->url('/user-addresses/create')
                        ->icon('heroicon-o-plus')
                        ->button(),
                ]);
        }
    }
?>
<x-layouts.app>
    @volt('user-addresses')
        <x-app.container>
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-5 gap-4">
                <div>
                    <x-app.heading title="My Addresses" description="Manage your shipping and billing addresses" :border="false" />
                    <p class="text-sm text-gray-500 mt-1">You can add multiple addresses and set one as your primary address</p>
                </div>
                <x-button tag="a" href="/user-addresses/create" class="flex items-center gap-x-2 self-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add New Address
                </x-button>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    {{ $this->table }}
                </div>
            </div>
            
            <div class="mt-6 text-sm text-gray-500 bg-gray-50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-1">Address Management Tips</h4>
                        <ul class="list-disc ml-5 space-y-1">
                            <li>Your primary address will be used as the default for shipping and billing</li>
                            <li>You can add multiple addresses for different purposes (home, office, etc.)</li>
                            <li>Make sure your phone number is correct to receive delivery notifications</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-app.container>
    @endvolt
</x-layouts.app>