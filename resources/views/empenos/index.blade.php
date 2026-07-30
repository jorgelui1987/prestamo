@extends('layouts.app')

@section('title', 'Empenos')
@section('topbar', 'Empenos')

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap">
        <div>
            <h1 class="page-title">Empeños</h1>
            <p class="page-subtitle" style="margin:0">Préstamos con garantía de artículos en custodia.</p>
        </div>
        <a href="{{ route('empenos.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo Empeño</a>
    </div>

    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
        <div class="stat-card bg-purple">
            <i class="bi bi-gem stat-icon"></i>
            <div class="stat-label">TOTAL EMPEÑOS</div>
            <div class="stat-value">{{ $resumen['total'] }}</div>
        </div>
        <div class="stat-card bg-teal">
            <i class="bi bi-check-circle stat-icon"></i>
            <div class="stat-label">VIGENTES</div>
            <div class="stat-value">{{ $resumen['vigentes'] }}</div>
        </div>
        <div class="stat-card bg-red">
            <i class="bi bi-exclamation-triangle stat-icon"></i>
            <div class="stat-label">VENCIDOS</div>
            <div class="stat-value">{{ $resumen['vencidos'] }}</div>
        </div>
        <div class="stat-card bg-blue">
            <i class="bi bi-cash-stack stat-icon"></i>
            <div class="stat-label">CAPITAL EN EMPEÑOS</div>
            <div class="stat-value">S/ {{ number_format($resumen['capital'], 2) }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card__header" style="gap:12px;flex-wrap:wrap">
            <span>Listado ({{ $empenos->total() }})</span>
            <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <select name="estado" class="form-control" style="width:auto" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    @foreach (['vigente'=>'Vigentes','vencido'=>'Vencidos','recuperado'=>'Recuperados','rematado'=>'Rematados'] as $v=>$l)
                        <option value="{{ $v }}" @selected($estado===$v)>{{ $l }}</option>
                    @endforeach
                </select>
                <div class="topbar__search" style="margin:0">
                    <i class="bi bi-search" style="color:#94a3b8"></i>
                    <input type="text" name="q" value="{{ $buscar }}" placeholder="Código, artículo o cliente...">
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Código</th><th>Artículo</th><th>Cliente</th><th>Tasación</th><th>Prestado</th><th>Recuperar</th><th>Vence</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    @forelse ($empenos as $e)
                        @php $bmap = ['vigente'=>'b-blue','recuperado'=>'b-green','vencido'=>'b-red','rematado'=>'b-gray']; @endphp
                        <tr>
                            <td><strong>{{ $e->codigo }}</strong></td>
                            <td>{{ $e->articulo }}</td>
                            <td>{{ $e->cliente->nombre_completo ?? '—' }}</td>
                            <td>S/ {{ number_format($e->valor_tasacion, 2) }}</td>
                            <td>S/ {{ number_format($e->monto_prestado, 2) }}</td>
                            <td><strong>S/ {{ number_format($e->total_recuperar, 2) }}</strong></td>
                            <td>{{ \Illuminate\Support\Carbon::parse($e->fecha_vencimiento)->format('d/m/Y') }}</td>
                            <td><span class="badge-pill {{ $bmap[$e->estado] ?? 'b-gray' }}">{{ ucfirst($e->estado) }}</span></td>
                            <td style="white-space:nowrap">
                                <a href="{{ route('empenos.show', $e) }}" class="btn btn-light btn-sm" title="Ver"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('empenos.edit', $e) }}" class="btn btn-light btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('empenos.destroy', $e) }}" method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este empeño?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:36px">No se encontraron empeños.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:0 20px 18px">{{ $empenos->links() }}</div>
    </div>
@endsection
