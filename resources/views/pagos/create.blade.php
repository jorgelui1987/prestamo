@extends('layouts.app')

@section('title', 'Registrar Pago')
@section('topbar', 'Registrar Pago')

@php
    $pendientes = $prestamo->cuotas->filter(fn ($c) => $c->estado !== 'pagado');
    $saldo = round((float) $prestamo->total_pagar - (float) $prestamo->cuotas->sum('monto_pagado'), 2);
    $proxima = $pendientes->sortBy('numero')->first();
    $proximaPend = $proxima ? round((float) $proxima->monto - (float) $proxima->monto_pagado, 2) : 0;
@endphp

@section('content')
    <div style="margin-bottom:22px">
        <a href="{{ route('prestamos.show', $prestamo) }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Volver al préstamo</a>
        <h1 class="page-title" style="margin-top:12px">Registrar Pago · {{ $prestamo->codigo }}</h1>
        <p class="page-subtitle">Cliente: <strong>{{ $prestamo->cliente?->nombre_completo ?? '—' }}</strong> · Saldo pendiente: <strong>S/ {{ number_format($saldo, 2) }}</strong></p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
    @endif

    <div class="grid-2" style="align-items:start">
        {{-- Form --}}
        <div class="card">
            <div class="card__header">Datos del pago</div>
            <div class="card__body">
                <form action="{{ route('pagos.store', $prestamo) }}" method="POST">
                    @csrf
                    <div class="form-group" style="margin-bottom:16px">
                        <label>Monto a pagar (S/) *</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $saldo }}" name="monto" id="f_monto" class="form-control" value="{{ old('monto', $proximaPend) }}" required>
                        <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap">
                            <button type="button" class="btn btn-light btn-sm" onclick="document.getElementById('f_monto').value={{ $proximaPend }}">
                                <i class="bi bi-1-circle"></i> Próxima cuota (S/ {{ number_format($proximaPend, 2) }})
                            </button>
                            <button type="button" class="btn btn-light btn-sm" onclick="document.getElementById('f_monto').value={{ $saldo }}">
                                <i class="bi bi-check-all"></i> Saldo total (S/ {{ number_format($saldo, 2) }})
                            </button>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Fecha de pago *</label>
                            <input type="date" name="fecha_pago" class="form-control" value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Método de pago *</label>
                            <select name="metodo" class="form-control" required>
                                @foreach ($metodos as $v => $l)
                                    <option value="{{ $v }}" @selected(old('metodo')===$v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group full">
                            <label>Referencia / N° operación</label>
                            <input type="text" name="referencia" class="form-control" value="{{ old('referencia') }}" placeholder="Opcional">
                        </div>
                    </div>

                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;margin-top:16px;font-size:12px;color:#1e40af">
                        <i class="bi bi-info-circle"></i> El pago se aplica automáticamente a las cuotas más antiguas pendientes. Admite pagos parciales y de varias cuotas a la vez.
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Registrar pago</button>
                        <a href="{{ route('prestamos.show', $prestamo) }}" class="btn btn-light">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Cuotas pendientes --}}
        <div class="card">
            <div class="card__header">Cuotas pendientes ({{ $pendientes->count() }})</div>
            <div class="table-wrap" style="max-height:420px;overflow-y:auto">
                <table class="data">
                    <thead><tr><th>#</th><th>Vence</th><th>Cuota</th><th>Pagado</th><th>Pendiente</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse ($pendientes->sortBy('numero') as $c)
                            @php
                                $vencida = \Illuminate\Support\Carbon::parse($c->fecha_vencimiento)->isPast();
                                $est = $vencida ? 'vencido' : $c->estado;
                                $bmap = ['pendiente'=>'b-yellow','vencido'=>'b-red','parcial'=>'b-blue'];
                            @endphp
                            <tr>
                                <td><strong>{{ $c->numero }}</strong></td>
                                <td>{{ \Illuminate\Support\Carbon::parse($c->fecha_vencimiento)->format('d/m/Y') }}</td>
                                <td>S/ {{ number_format($c->monto, 2) }}</td>
                                <td>S/ {{ number_format($c->monto_pagado, 2) }}</td>
                                <td><strong>S/ {{ number_format($c->monto - $c->monto_pagado, 2) }}</strong></td>
                                <td><span class="badge-pill {{ $bmap[$est] ?? 'b-gray' }}">{{ ucfirst($est) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:24px">Sin cuotas pendientes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
