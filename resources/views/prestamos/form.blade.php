@extends('layouts.app')

@php 
    $editando = $prestamo->exists; 
    $esMovil = request()->query('origen') === 'movil' || auth()->user()->rol === 'cobrador';
@endphp

@section('title', $editando ? 'Editar Préstamo' : 'Nuevo Préstamo')
@section('topbar', $editando ? 'Editar Préstamo' : 'Nuevo Préstamo')

@section('content')
    <div style="margin-bottom:22px">
        <a href="{{ $esMovil ? route('movil.index') : route('prestamos.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
        <h1 class="page-title" style="margin-top:12px">{{ $editando ? 'Editar Préstamo' : 'Nuevo Préstamo' }}</h1>
        <p class="page-subtitle">El sistema calcula automáticamente las cuotas e intereses.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <i class="bi bi-exclamation-circle"></i> Revisa los campos:
            <ul style="margin:6px 0 0 18px">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid-2" style="align-items:start">
        {{-- ===== Formulario ===== --}}
        <div class="card">
            <div class="card__header">Datos del préstamo</div>
            <div class="card__body">
                <form action="{{ $editando ? route('prestamos.update', $prestamo) : route('prestamos.store', ['origen' => request()->query('origen')]) }}" method="POST" id="formPrestamo">
                    @csrf
                    @if ($editando) @method('PUT') @endif

                    <div class="form-group" style="margin-bottom:18px;position:relative">
                        <label>Cliente *</label>
                        <div style="position:relative">
                            <input type="text" id="cliente_search" class="form-control" placeholder="🔍 Escribe código, nombre, DNI o teléfono para buscar..." value="{{ old('cliente_search', $prestamo->cliente ? $prestamo->cliente->codigo . ' · ' . $prestamo->cliente->nombre_completo : '') }}" required autocomplete="off">
                            <input type="hidden" name="cliente_id" id="cliente_id" value="{{ old('cliente_id', $prestamo->cliente_id) }}">
                            <div id="cliente_results" style="display:none;position:absolute;top:100%;left:0;right:0;background:white;border:1px solid #cbd5e1;border-radius:0 0 10px 10px;max-height:280px;overflow-y:auto;z-index:1000;box-shadow:0 8px 20px rgba(0,0,0,0.12)"></div>
                        </div>
                        <div id="cliente_seleccionado" style="display:{{ $prestamo->cliente ? 'flex' : 'none' }};margin-top:8px;padding:8px 12px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;font-size:13px;align-items:center;gap:8px">
                            <i class="bi bi-check-circle-fill" style="color:#16a34a"></i>
                            <span id="cliente_seleccionado_texto">{{ $prestamo->cliente ? $prestamo->cliente->codigo . ' · ' . $prestamo->cliente->nombre_completo : '' }}</span>
                            <button type="button" onclick="limpiarCliente()" style="margin-left:auto;background:none;border:none;color:#dc2626;font-size:16px;cursor:pointer"><i class="bi bi-x-circle"></i></button>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Monto del préstamo (S/) *</label>
                            <input type="number" step="0.01" min="1" name="monto" id="f_monto" class="form-control" value="{{ old('monto', $prestamo->monto ? rtrim(rtrim(number_format($prestamo->monto, 2, '.', ''), '0'), '.') : '') }}" required placeholder="Ej: 20000">
                        </div>
                        <div class="form-group">
                            <label>Tasa de interés (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="tasa_interes" id="f_tasa" class="form-control" value="{{ old('tasa_interes', $prestamo->tasa_interes) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Número de cuotas *</label>
                            <input type="number" min="1" max="360" name="numero_cuotas" id="f_cuotas" class="form-control" value="{{ old('numero_cuotas', $prestamo->numero_cuotas) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Frecuencia de pago *</label>
                            <select name="frecuencia" id="f_frecuencia" class="form-control" required>
                                @foreach ($frecuencias as $v=>$l)
                                    <option value="{{ $v }}" @selected(old('frecuencia', $prestamo->frecuencia) === $v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fecha de inicio *</label>
                            <input type="date" name="fecha_inicio" id="f_fecha" class="form-control" value="{{ old('fecha_inicio', \Illuminate\Support\Carbon::parse($prestamo->fecha_inicio ?? now())->format('Y-m-d')) }}" required>
                        </div>
                        @if (auth()->user()->rol !== 'cobrador')
                            <div class="form-group">
                                <label>Cobrador Asignado</label>
                                <select name="cobrador_id" class="form-control">
                                    <option value="">— Sin Cobrador —</option>
                                    @foreach ($cobradores as $cob)
                                        <option value="{{ $cob->id }}" @selected(old('cobrador_id', $prestamo->cobrador_id) == $cob->id)>
                                            {{ $cob->name }} ({{ $cob->rol_label }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Orden de Ruta (Posición) *</label>
                                <input type="number" min="1" name="orden_ruta" class="form-control" value="{{ old('orden_ruta', $prestamo->orden_ruta ?? 1) }}" required placeholder="Ej: 1, 2, 3...">
                                <small style="color:var(--text-muted);font-size:11px;display:block;margin-top:4px">
                                    Determina el orden en el que el cobrador visitará a este cliente en su ruta diaria.
                                </small>
                            </div>
                        @else
                            <input type="hidden" name="cobrador_id" value="{{ auth()->id() }}">
                            <input type="hidden" name="orden_ruta" value="{{ $prestamo->orden_ruta ?? 0 }}">
                        @endif
                        <div class="form-group">
                            <label>Número de Boleta (Rifa)</label>
                            <input type="text" name="numero_boleta" id="f_boleta" class="form-control" value="{{ old('numero_boleta', $prestamo->numero_boleta) }}" placeholder="Ej: BOLETA-1234">
                        </div>
                        <div class="form-group">
                            <label>Costo de Boleta (S/)</label>
                            <input type="number" step="0.01" min="0" name="costo_boleta" id="f_costo_boleta" class="form-control" value="{{ old('costo_boleta', $prestamo->costo_boleta ?? 0) }}">
                        </div>
                        <div class="form-group full">
                            <label>Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', $prestamo->observaciones) }}</textarea>
                        </div>

                        @if (!$editando)
                            <div class="form-group full" style="margin-top: 16px;">
                                <label style="font-weight: 700; font-size: 13px; color: var(--text-main); display: block; margin-bottom: 8px;">
                                    Firma de Conformidad del Cliente (Firma Digital)
                                </label>
                                <div style="background: white; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; max-width: 450px; margin: 0 auto;">
                                    <canvas id="canvasFirma" width="450" height="180" style="width: 100%; height: 180px; cursor: crosshair; display: block; background: #fff;"></canvas>
                                </div>
                                <div style="text-align: center; margin-top: 8px;">
                                    <button type="button" class="btn btn-light btn-sm" onclick="limpiarFirma()"><i class="bi bi-trash"></i> Limpiar Firma</button>
                                </div>
                                <input type="hidden" name="firma_base64" id="firma_base64">
                            </div>
                        @endif
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" onclick="guardarFirma()"><i class="bi bi-check-lg"></i> {{ $editando ? 'Guardar y regenerar' : 'Registrar préstamo' }}</button>
                        <a href="{{ $esMovil ? route('movil.index') : route('prestamos.index') }}" class="btn btn-light">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== Resumen / Preview ===== --}}
        <div class="card">
            <div class="card__header">Resumen del cálculo</div>
            <div class="card__body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px">
                    <div class="stat-card bg-blue" style="box-shadow:none">
                        <div class="stat-label">CAPITAL</div>
                        <div class="stat-value" id="r_capital" style="font-size:22px">S/ 0.00</div>
                    </div>
                    <div class="stat-card bg-purple" style="box-shadow:none">
                        <div class="stat-label">INTERÉS TOTAL</div>
                        <div class="stat-value" id="r_interes" style="font-size:22px">S/ 0.00</div>
                    </div>
                    <div class="stat-card bg-teal" style="box-shadow:none">
                        <div class="stat-label">TOTAL A PAGAR</div>
                        <div class="stat-value" id="r_total" style="font-size:22px">S/ 0.00</div>
                    </div>
                    <div class="stat-card bg-orange" style="box-shadow:none">
                        <div class="stat-label">VALOR CUOTA</div>
                        <div class="stat-value" id="r_cuota" style="font-size:22px">S/ 0.00</div>
                    </div>
                    <div class="stat-card bg-red" style="box-shadow:none; grid-column: span 2; background: linear-gradient(135deg, #ef4444, #b91c1c);">
                        <div class="stat-label" style="color: white; opacity: 0.9;">EFECTIVO NETO A ENTREGAR AL CLIENTE</div>
                        <div class="stat-value" id="r_neto" style="font-size:26px; color: white;">S/ 0.00</div>
                        <div class="stat-foot" style="color: white; opacity: 0.8; font-size: 11px;">(Capital solicitado menos costo de boleta de rifa)</div>
                    </div>
                </div>

                <h3 style="font-size:13px;color:var(--text-muted);margin-bottom:8px">Cronograma estimado</h3>
                <div class="table-wrap" style="max-height:300px;overflow-y:auto">
                    <table class="data">
                        <thead><tr><th>#</th><th>Vencimiento</th><th>Capital</th><th>Interés</th><th>Cuota</th></tr></thead>
                        <tbody id="r_cronograma">
                            <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px">Completa los datos para ver el cronograma.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const $ = id => document.getElementById(id);
    const money = n => '{{ \App\Models\Configuracion::get('moneda', 'S/') }} ' + (isFinite(n) ? n : 0).toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    function addPeriodo(fecha, frecuencia) {
        const d = new Date(fecha.getTime());
        if (frecuencia === 'diario') d.setDate(d.getDate() + 1);
        else if (frecuencia === 'semanal') d.setDate(d.getDate() + 7);
        else if (frecuencia === 'quincenal') d.setDate(d.getDate() + 15);
        else d.setMonth(d.getMonth() + 1);
        return d;
    }

    function calcular() {
        // Limpiar cualquier formato de texto (comas o espacios) antes de convertir a número
        const rawMonto = $('f_monto').value.toString().replace(/,/g, '').trim();
        const monto = parseFloat(rawMonto) || 0;
        const tasa = parseFloat($('f_tasa').value) || 0;
        const n = parseInt($('f_cuotas').value) || 0;
        const frecuencia = $('f_frecuencia').value;
        const fechaStr = $('f_fecha').value;
        const costoBoleta = parseFloat($('f_costo_boleta').value) || 0;

        const interesTotal = Math.round(monto * tasa) / 100;
        const total = Math.round((monto + interesTotal) * 100) / 100;
        const cuota = n > 0 ? Math.round(total / n * 100) / 100 : 0;
        const netoEntregar = Math.max(monto - costoBoleta, 0);

        $('r_capital').textContent = money(monto);
        $('r_interes').textContent = money(interesTotal);
        $('r_total').textContent = money(total);
        $('r_cuota').textContent = money(cuota);
        $('r_neto').textContent = money(netoEntregar);

        const tbody = $('r_cronograma');
        if (n < 1 || monto < 1 || !fechaStr) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px">Completa los datos para ver el cronograma.</td></tr>';
            return;
        }

        const capCuota = Math.round(monto / n * 100) / 100;
        const intCuota = Math.round(interesTotal / n * 100) / 100;
        let fecha = new Date(fechaStr + 'T00:00:00');
        let accCap = 0, accInt = 0, accTot = 0, rows = '';

        for (let i = 1; i <= n; i++) {
            fecha = addPeriodo(fecha, frecuencia);
            let cap, int, mon;
            if (i < n) { cap = capCuota; int = intCuota; mon = cuota; }
            else {
                cap = Math.round((monto - accCap) * 100) / 100;
                int = Math.round((interesTotal - accInt) * 100) / 100;
                mon = Math.round((total - accTot) * 100) / 100;
            }
            accCap += cap; accInt += int; accTot += mon;
            const f = fecha.toLocaleDateString('es-PE');
            rows += `<tr><td>${i}</td><td>${f}</td><td>${money(cap)}</td><td>${money(int)}</td><td><strong>${money(mon)}</strong></td></tr>`;
        }
        tbody.innerHTML = rows;
    }

    ['f_monto','f_tasa','f_cuotas','f_frecuencia','f_fecha','f_costo_boleta'].forEach(id => {
        $(id).addEventListener('input', calcular);
        $(id).addEventListener('change', calcular);
    });
    calcular();

    // ============================================================
    // BUSCADOR AJAX DE CLIENTES (evita duplicados)
    // ============================================================
    let timeoutBusqueda = null;
    let clienteSeleccionado = {{ $prestamo->cliente_id ? $prestamo->cliente_id : 'null' }};

    function limpiarCliente() {
        clienteSeleccionado = null;
        $('cliente_id').value = '';
        $('cliente_search').value = '';
        $('cliente_seleccionado').style.display = 'none';
        $('cliente_search').focus();
    }

    function seleccionarCliente(id, label) {
        clienteSeleccionado = id;
        $('cliente_id').value = id;
        $('cliente_search').value = label;
        $('cliente_seleccionado_texto').textContent = label;
        $('cliente_seleccionado').style.display = 'flex';
        $('cliente_results').style.display = 'none';
    }

    $('cliente_search').addEventListener('input', function() {
        const val = this.value.trim();
        
        // Si el usuario está escribiendo, limpiar selección anterior
        if (clienteSeleccionado && val !== $('cliente_seleccionado_texto').textContent) {
            clienteSeleccionado = null;
            $('cliente_id').value = '';
            $('cliente_seleccionado').style.display = 'none';
        }

        if (val.length < 2) {
            $('cliente_results').style.display = 'none';
            return;
        }

        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(() => {
            fetch('{{ route("clientes.buscar-json") }}?q=' + encodeURIComponent(val))
                .then(r => r.json())
                .then(data => {
                    const div = $('cliente_results');
                    if (data.length === 0) {
                        div.innerHTML = '<div style="padding:12px;color:#94a3b8;font-size:13px;text-align:center">❌ No se encontraron clientes. <a href="{{ route("clientes.create") }}" style="color:#3b82f6;font-weight:600">Crear nuevo</a></div>';
                        div.style.display = 'block';
                        return;
                    }
                    div.innerHTML = data.map(c => `
                        <div onclick="seleccionarCliente(${c.id}, '${c.label.replace(/'/g, "\\'")}')" style="padding:10px 14px;cursor:pointer;border-bottom:1px solid #f1f5f9;display:flex;flex-direction:column;gap:2px;transition:background 0.15s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                            <div style="font-weight:600;font-size:13px;color:#1e293b">${c.label}</div>
                            <div style="font-size:11px;color:#64748b;display:flex;gap:12px">
                                ${c.subtext ? '<span>📄 ' + c.subtext + '</span>' : ''}
                                ${c.telefono ? '<span>📞 ' + c.telefono + '</span>' : ''}
                                ${c.direccion ? '<span>📍 ' + c.direccion.substring(0, 30) + '</span>' : ''}
                            </div>
                        </div>
                    `).join('');
                    div.style.display = 'block';
                });
        }, 300);
    });

    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        const container = document.getElementById('cliente_search').parentElement;
        if (!container.contains(e.target)) {
            $('cliente_results').style.display = 'none';
        }
    });

    // Lógica de dibujo para la Firma Digital
    const canvas = $('canvasFirma');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let dibujando = false;
        let trazadoVacio = true;

        // Configuración del trazo
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function obtenerPosicion(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function iniciarDibujo(e) {
            dibujando = true;
            trazadoVacio = false;
            const pos = obtenerPosicion(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function dibujar(e) {
            if (!dibujando) return;
            const pos = obtenerPosicion(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            e.preventDefault();
        }

        function detenerDibujo() {
            dibujando = false;
        }

        // Eventos de Mouse
        canvas.addEventListener('mousedown', iniciarDibujo);
        canvas.addEventListener('mousemove', dibujar);
        window.addEventListener('mouseup', detenerDibujo);

        // Eventos de Pantalla Táctil (Celulares)
        canvas.addEventListener('touchstart', iniciarDibujo);
        canvas.addEventListener('touchmove', dibujar);
        canvas.addEventListener('touchend', detenerDibujo);

        window.limpiarFirma = function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            trazadoVacio = true;
            $('firma_base64').value = '';
        };

        window.guardarFirma = function() {
            if (!trazadoVacio) {
                $('firma_base64').value = canvas.toDataURL('image/png');
            }
        };
    } else {
        window.guardarFirma = function() {}; // No hacer nada si está editando
    }
</script>
@endpush
