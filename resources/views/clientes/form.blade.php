@extends('layouts.app')

@php 
    $editando = $cliente->exists; 
    $esMovil = request()->query('origen') === 'movil' || auth()->user()->rol === 'cobrador';
@endphp

@section('title', $editando ? 'Editar Cliente' : 'Nuevo Cliente')
@section('topbar', $editando ? 'Editar Cliente' : 'Nuevo Cliente')

@section('content')
    <div style="margin-bottom:22px">
        <a href="{{ $esMovil ? route('movil.index') : route('clientes.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
        <h1 class="page-title" style="margin-top:12px">{{ $editando ? 'Editar Cliente' : 'Nuevo Cliente' }}</h1>
        <p class="page-subtitle">{{ $editando ? 'Actualiza los datos del cliente.' : 'Completa la información para registrar un cliente.' }}</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <i class="bi bi-exclamation-circle"></i> Revisa los campos:
            <ul style="margin:6px 0 0 18px">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card__body">
            <form action="{{ $editando ? route('clientes.update', $cliente) : route('clientes.store', ['origen' => request()->query('origen')]) }}" method="POST">
                @csrf
                @if ($editando) @method('PUT') @endif

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombres *</label>
                        <input type="text" name="nombres" class="form-control" value="{{ old('nombres', $cliente->nombres) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Apellidos *</label>
                        <input type="text" name="apellidos" class="form-control" value="{{ old('apellidos', $cliente->apellidos) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Tipo de documento *</label>
                        <select name="tipo_documento" class="form-control" required>
                            @foreach (['DNI','RUC','CE','Pasaporte'] as $td)
                                <option value="{{ $td }}" @selected(old('tipo_documento', $cliente->tipo_documento) === $td)>{{ $td }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>N° Documento</label>
                        <input type="text" name="documento" class="form-control" value="{{ old('documento', $cliente->documento) }}">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $cliente->telefono) }}">
                    </div>
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $cliente->email) }}">
                    </div>
                    <div class="form-group">
                        <label>Ocupación</label>
                        <input type="text" name="ocupacion" class="form-control" value="{{ old('ocupacion', $cliente->ocupacion) }}">
                    </div>
                    <div class="form-group">
                        <label>Ingreso mensual (S/)</label>
                        <input type="number" step="0.01" name="ingreso_mensual" class="form-control" value="{{ old('ingreso_mensual', $cliente->ingreso_mensual ?? 0) }}">
                    </div>
                    <div class="form-group full">
                        <label>Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $cliente->direccion) }}">
                    </div>
                    <div class="form-group">
                        <label>Estado *</label>
                        <select name="estado" class="form-control" required>
                            @foreach (['activo'=>'Activo','inactivo'=>'Inactivo','moroso'=>'Moroso'] as $v=>$l)
                                <option value="{{ $v }}" @selected(old('estado', $cliente->estado ?? 'activo') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones', $cliente->observaciones) }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ $editando ? 'Guardar cambios' : 'Registrar cliente' }}</button>
                    <a href="{{ $esMovil ? route('movil.index') : route('clientes.index') }}" class="btn btn-light">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
