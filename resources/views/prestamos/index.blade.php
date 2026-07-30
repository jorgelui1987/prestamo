@extends('layouts.app')

@section('title', 'Préstamos')
@section('topbar', 'Préstamos')

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap">
        <div>
            <h1 class="page-title">Préstamos</h1>
            <p class="page-subtitle" style="margin:0">Registro y seguimiento de préstamos con su cronograma de cuotas.</p>
        </div>
        <a href="{{ route('prestamos.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo Préstamo</a>
    </div>

    {{-- Mini-resumen --}}
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
        <div class="stat-card bg-blue">
            <i class="bi bi-cash-stack stat-icon"></i>
            <div class="stat-label">TOTAL PRÉSTAMOS</div>
            <div class="stat-value">{{ $resumen['total'] }}</div>
        </div>
        <div class="stat-card bg-teal">
            <i class="bi bi-check-circle stat-icon"></i>
            <div class="stat-label">ACTIVOS</div>
            <div class="stat-value">{{ $resumen['activos'] }}</div>
        </div>
        <div class="stat-card bg-orange">
            <i class="bi bi-exclamation-triangle stat-icon"></i>
            <div class="stat-label">EN MORA</div>
            <div class="stat-value">{{ $resumen['mora'] }}</div>
        </div>
        <div class="stat-card bg-purple">
            <i class="bi bi-wallet2 stat-icon"></i>
            <div class="stat-label">SALDO POR COBRAR</div>
            <div class="stat-value">S/ {{ number_format($resumen['capital'], 2) }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card__header" style="gap:12px;flex-wrap:wrap">
            <span>Listado ({{ $prestamos->total() }})</span>
            <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:0">
                {{-- Filtro de Cobrador --}}
                <select name="cobrador_id" class="form-control" style="width:auto; padding:4px 10px; font-size:13px; height:32px;" onchange="this.form.submit()">
                    <option value="">— Todos los Cobradores —</option>
                    @foreach ($cobradores as $cob)
                        <option value="{{ $cob->id }}" @selected($cobradorId == $cob->id)>
                            {{ $cob->name }}
                        </option>
                    @endforeach
                </select>

                <select name="estado" class="form-control" style="width:auto; padding:4px 10px; font-size:13px; height:32px;" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    @foreach (['activo'=>'Activos','mora'=>'En mora','pagado'=>'Pagados','cancelado'=>'Cancelados'] as $v=>$l)
                        <option value="{{ $v }}" @selected($estado===$v)>{{ $l }}</option>
                    @endforeach
                </select>
                <div class="topbar__search" style="margin:0">
                    <i class="bi bi-search" style="color:#94a3b8"></i>
                    <input type="text" name="q" value="{{ $buscar }}" placeholder="Código o cliente...">
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Código</th><th>Cliente</th><th>Monto</th><th>Tasa</th>
                        <th>Cuotas</th><th>Cuota</th><th>Saldo</th><th>Inicio</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prestamos as $p)
                        <tr>
                             <td>
                                 <strong>{{ $p->codigo }}</strong>
                                 @if ($p->orden_ruta > 0)
                                     <br><span class="badge-pill b-gray" style="font-size:10px;padding:1px 6px;" title="Orden de Ruta">Ruta #{{ $p->orden_ruta }}</span>
                                 @endif
                             </td>
                            <td style="display:flex;align-items:center;gap:8px">
                                @if ($p->cliente)
                                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $p->cliente->semaforo_color }};display:inline-block;flex-shrink:0" title="{{ $p->cliente->semaforo_label }}"></span>
                                @endif
                                {{ $p->cliente?->nombre_completo ?? '—' }}
                            </td>
                            <td>S/ {{ number_format($p->monto, 2) }}</td>
                            <td>{{ rtrim(rtrim(number_format($p->tasa_interes,2), '0'), '.') }}%</td>
                            <td>{{ $p->numero_cuotas }} <span style="color:var(--text-muted);font-size:11px">{{ $p->frecuencia }}</span></td>
                            <td>S/ {{ number_format($p->monto_cuota, 2) }}</td>
                            <td>S/ {{ number_format($p->saldo, 2) }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($p->fecha_inicio)->format('d/m/Y') }}</td>
                            <td>
                                @php $map = ['activo'=>'b-blue','pagado'=>'b-green','mora'=>'b-red','cancelado'=>'b-gray']; @endphp
                                <span class="badge-pill {{ $map[$p->estado] ?? 'b-gray' }}">{{ ucfirst($p->estado) }}</span>
                            </td>
                            <td style="white-space:nowrap">
                                <a href="{{ route('prestamos.show', $p) }}" class="btn btn-light btn-sm" title="Ver"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('prestamos.edit', $p) }}" class="btn btn-light btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                                @if (auth()->user()->rol !== 'cobrador')
                                    <form action="{{ route('prestamos.destroy', $p) }}" method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este préstamo y su cronograma?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm" title="Eliminar"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" style="text-align:center;color:var(--text-muted);padding:36px">No se encontraron préstamos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:0 20px 18px">{{ $prestamos->links() }}</div>
    </div>
@endsection
