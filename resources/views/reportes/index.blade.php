@extends('layouts.app')

@section('title', 'Reportes')
@section('topbar', 'Reportes')

@section('content')
    @if (session('ok'))
        <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('ok') }}</div>
    @endif

    <h1 class="page-title">Reportes</h1>
    <p class="page-subtitle">Genera reportes de la operación. Exporta a Excel (CSV) o imprime en PDF.</p>

    {{-- Tarjeta de Reporte Diario Automático --}}
    <div class="card" style="margin-bottom:24px;background:linear-gradient(135deg,#1e3a8a,#2563eb);border:none;color:#fff">
        <div class="card__body" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:16px">
                <div style="width:54px;height:54px;border-radius:14px;background:rgba(255,255,255,.15);display:grid;place-items:center;font-size:26px;flex-shrink:0">
                    <i class="bi bi-envelope-paper-fill"></i>
                </div>
                <div>
                    <div style="font-size:17px;font-weight:800">📊 Reporte Diario Automático</div>
                    <div style="font-size:13px;opacity:.9">Recibe cada noche un resumen de cobros, gastos y mora en tu correo. También puedes enviarlo manualmente ahora.</div>
                </div>
            </div>
            <form action="{{ route('reportes.enviar-diario') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-light" onclick="this.disabled=true;this.innerHTML='<i class=\'bi bi-hourglass-split\'></i> Enviando...'">
                    <i class="bi bi-send-fill"></i> Enviar Reporte Ahora
                </button>
            </form>
        </div>
    </div>

    <div class="quick-grid" style="grid-template-columns:repeat(3,1fr)">
        @php $colors = ['bg-blue','bg-teal','bg-red','bg-purple','bg-orange','bg-cyan']; $i=0; @endphp
        @foreach ($tipos as $key => $t)
            <a href="{{ route('reportes.ver', $key) }}" class="quick-card">
                <div class="q-icon {{ $colors[$i++ % count($colors)] }}"><i class="bi {{ $t['icono'] }}"></i></div>
                <div>
                    <div class="q-title">{{ $t['titulo'] }}</div>
                    <div class="q-desc">{{ $t['fecha'] ? 'Filtrable por fecha' : 'Listado completo' }}</div>
                </div>
            </a>
        @endforeach
    </div>
@endsection