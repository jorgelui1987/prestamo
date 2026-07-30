@extends('layouts.app')

@section('title', 'Mi Perfil')
@section('topbar', 'Mi Perfil')

@php
    $rmap = ['superadmin'=>'b-indigo','admin'=>'b-red','gerente'=>'b-purple','operador'=>'b-blue','cobrador'=>'b-yellow'];
    $roles = ['superadmin'=>'Super Administrador','admin'=>'Administrador','gerente'=>'Gerente','operador'=>'Operador','cobrador'=>'Cobrador'];
@endphp

@section('content')
    {{-- ===== Mensajes ===== --}}
    @error('avatar')
        <div class="alert alert-error" style="margin-bottom:16px"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
    @enderror

    {{-- ===== Cabecera ===== --}}
    <div class="profile-header">
        <div class="profile-cover"></div>
        <div class="profile-body">
            <div class="profile-avatar">
                @if ($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
                <label class="avatar-cam" for="avatarInput" title="Cambiar foto de perfil">
                    <i class="bi bi-camera"></i>
                </label>
            </div>
            <form id="avatarForm" action="{{ route('perfil.foto') }}" method="POST" enctype="multipart/form-data" style="display:none">
                @csrf
                <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp"
                       onchange="if(this.files.length) document.getElementById('avatarForm').submit()">
            </form>
            <div class="profile-meta">
                <h1>
                    {{ $user->name }}
                    <span class="badge-pill {{ $rmap[$user->rol] ?? 'b-gray' }}">{{ $roles[$user->rol] ?? ucfirst($user->rol) }}</span>
                    @if ($user->activo)<span class="badge-pill b-green">Activo</span>@else<span class="badge-pill b-gray">Inactivo</span>@endif
                </h1>
                <div class="email"><i class="bi bi-envelope"></i> {{ $user->email }}</div>
                <div class="chips">
                    <span class="chip"><i class="bi bi-telephone"></i> {{ $user->telefono ?: 'Sin teléfono' }}</span>
                    <span class="chip"><i class="bi bi-calendar-check"></i> Miembro desde {{ $user->created_at?->format('d/m/Y') }}</span>
                    <span class="chip"><i class="bi bi-hash"></i> ID {{ $user->id }}</span>
                    @if ($user->avatar)
                        <form action="{{ route('perfil.foto.delete') }}" method="POST" style="display:inline"
                              onsubmit="return confirm('¿Quitar tu foto de perfil?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="chip" style="border:none;cursor:pointer;color:#dc2626">
                                <i class="bi bi-trash"></i> Quitar foto
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($user->esSuperAdmin())
        {{-- ===== Acceso exclusivo Super Admin ===== --}}
        <div class="card" style="margin-bottom:24px;background:linear-gradient(135deg,#1e3a8a,#2563eb);border:none;color:#fff">
            <div class="card__body" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
                <div style="display:flex;align-items:center;gap:16px">
                    <div style="width:54px;height:54px;border-radius:14px;background:rgba(255,255,255,.15);display:grid;place-items:center;font-size:26px;flex-shrink:0">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <div style="font-size:17px;font-weight:800">Centro de Control de la Plataforma</div>
                        <div style="font-size:13px;opacity:.9">Tienes privilegios de super administrador: métricas globales y administración central.</div>
                    </div>
                </div>
                <a href="{{ route('superadmin.index') }}" class="btn btn-light"><i class="bi bi-box-arrow-up-right"></i> Abrir panel</a>
            </div>
        </div>
    @endif

    @if ($resumen)
        {{-- ===== Panel de administracion (solo admin) ===== --}}
        <h2 class="section-title"><i class="bi bi-speedometer2"></i> Panel de Administración</h2>
        <div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
            <div class="stat-card bg-blue">
                <i class="bi bi-people-fill stat-icon"></i>
                <div class="stat-label">USUARIOS</div>
                <div class="stat-value">{{ $resumen['usuarios'] }}</div>
                <div class="stat-foot">{{ $resumen['usuarios_activos'] }} activos</div>
            </div>
            <div class="stat-card bg-teal">
                <i class="bi bi-person-vcard stat-icon"></i>
                <div class="stat-label">CLIENTES</div>
                <div class="stat-value">{{ $resumen['clientes'] }}</div>
            </div>
            <div class="stat-card bg-purple">
                <i class="bi bi-cash-stack stat-icon"></i>
                <div class="stat-label">PRÉSTAMOS ACTIVOS</div>
                <div class="stat-value">{{ $resumen['prestamos_activos'] }}</div>
            </div>
            <div class="stat-card bg-orange">
                <i class="bi bi-gem stat-icon"></i>
                <div class="stat-label">EMPEÑOS VIGENTES</div>
                <div class="stat-value">{{ $resumen['empenos_vigentes'] }}</div>
            </div>
            <div class="stat-card bg-cyan">
                <i class="bi bi-activity stat-icon"></i>
                <div class="stat-label">ACCIONES HOY</div>
                <div class="stat-value">{{ $resumen['acciones_hoy'] }}</div>
            </div>
            <a href="{{ route('usuarios.index') }}" class="stat-card bg-red" style="text-decoration:none">
                <i class="bi bi-gear-wide-connected stat-icon"></i>
                <div class="stat-label">GESTIÓN</div>
                <div class="stat-value" style="font-size:18px">Administrar</div>
                <div class="stat-foot">Usuarios y accesos</div>
            </a>
        </div>

        <div class="quick-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px">
            <a href="{{ route('usuarios.create') }}" class="quick-card"><div class="q-icon bg-blue"><i class="bi bi-person-plus"></i></div><div><div class="q-title">Nuevo usuario</div><div class="q-desc">Crear acceso</div></div></a>
            <a href="{{ route('config.index') }}" class="quick-card"><div class="q-icon bg-purple"><i class="bi bi-sliders"></i></div><div><div class="q-title">Configuración</div><div class="q-desc">Parámetros</div></div></a>
            <a href="{{ route('auditoria.index') }}" class="quick-card"><div class="q-icon bg-teal"><i class="bi bi-shield-check"></i></div><div><div class="q-title">Auditoría</div><div class="q-desc">Bitácora</div></div></a>
            <a href="{{ route('reportes.index') }}" class="quick-card"><div class="q-icon bg-orange"><i class="bi bi-file-earmark-spreadsheet"></i></div><div><div class="q-title">Reportes</div><div class="q-desc">Exportar</div></div></a>
        </div>
    @endif

    {{-- ===== Tabs ===== --}}
    <div class="tabs">
        <button class="tab-btn active" data-tab="datos" onclick="showTab(this,'datos')"><i class="bi bi-person"></i> Datos personales</button>
        @if (auth()->user()->rol === 'admin' && auth()->user()->tenant)
            <button class="tab-btn" data-tab="suscripcion" onclick="showTab(this,'suscripcion')"><i class="bi bi-building-fill"></i> Mi Suscripción</button>
        @endif
        @if (auth()->user()->esSuperAdmin())
            <button class="tab-btn" data-tab="logo" onclick="showTab(this,'logo')"><i class="bi bi-image"></i> Logo de la Plataforma</button>
        @endif
        <button class="tab-btn" data-tab="seguridad" onclick="showTab(this,'seguridad')"><i class="bi bi-lock"></i> Seguridad</button>
        <button class="tab-btn" data-tab="2fa" onclick="showTab(this,'2fa')"><i class="bi bi-shield-check"></i> 2FA</button>
        <button class="tab-btn" data-tab="actividad" onclick="showTab(this,'actividad')"><i class="bi bi-clock-history"></i> Actividad</button>
    </div>

    {{-- Datos --}}
    <div class="tab-pane active" id="tab-datos">
        <div class="grid-2" style="align-items:start">
            <div class="card">
                <div class="card__header">Editar datos personales</div>
                <div class="card__body">
                    @if ($errors->updatePerfil ?? false) @endif
                    <form action="{{ route('perfil.update') }}" method="POST">
                        @csrf @method('PUT')
                        <div class="form-group" style="margin-bottom:16px">
                            <label>Nombre completo *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-group" style="margin-bottom:16px">
                            <label>Correo electrónico *</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $user->telefono) }}">
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card__header">Información de la cuenta</div>
                <div class="card__body">
                    <table class="info-list">
                        <tr><th>Rol</th><td><span class="badge-pill {{ $rmap[$user->rol] ?? 'b-gray' }}">{{ $roles[$user->rol] ?? ucfirst($user->rol) }}</span></td></tr>
                        <tr><th>Estado</th><td>{{ $user->activo ? 'Activo' : 'Inactivo' }}</td></tr>
                        <tr><th>Fecha de registro</th><td>{{ $user->created_at?->format('d/m/Y H:i') }}</td></tr>
                        <tr><th>Última actualización</th><td>{{ $user->updated_at?->format('d/m/Y H:i') }}</td></tr>
                        <tr><th>Identificador</th><td>#{{ $user->id }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->rol === 'admin' && auth()->user()->tenant)
        @php
            $tenant = auth()->user()->tenant;
            $plan = $tenant->plan;
            
            // Calcular uso actual
            $usoUsuarios = \App\Models\User::count();
            $usoClientes = \App\Models\Cliente::count();
            $usoPrestamos = \App\Models\Prestamo::count();
        @endphp
        {{-- Mi Suscripción --}}
        <div class="tab-pane" id="tab-suscripcion">
            <div class="grid-2" style="align-items:start; gap: 24px;">
                <div class="card">
                    <div class="card__header">Detalles de mi Plan</div>
                    <div class="card__body">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <span class="badge-pill b-indigo" style="font-size: 16px; padding: 8px 16px;">
                                {{ $plan->nombre ?? 'Sin Plan' }}
                            </span>
                            <h3 style="font-size: 28px; margin-top: 10px; font-weight: 800; color: var(--primary)">
                                S/ {{ number_format($plan->precio ?? 0, 2) }} <span style="font-size: 14px; font-weight: normal; color: var(--text-muted)">/ mes</span>
                            </h3>
                            <p style="color: var(--text-muted); font-size: 14px; margin-top: 5px;">
                                {{ $plan->descripcion ?? 'No hay descripción disponible.' }}
                            </p>
                        </div>

                        <table class="info-list">
                            <tr>
                                <th>Estado de la Cuenta</th>
                                <td>
                                    <span class="badge-pill {{ $tenant->estado === 'activo' ? 'b-green' : ($tenant->estado === 'prueba' ? 'b-blue' : 'b-red') }}">
                                        {{ ucfirst($tenant->estado) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha de Vencimiento</th>
                                <td>
                                    <strong>{{ $tenant->fecha_vencimiento ? $tenant->fecha_vencimiento->format('d/m/Y') : 'Ilimitado' }}</strong>
                                    @if ($tenant->estado === 'prueba')
                                        <br><small class="text-muted">(Periodo de prueba gratuita)</small>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card__header">Límites y Uso del Sistema</div>
                    <div class="card__body">
                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px;">
                                <strong>Usuarios Registrados</strong>
                                <span>{{ $usoUsuarios }} / {{ $plan->limite_usuarios ?? 'Ilimitado' }}</span>
                            </div>
                            @php
                                $pctUsuarios = min(($usoUsuarios / max($plan->limite_usuarios ?? 1, 1)) * 100, 100);
                            @endphp
                            <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                <div style="width: {{ $pctUsuarios }}%; height: 100%; background: var(--primary); border-radius: 4px;"></div>
                            </div>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px;">
                                <strong>Clientes Registrados</strong>
                                <span>{{ $usoClientes }} / {{ $plan->limite_clientes ?? 'Ilimitado' }}</span>
                            </div>
                            @php
                                $pctClientes = min(($usoClientes / max($plan->limite_clientes ?? 1, 1)) * 100, 100);
                            @endphp
                            <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                <div style="width: {{ $pctClientes }}%; height: 100%; background: #10b981; border-radius: 4px;"></div>
                            </div>
                        </div>

                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px;">
                                <strong>Préstamos Registrados</strong>
                                <span>{{ $usoPrestamos }} / {{ $plan->limite_prestamos ?? 'Ilimitado' }}</span>
                            </div>
                            @php
                                $pctPrestamos = min(($usoPrestamos / max($plan->limite_prestamos ?? 1, 1)) * 100, 100);
                            @endphp
                            <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                <div style="width: {{ $pctPrestamos }}%; height: 100%; background: #f59e0b; border-radius: 4px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Seguridad --}}
    <div class="tab-pane" id="tab-seguridad">
        <div class="grid-2" style="align-items:start">
            <div class="card">
                <div class="card__header">Cambiar contraseña</div>
                <div class="card__body">
                    @if ($errors->any())
                        <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
                    @endif
                    <form action="{{ route('perfil.password') }}" method="POST">
                        @csrf @method('PUT')
                        <div class="form-group" style="margin-bottom:16px">
                            <label>Contraseña actual *</label>
                            <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                        </div>
                        <div class="form-group" style="margin-bottom:16px">
                            <label>Nueva contraseña *</label>
                            <input type="password" name="password" class="form-control" required autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label>Confirmar nueva contraseña *</label>
                            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                        </div>
                        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px;margin-top:16px;font-size:12px;color:#92400e">
                            <i class="bi bi-shield-lock"></i> Usa una contraseña de al menos 6 caracteres. No la compartas con nadie.
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-primary"><i class="bi bi-key"></i> Actualizar contraseña</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- 2FA --}}
    <div class="tab-pane" id="tab-2fa">
        <div class="grid-2" style="align-items:start">
            <div class="card">
                <div class="card__header"><i class="bi bi-shield-check"></i> Verificación en dos pasos (2FA)</div>
                <div class="card__body">
                    @if ($errors->two_factor ?? false)
                        <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first('two_factor') }}</div>
                    @endif

                    @if (session('2fa_backup_codes'))
                        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:16px;margin-bottom:20px">
                            <div style="font-weight:700;font-size:14px;color:#166534;margin-bottom:8px">
                                <i class="bi bi-check-circle-fill"></i> Tus códigos de respaldo
                            </div>
                            <p style="font-size:12px;color:#166534;margin-bottom:12px">
                                Guarda estos códigos en un lugar seguro. Cada código solo puede usarse una vez.
                            </p>
                            <div style="background:#fff;border:1px solid #bbf7d0;border-radius:8px;padding:12px;font-family:monospace;font-size:13px;line-height:2">
                                @foreach (session('2fa_backup_codes') as $code)
                                    <div>{{ $code }}</div>
                                @endforeach
                            </div>
                            <div style="margin-top:12px;font-size:12px;color:#166534">
                                <i class="bi bi-printer"></i> 
                                <a href="#" onclick="window.print();return false;" style="color:#166534;font-weight:600">Imprimir</a>
                                &nbsp;·&nbsp;
                                <i class="bi bi-download"></i>
                                <a href="#" onclick="downloadBackupCodes();return false;" style="color:#166534;font-weight:600">Descargar</a>
                            </div>
                        </div>
                    @endif

                    @if ($user->two_factor_enabled)
                        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:16px;margin-bottom:20px;display:flex;align-items:center;gap:12px">
                            <div style="width:40px;height:40px;background:#22c55e;border-radius:10px;display:grid;place-items:center;color:#fff;font-size:20px;flex-shrink:0">
                                <i class="bi bi-shield-fill-check"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:14px;color:#166534">2FA activo</div>
                                <div style="font-size:12px;color:#166534">Tu cuenta está protegida con verificación en dos pasos.</div>
                            </div>
                        </div>

                        <form action="{{ route('2fa.disable') }}" method="POST" onsubmit="return confirm('¿Estás seguro de desactivar la verificación en dos pasos?')">
                            @csrf
                            <div class="form-group" style="margin-bottom:16px">
                                <label>Ingresa tu contraseña para desactivar 2FA</label>
                                <input type="password" name="password" class="form-control" required autocomplete="current-password">
                            </div>
                            <div class="form-actions" style="display:flex;gap:8px;flex-wrap:wrap">
                                <button type="submit" class="btn btn-danger"><i class="bi bi-shield-slash"></i> Desactivar 2FA</button>
                                <a href="#" class="btn btn-light" onclick="event.preventDefault();document.getElementById('regenerateCodesForm').submit();">
                                    <i class="bi bi-arrow-clockwise"></i> Regenerar códigos
                                </a>
                            </div>
                        </form>
                        <form id="regenerateCodesForm" action="{{ route('2fa.regenerate-codes') }}" method="POST" style="display:none">
                            @csrf
                            <input type="hidden" name="password" id="regeneratePassword">
                        </form>
                        <script>
                            document.querySelector('[onclick*="regenerateCodesForm"]')?.addEventListener('click', function(e) {
                                e.preventDefault();
                                const pwd = prompt('Ingresa tu contraseña para regenerar los códigos de respaldo:');
                                if (pwd) {
                                    document.getElementById('regeneratePassword').value = pwd;
                                    document.getElementById('regenerateCodesForm').submit();
                                }
                            });
                        </script>
                    @else
                        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px;margin-bottom:20px;display:flex;align-items:center;gap:12px">
                            <div style="width:40px;height:40px;background:#ef4444;border-radius:10px;display:grid;place-items:center;color:#fff;font-size:20px;flex-shrink:0">
                                <i class="bi bi-shield-exclamation"></i>
                            </div>
                            <div>
                                <div style="font-weight:700;font-size:14px;color:#991b1b">2FA inactivo</div>
                                <div style="font-size:12px;color:#991b1b">Tu cuenta no tiene verificación en dos pasos. Actívala para mayor seguridad.</div>
                            </div>
                        </div>

                        <form action="{{ route('2fa.enable') }}" method="POST">
                            @csrf
                            <div class="form-group" style="margin-bottom:16px">
                                <label>Ingresa tu contraseña para activar 2FA</label>
                                <input type="password" name="password" class="form-control" required autocomplete="current-password">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-shield-check"></i> Activar 2FA</button>
                            </div>
                        </form>
                    @endif

                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;margin-top:16px;font-size:12px;color:#1e40af">
                        <i class="bi bi-info-circle"></i> 
                        <strong>¿Cómo funciona?</strong><br>
                        Al activar 2FA, cada vez que inicies sesión se te enviará un código de 6 dígitos a tu correo electrónico. 
                        También recibirás 10 códigos de respaldo que puedes usar si no recibes el código.
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->esSuperAdmin())
        {{-- Logo de la Plataforma --}}
        <div class="tab-pane" id="tab-logo">
            <div class="card" style="max-width:560px">
                <div class="card__header">Logo global de la plataforma</div>
                <div class="card__body">
                    <p style="font-size:14px;color:var(--text-muted);margin-bottom:20px">
                        Sube el logotipo oficial de tu plataforma SaaS. Este logo se mostrará en la barra lateral (sidebar) y en la pantalla de inicio de sesión para todos los usuarios.
                    </p>

                    @php
                        $logoPlataforma = \App\Models\Configuracion::get('empresa_logo');
                    @endphp

                    <div style="text-align:center;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:12px;padding:24px;margin-bottom:20px">
                        @if ($logoPlataforma)
                            <img src="{{ \App\Support\StorageHelper::url($logoPlataforma) }}" alt="Logo de la Plataforma" style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:12px">
                            <div style="font-size:12px;color:var(--text-muted)">Logo actual</div>
                        @else
                            <div style="font-size:48px;color:#94a3b8;margin-bottom:12px"><i class="bi bi-image"></i></div>
                            <div style="font-size:13px;color:var(--text-muted)">No se ha subido ningún logo personalizado. Se muestra el logo por defecto.</div>
                        @endif
                    </div>

                    <form action="{{ route('perfil.logo-plataforma') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group" style="margin-bottom:16px">
                            <label>Seleccionar imagen (PNG, JPG, WEBP - Máx. 2MB)</label>
                            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp" required onchange="previewLogo(event)">
                            <div id="logoPreview" style="display:none;margin-top:12px;text-align:center;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:12px;padding:16px">
                                <img id="logoPreviewImg" src="" alt="Vista previa" style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:8px">
                                <div style="font-size:12px;color:var(--text-muted)">Vista previa</div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn-primary"><i class="bi bi-upload"></i> Subir logotipo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Actividad --}}
    <div class="tab-pane" id="tab-actividad">
        <div class="card">
            <div class="card__header">Mi actividad reciente</div>
            <div class="card__body">
                @if ($actividad->isEmpty())
                    <p style="color:var(--text-muted);text-align:center;padding:24px">Aún no hay actividad registrada.</p>
                @else
                    @php $amap = ['creo'=>'#22c55e','actualizo'=>'#3b82f6','elimino'=>'#ef4444','inicio sesion'=>'#8b5cf6','cierre sesion'=>'#94a3b8']; @endphp
                    <div class="timeline">
                        @foreach ($actividad as $a)
                            <div class="timeline-item">
                                <span class="timeline-dot" style="background:{{ $amap[$a->accion] ?? '#94a3b8' }}"></span>
                                <div class="t-title">{{ ucfirst($a->accion) }} @if($a->modulo) · {{ $a->modulo }} @endif @if($a->referencia) <span style="color:var(--text-muted)">({{ $a->referencia }})</span> @endif</div>
                                <div class="t-time"><i class="bi bi-clock"></i> {{ $a->created_at->format('d/m/Y H:i:s') }} · IP {{ $a->ip ?? '—' }}</div>
                            </div>
                        @endforeach
                    </div>
                    @if (auth()->user()->esAdmin())
                        <div style="margin-top:16px"><a href="{{ route('auditoria.index') }}" class="btn btn-light btn-sm"><i class="bi bi-shield-check"></i> Ver auditoría completa</a></div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showTab(btn, tab) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + tab).classList.add('active');
    }

    function previewLogo(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logoPreview');
            const img = document.getElementById('logoPreviewImg');
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    // Si hay errores de contraseña, abrir pestana seguridad
    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', () => {
            const segBtn = document.querySelector('[data-tab="seguridad"]');
            if (segBtn) showTab(segBtn, 'seguridad');
        });
    @endif
</script>
@endpush
