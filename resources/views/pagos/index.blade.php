@extends('layouts.app')

@section('title', 'Pagos')
@section('topbar', 'Pagos / Cuotas')

@section('content')
    <h1 class="page-title">Historial de Pagos</h1>
    <p class="page-subtitle">Todos los pagos registrados en el sistema.</p>

    <div class="stats-grid" style="grid-template-columns:repeat(2,1fr);max-width:560px">
        <div class="stat-card bg-teal">
            <i class="bi bi-calendar-day stat-icon"></i>
            <div class="stat-label">COBRADO HOY</div>
            <div class="stat-value">S/ {{ number_format($totalHoy, 2) }}</div>
        </div>
        <div class="stat-card bg-blue">
            <i class="bi bi-calendar-month stat-icon"></i>
            <div class="stat-label">COBRADO ESTE MES</div>
            <div class="stat-value">S/ {{ number_format($totalMes, 2) }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card__header" style="gap:12px;flex-wrap:wrap">
            <span>Pagos ({{ $pagos->total() }})</span>
            <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <input type="date" name="desde" value="{{ $desde }}" class="form-control" style="width:auto" title="Desde">
                <input type="date" name="hasta" value="{{ $hasta }}" class="form-control" style="width:auto" title="Hasta">
                <div class="topbar__search" style="margin:0">
                    <i class="bi bi-search" style="color:#94a3b8"></i>
                    <input type="text" name="q" value="{{ $buscar }}" placeholder="Código de pago o préstamo...">
                </div>
                <button class="btn btn-light btn-sm"><i class="bi bi-funnel"></i> Filtrar</button>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Pago</th><th>Fecha</th><th>Préstamo</th><th>Cliente</th><th>Monto</th><th>Método</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    @forelse ($pagos as $p)
                        <tr>
                            <td><strong>{{ $p->codigo }}</strong></td>
                            <td>{{ \Illuminate\Support\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</td>
                            <td>
                                @if ($p->prestamo)
                                    <a href="{{ route('prestamos.show', $p->prestamo) }}" style="color:var(--primary);font-weight:600">{{ $p->prestamo->codigo }}</a>
                                @else — @endif
                            </td>
                            <td>{{ $p->prestamo?->cliente?->nombre_completo ?? '—' }}</td>
                            <td><strong>S/ {{ number_format($p->monto, 2) }}</strong></td>
                            <td><span class="badge-pill b-gray">{{ ucfirst($p->metodo) }}</span></td>
                            <td>
                                <form action="{{ route('pagos.destroy', $p) }}" method="POST" style="display:inline" onsubmit="return confirm('¿Anular este pago? Se revertirá el saldo de la cuota.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Anular"><i class="bi bi-x-circle"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:36px">No hay pagos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:0 20px 18px">{{ $pagos->links() }}</div>
    </div>
@endsection
