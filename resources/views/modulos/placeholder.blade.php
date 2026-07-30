@extends('layouts.app')

@section('title', $titulo)
@section('topbar', $titulo)

@section('content')
    <h1 class="page-title">{{ $titulo }}</h1>
    <p class="page-subtitle">{{ $descripcion }}</p>

    <div class="card">
        <div class="placeholder-box">
            <div class="p-icon"><i class="bi {{ $icono }}"></i></div>
            <h2>Módulo "{{ $titulo }}"</h2>
            <p>{{ $descripcion }}</p>
            <p>Este módulo forma parte del sistema y está listo para ser desarrollado en la siguiente iteración.</p>
            <span class="tag"><i class="bi bi-tools"></i> En construcción</span>
        </div>
    </div>
@endsection
