<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * Obtiene la URL pública de un archivo almacenado.
     * Soporta tanto rutas internas de storage como archivos en public/img/.
     */
    public static function url(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // Si la ruta ya es una URL completa, devolverla directamente
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Buscar primero en la carpeta pública public/img/{path}
        // Esto funciona para logos, avatars, firmas y cualquier otro tipo de archivo
        $publicImgPath = public_path('img/' . $path);
        if (file_exists($publicImgPath)) {
            return asset('img/' . $path);
        }

        // Si existe el symlink public/storage y el archivo está ahí, usarlo
        $publicStoragePath = public_path('storage/' . $path);
        if (is_link(public_path('storage')) && file_exists($publicStoragePath)) {
            return asset('storage/' . $path);
        }

        // Si no hay symlink pero el archivo existe físicamente en public/storage/
        if (file_exists($publicStoragePath)) {
            return asset('storage/' . $path);
        }

        // Fallback: servir a través del controlador StorageController
        // que busca en storage/app/public/ independientemente del symlink
        if (Storage::disk('public')->exists($path)) {
            return route('storage.serve', ['path' => $path]);
        }

        // No existe en ningún lado, devolver vacío
        return '';
    }
}
