<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Procesa la foto de perfil: recorta a un cuadrado centrado, redimensiona
 * a un tamaño fijo y la guarda optimizada en el disco "public".
 */
class AvatarService
{
    /** Lado (px) del avatar final. */
    public const SIZE = 400;

    /** Carpeta dentro del disco public. */
    public const DIR = 'avatars';

    /**
     * Recorta + redimensiona la imagen y la guarda como JPG.
     * Devuelve la ruta relativa (p. ej. "avatars/xxxx.jpg").
     * Si la extensión GD no está disponible, guarda el archivo original.
     */
    public static function store(UploadedFile $file): string
    {
        Storage::disk('public')->makeDirectory(self::DIR);

        if (! function_exists('imagecreatetruecolor')) {
            return $file->store(self::DIR, 'public');
        }

        $src = self::createImage($file);
        if (! $src) {
            return $file->store(self::DIR, 'public');
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $x = (int) (($w - $side) / 2);
        $y = (int) (($h - $side) / 2);

        $dst = imagecreatetruecolor(self::SIZE, self::SIZE);
        // Fondo blanco por si la imagen original tiene transparencia.
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, $x, $y, self::SIZE, self::SIZE, $side, $side);

        $relative = self::DIR.'/'.Str::random(40).'.jpg';
        imagejpeg($dst, Storage::disk('public')->path($relative), 85);

        imagedestroy($src);
        imagedestroy($dst);

        return $relative;
    }

    /** Crea el recurso GD a partir del archivo subido. */
    private static function createImage(UploadedFile $file)
    {
        $path = $file->getRealPath();

        return match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => @imagecreatefromstring(file_get_contents($path)),
        };
    }
}
