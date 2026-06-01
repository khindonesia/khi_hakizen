<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateFileController extends Controller
{
    /**
     * Download a private exported order CSV file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadExport(Request $request, string $filename): StreamedResponse
    {
        // 1. Ensure user is authenticated
        abort_unless(auth()->check(), 403);

        // 2. Ensure user has the admin role
        abort_unless(auth()->user()->hasRole('admin'), 403);

        // 3. Sanitize filename to prevent Directory Traversal attacks (e.g. ../../)
        $cleanFilename = basename($filename);
        $path = 'exports/' . $cleanFilename;

        // 4. Verify file exists on the private disk
        abort_unless(Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->download($path);
    }
}
