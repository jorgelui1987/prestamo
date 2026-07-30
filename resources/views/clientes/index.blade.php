@extends('layouts.app')

@section('title', 'Clientes')
@section('topbar', 'Clientes')

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap">
        <div>
            <h1 class="page-title">Clientes</h1>
            <p class="page-subtitle" style="margin:0">Administra la cartera de clientes del negocio.</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route('clientes.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Nuevo Cliente</a>
            <a href="{{ asset('plantilla_clientes.csv') }}" download class="btn btn-light"><i class="bi bi-download"></i> Plantilla Excel</a>
        </div>
    </div>

    <div class="card">
        <div class="card__header" style="gap:12px; flex-wrap:wrap">
            <span>Listado ({{ $clientes->total() }})</span>
            <form method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin:0">
                {{-- Filtro de Cobrador --}}
                <select name="cobrador_id" class="form-control" style="width:auto; padding:4px 10px; font-size:13px; height:32px;" onchange="this.form.submit()">
                    <option value="">— Todos los Cobradores —</option>
                    @foreach ($cobradores as $cob)
                        <option value="{{ $cob->id }}" @selected($cobradorId == $cob->id)>
                            {{ $cob->name }}
                        </option>
                    @endforeach
                </select>

                <div class="topbar__search" style="margin:0">
                    <i class="bi bi-search" style="color:#94a3b8"></i>
                    <input type="text" name="q" value="{{ $buscar }}" placeholder="Buscar por nombre, documento o código...">
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Código</th><th>Nombre</th><th>Documento</th><th>Teléfono</th><th>Ocupación</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $c)
                        <tr>
                            <td><strong>{{ $c->codigo }}</strong></td>
                            <td style="display:flex;align-items:center;gap:8px">
                                <span style="width:10px;height:10px;border-radius:50%;background:{{ $c->semaforo_color }};display:inline-block;flex-shrink:0" title="{{ $c->semaforo_label }}"></span>
                                {{ $c->nombre_completo }}
                            </td>
                            <td>{{ $c->tipo_documento }} {{ $c->documento ?: '—' }}</td>
                            <td>{{ $c->telefono ?: '—' }}</td>
                            <td>{{ $c->ocupacion ?: '—' }}</td>
                            <td>
                                @php $m = ['activo'=>'b-green','inactivo'=>'b-gray','moroso'=>'b-red']; @endphp
                                <span class="badge-pill {{ $m[$c->estado] ?? 'b-gray' }}">{{ ucfirst($c->estado) }}</span>
                            </td>
                            <td style="white-space:nowrap">
                                <a href="{{ route('clientes.edit', $c) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a>
                                @if (auth()->user()->rol !== 'cobrador')
                                    <form action="{{ route('clientes.destroy', $c) }}" method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este cliente?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:36px">No se encontraron clientes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:0 20px 18px">
            {{ $clientes->links() }}
        </div>
    </div>
@endsection
