@extends('layouts.app')

@section('title', 'Auditoria')
@section('topbar', 'Auditoria')

@section('content')
    <h1 class="page-title">Auditoría</h1>
    <p class="page-subtitle">Bitácora de acciones y trazabilidad del sistema.</p>

    <div class="card" style="margin-bottom:18px">
        <div class="card__body">
            <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
                <div class="form-group" style="margin:0">
                    <label>Usuario</label>
                    <select name="user_id" class="form-control" style="min-width:180px">
                        <option value="">Todos</option>
                        @foreach ($usuarios as $u)
                            <option value="{{ $u->id }}" @selected($userId == $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Acción</label>
                    <select name="accion" class="form-control" style="min-width:160px">
                        <option value="">Todas</option>
                        @foreach ($acciones as $a)
                            <option value="{{ $a }}" @selected($accion === $a)>{{ ucfirst($a) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Desde</label>
                    <input type="date" name="desde" value="{{ $desde }}" class="form-control">
                </div>
                <div class="form-group" style="margin:0">
                    <label>Hasta</label>
                    <input type="date" name="hasta" value="{{ $hasta }}" class="form-control">
                </div>
                <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
                <a href="{{ route('auditoria.index') }}" class="btn btn-light">Limpiar</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card__header">Registros ({{ $registros->total() }})</div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Fecha y hora</th><th>Usuario</th><th>Acción</th><th>Módulo</th><th>Referencia</th><th>IP</th></tr>
                </thead>
                <tbody>
                    @forelse ($registros as $r)
                        @php
                            $amap = ['creo'=>'b-green','actualizo'=>'b-blue','elimino'=>'b-red','inicio sesion'=>'b-purple','cierre sesion'=>'b-gray'];
                        @endphp
                        <tr>
                            <td>{{ $r->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $r->usuario_nombre ?? ($r->user->name ?? '—') }}</td>
                            <td><span class="badge-pill {{ $amap[$r->accion] ?? 'b-gray' }}">{{ ucfirst($r->accion) }}</span></td>
                            <td>{{ $r->modulo ?? '—' }}</td>
                            <td>{{ $r->referencia ?? '—' }}</td>
                            <td><span style="font-size:12px;color:var(--text-muted)">{{ $r->ip ?? '—' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:36px">No hay registros de auditoría.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:0 20px 18px">{{ $registros->links() }}</div>
    </div>
@endsection
