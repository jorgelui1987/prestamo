@extends('layouts.app')

@section('title', 'Rastreo de Cobradores')
@section('topbar', 'Rastreo')

@section('content')
    <h1 class="page-title">📍 Rastreo de Cobradores en Tiempo Real</h1>
    <p class="page-subtitle">Última ubicación registrada de cada cobrador basada en sus cobros y visitas del día.</p>

    <div class="card" style="margin-bottom:20px">
        <div class="card__body">
            <form method="GET" action="{{ route('reportes.rastreo') }}" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
                <div>
                    <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px">Cobrador</label>
                    <select name="cobrador_id" class="form-control" style="width:auto;padding:6px 12px;font-size:13px" onchange="this.form.submit()">
                        <option value="">— Todos —</option>
                        @foreach ($cobradores as $cob)
                            <option value="{{ $cob->id }}" @selected($cobradorId == $cob->id)>{{ $cob->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px">Fecha</label>
                    <input type="date" name="fecha" value="{{ $fecha }}" class="form-control" style="width:auto;padding:6px 12px;font-size:13px" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    {{-- Mapa --}}
    <div class="card" style="margin-bottom:20px">
        <div class="card__header">
            <span>🗺️ Mapa de Ubicaciones</span>
            <span style="font-size:12px;color:var(--text-muted)">{{ count($ubicaciones) }} punto(s) registrado(s)</span>
        </div>
        <div class="card__body" style="padding:0">
            <div id="mapa" style="width:100%;height:500px;border-radius:0 0 12px 12px"></div>
        </div>
    </div>

    {{-- Leyenda --}}
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:20px;padding:12px 16px;background:#f8fafc;border-radius:10px;font-size:12px">
        <span style="display:flex;align-items:center;gap:6px">
            <span style="width:14px;height:14px;border-radius:50%;background:#10b981;display:inline-block"></span> Cobro exitoso
        </span>
        <span style="display:flex;align-items:center;gap:6px">
            <span style="width:14px;height:14px;border-radius:50%;background:#ef4444;display:inline-block"></span> Visita sin éxito
        </span>
        <span style="display:flex;align-items:center;gap:6px">
            <span style="width:14px;height:14px;border-radius:50%;background:#f59e0b;display:inline-block"></span> Promesa de pago
        </span>
    </div>

    {{-- Tabla de ubicaciones --}}
    <div class="card">
        <div class="card__header">📋 Historial de Ubicaciones (Hoy)</div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Cobrador</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Ubicación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ubicaciones as $u)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($u['created_at'])->format('H:i') }}</td>
                            <td><strong>{{ $u['cobrador'] }}</strong></td>
                            <td>{{ $u['cliente'] }}</td>
                            <td>
                                @if ($u['tipo'] === 'cobro')
                                    <span class="badge-pill b-green">💰 Cobro</span>
                                @elseif ($u['tipo'] === 'promesa')
                                    <span class="badge-pill b-yellow">📅 Promesa</span>
                                @else
                                    <span class="badge-pill b-red">❌ Sin éxito</span>
                                @endif
                            </td>
                            <td>{{ $u['monto'] ? 'S/ '.number_format($u['monto'], 2) : '—' }}</td>
                            <td>
                                <a href="https://www.google.com/maps?q={{ $u['lat'] }},{{ $u['lng'] }}" target="_blank" style="color:var(--primary);font-size:12px">
                                    <i class="bi bi-geo-alt"></i> Ver mapa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;color:var(--text-muted);padding:36px">
                                No hay ubicaciones registradas para la fecha seleccionada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha384-cxOPjt7s7Iz04uaHJceBmS+qpjv2JkIHNVcuOrM+YHwZOmJGBXI00mdUXEq65HTH" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha384-sHL9NAb7lN7rfvG5lfHpm643Xkcjzp4jFvuavGOndn6pjVqS6ny56CAt3nsEVT4H" crossorigin="anonymous" />
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ubicaciones = @json($ubicaciones);
        
        if (ubicaciones.length === 0) {
            document.getElementById('mapa').innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-muted)">Sin ubicaciones para mostrar</div>';
            return;
        }

        const map = L.map('mapa').setView([-12.0464, -77.0428], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 18,
        }).addTo(map);

        const bounds = [];
        const colores = { cobro: '#10b981', visita: '#ef4444', promesa: '#f59e0b' };
        const iconos = { cobro: '💰', visita: '❌', promesa: '📅' };

        ubicaciones.forEach(function(u) {
            const color = colores[u.tipo] || '#3b82f6';
            const icono = iconos[u.tipo] || '📍';
            
            const markerIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background:${color};color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3)">${icono}</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
            });

            const popupContent = `
                <div style="font-family:sans-serif;font-size:13px;line-height:1.5">
                    <strong>${u.cobrador}</strong><br>
                    🧑 ${u.cliente}<br>
                    ${u.monto ? '💵 S/ ' + u.monto.toFixed(2) + '<br>' : ''}
                    🕐 ${u.created_at}<br>
                    <a href="https://www.google.com/maps?q=${u.lat},${u.lng}" target="_blank" style="color:#3b82f6">📍 Ver en Google Maps</a>
                </div>
            `;

            const marker = L.marker([u.lat, u.lng], { icon: markerIcon })
                .addTo(map)
                .bindPopup(popupContent);

            bounds.push([u.lat, u.lng]);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    });
</script>
<style>
    .custom-marker { background: none !important; border: none !important; }
    .leaflet-popup-content-wrapper { border-radius: 12px !important; }
    .leaflet-popup-content { margin: 12px 16px !important; }
</style>
@endpush