@extends('layouts.app')

@section('title', 'Usuarios')
@section('topbar', 'Usuarios')

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap">
        <div>
            <h1 class="page-title">Usuarios</h1>
            <p class="page-subtitle" style="margin:0">Gestión de usuarios, roles y accesos al sistema.</p>
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Nuevo Usuario</a>
    </div>

    <div class="card">
        <div class="card__header" style="gap:12px;flex-wrap:wrap">
            <span>Listado ({{ $usuarios->total() }})</span>
            <form method="GET" class="topbar__search" style="margin:0">
                <i class="bi bi-search" style="color:#94a3b8"></i>
                <input type="text" name="q" value="{{ $buscar }}" placeholder="Nombre o correo...">
            </form>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Usuario</th><th>Correo</th><th>Rol</th><th>Teléfono</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $u)
                        @php
                            $rmap = ['superadmin'=>'b-indigo','admin'=>'b-red','gerente'=>'b-purple','operador'=>'b-blue','cobrador'=>'b-yellow'];
                            $toggleLabel = $u->activo ? 'Desactivar' : 'Activar';
                            $toggleClase = $u->activo ? 'btn-warning' : 'btn-success';
                            $toggleIcono = $u->activo ? 'bi-pause-fill' : 'bi-play-fill';
                        @endphp
                        <tr>
                            <td style="display:flex;align-items:center;gap:10px">
                                <span class="user-menu" style="pointer-events:none"><span class="avatar" style="width:32px;height:32px;font-size:13px">@if ($u->avatar_url)<img src="{{ $u->avatar_url }}" alt="{{ $u->name }}">@else{{ strtoupper(substr($u->name,0,1)) }}@endif</span></span>
                                <strong>{{ $u->name }}</strong>
                            </td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge-pill {{ $rmap[$u->rol] ?? 'b-gray' }}">{{ $u->rol_label }}</span></td>
                            <td>{{ $u->telefono ?: '—' }}</td>
                            <td>
                                @if ($u->activo)
                                    <span class="badge-pill b-green">Activo</span>
                                @else
                                    <span class="badge-pill b-gray">Inactivo</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap">
                                <a href="{{ route('usuarios.edit', $u) }}" class="btn btn-light btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                                @if ($u->id !== auth()->id())
                                    <a href="{{ route('usuarios.toggle-activo', $u) }}" class="btn btn-sm {{ $toggleClase }}" title="{{ $toggleLabel }}">
                                        <i class="bi {{ $toggleIcono }}"></i> {{ $toggleLabel }}
                                    </a>
                                    <form action="{{ route('usuarios.destroy', $u) }}" method="POST" style="display:inline" onsubmit="return confirm('Seguro de eliminar a ' + '{{ $u->name }}' + '?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:36px">No hay usuarios.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:0 20px 18px">{{ $usuarios->links() }}</div>
    </div>
@endsection

@push('scripts')
<script>
// Sin scripts adicionales necesarios
</script>
@endpush


