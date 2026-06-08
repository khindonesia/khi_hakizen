<?php
use function Laravel\Folio\{middleware, name};
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Livewire\Volt\Component;
use Wave\Traits\HasDynamicFields;
use App\Models\UserKyc;
use Illuminate\Support\Facades\Storage;

middleware('auth');
name('settings.profile');

new class extends Component implements HasForms {
    use InteractsWithForms, HasDynamicFields;

    public ?array $data = [];
    public ?string $avatar = null;

    // Properti murni untuk menampung data temporary upload KYC
    public $ktp_file = null;
    public $selfie_file = null;

    public string $kycStatus = 'none';
    public ?string $kycRejectionReason = null;

    // Menyimpan path string lama untuk ditampilkan di view jika ada
    public ?string $oldKtpPath = null;
    public ?string $oldSelfiePath = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->kycStatus = $user->kyc_status ?? 'none';

        $kycData = $user->kyc;
        $this->kycRejectionReason = $kycData?->rejection_reason;
        $this->oldKtpPath = $kycData?->ktp_image_path;
        $this->oldSelfiePath = $kycData?->selfie_image_path;

        // ISI DATA AWAL: Petakan seluruh kolom model User & UserKyc ke dalam Form State
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'age' => $user->age,
            'birth_year' => $user->birth_year,
            'occupation' => $user->occupation,
            'reason_for_joining' => $user->reason_for_joining,
            'country' => $user->country,
            'province' => $user->province,
            'city' => $user->city,
            'nik' => $kycData?->nik ?? '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Profile Information')
                    ->columns(2) // Buat layout menjadi 2 kolom agar lebih rapi dan ringkas
                    ->schema([
                        TextInput::make('name')->label('Full Name')->required()->columnSpan(1),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->required()
                            ->rules('sometimes|required|email|unique:users,email,' . auth()->user()->id)
                            ->columnSpan(1),
                        TextInput::make('phone_number')->label('Phone Number')->tel()->maxLength(20),
                        TextInput::make('occupation')->label('Occupation')->maxLength(100),
                        TextInput::make('age')->label('Age')->numeric()->minValue(1)->maxValue(150),
                        TextInput::make('birth_year')->label('Birth Year')->numeric()->length(4),

                        // Regional / Address Info
                        TextInput::make('country')->label('Country')->maxLength(100),
                        TextInput::make('province')->label('Province')->maxLength(100),
                        TextInput::make('city')->label('City')->maxLength(100)->columnSpan(2),

                        Textarea::make('reason_for_joining')->label('Reason for Joining')->rows(3)->maxLength(500)->columnSpan(2),
                    ]),

                Section::make('Identity Verification (KYC)')
                    ->description('Verify your identity to upgrade your account to Member.')
                    ->schema([
                        Placeholder::make('rejection_info')
                            ->label('⚠️ KYC Rejected')
                            ->content($this->kycRejectionReason)
                            ->visible(fn() => $this->kycStatus === 'rejected')
                            ->extraAttributes(['class' => 'text-red-600 bg-red-50 p-3 rounded-lg border border-red-200']),

                        TextInput::make('nik')->label('NIK (Nomor Induk Kependudukan)')->length(16)->numeric()->required(fn() => in_array($this->kycStatus, ['none', 'rejected']))->disabled(fn() => in_array($this->kycStatus, ['pending', 'approved'])),
                    ]),
            ])
            ->statePath('data');
    }

    public function save()
    {
        if (in_array($this->kycStatus, ['none', 'rejected'])) {
            $this->validate(
                [
                    'data.nik' => 'required|numeric|digits:16',
                    'ktp_file' => 'required',
                    'selfie_file' => 'required',
                ],
                [
                    'data.nik.required' => 'NIK wajib diisi.',
                    'data.nik.digits' => 'NIK harus berjumlah 16 digit.',
                    'ktp_file.required' => 'Foto KTP wajib diunggah.',
                    'selfie_file.required' => 'Foto Selfie wajib diunggah.',
                ],
            );
        }

        $this->validate([
            'avatar' => 'sometimes|nullable|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $state = $this->form->getState();
        $user = auth()->user();

        if ($this->avatar != null) {
            $this->saveNewUserAvatar();
        }

        // SIMPAN DATA: Mass update semua kolom profil baru dari Form State ke Model User
        $user->forceFill([
            'name' => $state['name'],
            'email' => $state['email'],
            'phone_number' => $state['phone_number'] ?? null,
            'age' => $state['age'] ?? null,
            'birth_year' => $state['birth_year'] ?? null,
            'occupation' => $state['occupation'] ?? null,
            'reason_for_joining' => $state['reason_for_joining'] ?? null,
            'country' => $state['country'] ?? null,
            'province' => $state['province'] ?? null,
            'city' => $state['city'] ?? null,
        ]);

        if (in_array($this->kycStatus, ['none', 'rejected']) && !empty($state['nik'])) {
            $user->kyc_status = 'pending';
            $this->kycStatus = 'pending';
        }
        $user->save();

        if (in_array($user->kyc_status, ['pending', 'approved'])) {
            $userId = $user->id;

            $ktpPath = $this->oldKtpPath;
            if (str_starts_with($this->ktp_file, 'data:image')) {
                $ktpPath = 'private/kyc/ktp/' . $userId . '_ktp_' . time() . '.png';
                $ktpImg = \Intervention\Image\ImageManagerStatic::make($this->ktp_file)
                    ->resize(1200, 1200, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('png', 80);
                Storage::put($ktpPath, $ktpImg);
            }

            $selfiePath = $this->oldSelfiePath;
            if (str_starts_with($this->selfie_file, 'data:image')) {
                $selfiePath = 'private/kyc/selfie/' . $userId . '_selfie_' . time() . '.png';
                $selfieImg = \Intervention\Image\ImageManagerStatic::make($this->selfie_file)
                    ->resize(1200, 1200, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('png', 80);
                Storage::put($selfiePath, $selfieImg);
            }

            $user->kyc()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => $state['nik'],
                    'ktp_image_path' => $ktpPath,
                    'selfie_image_path' => $selfiePath,
                    'rejection_reason' => null,
                ],
            );
        }

        $fieldsToSave = config('profile.fields');
        $this->saveDynamicFields($fieldsToSave);

        Notification::make()->title('Successfully saved your profile & KYC application')->success()->send();
    }

    private function saveNewUserAvatar(): void
    {
        $userId = auth()->id();
        $username = auth()->user()->username;
        $safeUsername = preg_replace('/[^a-zA-Z0-9_\-]/', '', $username);
        if (empty($safeUsername)) {
            $safeUsername = (string) $userId;
        }

        $path = 'avatars/' . $userId . '_' . $safeUsername . '.png';
        $image = \Intervention\Image\ImageManagerStatic::make($this->avatar)
            ->resize(800, 800, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('png', 90);

        Storage::disk('public')->put($path, $image);
        auth()->user()->avatar = $path;
        auth()->user()->save();

        $this->js('window.dispatchEvent(new CustomEvent("refresh-avatar"));');
    }
};
?>

<x-layouts.app>
    <x-app.settings-layout title="Settings"
        description="Manage your account avatar, name, email, and identity verification.">

        @volt('settings.profile')
            <div x-data="{
                uploadCropEl: null,
                uploadLoading: null,
                fileTypes: null,
                avatar: @entangle('avatar'),
                ktp_file: @entangle('ktp_file'),
                selfie_file: @entangle('selfie_file'),
            
                readFile() {
                    input = document.getElementById('upload');
                    if (input.files && input.files[0]) {
                        let reader = new FileReader();
                        let fileType = input.files[0].name.split('.').pop().toLowerCase();
                        if (this.fileTypes.indexOf(fileType) < 0) {
                            alert('Invalid file type. Please select a JPG or PNG file.');
                            return false;
                        }
                        reader.onload = function(e) {
                            uploadCrop.bind({ url: e.target.result, orientation: 4 });
                        }
                        reader.readAsDataURL(input.files[0]);
                    }
                },
                applyImageCrop() {
                    let that = this;
                    uploadCrop.result({ type: 'base64', size: 'original', format: 'png', quality: 1 }).then(function(base64) {
                        that.avatar = base64;
                        document.getElementById('preview').src = that.avatar;
                    });
                },
            
                readKycFile(inputElId, previewImgId, targetProperty) {
                    let input = document.getElementById(inputElId);
                    if (input.files && input.files[0]) {
                        let reader = new FileReader();
                        reader.onload = (e) => {
                            this[targetProperty] = e.target.result;
                            document.getElementById(previewImgId).src = e.target.result;
                            document.getElementById(previewImgId).classList.remove('hidden');
                        }
                        reader.readAsDataURL(input.files[0]);
                    }
                }
            }" x-init="uploadCropEl = document.getElementById('upload-crop');
            uploadLoading = document.getElementById('uploadLoading');
            fileTypes = ['jpg', 'jpeg', 'png'];
            
            if (document.getElementById('upload')) {
                document.getElementById('upload').addEventListener('change', function() {
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'profile-avatar-crop' } }));
                    uploadCropEl.classList.add('hidden');
                    uploadLoading.classList.remove('hidden');
                    setTimeout(function() {
                        uploadLoading.classList.add('hidden');
                        uploadCropEl.classList.remove('hidden');
                        if (typeof(uploadCrop) != 'undefined') { uploadCrop.destroy(); }
                        uploadCrop = new Croppie(uploadCropEl, {
                            viewport: { width: 190, height: 190, type: 'square' },
                            boundary: { width: 190, height: 190 },
                            enableExif: true,
                        });
                        readFile();
                    }, 800);
                });
            }" class="relative w-full">

                <div
                    class="mb-5 lg:px-10 flex items-center justify-between p-4 rounded-xl border {{ $kycStatus === 'approved'
                        ? 'bg-green-50 border-green-200 text-green-800'
                        : ($kycStatus === 'pending'
                            ? 'bg-yellow-50 border-yellow-200 text-yellow-800'
                            : ($kycStatus === 'rejected'
                                ? 'bg-red-50 border-red-200 text-red-800'
                                : 'bg-zinc-50 border-zinc-200 text-zinc-800')) }}">
                    <div>
                        <h4 class="font-bold">Account Verification Status</h4>
                        <p class="text-sm opacity-90">
                            {{ $kycStatus === 'approved'
                                ? 'Your identity is fully verified. You are a premium Member.'
                                : ($kycStatus === 'pending'
                                    ? 'Your KYC application is currently being reviewed by admin.'
                                    : ($kycStatus === 'rejected'
                                        ? 'Your KYC application was rejected. Please re-submit correct data.'
                                        : 'Verify your account identity to unlock more features.')) }}
                        </p>
                    </div>
                    <span
                        class="px-3 py-1 text-xs font-semibold uppercase tracking-wider rounded-full {{ $kycStatus === 'approved'
                            ? 'bg-green-200 text-green-900'
                            : ($kycStatus === 'pending'
                                ? 'bg-yellow-200 text-yellow-900'
                                : ($kycStatus === 'rejected'
                                    ? 'bg-red-200 text-red-900'
                                    : 'bg-zinc-200 text-zinc-900')) }}">
                        {{ $kycStatus }}
                    </span>
                </div>

                <form wire:submit="save" class="w-full">
                    <div class="relative flex flex-col lg:px-10">

                        <div class="relative flex-shrink-0 w-32 h-32 cursor-pointer group">
                            <img id="preview" src="{{ auth()->user()->avatar() . '?' . time() }}"
                                class="w-32 h-32 rounded-full">
                            <div class="absolute inset-0 w-full h-full">
                                <input type="file" id="upload" accept="image/jpeg,image/png,image/webp"
                                    class="absolute inset-0 z-20 w-full h-full opacity-0 cursor-pointer group">
                                <button type="button"
                                    class="absolute bottom-0 z-10 flex items-center justify-center w-10 h-10 mb-2 -ml-5 bg-black bg-opacity-75 rounded-full opacity-75 left-1/2 group-hover:opacity-100">
                                    <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('avatar')
                            <p class="mt-3 text-sm text-red-600">The avatar must be a valid image type.</p>
                        @enderror

                        <div class="w-full mt-8">
                            {{ $this->form }}
                        </div>

                        <div class="w-full mt-6 space-y-6 bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
                            <h4 class="font-bold text-zinc-900">KYC Documents Upload</h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-zinc-700">Foto KTP</label>
                                    <div
                                        class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-zinc-300 rounded-lg">
                                        <img id="ktp_preview" src="#" class="max-h-40 mb-3 hidden rounded border">
                                        @if ($oldKtpPath && !$ktp_file)
                                            <p class="text-xs text-green-600 mb-2 font-medium">✓ File KTP sudah terunggah
                                                sebelumnya</p>
                                        @endif
                                        <input type="file" id="ktp_input" accept="image/jpeg,image/png"
                                            @change="readKycFile('ktp_input', 'ktp_preview', 'ktp_file')"
                                            {{ in_array($kycStatus, ['pending', 'approved']) ? 'disabled' : '' }}
                                            class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200">
                                    </div>
                                    @error('ktp_file')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-zinc-700">Foto Selfie dengan KTP</label>
                                    <div
                                        class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-zinc-300 rounded-lg">
                                        <img id="selfie_preview" src="#" class="max-h-40 mb-3 hidden rounded border">
                                        @if ($oldSelfiePath && !$selfie_file)
                                            <p class="text-xs text-green-600 mb-2 font-medium">✓ File Selfie sudah terunggah
                                                sebelumnya</p>
                                        @endif
                                        <input type="file" id="selfie_input" accept="image/jpeg,image/png"
                                            @change="readKycFile('selfie_input', 'selfie_preview', 'selfie_file')"
                                            {{ in_array($kycStatus, ['pending', 'approved']) ? 'disabled' : '' }}
                                            class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200">
                                    </div>
                                    @error('selfie_file')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        <div class="w-full pt-6 text-right">
                            <x-button type="submit">Save Settings</x-button>
                        </div>
                    </div>
                </form>

                <div class="z-[99]">
                    <x-filament::modal id="profile-avatar-crop">
                        <div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-lg font-medium leading-6 text-zinc-900">Position and resize your photo</h3>
                                <div class="mt-2">
                                    <div id="upload-crop-container"
                                        class="relative flex items-center justify-center h-56 mt-5">
                                        <div id="uploadLoading" class="flex items-center justify-center w-full h-full">
                                            <svg class="w-5 h-5 mr-3 -ml-1 animate-spin text-zinc-400" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div id="upload-crop"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6">
                            <span class="flex w-full rounded-md shadow-sm">
                                <button
                                    @click="window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'profile-avatar-crop' }}));"
                                    class="inline-flex justify-center w-full px-4 py-2 mr-2 text-base font-medium leading-6 bg-white border rounded-md text-zinc-700 hover:text-zinc-500 sm:text-sm"
                                    type="button">Cancel</button>
                                <button
                                    @click="window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'profile-avatar-crop' }})); applyImageCrop()"
                                    class="inline-flex justify-center w-full px-4 py-2 ml-2 text-base font-medium leading-6 text-white bg-red-600 border rounded-md hover:bg-red-500 sm:text-sm"
                                    type="button">Apply</button>
                            </span>
                        </div>
                    </x-filament::modal>
                </div>
            </div>
        @endvolt
    </x-app.settings-layout>

    <x-slot:javascript>
        <style>
            #upload-crop-container .croppie-container .cr-resizer,
            #upload-crop-container .croppie-container .cr-viewport {
                box-shadow: 0 0 2000px 2000px rgba(255, 255, 255, 1) !important;
                border: 0px !important;
            }

            .croppie-container .cr-boundary {
                border-radius: 50% !important;
                overflow: hidden;
            }

            .croppie-container .cr-slider-wrap {
                margin-bottom: 0px !important;
            }

            .croppie-container {
                height: auto !important;
            }
        </style>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/exif-js/2.3.0/exif.min.js"></script>
        <link rel="stylesheet" type="text/css"
            href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.min.css">
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.2/croppie.min.js"></script>
    </x-slot>
</x-layouts.app>
