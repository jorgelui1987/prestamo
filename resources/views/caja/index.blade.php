@extends('layouts.app')

@section('title', 'Caja')
@section('topbar', 'Caja')  

@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:12px;flex-wrap:wrap">
        <div>
            <h1 class="page-title">Caja</h1>
            <p class="page-subtitle" style="margin:0">Movimientos de ingresos y egresos del día.</p>
        </div>
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:0">
            {{-- Filtro de Cobrador --}}
            <select name="cobrador_id" class="form-control" style="width:auto; padding:4px 10px; font-size:13px; height:32px;" onchange="this.form.submit()">
                <option value="">— Caja Central (Oficina) —</option>
                @foreach ($cobradores as $cob)
                    <option value="{{ $cob->id }}" @selected($cobradorId == $cob->id)>
                        Caja de: {{ $cob->name }} (Calle)
                    </option>
                @endforeach
            </select>

            <label style="font-size:13px;color:var(--text-muted)">Fecha:</label>
            <input type="date" name="fecha" value="{{ $fecha }}" class="form-control" style="width:auto" onchange="this.form.submit()">
        </form>
    </div>

    {{-- Banner Informativo Amigable --}}
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 22px; display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $cobradorId ? 'rgba(245, 158, 11, 0.15)' : 'rgba(37, 99, 235, 0.15)' }}; display: grid; place-items: center; font-size: 20px; color: {{ $cobradorId ? '#d97706' : '#2563eb' }}; flex-shrink: 0;">
            <i class="bi {{ $cobradorId ? 'bi-bicycle' : 'bi-building' }}"></i>
        </div>
        <div>
            @if ($cobradorId)
                @php $cobSeleccionado = $cobradores->firstWhere('id', $cobradorId); @endphp
                <div style="font-size: 14px; font-weight: 800; color: #1e293b;">Viendo la Caja de: {{ $cobSeleccionado->name ?? 'Cobrador' }} (En Calle)</div>
                <div style="font-size: 12px; color: #64748b;">Estás viendo el dinero que este cobrador lleva consigo en la calle. Al final del día, debes realizar el arqueo para recibir su efectivo.</div>
            @else
                <div style="font-size: 14px; font-weight: 800; color: #1e293b;">Viendo la Caja Central (Oficina)</div>
                <div style="font-size: 12px; color: #64748b;">Estás viendo el dinero físico que se encuentra guardado en la oficina principal. No incluye el dinero que los cobradores llevan en la calle.</div>
            @endif
        </div>
    </div>

    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
        <div class="stat-card bg-teal">
            <i class="bi bi-wallet2 stat-icon"></i>
            <div class="stat-label">COBROS DEL DÍA</div>
            <div class="stat-value">S/ {{ number_format($cobros, 2) }}</div>
            <div class="stat-foot">Pagos de cuotas</div>
        </div>
        <div class="stat-card bg-blue">
            <i class="bi bi-arrow-down-circle stat-icon"></i>
            <div class="stat-label">OTRAS ENTRADAS</div>
            <div class="stat-value">S/ {{ number_format($ingresos, 2) }}</div>
        </div>
        <div class="stat-card bg-orange">
            <i class="bi bi-arrow-up-circle stat-icon"></i>
            <div class="stat-label">SALIDAS</div>
            <div class="stat-value">S/ {{ number_format($egresos, 2) }}</div>
        </div>
        <div class="stat-card bg-purple">
            <i class="bi bi-cash-stack stat-icon"></i>
            <div class="stat-label">SALDO DEL DÍA</div>
            <div class="stat-value">S/ {{ number_format($saldo, 2) }}</div>
        </div>
    </div>

    <div class="grid-2" style="align-items:start">
        <div class="card">
            <div class="card__header">Registrar movimiento</div>
            <div class="card__body">
                @if ($errors->any())
                    <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}</div>
                @endif

                @if ($cobradorId)
                    @php $cobSeleccionado = $cobradores->firstWhere('id', $cobradorId); @endphp
                    <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 10px; margin-bottom: 16px; font-size: 12px; color: #b45309; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Registrando movimiento directamente en la caja de <strong>{{ $cobSeleccionado->name ?? 'Cobrador' }}</strong>.</span>
                    </div>
                @endif

                <form action="{{ route('caja.store', ['cobrador_id' => $cobradorId]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="fecha" value="{{ $fecha }}">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tipo *</label>
                            <select name="tipo" id="c_tipo" class="form-control" required onchange="updateCategorias()">
                                <option value="ingreso">Entrada</option>
                                <option value="egreso">Salida</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Categoría *</label>
                            <select name="categoria" id="c_categoria" class="form-control" required></select>
                        </div>
                        <div class="form-group full">
                            <label>Concepto *</label>
                            <input type="text" name="concepto" class="form-control" placeholder="Descripción del movimiento" required>
                        </div>
                        <div class="form-group">
                            <label>Monto (S/) *</label>
                            <input type="number" step="0.01" min="0.01" name="monto" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Método *</label>
                            <select name="metodo" class="form-control" required>
                                @foreach ($metodos as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group" id="div_cobrador" style="display:none">
                            <label>Asociar a Cobrador</label>
                            <select name="cobrador_id" class="form-control">
                                <option value="">— Ninguno (Caja Central) —</option>
                                @foreach ($cobradores as $cob)
                                    <option value="{{ $cob->id }}" @selected($cobradorId == $cob->id)>{{ $cob->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Registrar</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Calculadora de Cuadre Diario --}}
        @if ($cobradorId)
            <div class="card" style="margin-bottom: 20px; border: 1px solid #cbd5e1;">
                <div class="card__header" style="background-color: #f8fafc; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="bi bi-calculator-fill" style="color: var(--primary)"></i> Calculadora de Cuadre Diario</span>
                    <button type="button" class="btn btn-light btn-sm" onclick="limpiarCalculadora()" style="font-size: 11px; padding: 2px 8px;"><i class="bi bi-trash"></i> Limpiar datos</button>
                </div>
                <div class="card__body" style="padding: 20px;">
                    
                    {{-- Dinero que ENTRA (+) --}}
                    <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                        <div style="font-size: 12px; font-weight: 800; color: #15803d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-plus-circle-fill"></i> Dinero que ENTRA (+)
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="form-group" style="margin: 0;">
                                <label style="font-size: 11px; font-weight: 700; color: #1e293b;">1. Base Inicial (S/)</label>
                                <input type="number" step="0.01" id="calc_base" class="form-control" style="font-weight: 700;" value="{{ number_format($baseInicial, 2, '.', '') }}" oninput="recalcularCuadre()">
                            </div>
                            <div class="form-group" style="margin: 0;">
                                <label style="font-size: 11px; font-weight: 700; color: #1e293b;">2. Total Cobrado (S/)</label>
                                <input type="number" step="0.01" id="calc_cobrado" class="form-control" style="font-weight: 700;" value="{{ number_format($cobros, 2, '.', '') }}" oninput="recalcularCuadre()">
                            </div>
                        </div>
                    </div>

                    {{-- Dinero que SALE (-) --}}
                    <div style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                        <div style="font-size: 12px; font-weight: 800; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-dash-circle-fill"></i> Dinero que SALE (-)
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="form-group" style="margin: 0;">
                                <label style="font-size: 11px; font-weight: 700; color: #1e293b;">3. Préstamos Nuevos (S/)</label>
                                <input type="number" step="0.01" id="calc_prestamos" class="form-control" style="font-weight: 700;" value="{{ number_format($prestamosNuevos, 2, '.', '') }}" oninput="recalcularCuadre()">
                            </div>
                            <div class="form-group" style="margin: 0;">
                                <label style="font-size: 11px; font-weight: 700; color: #1e293b;">4. Gastos del Día (S/)</label>
                                <input type="number" step="0.01" id="calc_gastos" class="form-control" style="font-weight: 700;" value="{{ number_format($gastosDia, 2, '.', '') }}" oninput="recalcularCuadre()">
                            </div>
                        </div>
                    </div>

                    {{-- Efectivo Esperado (Debe haber) --}}
                    <div style="background: #1e293b; border-radius: 12px; padding: 16px; text-align: center; color: white; margin-bottom: 16px;">
                        <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; margin-bottom: 4px;">Efectivo Esperado (Debe haber)</div>
                        <div style="font-size: 28px; font-weight: 800; color: #38bdf8;" id="calc_esperado_text">S/ {{ number_format($saldo, 2) }}</div>
                    </div>

                    {{-- Verificación Física --}}
                    <form action="{{ route('caja.arqueo') }}" method="POST" id="form_guardar_arqueo">
                        @csrf
                        <input type="hidden" name="fecha" value="{{ $fecha }}">
                        <input type="hidden" name="cobrador_id" value="{{ $cobradorId }}">
                        <input type="hidden" name="monto_contado" id="monto_contado_hidden" value="0.00">
                        
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-weight: 700; font-size: 13px; color: var(--text-main); display: block; margin-bottom: 8px; text-align: center;">
                                ¿Cuánto efectivo hay en la mesa realmente? (S/)
                            </label>
                            <input type="number" step="0.01" id="efectivo_fisico" class="form-control" style="font-size: 20px; font-weight: 800; text-align: center; border-color: #3b82f6; background: #eff6ff;" placeholder="0.00" required oninput="recalcularCuadre()">
                        </div>

                        <div id="resultado_arqueo_box" style="display: none; border-radius: 12px; padding: 16px; text-align: center; font-weight: 700; font-size: 15px; margin-bottom: 16px;">
                            <div id="resultado_arqueo_lbl" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Diferencia</div>
                            <div id="resultado_arqueo_monto" style="font-size: 22px; font-weight: 800; margin-bottom: 8px;">S/ 0.00</div>
                            <div id="resultado_arqueo_status" style="margin-bottom: 4px;"></div>
                        </div>

                        <button type="submit" style="width: 100%; background-color: #10b981; color: white; border: none; border-radius: 10px; padding: 14px; font-weight: 800; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                            <i class="bi bi-shield-check"></i> Guardar y Cerrar Caja
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card__header">Movimientos del {{ \Illuminate\Support\Carbon::parse($fecha)->format('d/m/Y') }} ({{ $movimientos->count() }})</div>
            <div class="table-wrap" style="max-height:420px;overflow-y:auto">
                <table class="data">
                    <thead><tr><th>Código</th><th>Concepto</th><th>Tipo</th><th>Monto</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($movimientos as $m)
                            <tr>
                                <td><strong>{{ $m->codigo }}</strong><br><span style="font-size:11px;color:var(--text-muted)">{{ ucfirst(str_replace('_',' ',$m->categoria)) }}</span></td>
                                <td>{{ $m->concepto }}</td>
                                <td>
                                    @if ($m->tipo === 'ingreso')
                                        <span class="badge-pill b-green">Entrada</span>
                                    @else
                                        <span class="badge-pill b-red">Salida</span>
                                    @endif
                                </td>
                                <td><strong style="color:{{ $m->tipo==='ingreso' ? '#166534' : '#991b1b' }}">{{ $m->tipo==='ingreso'?'+':'-' }} S/ {{ number_format($m->monto, 2) }}</strong></td>
                                <td>
                                    <form action="{{ route('caja.destroy', [$m, 'cobrador_id' => $cobradorId]) }}" method="POST" onsubmit="return confirm('¿Eliminar movimiento?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px">Sin movimientos manuales este día.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const CATS = @json($categorias);
    function updateCategorias() {
        const tipo = document.getElementById('c_tipo').value;
        const sel = document.getElementById('c_categoria');
        sel.innerHTML = '';
        Object.entries(CATS[tipo]).forEach(([v, l]) => {
            const o = document.createElement('option'); o.value = v; o.textContent = l; sel.appendChild(o);
        });
        toggleCobradorField();
    }

    function toggleCobradorField() {
        const tipo = document.getElementById('c_tipo').value;
        const cat = document.getElementById('c_categoria').value;
        const div = document.getElementById('div_cobrador');
        
        // Mostrar el campo de cobrador si es un egreso de tipo "Entrega de caja a cobrador"
        if (tipo === 'egreso' && cat === 'entrega_caja_cobrador') {
            div.style.display = 'block';
        } else {
            div.style.display = 'none';
        }
    }

    document.getElementById('c_categoria').addEventListener('change', toggleCobradorField);
    updateCategorias();

    // Lógica interactiva para la Calculadora de Cuadre Diario
    function recalcularCuadre() {
        const base = parseFloat(document.getElementById('calc_base').value) || 0;
        const cobrado = parseFloat(document.getElementById('calc_cobrado').value) || 0;
        const prestamos = parseFloat(document.getElementById('calc_prestamos').value) || 0;
        const gastos = parseFloat(document.getElementById('calc_gastos').value) || 0;
        
        const esperado = (base + cobrado) - (prestamos + gastos);
        
        // Actualizar texto de efectivo esperado
        document.getElementById('calc_esperado_text').textContent = 'S/ ' + esperado.toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        const fisicoInput = document.getElementById('efectivo_fisico');
        const fisico = parseFloat(fisicoInput.value) || 0;
        const diferencia = fisico - esperado;
        
        const box = document.getElementById('resultado_arqueo_box');
        const montoDiv = document.getElementById('resultado_arqueo_monto');
        const statusDiv = document.getElementById('resultado_arqueo_status');
        
        if (fisicoInput.value === '') {
            box.style.display = 'none';
            document.getElementById('monto_contado_hidden').value = '0.00';
            return;
        }
        
        box.style.display = 'block';
        
        // Formatear diferencia
        const diffFormated = 'S/ ' + Math.abs(diferencia).toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        if (Math.abs(diferencia) < 0.01) {
            // Caja Cuadrada
            box.style.backgroundColor = 'rgba(16, 185, 129, 0.15)';
            box.style.border = '1px solid #10b981';
            box.style.color = '#10b981';
            montoDiv.textContent = 'S/ 0.00';
            statusDiv.innerHTML = '<i class="bi bi-check-circle-fill"></i> ✔ CAJA CUADRADA (CONFORME)';
        } else if (diferencia < 0) {
            // Faltante
            box.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
            box.style.border = '1px solid #ef4444';
            box.style.color = '#ef4444';
            montoDiv.textContent = '- ' + diffFormated;
            statusDiv.innerHTML = '<i class="bi bi-x-circle-fill"></i> ❌ DESCUADRE: FALTA DINERO';
        } else {
            // Sobrante
            box.style.backgroundColor = 'rgba(59, 130, 246, 0.15)';
            box.style.border = '1px solid #3b82f6';
            box.style.color = '#3b82f6';
            montoDiv.textContent = '+ ' + diffFormated;
            statusDiv.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i> ⚠ DESCUADRE: SOBRA DINERO';
        }

        // Sincronizar el valor con el campo oculto para el envío del formulario
        document.getElementById('monto_contado_hidden').value = fisico.toFixed(2);
    }

    function limpiarCalculadora() {
        document.getElementById('calc_base').value = '0.00';
        document.getElementById('calc_cobrado').value = '0.00';
        document.getElementById('calc_prestamos').value = '0.00';
        document.getElementById('calc_gastos').value = '0.00';
        document.getElementById('efectivo_fisico').value = '';
        recalcularCuadre();
        document.getElementById('calc_base').focus();
    }

    // Inicializar la calculadora al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('calc_base')) {
            recalcularCuadre();
        }
    });
</script>
@endpush
