<?php

use App\Filament\Resources\EbookResource;
use App\Models\Ebook;
use App\Models\User;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Support\Contracts\TranslatableContentDriver;
use Illuminate\Support\Facades\Hash;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('configures ebook uploads as an integrated file card', function (): void {
    $livewire = new class implements HasForms {
        public function dispatchFormEvent(mixed ...$args): void
        {
        }

        public function getActiveFormsLocale(): ?string
        {
            return null;
        }

        public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
        {
            return null;
        }

        public function getForm(string $name): ?Form
        {
            return null;
        }

        public function getFormComponentFileAttachment(string $statePath): ?TemporaryUploadedFile
        {
            return null;
        }

        public function getFormComponentFileAttachmentUrl(string $statePath): ?string
        {
            return null;
        }

        public function getFormSelectOptionLabels(string $statePath): array
        {
            return [];
        }

        public function getFormSelectOptionLabel(string $statePath): ?string
        {
            return null;
        }

        public function getFormSelectOptions(string $statePath): array
        {
            return [];
        }

        public function getFormSelectSearchResults(string $statePath, string $search): array
        {
            return [];
        }

        public function getFormUploadedFiles(string $statePath): ?array
        {
            return null;
        }

        public function getOldFormState(string $statePath): mixed
        {
            return null;
        }

        public function isCachingForms(): bool
        {
            return false;
        }

        public function removeFormUploadedFile(string $statePath, string $fileKey): void
        {
        }

        public function reorderFormUploadedFiles(string $statePath, array $fileKeys): void
        {
        }

        public function validate($rules = null, $messages = [], $attributes = []): array
        {
            return [];
        }

        public function currentlyValidatingForm(?ComponentContainer $form): void
        {
        }
    };

    $form = EbookResource::form(Form::make($livewire));
    $field = $form->getComponent('ebook_file');

    expect($field)->toBeInstanceOf(FileUpload::class);
    expect($field->getPanelLayout())->toBe('integrated');
    expect($field->isOpenable())->toBeTrue();
    expect($field->isDownloadable())->toBeTrue();
    expect($field->isPreviewable())->toBeFalse();
});

it('opens ebook downloads in a new tab on the library detail page', function (): void {
    $user = User::create([
        'name' => 'Library Reader',
        'email' => 'library-reader@example.com',
        'username' => 'library-reader',
        'password' => Hash::make('secret123'),
        'verified' => 1,
    ]);

    $book = Ebook::create([
        'title' => 'Arsip Laut Nusantara',
        'slug' => 'arsip-laut-nusantara',
        'cover_image' => 'covers/arsip-laut-nusantara.jpg',
        'author' => 'Tim Arsip KHI',
        'description' => 'Koleksi arsip laut untuk uji link unduhan.',
        'status' => 'PUBLISHED',
        'ebook_file' => 'ebooks/arsip-laut-nusantara.pdf',
    ]);

    actingAs($user);

    get(route('library.book', ['slug' => $book->slug]))
        ->assertOk()
        ->assertSeeHtml('object-contain')
        ->assertSeeHtml('target="_blank"')
        ->assertSeeHtml('rel="noopener noreferrer"');
});
