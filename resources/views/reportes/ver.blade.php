@extends('layouts.app')

@section('title', $meta['titulo'])
@section('topbar', 'Reportes')

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap">
        <div>
            <a href="{{ route('reportes.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Reportes</a>
            <h1 class="page-title" style="margin-top:12px"><i class="bi {{ $meta['icono'] }}"></i> {{ $meta['titulo'] }}</h1>
            <p class="page-subtitle" style="margin:0">
                @if ($meta['fecha']) Periodo: {{ \Illuminate\Support\Carbon::parse($desde)->format('d/m/Y') }} al {{ \Illuminate\Support\Carbon::parse($hasta)->format('d/m/Y') }} · @endif
                {{ count($rows) }} registro(s)
            </p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route('reportes.excel', array_merge(['tipo'=>$tipo], request()->only('desde','hasta'))) }}" class="btn btn-primary"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel</a>
            <button class="btn btn-light" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir / PDF</button>
        </div>
    </div>

    @if ($meta['fecha'])
        <div class="card" style="margin-bottom:18px">
            <div class="card__body">
                <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
                    <div class="form-group" style="margin:0">
                        <label>Desde</label>
                        <input type="date" name="desde" value="{{ $desde }}" class="form-control">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label>Hasta</label>
                        <input type="date" name="hasta" value="{{ $hasta }}" class="form-control">
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
                </form>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>@foreach ($headers as $h)<th>{{ $h }}</th>@endforeach</tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
                    @empty
                        <tr><td colspan="{{ count($headers) }}" style="text-align:center;color:var(--text-muted);padding:36px">Sin datos para este reporte.</td></tr>
                    @endforelse
                </tbody>
                @if (!empty($totales))
                    <tfoot>
                        <tr style="font-weight:700;background:#f8fafc">
                            <td colspan="{{ count($headers) }}">
                                @foreach ($totales as $label => $val)
                                    <span style="margin-right:24px">{{ $label }}: S/ {{ number_format($val, 2) }}</span>
                                @endforeach
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
