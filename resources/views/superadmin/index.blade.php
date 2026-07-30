@extends('layouts.app')

@section('title', 'Super Admin')
@section('topbar', 'Panel Super Administrador')

@php
    $rmap = ['superadmin'=>'b-indigo','admin'=>'b-red','gerente'=>'b-purple','operador'=>'b-blue','cobrador'=>'b-yellow'];
    $roles = ['superadmin'=>'Super Administrador','admin'=>'Administrador','gerente'=>'Gerente','operador'=>'Operador','cobrador'=>'Cobrador'];
    $amap = ['creo'=>'#22c55e','actualizo'=>'#3b82f6','elimino'=>'#ef4444','inicio sesion'=>'#8b5cf6','cierre sesion'=>'#94a3b8'];
@endphp

@section('content')
    <div style="margin-bottom:22px">
        <h1 class="page-title"><i class="bi bi-shield-lock-fill" style="color:var(--primary)"></i> Centro de Control</h1>
        <p class="page-subtitle">Visión global de la plataforma y administración central. Acceso exclusivo del super administrador.</p>
    </div>

    {{-- ===== KPIs globales ===== --}}
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
        <div class="stat-card bg-blue">
            <i class="bi bi-people-fill stat-icon"></i>
            <div class="stat-label">USUARIOS</div>
            <div class="stat-value">{{ number_format($metricas['usuarios']) }}</div>
            <div class="stat-foot">{{ number_format($metricas['usuarios_activos']) }} activos</div>
        </div>
        <div class="stat-card bg-teal">
            <i class="bi bi-person-vcard stat-icon"></i>
            <div class="stat-label">CLIENTES</div>
            <div class="stat-value">{{ number_format($metricas['clientes']) }}</div>
        </div>
        <div class="stat-card bg-purple">
            <i class="bi bi-cash-stack stat-icon"></i>
            <div class="stat-label">PRÉSTAMOS ACTIVOS</div>
            <div class="stat-value">{{ number_format($metricas['prestamos_activos']) }}</div>
        </div>
        <div class="stat-card bg-orange">
            <i class="bi bi-wallet2 stat-icon"></i>
            <div class="stat-label">CARTERA POR COBRAR</div>
            <div class="stat-value" style="font-size:20px">S/ {{ number_format($metricas['cartera'], 2) }}</div>
        </div>
        <div class="stat-card bg-cyan">
            <i class="bi bi-graph-up-arrow stat-icon"></i>
            <div class="stat-label">RECAUDADO (MES)</div>
            <div class="stat-value" style="font-size:20px">S/ {{ number_format($metricas['recaudado_mes'], 2) }}</div>
        </div>
        <div class="stat-card bg-red">
            <i class="bi bi-gem stat-icon"></i>
            <div class="stat-label">EMPEÑOS VIGENTES</div>
            <div class="stat-value">{{ number_format($metricas['empenos_vigentes']) }}</div>
        </div>
        <div class="stat-card bg-blue">
            <i class="bi bi-activity stat-icon"></i>
            <div class="stat-label">ACCIONES HOY</div>
            <div class="stat-value">{{ number_format($metricas['acciones_hoy']) }}</div>
        </div>
        <a href="{{ route('usuarios.create') }}" class="stat-card bg-teal" style="text-decoration:none">
            <i class="bi bi-person-plus stat-icon"></i>
            <div class="stat-label">ACCIÓN RÁPIDA</div>
            <div class="stat-value" style="font-size:18px">Nuevo usuario</div>
            <div class="stat-foot">Crear acceso</div>
        </a>
    </div>

    <div class="grid-2" style="align-items:start">
        {{-- ===== Usuarios por rol ===== --}}
        <div class="card">
            <div class="card__header">Usuarios por rol</div>
            <div class="card__body">
                <table class="info-list">
                    @forelse ($porRol as $rol => $total)
                        <tr>
                            <th><span class="badge-pill {{ $rmap[$rol] ?? 'b-gray' }}">{{ $roles[$rol] ?? ucfirst($rol) }}</span></th>
                            <td style="text-align:right;font-weight:700">{{ number_format($total) }}</td>
                        </tr>
                    @empty
                        <tr><td>Sin usuarios registrados.</td></tr>
                    @endforelse
                </table>
                <div style="margin-top:16px">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-light btn-sm"><i class="bi bi-people"></i> Gestionar usuarios</a>
                </div>
            </div>
        </div>

        {{-- ===== Información del sistema ===== --}}
        <div class="card">
            <div class="card__header">Información del sistema</div>
            <div class="card__body">
                <table class="info-list">
                    <tr><th>Aplicación</th><td>{{ $sistema['app'] }}</td></tr>
                    <tr><th>Entorno</th><td><span class="badge-pill {{ $sistema['entorno'] === 'production' ? 'b-green' : 'b-yellow' }}">{{ ucfirst($sistema['entorno']) }}</span></td></tr>
                    <tr><th>Versión Laravel</th><td>{{ $sistema['laravel'] }}</td></tr>
                    <tr><th>Versión PHP</th><td>{{ $sistema['php'] }}</td></tr>
                    <tr><th>Fecha del servidor</th><td>{{ $sistema['fecha'] }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Accesos de administración ===== --}}
    <h2 class="section-title"><i class="bi bi-grid-3x3-gap"></i> Administración central</h2>
    <div class="quick-grid" style="grid-template-columns:repeat(5,1fr);margin-bottom:24px">
        <a href="{{ route('superadmin.tenants.index') }}" class="quick-card"><div class="q-icon bg-indigo"><i class="bi bi-building"></i></div><div><div class="q-title">Empresas y Planes</div><div class="q-desc">SaaS Tenants</div></div></a>
        <a href="{{ route('usuarios.index') }}" class="quick-card"><div class="q-icon bg-blue"><i class="bi bi-person-badge"></i></div><div><div class="q-title">Usuarios y accesos</div><div class="q-desc">Roles y permisos</div></div></a>
        <a href="{{ route('config.index') }}" class="quick-card"><div class="q-icon bg-purple"><i class="bi bi-sliders"></i></div><div><div class="q-title">Configuración</div><div class="q-desc">Parámetros del sistema</div></div></a>
        <a href="{{ route('auditoria.index') }}" class="quick-card"><div class="q-icon bg-teal"><i class="bi bi-shield-check"></i></div><div><div class="q-title">Auditoría</div><div class="q-desc">Bitácora completa</div></div></a>
        <a href="{{ route('reportes.index') }}" class="quick-card"><div class="q-icon bg-orange"><i class="bi bi-file-earmark-spreadsheet"></i></div><div><div class="q-title">Reportes</div><div class="q-desc">Exportar datos</div></div></a>
    </div>

    {{-- ===== Configuración SMTP (solo Super Admin) ===== --}}
    @php
        $mailHost = \App\Models\Configuracion::get('mail_host', 'smtp.hostinger.com');
        $mailPort = \App\Models\Configuracion::get('mail_port', '587');
        $mailUsername = \App\Models\Configuracion::get('mail_username', '');
        $mailPassword = \App\Models\Configuracion::get('mail_password', '');
        $mailEncryption = \App\Models\Configuracion::get('mail_encryption', 'tls');
        $mailFromAddress = \App\Models\Configuracion::get('mail_from_address', '');
        $mailFromName = \App\Models\Configuracion::get('mail_from_name', 'Sistema de Prestamos');
    @endphp
    <div class="card" style="margin-bottom:24px">
        <div class="card__header"><i class="bi bi-envelope-paper-fill"></i> 📧 Configuración SMTP (Reportes automáticos)</div>
        <div class="card__body">
            @if (session('ok'))
                <div class="alert alert-success" style="margin-bottom:16px"><i class="bi bi-check-circle-fill"></i> {{ session('ok') }}</div>
            @endif
            <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;line-height:1.5">
                Configura el servidor de correo para que el sistema envíe los <strong>reportes diarios</strong> a los administradores de cada empresa.
            </p>
            <form action="{{ route('superadmin.smtp.guardar') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label>Servidor SMTP</label>
                        <input type="text" name="mail_host" class="form-control" value="{{ old('mail_host', $mailHost) }}">
                    </div>
                    <div class="form-group">
                        <label>Puerto</label>
                        <input type="text" name="mail_port" class="form-control" value="{{ old('mail_port', $mailPort) }}">
                    </div>
                    <div class="form-group">
                        <label>Usuario (correo)</label>
                        <input type="email" name="mail_username" class="form-control" value="{{ old('mail_username', $mailUsername) }}" placeholder="correo@tudominio.com">
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="mail_password" class="form-control" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label>Encriptación</label>
                        <select name="mail_encryption" class="form-control">
                            <option value="tls" @selected($mailEncryption === 'tls')>TLS</option>
                            <option value="ssl" @selected($mailEncryption === 'ssl')>SSL</option>
                            <option value="" @selected(empty($mailEncryption))>Ninguna</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Correo remitente (From)</label>
                        <input type="email" name="mail_from_address" class="form-control" value="{{ old('mail_from_address', $mailFromAddress) }}">
                    </div>
                    <div class="form-group full">
                        <label>Nombre del remitente</label>
                        <input type="text" name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $mailFromName) }}">
                    </div>
                </div>
                <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:12px;margin-top:12px;font-size:12px;color:#166534">
                    <i class="bi bi-check-circle-fill"></i> 
                    <strong>Hostinger:</strong> Servidor <strong>smtp.hostinger.com</strong> · Puerto <strong>587</strong> · TLS<br>
                    Usa el correo <strong>camila1987chile@tallerluitech.fun</strong> con su contraseña.
                </div>
                <div class="form-actions" style="margin-top:16px">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Guardar SMTP</button>
                    <a href="{{ route('reportes.enviar-diario') }}" class="btn btn-success" onclick="event.preventDefault(); document.getElementById('form-enviar-reporte').submit();">
                        <i class="bi bi-send-fill"></i> Enviar Reporte de Prueba
                    </a>
                </div>
            </form>
            <form id="form-enviar-reporte" action="{{ route('reportes.enviar-diario') }}" method="POST" style="display:none">@csrf</form>
        </div>
    </div>

    {{-- ===== Actividad global ===== --}}
    <div class="card">
        <div class="card__header">
            Actividad reciente de la plataforma
            <a href="{{ route('auditoria.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-right"></i> Ver todo</a>
        </div>
        <div class="card__body">
            @if ($actividad->isEmpty())
                <p style="color:var(--text-muted);text-align:center;padding:24px">Aún no hay actividad registrada.</p>
            @else
                <div class="timeline">
                    @foreach ($actividad as $a)
                        <div class="timeline-item">
                            <span class="timeline-dot" style="background:{{ $amap[$a->accion] ?? '#94a3b8' }}"></span>
                            <div class="t-title">
                                <strong>{{ $a->usuario_nombre }}</strong> · {{ ucfirst($a->accion) }}
                                @if($a->modulo) <span style="color:var(--text-muted)">en {{ $a->modulo }}</span> @endif
                                @if($a->referencia) ({{ $a->referencia }}) @endif
                            </div>
                            <div class="t-time"><i class="bi bi-clock"></i> {{ $a->created_at->format('d/m/Y H:i:s') }} · IP {{ $a->ip ?? '—' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
