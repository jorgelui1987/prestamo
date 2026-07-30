<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\CorteCajaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpenoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RastreoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReporteEfectividadController;
use App\Http\Controllers\PromesasController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas publicas (invitado)
|--------------------------------------------------------------------------
*/
// Ruta para servir archivos de storage (funciona sin enlace simbólico)
Route::get('/storage/{path}', [\App\Http\Controllers\StorageController::class, 'serve'])
    ->where('path', '.*')
    ->name('storage.serve');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Registro de nuevas empresas (SaaS Onboarding)
    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Recuperación de contraseña
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequest'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Rutas 2FA para usuarios no autenticados (deben estar fuera del grupo auth)
Route::middleware('guest')->group(function () {
    Route::get('/2fa/verify', [\App\Http\Controllers\TwoFactorController::class, 'showVerifyForm'])->name('2fa.verify.form');
    Route::post('/2fa/send', [\App\Http\Controllers\TwoFactorController::class, 'sendCode'])->name('2fa.send');
    Route::post('/2fa/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/resend', [\App\Http\Controllers\TwoFactorController::class, 'resend'])->name('2fa.resend');
    Route::get('/2fa/recovery', [\App\Http\Controllers\TwoFactorController::class, 'showRecoveryForm'])->name('2fa.recovery.form');
    Route::post('/2fa/recovery', [\App\Http\Controllers\TwoFactorController::class, 'verifyRecovery'])->name('2fa.recovery.verify');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')->name('logout');

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    $planes = \App\Models\Plan::orderBy('precio')->get();
    return view('landing', compact('planes'));
})->name('home');

/*
|--------------------------------------------------------------------------
| Rutas protegidas (autenticadas)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Búsqueda global (todos los roles)
    Route::get('/buscar', [SearchController::class, 'index'])->name('buscar');
    Route::get('/prestamos/buscar-global', [PrestamoController::class, 'buscarGlobal'])->name('prestamos.buscar-global');
    Route::get('/clientes/buscar-json', [ClienteController::class, 'buscarJson'])->name('clientes.buscar-json');

        // Panel exclusivo del Super Administrador
        Route::get('/super-admin', [SuperAdminController::class, 'index'])->name('superadmin.index')->middleware('rol:superadmin');
        Route::get('/super-admin/tenants', [\App\Http\Controllers\TenantController::class, 'index'])->name('superadmin.tenants.index')->middleware('rol:superadmin');
        Route::post('/super-admin/tenants', [\App\Http\Controllers\TenantController::class, 'storeTenant'])->name('superadmin.tenants.store')->middleware('rol:superadmin');
        Route::put('/super-admin/tenants/{tenant}', [\App\Http\Controllers\TenantController::class, 'updateTenant'])->name('superadmin.tenants.update')->middleware('rol:superadmin');
        Route::post('/super-admin/tenants/{tenant}/toggle', [\App\Http\Controllers\TenantController::class, 'toggleActivo'])->name('superadmin.tenants.toggle')->middleware('rol:superadmin');
        Route::post('/super-admin/tenants/{tenant}/reset-password', [\App\Http\Controllers\TenantController::class, 'resetAdminPassword'])->name('superadmin.tenants.reset-password')->middleware('rol:superadmin');
        Route::post('/super-admin/tenants/{tenant}/reset-data', [\App\Http\Controllers\TenantController::class, 'resetTenantData'])->name('superadmin.tenants.reset-data')->middleware('rol:superadmin');
        Route::post('/super-admin/tenants/{tenant}/create-admin', [\App\Http\Controllers\TenantController::class, 'createAdminUser'])->name('superadmin.tenants.create-admin')->middleware('rol:superadmin');
        Route::post('/super-admin/tenants/{tenant}/eliminar', [\App\Http\Controllers\TenantController::class, 'destroyTenant'])->name('superadmin.tenants.destroy')->middleware('rol:superadmin');
        Route::post('/super-admin/planes', [\App\Http\Controllers\TenantController::class, 'storePlan'])->name('superadmin.planes.store')->middleware('rol:superadmin');
        Route::put('/super-admin/planes/{plan}', [\App\Http\Controllers\TenantController::class, 'updatePlan'])->name('superadmin.planes.update')->middleware('rol:superadmin');
        Route::post('/super-admin/smtp', [\App\Http\Controllers\SuperAdminController::class, 'guardarSmtp'])->name('superadmin.smtp.guardar')->middleware('rol:superadmin');

    // Modulos funcionales (CRUD)
    Route::resource('clientes', ClienteController::class)->except('show')->middleware('rol:admin,gerente,operador,cobrador');
    Route::resource('prestamos', PrestamoController::class)->middleware('rol:admin,gerente,operador,cobrador');

    // Pagos
    Route::middleware('rol:admin,gerente,operador,cobrador')->group(function () {
        Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
        Route::get('/prestamos/{prestamo}/pagar', [PagoController::class, 'create'])->name('pagos.create');
        Route::post('/prestamos/{prestamo}/pagar', [PagoController::class, 'store'])->name('pagos.store');
        Route::delete('/pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy');

        // Cobranzas y Mora
        Route::get('/cobranzas', [CobranzaController::class, 'index'])->name('cobranzas.index');
        Route::get('/mora', [CobranzaController::class, 'mora'])->name('mora.index');
    });

    // Caja (Excluido el cobrador)
    Route::middleware('rol:admin,gerente,operador')->group(function () {
        Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
        Route::post('/caja', [CajaController::class, 'store'])->name('caja.store');
        Route::post('/caja/arqueo', [CajaController::class, 'guardarArqueo'])->name('caja.arqueo');
        Route::delete('/caja/{movimiento}', [CajaController::class, 'destroy'])->name('caja.destroy');
    });

    // Empenos
    Route::middleware('rol:admin,gerente,operador')->group(function () {
        Route::resource('empenos', EmpenoController::class);
        Route::patch('/empenos/{empeno}/estado', [EmpenoController::class, 'cambiarEstado'])->name('empenos.estado');
    });

    // Corte de caja, reportes y auditoria (gestión)
    Route::middleware('rol:admin,gerente')->group(function () {
        Route::get('/corte-caja', [CorteCajaController::class, 'index'])->name('corte.index');
        Route::post('/corte-caja', [CorteCajaController::class, 'store'])->name('corte.store');

        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::post('/reportes/enviar-diario', [ReporteController::class, 'enviarReporteDiario'])->name('reportes.enviar-diario');

        // Reporte de efectividad de cobradores
        Route::get('/reportes/efectividad', [ReporteEfectividadController::class, 'index'])->name('reportes.efectividad');

        // Rastreo de cobradores en mapa
        Route::get('/reportes/rastreo', [RastreoController::class, 'index'])->name('reportes.rastreo');

        // Seguimiento de promesas de pago
        Route::get('/reportes/promesas', [PromesasController::class, 'index'])->name('reportes.promesas');
        Route::patch('/promesas/{visita}/cumplir', [PromesasController::class, 'cumplir'])->name('promesas.cumplir');

        // Rutas genéricas de reportes (deben ir DESPUÉS de las rutas específicas)
        Route::get('/reportes/{tipo}', [ReporteController::class, 'ver'])->name('reportes.ver');
        Route::get('/reportes/{tipo}/excel', [ReporteController::class, 'excel'])->name('reportes.excel');

        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    });

    // Administracion (solo admin / superadmin)
    Route::middleware('rol:admin')->group(function () {
        Route::resource('usuarios', UserController::class)->parameters(['usuarios' => 'usuario'])->except('show');
        Route::get('/usuarios/{usuario}/toggle-activo', [UserController::class, 'toggleActivo'])->name('usuarios.toggle-activo');
        Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('config.index');
        Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('config.update');
    });
    // Perfil del usuario
    Route::get('/perfil', [ProfileController::class, 'show'])->name('perfil.show');
    Route::put('/perfil', [ProfileController::class, 'updatePerfil'])->name('perfil.update');
    Route::put('/perfil/password', [ProfileController::class, 'updatePassword'])->name('perfil.password');
    Route::post('/perfil/foto', [ProfileController::class, 'updateFoto'])->name('perfil.foto');
    Route::delete('/perfil/foto', [ProfileController::class, 'deleteFoto'])->name('perfil.foto.delete');
    Route::post('/perfil/logo-plataforma', [ProfileController::class, 'updateLogoPlataforma'])->name('perfil.logo-plataforma')->middleware('rol:superadmin');

    // Gestión de 2FA (autenticado)
    Route::post('/2fa/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('2fa.disable');
    Route::post('/2fa/regenerate-codes', [\App\Http\Controllers\TwoFactorController::class, 'regenerateBackupCodes'])->name('2fa.regenerate-codes');

    // Rutas de la App Móvil para Cobradores
    Route::middleware('rol:cobrador,admin,gerente,operador')->group(function () {
        Route::get('/movil', [\App\Http\Controllers\MovilCobradorController::class, 'index'])->name('movil.index');
        Route::get('/movil/detalle/{prestamo}', [\App\Http\Controllers\MovilCobradorController::class, 'detalle'])->name('movil.detalle');
        Route::get('/movil/cobrar/{prestamo}', [\App\Http\Controllers\MovilCobradorController::class, 'cobroExpress'])->name('movil.cobrar');
        Route::post('/movil/gasto', [\App\Http\Controllers\MovilCobradorController::class, 'registrarGasto'])->name('movil.gasto');
        Route::post('/movil/ruta', [\App\Http\Controllers\MovilCobradorController::class, 'actualizarRuta'])->name('movil.ruta');
        Route::get('/movil/historial', [\App\Http\Controllers\MovilCobradorController::class, 'historialPagos'])->name('movil.historial');
        Route::get('/movil/exito', [\App\Http\Controllers\MovilCobradorController::class, 'exito'])->name('movil.exito');
        Route::post('/movil/no-pago', [\App\Http\Controllers\MovilCobradorController::class, 'registrarNoPago'])->name('movil.no-pago');
        Route::delete('/movil/no-pago/{visita}', [\App\Http\Controllers\MovilCobradorController::class, 'anularNoPago'])->name('movil.no-pago.anular');
        Route::post('/movil/sync-batch', [\App\Http\Controllers\MovilCobradorController::class, 'syncBatch'])->name('movil.sync-batch');
    });
});
