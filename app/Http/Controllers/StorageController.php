<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    /**
     * Sirve archivos de storage/app/public sin necesidad de enlace simbólico.
     * Útil para hosting donde no se puede crear el symlink public/storage.
     */
    public function serve($path)
    {
        $fullPath = 'public/' . $path;

        if (!Storage::exists($fullPath)) {
            abort(404);
        }

        $file = Storage::get($fullPath);
        $mimeType = Storage::mimeType($fullPath);

        return response($file, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}