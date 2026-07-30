@extends('layouts.app')

@php $editando = $usuario->exists; @endphp

@section('title', $editando ? 'Editar Usuario' : 'Nuevo Usuario')
@section('topbar', $editando ? 'Editar Usuario' : 'Nuevo Usuario')

@section('content')
    <div style="margin-bottom:22px">
        <a href="{{ route('usuarios.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Volver</a>
        <h1 class="page-title" style="margin-top:12px">{{ $editando ? 'Editar Usuario' : 'Nuevo Usuario' }}</h1>
        <p class="page-subtitle">{{ $editando ? 'Actualiza los datos y permisos del usuario.' : 'Registra un nuevo usuario del sistema.' }}</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <i class="bi bi-exclamation-circle"></i> Revisa los campos:
            <ul style="margin:6px 0 0 18px">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card" style="max-width:760px">
        <div class="card__body">
            <form action="{{ $editando ? route('usuarios.update', $usuario) : route('usuarios.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if ($editando) @method('PUT') @endif

                <div class="form-group full" style="margin-bottom:18px">
                    <label>Foto de perfil</label>
                    <div style="display:flex;align-items:center;gap:16px">
                        <span class="avatar" style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;display:grid;place-items:center;font-size:24px;font-weight:800;overflow:hidden;flex-shrink:0">
                            <img id="avatarPreview" src="{{ $editando && $usuario->avatar_url ? $usuario->avatar_url : '' }}" alt=""
                                 style="width:100%;height:100%;object-fit:cover;{{ $editando && $usuario->avatar_url ? '' : 'display:none' }}">
                            <span id="avatarInitial" style="{{ $editando && $usuario->avatar_url ? 'display:none' : '' }}">{{ strtoupper(substr($usuario->name ?: 'U', 0, 1)) }}</span>
                        </span>
                        <div>
                            <input type="file" name="avatar" id="avatarFile" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small style="color:var(--text-muted)">JPG, PNG o WEBP. Máximo 2 MB.</small>
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nombre completo *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $usuario->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Correo electrónico *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $usuario->email) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Rol *</label>
                        <select name="rol" class="form-control" required>
                            @foreach ($roles as $v => $l)
                                <option value="{{ $v }}" @selected(old('rol', $usuario->rol) === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $usuario->telefono) }}">
                    </div>
                    <div class="form-group">
                        <label>Contraseña {{ $editando ? '(dejar en blanco para no cambiar)' : '*' }}</label>
                        <input type="password" name="password" class="form-control" {{ $editando ? '' : 'required' }} autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                    <div class="form-group full">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                            <input type="checkbox" name="activo" value="1" @checked(old('activo', $usuario->activo ?? true))>
                            Usuario activo (puede iniciar sesión)
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> {{ $editando ? 'Guardar cambios' : 'Crear usuario' }}</button>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-light">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('avatarFile')?.addEventListener('change', function (e) {
        const file = e.target.files[0];
        const img = document.getElementById('avatarPreview');
        const ini = document.getElementById('avatarInitial');
        if (!file) return;
        const url = URL.createObjectURL(file);
        img.src = url;
        img.style.display = '';
        if (ini) ini.style.display = 'none';
    });
</script>
@endpush
