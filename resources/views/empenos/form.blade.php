@extends('layouts.app')

@php $editando = $empeno->exists; @endphp

@section('title', $editando ? 'Editar Empeno' : 'Nuevo Empeno')
@section('topbar', $editando ? 'Editar Empeno' : 'Nuevo Empeno')

@section('content')
    <div style="margin-bottom:22px">
        <a href="{{ route('empenos.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
        <h1 class="page-title" style="margin-top:12px">{{ $editando ? 'Editar Empeño' : 'Nuevo Empeño' }}</h1>
        <p class="page-subtitle">Registra el artículo en garantía y el monto prestado.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <i class="bi bi-exclamation-circle"></i> Revisa los campos:
            <ul style="margin:6px 0 0 18px">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid-2" style="align-items:start">
        <div class="card">
            <div class="card__header">Datos del empeño</div>
            <div class="card__body">
                <form action="{{ $editando ? route('empenos.update', $empeno) : route('empenos.store') }}" method="POST">
                    @csrf
                    @if ($editando) @method('PUT') @endif

                    <div class="form-group" style="margin-bottom:18px">
                        <label>Cliente *</label>
                        <select name="cliente_id" class="form-control" required>
                            <option value="">— Selecciona un cliente —</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id }}" @selected(old('cliente_id', $empeno->cliente_id) == $c->id)>{{ $c->codigo }} · {{ $c->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Artículo *</label>
                            <input type="text" name="articulo" class="form-control" value="{{ old('articulo', $empeno->articulo) }}" placeholder="Ej: Laptop HP, Anillo de oro 18k..." required>
                        </div>
                        <div class="form-group full">
                            <label>Descripción / características</label>
                            <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $empeno->descripcion) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Valor de tasación (S/) *</label>
                            <input type="number" step="0.01" min="0" name="valor_tasacion" id="f_tasacion" class="form-control" value="{{ old('valor_tasacion', $empeno->valor_tasacion) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Monto prestado (S/) *</label>
                            <input type="number" step="0.01" min="1" name="monto_prestado" id="f_monto" class="form-control" value="{{ old('monto_prestado', $empeno->monto_prestado) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Tasa de interés (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="tasa_interes" id="f_tasa" class="form-control" value="{{ old('tasa_interes', $empeno->tasa_interes) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha de inicio *</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio', \Illuminate\Support\Carbon::parse($empeno->fecha_inicio ?? now())->format('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha de vencimiento *</label>
                            <input type="date" name="fecha_vencimiento" class="form-control" value="{{ old('fecha_vencimiento', \Illuminate\Support\Carbon::parse($empeno->fecha_vencimiento ?? now()->addDays(30))->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ $editando ? 'Guardar cambios' : 'Registrar empeño' }}</button>
                        <a href="{{ route('empenos.index') }}" class="btn btn-light">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card__header">Resumen</div>
            <div class="card__body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="stat-card bg-purple" style="box-shadow:none">
                        <div class="stat-label">INTERÉS</div>
                        <div class="stat-value" id="r_interes" style="font-size:22px">S/ 0.00</div>
                    </div>
                    <div class="stat-card bg-teal" style="box-shadow:none">
                        <div class="stat-label">TOTAL A RECUPERAR</div>
                        <div class="stat-value" id="r_total" style="font-size:22px">S/ 0.00</div>
                    </div>
                    <div class="stat-card bg-orange" style="box-shadow:none">
                        <div class="stat-label">% S/ TASACIÓN</div>
                        <div class="stat-value" id="r_pct" style="font-size:22px">0%</div>
                    </div>
                    <div class="stat-card bg-blue" style="box-shadow:none">
                        <div class="stat-label">MARGEN GARANTÍA</div>
                        <div class="stat-value" id="r_margen" style="font-size:22px">S/ 0.00</div>
                    </div>
                </div>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;margin-top:16px;font-size:12px;color:#1e40af">
                    <i class="bi bi-info-circle"></i> Se recomienda prestar entre el 50% y 70% del valor de tasación para cubrir el riesgo.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const $ = id => document.getElementById(id);
    const money = n => 'S/ ' + (isFinite(n) ? n : 0).toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    function calc() {
        const tasacion = parseFloat($('f_tasacion').value) || 0;
        const monto = parseFloat($('f_monto').value) || 0;
        const tasa = parseFloat($('f_tasa').value) || 0;
        const interes = Math.round(monto * tasa) / 100;
        const total = Math.round((monto + interes) * 100) / 100;
        const pct = tasacion > 0 ? Math.round(monto / tasacion * 100) : 0;
        $('r_interes').textContent = money(interes);
        $('r_total').textContent = money(total);
        $('r_pct').textContent = pct + '%';
        $('r_margen').textContent = money(Math.round((tasacion - monto) * 100) / 100);
    }
    ['f_tasacion','f_monto','f_tasa'].forEach(id => { $(id).addEventListener('input', calc); });
    calc();
</script>
@endpush
