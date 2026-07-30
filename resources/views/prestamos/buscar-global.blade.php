@extends('layouts.app')

@section('title', 'Buscar Préstamo')
@section('topbar', 'Buscador Global de Préstamos')

@section('content')
    <h1 class="page-title">Buscador Global</h1>
    <p class="page-subtitle">Encuentra préstamos por código, cliente, monto o estado.</p>

    {{-- Filtros de búsqueda --}}
    <div class="card" style="margin-bottom:22px">
        <div class="card__body" style="padding:20px">
            <form method="GET" action="{{ route('prestamos.buscar-global') }}" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:12px;align-items:end">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px">Buscar</label>
                    <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Código, cliente, documento...">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px">Tipo</label>
                    <select name="tipo" class="form-control" style="padding:8px 12px;border-radius:8px;border:1px solid #cbd5e1;width:100%">
                        <option value="todo" {{ $tipoBusqueda == 'todo' ? 'selected' : '' }}>Todo</option>
                        <option value="codigo" {{ $tipoBusqueda == 'codigo' ? 'selected' : '' }}>Código</option>
                        <option value="cliente" {{ $tipoBusqueda == 'cliente' ? 'selected' : '' }}>Cliente</option>
                        <option value="monto" {{ $tipoBusqueda == 'monto' ? 'selected' : '' }}>Monto</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px">Estado</label>
                    <select name="estado" class="form-control" style="padding:8px 12px;border-radius:8px;border:1px solid #cbd5e1;width:100%">
                        <option value="">Todos</option>
                        <option value="activo" {{ $estado == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="mora" {{ $estado == 'mora' ? 'selected' : '' }}>Mora</option>
                        <option value="pagado" {{ $estado == 'pagado' ? 'selected' : '' }}>Pagado</option>
                        <option value="anulado" {{ $estado == 'anulado' ? 'selected' : '' }}>Anulado</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px">Cobrador</label>
                    <select name="cobrador_id" class="form-control" style="padding:8px 12px;border-radius:8px;border:1px solid #cbd5e1;width:100%">
                        <option value="">Todos</option>
                        @foreach ($cobradores as $c)
                            <option value="{{ $c->id }}" {{ $cobradorId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="padding:8px 20px">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </form>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="stats-grid" style="grid-template-columns:repeat(5,1fr)">
        <div class="stat-card bg-blue">
            <i class="bi bi-cash-stack stat-icon"></i>
            <div class="stat-label">TOTAL PRÉSTAMOS</div>
            <div class="stat-value">{{ $totalPrestamos }}</div>
        </div>
        <div class="stat-card bg-teal">
            <i class="bi bi-check-circle stat-icon"></i>
            <div class="stat-label">ACTIVOS</div>
            <div class="stat-value">{{ $totalActivos }}</div>
        </div>
        <div class="stat-card bg-orange">
            <i class="bi bi-exclamation-triangle stat-icon"></i>
            <div class="stat-label">EN MORA</div>
            <div class="stat-value">{{ $totalMora }}</div>
        </div>
        <div class="stat-card bg-cyan">
            <i class="bi bi-check2-all stat-icon"></i>
            <div class="stat-label">PAGADOS</div>
            <div class="stat-value">{{ $totalPagados }}</div>
        </div>
        <div class="stat-card bg-purple">
            <i class="bi bi-piggy-bank stat-icon"></i>
            <div class="stat-label">CAPITAL PENDIENTE</div>
            <div class="stat-value">${{ number_format($capitalPendiente, 0) }}</div>
        </div>
    </div>

    {{-- Resultados --}}
    @if ($q || $estado || $cobradorId || $fechaDesde || $fechaHasta)
        <div style="display:flex;align-items:center;justify-content:space-between;margin:18px 0 12px">
            <span style="font-size:14px;color:#64748b">
                <i class="bi bi-search-heart"></i> 
                <strong>{{ $totalResultados }}</strong> préstamo(s) encontrado(s)
            </span>
            <a href="{{ route('prestamos.buscar-global') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        </div>
    @endif

    <div class="card">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Saldo</th>
                        <th>Cuotas</th>
                        <th>Estado</th>
                        <th>Cobrador</th>
                        <th>Inicio</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prestamos as $p)
                        <tr>
                            <td><strong>{{ $p->codigo }}</strong></td>
                            <td>
                                <a href="{{ route('clientes.edit', $p->cliente) }}" class="text-decoration-none">
                                    {{ $p->cliente->nombres }} {{ $p->cliente->apellidos }}
                                </a>
                                <br><small style="color:#94a3b8">{{ $p->cliente->documento }}</small>
                            </td>
                            <td>${{ number_format($p->monto, 0) }}</td>
                            <td>${{ number_format($p->saldo, 0) }}</td>
                            <td>{{ $p->cuotas_count ?? $p->cuotas->count() }}</td>
                            <td>
                                @php
                                    $badge = match($p->estado) {
                                        'activo' => 'b-green',
                                        'mora' => 'b-red',
                                        'pagado' => 'b-blue',
                                        'anulado' => 'b-gray',
                                        default => 'b-yellow'
                                    };
                                @endphp
                                <span class="badge-pill {{ $badge }}">{{ ucfirst($p->estado) }}</span>
                            </td>
                            <td>{{ $p->cobrador?->name ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->fecha_inicio)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('prestamos.show', $p) }}" class="btn btn-sm btn-outline-primary" style="padding:4px 8px;font-size:13px">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:40px 20px;color:#94a3b8">
                                <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
                                @if ($q || $estado || $cobradorId || $fechaDesde || $fechaHasta)
                                    No se encontraron préstamos con esos filtros.
                                @else
                                    Ingresa un término de búsqueda para comenzar.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="display:flex;justify-content:center;margin-top:18px">
        {{ $prestamos->links() }}
    </div>
@endsection