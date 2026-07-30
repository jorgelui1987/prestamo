@extends('layouts.app')

@section('title', 'Configuracion')
@section('topbar', 'Configuracion')

@section('content')
    <h1 class="page-title">Configuración del Sistema</h1>
    <p class="page-subtitle">Parámetros generales, datos de la empresa y valores por defecto.</p>

    @if ($errors->any())
        <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
    @endif

    <form action="{{ route('config.update') }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="grid-2" style="align-items:start">
            <div class="card">
                <div class="card__header"><i class="bi bi-building"></i> &nbsp;Datos de la empresa</div>
                <div class="card__body">
                    <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 20px; flex-wrap: wrap;">
                        <div style="width: 80px; height: 80px; border-radius: 12px; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f8fafc;">
                            @if (!empty($config['empresa_logo']))
                                <img src="{{ \App\Support\StorageHelper::url($config['empresa_logo']) }}" style="width: 100%; height: 100%; object-fit: contain;">
                            @else
                                <i class="bi bi-image" style="font-size: 24px; color: #94a3b8;"></i>
                            @endif
                        </div>
                        <div style="flex-grow: 1;">
                            <label style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 6px;">Logo de la Empresa</label>
                            <input type="file" name="logo" class="form-control" accept="image/*" onchange="previewConfigLogo(event)">
                            <div id="configLogoPreview" style="display:none;margin-top:10px;text-align:center;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:12px">
                                <img id="configLogoPreviewImg" src="" alt="Vista previa" style="max-height:64px;max-width:100%;object-fit:contain;margin-bottom:6px">
                                <div style="font-size:11px;color:var(--text-muted)">Vista previa</div>
                            </div>
                            <small style="color: var(--text-muted); font-size: 11px;">Formatos permitidos: PNG, JPG, JPEG, WEBP. Máx: 2MB.</small>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:16px">
                        <label>Nombre de la empresa *</label>
                        <input type="text" name="empresa_nombre" class="form-control" value="{{ old('empresa_nombre', $config['empresa_nombre']) }}" required>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>RUC</label>
                            <input type="text" name="empresa_ruc" class="form-control" value="{{ old('empresa_ruc', $config['empresa_ruc']) }}">
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="empresa_telefono" class="form-control" value="{{ old('empresa_telefono', $config['empresa_telefono']) }}">
                        </div>
                        <div class="form-group full">
                            <label>Dirección</label>
                            <input type="text" name="empresa_direccion" class="form-control" value="{{ old('empresa_direccion', $config['empresa_direccion']) }}">
                        </div>
                        <div class="form-group full">
                            <label>Correo</label>
                            <input type="email" name="empresa_email" class="form-control" value="{{ old('empresa_email', $config['empresa_email']) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card__header"><i class="bi bi-sliders"></i> &nbsp;Parámetros financieros</div>
                <div class="card__body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Moneda *</label>
                            <select name="moneda" class="form-control" required>
                                @foreach ([
                                    'S/' => 'Soles (S/) - Perú',
                                    '$' => 'Dólares ($) - EE.UU. / Latam',
                                    'RD$' => 'Pesos (RD$) - Rep. Dominicana',
                                    'COP$' => 'Pesos (COP$) - Colombia',
                                    'MXN$' => 'Pesos (MXN$) - México',
                                    'CLP$' => 'Pesos (CLP$) - Chile',
                                    'ARS$' => 'Pesos (ARS$) - Argentina',
                                    'Bs' => 'Bolivianos (Bs) - Bolivia',
                                    'Gs' => 'Guaraníes (Gs) - Paraguay',
                                    'UYU$' => 'Pesos (UYU$) - Uruguay',
                                    'L' => 'Lempiras (L) - Honduras',
                                    'Q' => 'Quetzales (Q) - Guatemala',
                                    'C$' => 'Córdobas (C$) - Nicaragua',
                                    '₡' => 'Colones (₡) - Costa Rica',
                                ] as $m_val => $m_lbl)
                                    <option value="{{ $m_val }}" @selected(old('moneda', $config['moneda'] ?? 'S/') === $m_val)>{{ $m_lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tasa de interés por defecto (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="tasa_default" class="form-control" value="{{ old('tasa_default', $config['tasa_default']) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Mora diaria (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="mora_diaria" class="form-control" value="{{ old('mora_diaria', $config['mora_diaria']) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Días de gracia *</label>
                            <input type="number" min="0" max="60" name="dias_gracia" class="form-control" value="{{ old('dias_gracia', $config['dias_gracia']) }}" required>
                        </div>
                        <div class="form-group full">
                            <label>Zona Horaria *</label>
                            <select name="zona_horaria" class="form-control" required>
                                @foreach ([
                                    'America/Lima' => 'Perú (Lima)',
                                    'America/Bogota' => 'Colombia (Bogotá)',
                                    'America/Santo_Domingo' => 'República Dominicana (Santo Domingo)',
                                    'America/Caracas' => 'Venezuela (Caracas)',
                                    'America/Mexico_City' => 'México (CDMX)',
                                    'America/Santiago' => 'Chile (Santiago)',
                                    'America/Argentina/Buenos_Aires' => 'Argentina (Buenos Aires)',
                                    'America/Guayaquil' => 'Ecuador (Guayaquil)',
                                    'America/La_Paz' => 'Bolivia (La Paz)',
                                    'America/Asuncion' => 'Paraguay (Asunción)',
                                    'America/Montevideo' => 'Uruguay (Montevideo)',
                                    'America/Tegucigalpa' => 'Honduras (Tegucigalpa)',
                                    'America/Guatemala' => 'Guatemala (Guatemala)',
                                    'America/El_Salvador' => 'El Salvador (San Salvador)',
                                    'America/Costa_Rica' => 'Costa Rica (San José)',
                                    'America/Panama' => 'Panamá (Panamá)',
                                ] as $tz => $label)
                                    <option value="{{ $tz }}" @selected(old('zona_horaria', $config['zona_horaria'] ?? 'America/Lima') === $tz)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;margin-top:16px;font-size:12px;color:#1e40af">
                        <i class="bi bi-info-circle"></i> Estos valores se usan como sugerencia al crear nuevos préstamos y para el cálculo de mora.
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card__header"><i class="bi bi-cloud-arrow-down"></i> &nbsp;Respaldo Offline</div>
                <div class="card__body">
                    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:12px;font-size:12px;color:#166534">
                        <i class="bi bi-check-circle-fill"></i> El sistema ya guarda los cobros automáticamente en el celular cuando no hay conexión. No se necesita configuración adicional.
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Guardar configuración</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
function previewConfigLogo(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('configLogoPreview');
        const img = document.getElementById('configLogoPreviewImg');
        img.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
