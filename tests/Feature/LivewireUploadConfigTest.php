<?php

use Livewire\Features\SupportFileUploads\FileUploadConfiguration;

it('allows livewire temp uploads up to 64mb', function (): void {
    expect(config('livewire.temporary_file_upload.rules'))->toBe([
        'required',
        'file',
        'max:65536',
    ]);

    expect(FileUploadConfiguration::rules())->toBe([
        'required',
        'file',
        'max:65536',
    ]);
});
