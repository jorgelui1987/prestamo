@extends('layouts.app')

@section('title', 'Tarjeta de Abonos')
@section('topbar', 'Tarjeta de Abonos')

@push('styles')
<style>
    @media print {
        body { background: white !important; }
        .no-print { display: none !important; }
        .sidebar, .topbar, .sidebar-backdrop { display: none !important; }
        .main { margin-left: 0 !important; }
        .content { padding: 0 !important; }
        .card-print { border: 2px solid #000 !important; box-shadow: none !important; }
        .print-break { page-break-after: always; }
    }
    .card-print {
        max-width: 420px;
        margin: 0 auto;
        background-color: #fef08a;
        border: 2px solid #1e3a8a;
        padding: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.15);
        position: relative;
    }
    .card-print::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, #1e3a8a, #3b82f6, #1e3a8a);
    }
    .card-print input, .card-print select {
        background: transparent;
        border: none;
        border-bottom: 1px solid #1e3a8a;
        outline: none;
        font-family: inherit;
        font-size: inherit;
        color: #1e3a8a;
        font-weight: 600;
    }
    .card-print input:focus {
        background: rgba(255,255,255,0.5);
        border-bottom: 2px solid #2563eb;
    }
    .card-print .field-box {
        background: white;
        border: 1px solid #1e3a8a;
        text-align: center;
    }
    .table-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
    }
    .header-cell {
        border: 1px solid #1e3a8a;
        padding: 4px 2px;
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        color: #1e3a8a;
        background: rgba(30, 58, 138, 0.05);
    }
    .row-cell {
        border: 1px solid #1e3a8a;
        padding: 2px;
        min-height: 30px;
    }
    .row-cell input {
        width: 100%;
        height: 100%;
        border: none;
        background: transparent;
        text-align: center;
        font-size: 11px;
        font-weight: 600;
        outline: none;
        font-family: inherit;
        color: #1e3a8a;
    }
    .row-cell input:focus {
        background: rgba(59, 130, 246, 0.1);
        border-radius: 2px;
    }
    .btn-print {
        background: #1e3a8a;
        color: white;
        padding: 10px 24px;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-print:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    .btn-clear {
        background: #fff;
        color: #1e3a8a;
        border: 1px solid #1e3a8a;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-clear:hover {
        background: #f8fafc;
        border-color: #2563eb;
    }

    /* Efecto de desgaste (parece físico) */
    .card-print::after {
        content: '';
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            0deg,
            transparent,
            transparent 40px,
            rgba(139, 92, 246, 0.02) 40px,
            rgba(139, 92, 246, 0.02) 41px
        );
        pointer-events: none;
    }

    /* Animación al llenar */
    .filled-highlight {
        animation: fillPulse 0.6s ease;
    }
    @keyframes fillPulse {
        0% { background: rgba(37, 99, 235, 0.15); }
        100% { background: transparent; }
    }
</style>
@endpush

@section('content')
<div class="no-print mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="page-title">📇 Tarjeta de Abonos</h1>
        <p class="page-subtitle">Llena los campos y luego imprime para entregar al cliente.</p>
    </div>
    <div class="flex gap-3">
        <button onclick="limpiarTarjeta()" class="btn btn-light"><i class="bi bi-eraser"></i> Limpiar</button>
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Imprimir Tarjeta</button>
    </div>
</div>

{{-- Vista previa en tiempo real --}}
<div class="no-print mb-6">
    <div class="card">
        <div class="card__header">
            <span><i class="bi bi-eye"></i> Vista previa en tiempo real</span>
            <span class="badge-pill b-blue">Escribe en los campos de la tarjeta</span>
        </div>
    </div>
</div>

{{-- ========== TARJETA FÍSICA ========== --}}
<div class="card-print" id="tarjetaAbonos">

    <!-- Header: Nombre + Valor$ -->
    <div class="flex justify-between items-start border-b-2 border-blue-900 pb-2 mb-2">
        <div style="flex:1">
            <div style="font-size:13px;font-weight:600;color:#1e3a8a;">
                NOMBRE: <input type="text" id="nombre" style="border-bottom:1px solid #1e3a8a;background:transparent;border:none;border-bottom:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:13px;color:#1e3a8a;font-weight:600;width:100%" placeholder="Nombre completo del cliente">
            </div>
        </div>
        <div class="text-right" style="flex-shrink:0;margin-left:8px;white-space:nowrap;">
            <span style="font-size:13px;font-weight:700;color:#1e3a8a;">Valor $</span>
            <input type="text" id="valor" style="border-bottom:1px solid #1e3a8a;background:transparent;border:none;border-bottom:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:13px;color:#1e3a8a;font-weight:600;width:70px;" placeholder="0.00">
        </div>
    </div>

    <!-- Info Fields -->
    <div class="mb-4" style="color: #1e3a8a; font-size: 13px; font-weight: 600;">
        <p style="margin-bottom:4px;">CUOTA DIARIA <input type="text" id="cuota" style="border-bottom:1px solid #1e3a8a;background:transparent;border:none;border-bottom:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:13px;color:#1e3a8a;font-weight:600;width:100%" placeholder="Valor cuota diaria"></p>
        <p style="margin-bottom:4px;">C.C. No. <input type="text" id="cc" style="border-bottom:1px solid #1e3a8a;background:transparent;border:none;border-bottom:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:13px;color:#1e3a8a;font-weight:600;width:160px" placeholder="Número de cédula"></p>
        <div style="display:flex;gap:6px;margin-bottom:4px;">
            <p style="flex:1;">DIR. <input type="text" id="dir" style="border-bottom:1px solid #1e3a8a;background:transparent;border:none;border-bottom:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:13px;color:#1e3a8a;font-weight:600;width:100%" placeholder="Dirección"></p>
            <p style="flex:1;">CRA. <input type="text" id="cra" style="border-bottom:1px solid #1e3a8a;background:transparent;border:none;border-bottom:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:13px;color:#1e3a8a;font-weight:600;width:100%" placeholder="Carrera"></p>
            <p style="flex:1;">No. <input type="text" id="no" style="border-bottom:1px solid #1e3a8a;background:transparent;border:none;border-bottom:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:13px;color:#1e3a8a;font-weight:600;width:100%" placeholder="Número"></p>
        </div>
        <div style="display:flex;gap:12px;">
            <p>Fecha 
                <input type="text" id="fechaD" style="width:24px;text-align:center;background:white;border:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:12px;color:#1e3a8a;font-weight:600;" maxlength="2" placeholder="D"> / 
                <input type="text" id="fechaM" style="width:24px;text-align:center;background:white;border:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:12px;color:#1e3a8a;font-weight:600;" maxlength="2" placeholder="M"> / 
                <input type="text" id="fechaA" style="width:36px;text-align:center;background:white;border:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:12px;color:#1e3a8a;font-weight:600;" maxlength="4" placeholder="AAAA">
            </p>
            <p>Tel <input type="text" id="tel" style="border-bottom:1px solid #1e3a8a;background:transparent;border:none;border-bottom:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:13px;color:#1e3a8a;font-weight:600;width:100px" placeholder="Teléfono"></p>
        </div>
    </div>

    <!-- Table: Fecha | Abonó | Resta (32 filas) -->
    <div class="table-grid-3" style="border-collapse: collapse; border: 1px solid #1e3a8a;">
        <!-- Headers -->
        <div class="header-cell">Fecha</div>
        <div class="header-cell">Abonó</div>
        <div class="header-cell">Resta</div>
        
        <!-- 32 Rows -->
        @for ($i = 1; $i <= 32; $i++)
            <div class="row-cell"><input type="text" id="f{{ $i }}" maxlength="10" placeholder="__/__"></div>
            <div class="row-cell"><input type="text" id="a{{ $i }}" maxlength="8" placeholder="$0"></div>
            <div class="row-cell"><input type="text" id="r{{ $i }}" maxlength="8" placeholder="$0"></div>
        @endfor
    </div>

    <!-- Legal Footer -->
    <div class="mt-4 text-[10px] text-center italic" style="color: #1e3a8a; font-size: 10px; font-style: italic;">
        Este documento se asimila a la Letra de Cambio y la mora en el pago acarrea intereses de conformidad con el artículo 774 del Código de Comercio.
    </div>

    <!-- Codeudor -->
    <div class="mt-4 space-y-2" style="color: #1e3a8a; font-weight: 600; font-size: 13px;">
        <p>Codeudor: <input type="text" id="codeudor" style="border-bottom:1px solid #1e3a8a;background:transparent;border:none;border-bottom:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:13px;color:#1e3a8a;font-weight:600;width:100%" placeholder="Nombre del codeudor"></p>
        <p>C.C. No. <input type="text" id="ccCodeudor" style="border-bottom:1px solid #1e3a8a;background:transparent;border:none;border-bottom:1px solid #1e3a8a;outline:none;font-family:inherit;font-size:13px;color:#1e3a8a;font-weight:600;width:160px" placeholder="Cédula del codeudor"></p>
    </div>
</div>

{{-- Consejos de uso --}}
<div class="no-print mt-8">
    <div class="card">
        <div class="card__header">
            <span><i class="bi bi-info-circle"></i> Cómo usar esta tarjeta</span>
        </div>
        <div class="card__body" style="font-size: 14px; color: var(--text-muted); line-height: 1.7;">
            <ul style="list-style: disc; padding-left: 20px;">
                <li><strong>Llena los campos</strong> directamente en la tarjeta amarilla — se ve como una tarjeta física real.</li>
                <li>La <strong>cuota diaria</strong> se replica automáticamente en la columna "Abonó" si lo deseas.</li>
                <li>Usa el botón <strong>"Imprimir Tarjeta"</strong> para obtener una copia física para el cliente.</li>
                <li>La tarjeta tiene un <strong>diseño profesional</strong> que se asimila a una Letra de Cambio.</li>
                <li>Al imprimir, solo se ve la tarjeta (sin menú, sin barra superior).</li>
                <li>Puedes <strong>limpiar todos los campos</strong> con el botón "Limpiar" para empezar de nuevo.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-enfoque al primer campo
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('cuota')?.focus();
});

// Función para limpiar todos los campos
function limpiarTarjeta() {
    if (!confirm('¿Estás seguro de limpiar todos los campos de la tarjeta?')) return;
    
    const inputs = document.querySelectorAll('#tarjetaAbonos input');
    inputs.forEach(input => {
        input.value = '';
    });
    
    // Feedback visual
    const card = document.getElementById('tarjetaAbonos');
    card.style.transition = 'opacity 0.3s';
    card.style.opacity = '0.5';
    setTimeout(() => {
        card.style.opacity = '1';
    }, 300);
}

// Auto-pasar al siguiente campo en fechas (D/M/A)
document.querySelectorAll('#fechaD, #fechaM').forEach(input => {
    input.addEventListener('input', function() {
        if (this.value.length === parseInt(this.maxLength)) {
            if (this.id === 'fechaD') {
                document.getElementById('fechaM')?.focus();
            } else if (this.id === 'fechaM') {
                document.getElementById('fechaA')?.focus();
            }
        }
    });
});

// Calcular resta automáticamente
document.querySelectorAll('[id^="a"]').forEach(input => {
    input.addEventListener('input', function() {
        const match = this.id.match(/^a(\d+)$/);
        if (!match) return;
        const num = match[1];
        const cuotaInput = document.getElementById('cuota');
        const restaInput = document.getElementById('r' + num);
        if (!cuotaInput || !restaInput) return;
        
        const cuota = parseFloat(cuotaInput.value.replace(/[^0-9.]/g, '')) || 0;
        const abono = parseFloat(this.value.replace(/[^0-9.]/g, '')) || 0;
        
        if (cuota > 0 && abono > 0) {
            restaInput.value = '$' + Math.max(0, cuota - abono).toFixed(0);
        }
    });
});
</script>
@endpush