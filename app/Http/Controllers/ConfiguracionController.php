<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    /** Valores por defecto del sistema */
    public const DEFAULTS = [
        'empresa_nombre' => 'Sistema de Prestamos Pro',
        'empresa_ruc' => '',
        'empresa_direccion' => '',
        'empresa_telefono' => '',
        'empresa_email' => '',
        'moneda' => 'S/',
        'tasa_default' => '15',
        'mora_diaria' => '1',
        'dias_gracia' => '0',
        'empresa_logo' => '',
        'zona_horaria' => 'America/Lima',
        'enlace_respaldo_sheets' => '',
        // Configuración SMTP para correos
        'mail_host' => 'smtp.hostinger.com',
        'mail_port' => '587',
        'mail_username' => '',
        'mail_password' => '',
        'mail_encryption' => 'tls',
        'mail_from_address' => '',
        'mail_from_name' => 'Sistema de Prestamos',
    ];

    private function soloAdmin(): void
    {
        abort_unless(auth()->user() && auth()->user()->esAdmin(), 403, 'Acceso restringido a administradores.');
    }

    public function index()
    {
        $this->soloAdmin();

        $config = [];
        foreach (self::DEFAULTS as $clave => $default) {
            $config[$clave] = Configuracion::get($clave, $default);
        }

        return view('configuracion.index', compact('config'));
    }

    public function update(Request $request)
    {
        $this->soloAdmin();

        $data = $request->validate([
            'empresa_nombre' => ['required', 'string', 'max:150'],
            'empresa_ruc' => ['nullable', 'string', 'max:30'],
            'empresa_direccion' => ['nullable', 'string', 'max:200'],
            'empresa_telefono' => ['nullable', 'string', 'max:30'],
            'empresa_email' => ['nullable', 'email', 'max:120'],
            'moneda' => ['required', 'string', 'max:5'],
            'tasa_default' => ['required', 'numeric', 'min:0', 'max:100'],
            'mora_diaria' => ['required', 'numeric', 'min:0', 'max:100'],
            'dias_gracia' => ['required', 'integer', 'min:0', 'max:60'],
            'zona_horaria' => ['required', 'string', 'max:100'],
            'enlace_respaldo_sheets' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Procesar la subida del logo si existe
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img/logos'), $filename);
            $path = 'logos/' . $filename;
            Configuracion::set('empresa_logo', $path, 'general');
        }

        // Guardar el resto de configuraciones
        unset($data['logo']);
        foreach ($data as $clave => $valor) {
            Configuracion::set($clave, $valor, 'general');
        }

        return back()->with('ok', 'Configuración guardada correctamente.');
    }
}
