@extends('layouts.app')

@section('title', 'Gestión de Empresas y Planes')
@section('topbar', 'SaaS - Empresas y Planes')

@section('content')
    <div style="margin-bottom:22px">
        <h1 class="page-title"><i class="bi bi-building-fill" style="color:var(--primary)"></i> Gestión de Empresas (Tenants)</h1>
        <p class="page-subtitle">Administra las empresas clientes registradas en tu plataforma SaaS y sus planes de suscripción.</p>
    </div>

    @if (session('ok'))
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> {{ session('ok') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <i class="bi bi-exclamation-circle"></i> Revisa los campos:
            <ul style="margin:6px 0 0 18px">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid-2" style="align-items:start; gap: 24px;">
        {{-- ===== LISTADO DE EMPRESAS ===== --}}
        <div class="card">
            <div class="card__header">Empresas Registradas</div>
            <div class="card__body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Administrador</th>
                                <th>Plan</th>
                                <th>Estado</th>
                                <th>Vencimiento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tenants as $t)
                                @php
                                    $admin = $t->users->first();
                                @endphp
                                <tr>
                                    <td><strong>{{ $t->nombre }}</strong><br><small class="text-muted">{{ $t->slug }}</small></td>
                                    <td>
                                        @if ($admin)
                                            <strong>{{ $admin->name }}</strong><br>
                                            <small class="text-muted">{{ $admin->email }}</small>
                                        @else
                                            <span class="text-muted">Sin Administrador</span>
                                        @endif
                                    </td>
                                    <td>{{ $t->plan->nombre ?? 'Sin Plan' }}</td>
                                    <td>
                                        <span class="badge-pill {{ $t->estado === 'activo' ? 'b-green' : ($t->estado === 'prueba' ? 'b-blue' : 'b-red') }}">
                                            {{ ucfirst($t->estado) }}
                                        </span>
                                    </td>
                                    <td>{{ $t->fecha_vencimiento ? $t->fecha_vencimiento->format('d/m/Y') : 'Ilimitado' }}</td>
                                    <td style="white-space:nowrap">
                                        <button class="btn btn-light btn-sm" onclick="editarTenant({{ json_encode($t) }})" title="Editar Empresa">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-light btn-sm" onclick="abrirResetPassword({{ json_encode($t) }})" title="Restablecer Clave Admin" style="color: var(--danger)">
                                            <i class="bi bi-key-fill"></i>
                                        </button>
                                        <button class="btn btn-light btn-sm" onclick="abrirResetData({{ json_encode($t) }})" title="Resetear Datos de la Empresa" style="color: #f59e0b">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                        <button class="btn btn-sm {{ $t->activo ? 'btn-warning' : 'btn-success' }}" onclick="toggleActivo({{ json_encode($t) }})" title="{{ $t->activo ? 'Desactivar Empresa' : 'Activar Empresa' }}">
                                            <i class="bi {{ $t->activo ? 'bi-pause-fill' : 'bi-play-fill' }}"></i> {{ $t->activo ? 'Desactivar' : 'Activar' }}
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="abrirEliminarTenant({{ json_encode($t) }})" title="Eliminar Empresa (BORRADO TOTAL)">
                                            <i class="bi bi-building-x"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center;color:var(--text-muted)">No hay empresas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:16px">
                    {{ $tenants->links() }}
                </div>
            </div>
        </div>

        {{-- ===== FORMULARIO EMPRESA ===== --}}
        <div class="card">
            <div class="card__header" id="tenantFormTitle">Registrar Nueva Empresa</div>
            <div class="card__body">
                <form id="tenantForm" action="{{ route('superadmin.tenants.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="tenantMethod" value="POST">

                    <div class="form-group">
                        <label>Nombre de la Empresa *</label>
                        <input type="text" name="nombre" id="tenantNombre" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Plan de Suscripción *</label>
                        <select name="plan_id" id="tenantPlanId" class="form-control" required>
                            <option value="">Selecciona un plan</option>
                            @foreach ($planes as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }} (S/ {{ number_format($p->precio, 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Estado *</label>
                        <select name="estado" id="tenantEstado" class="form-control" required>
                            <option value="prueba">Prueba Gratuita</option>
                            <option value="activo">Activo</option>
                            <option value="suspendido">Suspendido</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" id="tenantVencimiento" class="form-control">
                    </div>

                    <div class="form-actions" style="margin-top:20px">
                        <button type="submit" class="btn btn-primary" id="tenantSubmitBtn">Registrar Empresa</button>
                        <button type="button" class="btn btn-light" onclick="resetTenantForm()" style="display:none" id="tenantCancelBtn">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="grid-2" style="align-items:start; gap: 24px; margin-top: 30px;">
        {{-- ===== LISTADO DE PLANES ===== --}}
        <div class="card">
            <div class="card__header">Planes de Suscripción</div>
            <div class="card__body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Límites (Usr/Cli/Prést)</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($planes as $p)
                                <tr>
                                    <td><strong>{{ $p->nombre }}</strong></td>
                                    <td>S/ {{ number_format($p->precio, 2) }}</td>
                                    <td>{{ $p->limite_usuarios }} / {{ $p->limite_clientes }} / {{ $p->limite_prestamos }}</td>
                                    <td>
                                        <button class="btn btn-light btn-sm" onclick="editarPlan({{ json_encode($p) }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center;color:var(--text-muted)">No hay planes creados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===== FORMULARIO PLAN ===== --}}
        <div class="card">
            <div class="card__header" id="planFormTitle">Crear Nuevo Plan</div>
            <div class="card__body">
                <form id="planForm" action="{{ route('superadmin.planes.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="planMethod" value="POST">

                    <div class="form-group">
                        <label>Nombre del Plan *</label>
                        <input type="text" name="nombre" id="planNombre" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" id="planDescripcion" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Precio Mensual (S/) *</label>
                        <input type="number" step="0.01" name="precio" id="planPrecio" class="form-control" required>
                    </div>

                    <div class="form-grid" style="grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <div class="form-group">
                            <label>Límite Usuarios *</label>
                            <input type="number" name="limite_usuarios" id="planUsuarios" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Límite Clientes *</label>
                            <input type="number" name="limite_clientes" id="planClientes" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Límite Préstamos *</label>
                            <input type="number" name="limite_prestamos" id="planPrestamos" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top:20px">
                        <button type="submit" class="btn btn-primary" id="planSubmitBtn">Crear Plan</button>
                        <button type="button" class="btn btn-light" onclick="resetPlanForm()" style="display:none" id="planCancelBtn">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Restablecer Contraseña -->
    <div id="resetPasswordModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div class="card" style="width:100%; max-width:450px; margin:20px;">
            <div class="card__header">Restablecer Clave de Administrador</div>
            <div class="card__body">
                <form id="resetPasswordForm" action="" method="POST">
                    @csrf
                    @method('POST')
                    <p style="margin-bottom:15px; font-size:14px; color:var(--text-muted)">
                        Estás restableciendo la contraseña del usuario administrador de la empresa <strong id="resetTenantName"></strong>.
                    </p>
                    <div class="form-group">
                        <label>Nueva Contraseña *</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirmar Nueva Contraseña *</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                    </div>
                    <div class="form-actions" style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                        <button type="button" class="btn btn-light" onclick="cerrarResetPassword()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Restablecer Clave</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para ELIMINAR Empresa (BORRADO TOTAL) -->
    <div id="eliminarTenantModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div class="card" style="width:100%; max-width:450px; margin:20px;">
            <div class="card__header" style="background: #dc2626; color: #fff;">☠️ ELIMINAR EMPRESA COMPLETAMENTE</div>
            <div class="card__body">
                <form id="eliminarTenantForm" action="" method="POST">
                    @csrf
                    <p style="margin-bottom:15px; font-size:14px; color:var(--text-muted)">
                        Esta acción <strong>ELIMINARÁ PERMANENTEMENTE</strong> la empresa <strong id="eliminarTenantName"></strong> junto con <strong>TODOS</strong> sus datos:
                    </p>
                    <ul style="margin-bottom:15px; font-size:13px; color:var(--danger); list-style:disc; padding-left:20px">
                        <li>Usuarios (admin, cobradores, operadores)</li>
                        <li>Clientes, préstamos, cuotas y pagos</li>
                        <li>Empeños, movimientos de caja</li>
                        <li>Auditorías y configuraciones</li>
                    </ul>
                    <p style="margin-bottom:15px; font-size:14px; font-weight: bold; color: #dc2626;">
                        ⚠️ Esta acción NO se puede deshacer. Para confirmar, escribe la palabra <strong>ELIMINAR</strong>:
                    </p>
                    <div class="form-group">
                        <input type="text" name="confirmacion" id="eliminarConfirmInput" class="form-control" placeholder="Escribe ELIMINAR" required autocomplete="off">
                    </div>
                    <div class="form-actions" style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                        <button type="button" class="btn btn-light" onclick="cerrarEliminarTenant()">Cancelar</button>
                        <button type="submit" class="btn btn-danger" style="background: #dc2626; color: #fff;">Eliminar Empresa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Resetear Datos de la Empresa -->
    <div id="resetDataModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div class="card" style="width:100%; max-width:450px; margin:20px;">
            <div class="card__header" style="background: var(--danger); color: #fff;">⚠️ RESETEAR DATOS OPERATIVOS</div>
            <div class="card__body">
                <form id="resetDataForm" action="" method="POST">
                    @csrf
                    <p style="margin-bottom:15px; font-size:14px; color:var(--text-muted)">
                        Esta acción eliminará **permanentemente** todos los clientes, préstamos, cuotas, pagos, empeños, movimientos de caja y auditorías de la empresa <strong id="resetDataTenantName"></strong>.
                    </p>
                    <p style="margin-bottom:15px; font-size:14px; font-weight: bold; color: var(--danger)">
                        Esta acción NO se puede deshacer. Para confirmar, escribe la palabra **RESET** en el campo de abajo:
                    </p>
                    <div class="form-group">
                        <input type="text" name="confirmacion" id="resetConfirmInput" class="form-control" placeholder="Escribe RESET" required autocomplete="off">
                    </div>
                    <div class="form-actions" style="margin-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                        <button type="button" class="btn btn-light" onclick="cerrarResetData()">Cancelar</button>
                        <button type="submit" class="btn btn-danger" style="background: var(--danger); color: #fff;">Resetear Todo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function editarTenant(tenant) {
        document.getElementById('tenantFormTitle').innerText = 'Editar Empresa: ' + tenant.nombre;
        document.getElementById('tenantForm').action = '/super-admin/tenants/' + tenant.id;
        document.getElementById('tenantMethod').value = 'PUT';
        document.getElementById('tenantNombre').value = tenant.nombre;
        document.getElementById('tenantPlanId').value = tenant.plan_id;
        document.getElementById('tenantEstado').value = tenant.estado;
        document.getElementById('tenantVencimiento').value = tenant.fecha_vencimiento ? tenant.fecha_vencimiento.substring(0, 10) : '';
        document.getElementById('tenantSubmitBtn').innerText = 'Guardar Cambios';
        document.getElementById('tenantCancelBtn').style.display = '';
    }

    function resetTenantForm() {
        document.getElementById('tenantFormTitle').innerText = 'Registrar Nueva Empresa';
        document.getElementById('tenantForm').action = '{{ route("superadmin.tenants.store") }}';
        document.getElementById('tenantMethod').value = 'POST';
        document.getElementById('tenantForm').reset();
        document.getElementById('tenantSubmitBtn').innerText = 'Registrar Empresa';
        document.getElementById('tenantCancelBtn').style.display = 'none';
    }

    function editarPlan(plan) {
        document.getElementById('planFormTitle').innerText = 'Editar Plan: ' + plan.nombre;
        document.getElementById('planForm').action = '/super-admin/planes/' + plan.id;
        document.getElementById('planMethod').value = 'PUT';
        document.getElementById('planNombre').value = plan.nombre;
        document.getElementById('planDescripcion').value = plan.descripcion || '';
        document.getElementById('planPrecio').value = plan.precio;
        document.getElementById('planUsuarios').value = plan.limite_usuarios;
        document.getElementById('planClientes').value = plan.limite_clientes;
        document.getElementById('planPrestamos').value = plan.limite_prestamos;
        document.getElementById('planSubmitBtn').innerText = 'Guardar Cambios';
        document.getElementById('planCancelBtn').style.display = '';
    }

    function resetPlanForm() {
        document.getElementById('planFormTitle').innerText = 'Crear Nuevo Plan';
        document.getElementById('planForm').action = '{{ route("superadmin.planes.store") }}';
        document.getElementById('planMethod').value = 'POST';
        document.getElementById('planForm').reset();
        document.getElementById('planSubmitBtn').innerText = 'Crear Plan';
        document.getElementById('planCancelBtn').style.display = 'none';
    }

    function abrirResetPassword(tenant) {
        document.getElementById('resetTenantName').innerText = tenant.nombre;
        document.getElementById('resetPasswordForm').action = '/super-admin/tenants/' + tenant.id + '/reset-password';
        document.getElementById('resetPasswordModal').style.display = 'flex';
    }

    function cerrarResetPassword() {
        document.getElementById('resetPasswordModal').style.display = 'none';
        document.getElementById('resetPasswordForm').reset();
    }

    function abrirResetData(tenant) {
        document.getElementById('resetDataTenantName').innerText = tenant.nombre;
        document.getElementById('resetDataForm').action = '/super-admin/tenants/' + tenant.id + '/reset-data';
        document.getElementById('resetDataModal').style.display = 'flex';
    }

    function cerrarResetData() {
        document.getElementById('resetDataModal').style.display = 'none';
        document.getElementById('resetDataForm').reset();
    }

    function abrirEliminarTenant(tenant) {
        document.getElementById('eliminarTenantName').innerText = tenant.nombre;
        document.getElementById('eliminarTenantForm').action = '/super-admin/tenants/' + tenant.id + '/eliminar';
        document.getElementById('eliminarTenantModal').style.display = 'flex';
    }

    function cerrarEliminarTenant() {
        document.getElementById('eliminarTenantModal').style.display = 'none';
        document.getElementById('eliminarTenantForm').reset();
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function toggleActivo(tenant) {
        const accion = tenant.activo ? 'desactivar' : 'activar';
        if (!confirm(`¿Estás seguro de ${accion} la empresa "${tenant.nombre}"?\n\nLos usuarios de esta empresa no podrán acceder al sistema hasta que la actives nuevamente.`)) {
            return;
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/super-admin/tenants/' + tenant.id + '/toggle';
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = getCsrfToken();
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endpush
