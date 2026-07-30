@extends('layouts.app')

@section('title', 'Corte de Caja')
@section('topbar', 'Corte de Caja')

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap">
        <div>
            <h1 class="page-title">Corte de Caja</h1>
            <p class="page-subtitle" style="margin:0">Arqueo y cierre de caja por día.</p>
        </div>
        <form method="GET" style="display:flex;gap:8px;align-items:center">
            <label style="font-size:13px;color:var(--text-muted)">Fecha:</label>
            <input type="date" name="fecha" value="{{ $fecha }}" class="form-control" style="width:auto" onchange="this.form.submit()">
            <button class="btn btn-light btn-sm" type="button" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
        </form>
    </div>

    <div class="grid-2" style="align-items:start">
        <div class="card">
            <div class="card__header">Resumen del {{ \Illuminate\Support\Carbon::parse($fecha)->format('d/m/Y') }}</div>
            <div class="card__body">
                <table class="data" style="width:100%">
                    <tbody>
                        <tr><th>Cobros de cuotas</th><td style="text-align:right;color:#166534"><strong>+ S/ {{ number_format($resumen['cobros'], 2) }}</strong></td></tr>
                        <tr><th>Otros ingresos</th><td style="text-align:right;color:#166534"><strong>+ S/ {{ number_format($resumen['ingresos'], 2) }}</strong></td></tr>
                        <tr><th>Egresos</th><td style="text-align:right;color:#991b1b"><strong>- S/ {{ number_format($resumen['egresos'], 2) }}</strong></td></tr>
                        <tr style="background:#f8fafc"><th style="font-size:15px">SALDO ESPERADO EN CAJA</th><td style="text-align:right;font-size:18px;font-weight:800">S/ {{ number_format($resumen['saldo'], 2) }}</td></tr>
                    </tbody>
                </table>

                <h3 style="font-size:13px;color:var(--text-muted);margin:18px 0 8px">Cobros por método</h3>
                <table class="data" style="width:100%">
                    <tbody>
                        @forelse ($cobrosPorMetodo as $metodo => $total)
                            <tr><th>{{ ucfirst($metodo) }}</th><td style="text-align:right">S/ {{ number_format($total, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2" style="text-align:center;color:var(--text-muted)">Sin cobros este día.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card__header">Arqueo y cierre</div>
            <div class="card__body">
                @if ($errors->any())
                    <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
                @endif
                <form action="{{ route('corte.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="fecha" value="{{ $fecha }}">
                    <input type="hidden" id="saldo_esperado" value="{{ $resumen['saldo'] }}">
                    <div class="form-group" style="margin-bottom:16px">
                        <label>Monto contado físicamente (S/) *</label>
                        <input type="number" step="0.01" min="0" name="monto_contado" id="monto_contado" class="form-control" oninput="calcDif()" required>
                    </div>
                    <div class="stat-card bg-blue" id="dif_box" style="box-shadow:none;margin-bottom:16px">
                        <div class="stat-label">DIFERENCIA</div>
                        <div class="stat-value" id="dif_val" style="font-size:24px">S/ 0.00</div>
                        <div class="stat-foot" id="dif_txt">Ingresa el monto contado</div>
                    </div>
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-journal-check"></i> Guardar corte</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:24px">
        <div class="card__header">Cortes recientes</div>
        <div class="table-wrap">
            <table class="data">
                <thead><tr><th>Fecha</th><th>Responsable</th><th>Saldo esperado</th><th>Contado</th><th>Diferencia</th></tr></thead>
                <tbody>
                    @forelse ($cortesPrevios as $c)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($c->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $c->user->name ?? '—' }}</td>
                            <td>S/ {{ number_format($c->saldo_calculado, 2) }}</td>
                            <td>S/ {{ number_format($c->monto_contado, 2) }}</td>
                            <td>
                                @if ($c->diferencia == 0)
                                    <span class="badge-pill b-green">Cuadrado</span>
                                @elseif ($c->diferencia > 0)
                                    <span class="badge-pill b-blue">+ S/ {{ number_format($c->diferencia, 2) }}</span>
                                @else
                                    <span class="badge-pill b-red">S/ {{ number_format($c->diferencia, 2) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px">Aún no hay cortes guardados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function calcDif() {
        const esperado = parseFloat(document.getElementById('saldo_esperado').value) || 0;
        const contado = parseFloat(document.getElementById('monto_contado').value) || 0;
        const dif = Math.round((contado - esperado) * 100) / 100;
        document.getElementById('dif_val').textContent = 'S/ ' + dif.toLocaleString('es-PE', {minimumFractionDigits:2, maximumFractionDigits:2});
        const box = document.getElementById('dif_box');
        const txt = document.getElementById('dif_txt');
        box.className = 'stat-card ' + (dif === 0 ? 'bg-teal' : (dif > 0 ? 'bg-blue' : 'bg-red'));
        box.style.boxShadow = 'none';
        txt.textContent = dif === 0 ? 'Caja cuadrada' : (dif > 0 ? 'Sobrante en caja' : 'Faltante en caja');
    }
</script>
@endpush
