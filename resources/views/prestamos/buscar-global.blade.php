@extends('layouts.app')

@section('title', 'Buscar Préstamo')
@section('topbar', 'Buscador Global de Préstamos')

@section('content')
<div class="container-fluid">
    {{-- Filtros de búsqueda --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('prestamos.buscar-global') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Código, cliente, documento, teléfono...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="todo" {{ $tipoBusqueda == 'todo' ? 'selected' : '' }}>Todo</option>
                        <option value="codigo" {{ $tipoBusqueda == 'codigo' ? 'selected' : '' }}>Código</option>
                        <option value="cliente" {{ $tipoBusqueda == 'cliente' ? 'selected' : '' }}>Cliente</option>
                        <option value="monto" {{ $tipoBusqueda == 'monto' ? 'selected' : '' }}>Monto</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="activo" {{ $estado == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="mora" {{ $estado == 'mora' ? 'selected' : '' }}>Mora</option>
                        <option value="pagado" {{ $estado == 'pagado' ? 'selected' : '' }}>Pagado</option>
                        <option value="anulado" {{ $estado == 'anulado' ? 'selected' : '' }}>Anulado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cobrador</label>
                    <select name="cobrador_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($cobradores as $c)
                            <option value="{{ $c->id }}" {{ $cobradorId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="stats-grid mb-4" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
        <div class="stat-card">
            <div class="stat-value">{{ $totalPrestamos }}</div>
            <div class="stat-label">Total Préstamos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--success)">{{ $totalActivos }}</div>
            <div class="stat-label">Activos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--danger)">{{ $totalMora }}</div>
            <div class="stat-label">En Mora</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--info)">{{ $totalPagados }}</div>
            <div class="stat-label">Pagados</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">${{ number_format($capitalPendiente, 0) }}</div>
            <div class="stat-label">Capital Pendiente</div>
        </div>
    </div>

    {{-- Resultados --}}
    @if ($q || $estado || $cobradorId || $fechaDesde || $fechaHasta)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="bi bi-search-heart"></i> 
                Resultados: <strong>{{ $totalResultados }}</strong> préstamo(s) encontrado(s)
            </h5>
            <a href="{{ route('prestamos.buscar-global') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Limpiar filtros
            </a>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Monto</th>
                    <th>Saldo</th>
                    <th>Cuotas</th>
                    <th>Estado</th>
                    <th>Cobrador</th>
                    <th>Inicio</th>
                    <th></th>
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
                            <br><small class="text-muted">{{ $p->cliente->documento }}</small>
                        </td>
                        <td>${{ number_format($p->monto, 0) }}</td>
                        <td>${{ number_format($p->saldo, 0) }}</td>
                        <td>{{ $p->cuotas_count ?? $p->cuotas->count() }}</td>
                        <td>
                            @php
                                $badge = match($p->estado) {
                                    'activo' => 'bg-success',
                                    'mora' => 'bg-danger',
                                    'pagado' => 'bg-info',
                                    'anulado' => 'bg-secondary',
                                    default => 'bg-warning'
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($p->estado) }}</span>
                        </td>
                        <td>{{ $p->cobrador?->name ?? '—' }}</td>
                        <td>{{ $p->fecha_inicio->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('prestamos.show', $p) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox" style="font-size:2rem"></i><br>
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

    <div class="d-flex justify-content-center mt-3">
        {{ $prestamos->links() }}
    </div>
</div>
@endsection