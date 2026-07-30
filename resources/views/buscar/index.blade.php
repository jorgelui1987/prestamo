@extends('layouts.app')

@section('title', 'Búsqueda')
@section('topbar', 'Resultados de búsqueda')

@section('content')
    <div style="margin-bottom:22px">
        <h1 class="page-title"><i class="bi bi-search" style="color:var(--primary)"></i> Búsqueda global</h1>
        @if ($q !== '')
            <p class="page-subtitle">{{ $total }} resultado(s) para «<strong>{{ $q }}</strong>»</p>
        @else
            <p class="page-subtitle">Escribe al menos 2 caracteres en el buscador del encabezado.</p>
        @endif
    </div>

    {{-- Buscador en página --}}
    <form action="{{ route('buscar') }}" method="GET" class="card" style="margin-bottom:24px">
        <div class="card__body" style="display:flex;gap:10px">
            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Buscar por nombre, documento, código, teléfono..." autofocus>
            <button class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
        </div>
    </form>

    @if ($q !== '' && $total === 0)
        <div class="card"><div class="placeholder-box">
            <div class="p-icon"><i class="bi bi-emoji-frown"></i></div>
            <h2>Sin resultados</h2>
            <p>No encontramos coincidencias para «{{ $q }}». Prueba con otro término.</p>
        </div></div>
    @endif

    {{-- Clientes --}}
    @if ($clientes->isNotEmpty())
        <div class="card" style="margin-bottom:20px">
            <div class="card__header"><span><i class="bi bi-people-fill"></i> Clientes</span> <span class="badge-pill b-blue">{{ $clientes->count() }}</span></div>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Código</th><th>Nombre</th><th>Documento</th><th>Teléfono</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($clientes as $c)
                            <tr>
                                <td><strong>{{ $c->codigo }}</strong></td>
                                <td>{{ $c->nombre_completo }}</td>
                                <td>{{ $c->documento ?: '—' }}</td>
                                <td>{{ $c->telefono ?: '—' }}</td>
                                <td style="text-align:right"><a href="{{ route('clientes.edit', $c) }}" class="btn btn-light btn-sm"><i class="bi bi-eye"></i> Ver</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Préstamos --}}
    @if ($prestamos->isNotEmpty())
        <div class="card" style="margin-bottom:20px">
            <div class="card__header"><span><i class="bi bi-cash-stack"></i> Préstamos</span> <span class="badge-pill b-purple">{{ $prestamos->count() }}</span></div>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Código</th><th>Cliente</th><th>Monto</th><th>Saldo</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($prestamos as $p)
                            <tr>
                                <td><strong>{{ $p->codigo }}</strong></td>
                                <td>{{ $p->cliente?->nombre_completo ?? '—' }}</td>
                                <td>S/ {{ number_format($p->monto, 2) }}</td>
                                <td>S/ {{ number_format($p->saldo, 2) }}</td>
                                <td><span class="badge-pill {{ $p->estado === 'mora' ? 'b-red' : ($p->estado === 'pagado' ? 'b-green' : 'b-blue') }}">{{ ucfirst($p->estado) }}</span></td>
                                <td style="text-align:right"><a href="{{ route('prestamos.show', $p) }}" class="btn btn-light btn-sm"><i class="bi bi-eye"></i> Ver</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Empeños --}}
    @if ($empenos->isNotEmpty())
        <div class="card" style="margin-bottom:20px">
            <div class="card__header"><span><i class="bi bi-gem"></i> Empeños</span> <span class="badge-pill b-yellow">{{ $empenos->count() }}</span></div>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Código</th><th>Artículo</th><th>Cliente</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($empenos as $e)
                            <tr>
                                <td><strong>{{ $e->codigo }}</strong></td>
                                <td>{{ $e->articulo }}</td>
                                <td>{{ $e->cliente?->nombre_completo ?? '—' }}</td>
                                <td><span class="badge-pill {{ $e->estado === 'vigente' ? 'b-green' : 'b-gray' }}">{{ ucfirst($e->estado) }}</span></td>
                                <td style="text-align:right"><a href="{{ route('empenos.show', $e) }}" class="btn btn-light btn-sm"><i class="bi bi-eye"></i> Ver</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
