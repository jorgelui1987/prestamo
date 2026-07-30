@extends('layouts.app')

@section('title', 'Empeno '.$empeno->codigo)
@section('topbar', 'Detalle de Empeno')

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap">
        <div>
            <a href="{{ route('empenos.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
            <h1 class="page-title" style="margin-top:12px">
                {{ $empeno->codigo }} · {{ $empeno->articulo }}
                @php $bmap = ['vigente'=>'b-blue','recuperado'=>'b-green','vencido'=>'b-red','rematado'=>'b-gray']; @endphp
                <span class="badge-pill {{ $bmap[$empeno->estado] ?? 'b-gray' }}" style="font-size:13px;vertical-align:middle">{{ ucfirst($empeno->estado) }}</span>
            </h1>
            <p class="page-subtitle" style="margin:0">Cliente: <strong>{{ $empeno->cliente->nombre_completo ?? '-' }}</strong> · {{ $empeno->cliente->telefono ?? '' }}</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @if (in_array($empeno->estado, ['vigente','vencido']))
                <form action="{{ route('empenos.estado', $empeno) }}" method="POST" style="display:inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="estado" value="recuperado">
                    <button class="btn btn-primary" onclick="return confirm('¿Marcar como recuperado? El cliente pagó y retira su artículo.')"><i class="bi bi-check2-circle"></i> Recuperado</button>
                </form>
                <form action="{{ route('empenos.estado', $empeno) }}" method="POST" style="display:inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="estado" value="rematado">
                    <button class="btn btn-danger" onclick="return confirm('¿Marcar como rematado? El artículo pasa a venta.')"><i class="bi bi-hammer"></i> Rematar</button>
                </form>
            @endif
            <a href="{{ route('empenos.edit', $empeno) }}" class="btn btn-light"><i class="bi bi-pencil"></i> Editar</a>
            <button class="btn btn-light" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
        </div>
    </div>

    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
        <div class="stat-card bg-blue">
            <i class="bi bi-tag stat-icon"></i>
            <div class="stat-label">VALOR TASACIÓN</div>
            <div class="stat-value">S/ {{ number_format($empeno->valor_tasacion, 2) }}</div>
        </div>
        <div class="stat-card bg-teal">
            <i class="bi bi-cash stat-icon"></i>
            <div class="stat-label">MONTO PRESTADO</div>
            <div class="stat-value">S/ {{ number_format($empeno->monto_prestado, 2) }}</div>
            <div class="stat-foot">Tasa {{ rtrim(rtrim(number_format($empeno->tasa_interes,2),'0'),'.') }}%</div>
        </div>
        <div class="stat-card bg-purple">
            <i class="bi bi-graph-up-arrow stat-icon"></i>
            <div class="stat-label">INTERÉS</div>
            <div class="stat-value">S/ {{ number_format($empeno->interes, 2) }}</div>
        </div>
        <div class="stat-card bg-orange">
            <i class="bi bi-wallet2 stat-icon"></i>
            <div class="stat-label">TOTAL A RECUPERAR</div>
            <div class="stat-value">S/ {{ number_format($empeno->total_recuperar, 2) }}</div>
        </div>
    </div>

    <div class="grid-2" style="align-items:start">
        <div class="card">
            <div class="card__header">Información del artículo</div>
            <div class="card__body">
                <table class="data" style="width:100%">
                    <tbody>
                        <tr><th style="width:40%">Artículo</th><td>{{ $empeno->articulo }}</td></tr>
                        <tr><th>Descripción</th><td>{{ $empeno->descripcion ?: '—' }}</td></tr>
                        <tr><th>Fecha inicio</th><td>{{ \Illuminate\Support\Carbon::parse($empeno->fecha_inicio)->format('d/m/Y') }}</td></tr>
                        <tr><th>Fecha vencimiento</th><td>{{ \Illuminate\Support\Carbon::parse($empeno->fecha_vencimiento)->format('d/m/Y') }}</td></tr>
                        <tr>
                            <th>Días restantes</th>
                            <td>
                                @if ($empeno->estado === 'vigente')
                                    @if ($empeno->dias_restantes >= 0)
                                        <span class="badge-pill b-green">{{ $empeno->dias_restantes }} día(s)</span>
                                    @else
                                        <span class="badge-pill b-red">Vencido hace {{ abs($empeno->dias_restantes) }} día(s)</span>
                                    @endif
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card__header">Datos del cliente</div>
            <div class="card__body">
                <table class="data" style="width:100%">
                    <tbody>
                        <tr><th style="width:40%">Nombre</th><td>{{ $empeno->cliente->nombre_completo ?? '—' }}</td></tr>
                        <tr><th>Documento</th><td>{{ $empeno->cliente->tipo_documento ?? '' }} {{ $empeno->cliente->documento ?? '—' }}</td></tr>
                        <tr><th>Teléfono</th><td>{{ $empeno->cliente->telefono ?? '—' }}</td></tr>
                        <tr><th>Dirección</th><td>{{ $empeno->cliente->direccion ?? '—' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
