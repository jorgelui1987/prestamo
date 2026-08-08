<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <title>@yield('title', 'Panel') | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" integrity="sha384-9nhczxUqK87bcKHh20fSQcTGD4qq5GhayNYSYWqwBkINBhOfQLg/P5HG5lF1urn4" crossorigin="anonymous"></script>
</head>
<body>
<div class="app">
    {{-- ============ SIDEBAR ============ --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar__brand">
            @php
                $logo = \App\Models\Configuracion::get('empresa_logo');
                $nombreEmpresa = \App\Models\Configuracion::get('empresa_nombre', 'SISTEMA PRÉSTAMOS');
            @endphp
            @if (!empty($logo))
                <div class="logo" style="background: none; border: none; width: 45px; height: 45px; overflow: hidden; border-radius: 8px;">
                    <img src="{{ \App\Support\StorageHelper::url($logo) }}" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
            @else
                <div class="logo"><i class="bi bi-cash-coin"></i></div>
            @endif
            <div>
                <div class="title" style="font-size: 13px; font-weight: 800; text-transform: uppercase; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ $nombreEmpresa }}
                </div>
                <div class="subtitle">Pro · Cobranzas</div>
            </div>
        </div>

        <nav class="sidebar__nav">
            @php
                $rol = auth()->user()->rol ?? null;
                $su = auth()->user()?->esSuperAdmin() ?? false;
                $puede = fn (array $roles) => !$su && in_array($rol, $roles, true);

                $secciones = [
                    'Operación' => [
                        ['dashboard', 'Panel Principal', 'bi-grid-1x2-fill', ['admin','gerente','operador','cobrador']],
                        ['clientes.index', 'Clientes', 'bi-people-fill', ['admin','gerente','operador','cobrador']],
                        ['prestamos.index', 'Préstamos', 'bi-cash-stack', ['admin','gerente','operador','cobrador']],
                        ['prestamos.buscar-global', 'Buscar Préstamo', 'bi-search-heart', ['admin','gerente','operador','cobrador']],
                        ['cobranzas.index', 'Cobranzas', 'bi-wallet2', ['admin','gerente','operador','cobrador']],
                        ['pagos.index', 'Pagos / Cuotas', 'bi-credit-card-2-front', ['admin','gerente','operador','cobrador']],
                        ['mora.index', 'Mora', 'bi-exclamation-triangle-fill', ['admin','gerente','operador','cobrador']],
                        ['empenos.index', 'Empeños', 'bi-gem', ['admin','gerente','operador']],
                    ],
                    'Finanzas' => [
                        ['reportes.index', 'Reportes', 'bi-file-earmark-spreadsheet', ['admin','gerente']],
                        ['reportes.rastreo', 'Rastreo', 'bi-geo-alt-fill', ['admin','gerente']],
                        ['caja.index', 'Caja', 'bi-cash', ['admin','gerente','operador']],
                        ['corte.index', 'Corte de Caja', 'bi-journal-check', ['admin','gerente']],
                        ['auditoria.index', 'Auditoría', 'bi-shield-check', ['admin','gerente']],
                    ],
                    'Administración' => [
                        ['usuarios.index', 'Usuarios', 'bi-person-badge', ['admin']],
                        ['config.index', 'Configuración', 'bi-gear-fill', ['admin']],
                    ],
                ];
            @endphp

            @foreach ($secciones as $titulo => $items)
                @php $visibles = array_filter($items, fn ($i) => $puede($i[3])); @endphp
                @if (count($visibles))
                    <div class="nav-section">{{ $titulo }}</div>
                    @foreach ($visibles as [$route, $label, $icon, $r])
                        <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs(explode('.', $route)[0].'*') ? 'active' : '' }}">
                            <i class="bi {{ $icon }}"></i> <span>{{ $label }}</span>
                        </a>
                    @endforeach
                @endif
            @endforeach

            @if (auth()->user()?->esSuperAdmin())
                <div class="nav-section">Plataforma</div>
                <a href="{{ route('superadmin.index') }}" class="nav-link {{ request()->routeIs('superadmin.index') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock-fill"></i> <span>Centro de Control</span>
                </a>
                <a href="{{ route('superadmin.tenants.index') }}" class="nav-link {{ request()->routeIs('superadmin.tenants*') ? 'active' : '' }}">
                    <i class="bi bi-building-fill"></i> <span>Empresas y Planes</span>
                </a>
                <a href="{{ route('auditoria.index') }}" class="nav-link {{ request()->routeIs('auditoria*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i> <span>Auditoría Global</span>
                </a>
            @endif

            @if (!auth()->user()?->esSuperAdmin())
                <div class="nav-section">Acceso Móvil</div>
                <a href="{{ route('movil.index') }}" class="nav-link" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                    <i class="bi bi-phone-vibrate-fill"></i> <span>App Celular</span>
                </a>
            @endif
        </nav>

        <div class="sidebar__footer">
            v1.0 · &copy; {{ date('Y') }} Préstamos Pro
        </div>
    </aside>

    {{-- ============ MAIN ============ --}}
    <div class="main">
        <header class="topbar">
            <div class="topbar__left">
                <button class="menu-toggle" onclick="toggleSidebar()" aria-label="Abrir menú">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar__title">@yield('topbar', 'Panel de Control')</div>
            </div>

            <form class="topbar__search" action="{{ route('buscar') }}" method="GET">
                <i class="bi bi-search" style="color:#94a3b8"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar cliente, préstamo, documento..." autocomplete="off">
            </form>

            <div class="topbar__right">
                <div class="noti">
                    <button class="icon-btn" type="button" onclick="toggleNoti(event)" aria-label="Notificaciones">
                        <i class="bi bi-bell"></i>
                        @if (($notificaciones['total'] ?? 0) > 0)
                            <span class="badge">{{ $notificaciones['total'] }}</span>
                        @endif
                    </button>
                    <div class="noti-panel" id="notiPanel">
                        <div class="noti-head"><i class="bi bi-bell"></i> Notificaciones</div>
                        @forelse ($notificaciones['items'] ?? [] as $n)
                            <a href="{{ $n['url'] }}" class="noti-item">
                                <span class="noti-ic" style="background:{{ $n['color'] }}"><i class="bi {{ $n['icono'] }}"></i></span>
                                <div><div class="noti-t">{{ $n['titulo'] }}</div><div class="noti-d">{{ $n['detalle'] }}</div></div>
                            </a>
                        @empty
                            <div class="noti-empty"><i class="bi bi-check2-circle"></i> Sin notificaciones pendientes</div>
                        @endforelse
                    </div>
                </div>
                <a href="{{ route('perfil.show') }}" class="user-menu" title="Ver mi perfil">
                    <div class="avatar">
                        @if (auth()->user()?->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
                        @else
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div class="name">{{ auth()->user()->name ?? 'Usuario' }}</div>
                        <div class="role">{{ ucfirst(auth()->user()->rol ?? 'operador') }}</div>
                    </div>
                </a>
                <form action="{{ route('logout') }}" method="POST" style="margin-left:4px">
                    @csrf
                    <button class="icon-btn" type="submit" title="Cerrar sesión" style="background:none;border:none">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </header>

        <main class="content">
            @if (session('ok'))
                <div class="alert alert-success"><i class="bi bi-check-circle"></i> {{ session('ok') }}</div>
            @endif
            @yield('content')
        </main>
    </div>

    {{-- Fondo oscuro para cerrar el menú en móvil --}}
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarBackdrop').classList.toggle('show');
    }
    function toggleNoti(e) {
        e.stopPropagation();
        document.getElementById('notiPanel').classList.toggle('open');
    }
    // Cerrar el panel de notificaciones al hacer clic fuera
    document.addEventListener('click', function (e) {
        const panel = document.getElementById('notiPanel');
        if (panel && !e.target.closest('.noti')) panel.classList.remove('open');
    });
    // Cerrar el menú al tocar un enlace en móvil
    document.querySelectorAll('.sidebar__nav .nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 860) {
                document.getElementById('sidebar').classList.remove('open');
                document.getElementById('sidebarBackdrop').classList.remove('show');
            }
        });
    });
</script>
@stack('scripts')
</body>
</html>
