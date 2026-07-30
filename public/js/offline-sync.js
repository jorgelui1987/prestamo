/**
 * offline-sync.js v2 - Sistema de sincronización offline mejorado
 * 
 * Guarda cobros, gastos y visitas en IndexedDB cuando no hay internet
 * y los sincroniza en batch cuando se recupera la conexión.
 */

const DB_NAME = 'tecnicell-offline';
const DB_VERSION = 2;
const STORE_NAME = 'pending_sync';

// Abrir la base de datos IndexedDB
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                const store = db.createObjectStore(STORE_NAME, { 
                    keyPath: 'id', 
                    autoIncrement: true 
                });
                store.createIndex('tipo', 'tipo', { unique: false });
                store.createIndex('fecha', 'fecha', { unique: false });
                store.createIndex('sincronizado', 'sincronizado', { unique: false });
                store.createIndex('intentos', 'intentos', { unique: false });
            }
        };
        
        request.onsuccess = (event) => resolve(event.target.result);
        request.onerror = (event) => reject(event.target.error);
    });
}

// Obtener CSRF token
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

// Guardar una operación pendiente en IndexedDB
async function guardarOffline(tipo, datos) {
    try {
        const db = await openDB();
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);
        
        const registro = {
            tipo: tipo,
            datos: JSON.stringify(datos),
            fecha: new Date().toISOString(),
            sincronizado: false,
            intentos: 0,
            ultimo_intento: null
        };
        
        const result = await new Promise((resolve, reject) => {
            const request = store.add(registro);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
        
        console.log(`✅ Operación offline guardada: ${tipo}`, result);
        actualizarContadorPendientes();
        mostrarNotificacionOffline(tipo);
        return result;
    } catch (error) {
        console.error('❌ Error al guardar offline:', error);
        throw error;
    }
}

// Mostrar notificación cuando se guarda algo offline
function mostrarNotificacionOffline(tipo) {
    const nombres = { cobro: 'Cobro', gasto: 'Gasto', visita: 'Visita sin éxito' };
    const nombre = nombres[tipo] || tipo;
    
    // Crear toast de notificación
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed; bottom: 100px; left: 16px; right: 16px; z-index: 9999;
        background: #1e293b; border: 1px solid #f59e0b; border-radius: 12px;
        padding: 14px 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.5);
        display: flex; align-items: center; gap: 10px;
        font-size: 13px; color: #f8fafc; animation: slideUp 0.3s ease;
    `;
    toast.innerHTML = `
        <div style="width:36px;height:36px;background:rgba(245,158,11,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="bi bi-cloud-arrow-down" style="color:#f59e0b;font-size:18px"></i>
        </div>
        <div>
            <strong>${nombre} guardado offline</strong><br>
            <span style="color:#94a3b8;font-size:12px">Se sincronizará automáticamente cuando tengas señal</span>
        </div>
    `;
    document.body.appendChild(toast);
    
    // Agregar animación
    if (!document.getElementById('offline-toast-style')) {
        const style = document.createElement('style');
        style.id = 'offline-toast-style';
        style.textContent = `
            @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
            @keyframes slideDown { from { transform: translateY(0); opacity: 1; } to { transform: translateY(20px); opacity: 0; } }
        `;
        document.head.appendChild(style);
    }
    
    setTimeout(() => {
        toast.style.animation = 'slideDown 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Obtener todas las operaciones pendientes
async function obtenerPendientes() {
    try {
        const db = await openDB();
        const tx = db.transaction(STORE_NAME, 'readonly');
        const store = tx.objectStore(STORE_NAME);
        const index = store.index('sincronizado');
        
        const pendientes = await new Promise((resolve, reject) => {
            const request = index.getAll(false);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
        
        return pendientes;
    } catch (error) {
        console.error('❌ Error al obtener pendientes:', error);
        return [];
    }
}

// Sincronizar usando el endpoint batch (mucho más eficiente)
async function sincronizarTodo() {
    const pendientes = await obtenerPendientes();
    
    if (pendientes.length === 0) {
        console.log('📭 No hay operaciones pendientes');
        return { sincronizados: 0, fallidos: 0 };
    }
    
    console.log(`🔄 Sincronizando ${pendientes.length} operaciones en batch...`);
    
    try {
        // Preparar datos para el batch
        const operaciones = pendientes.map(p => ({
            id: p.id,
            tipo: p.tipo,
            datos: JSON.parse(p.datos)
        }));
        
        const response = await fetch('/movil/sync-batch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ operaciones: operaciones })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const result = await response.json();
        
        // Marcar como sincronizados los que funcionaron
        const db = await openDB();
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);
        
        for (const op of operaciones) {
            try {
                const registro = await new Promise((resolve, reject) => {
                    const req = store.get(op.id);
                    req.onsuccess = () => resolve(req.result);
                    req.onerror = () => reject(req.error);
                });
                if (registro) {
                    registro.sincronizado = true;
                    store.put(registro);
                }
            } catch(e) {
                console.warn('Error marcando registro:', e);
            }
        }
        
        console.log(`✅ Batch completado: ${result.sincronizados} éxitos, ${result.fallidos} fallidos`);
        actualizarContadorPendientes();
        
        return { sincronizados: result.sincronizados, fallidos: result.fallidos };
    } catch (error) {
        console.error('❌ Error en sincronización batch:', error);
        
        // Si falla el batch, intentar uno por uno (fallback)
        return await sincronizarIndividual(pendientes);
    }
}

// Fallback: sincronizar uno por uno
async function sincronizarIndividual(pendientes) {
    let sincronizados = 0;
    let fallidos = 0;
    
    for (const registro of pendientes) {
        const exito = await sincronizarOperacion(registro);
        if (exito) sincronizados++;
        else fallidos++;
    }
    
    actualizarContadorPendientes();
    return { sincronizados, fallidos };
}

// Sincronizar una operación individual (fallback)
async function sincronizarOperacion(registro) {
    const datos = JSON.parse(registro.datos);
    const csrf = getCsrfToken();
    
    try {
        let response;
        
        switch (registro.tipo) {
            case 'cobro':
                response = await fetch(`/prestamos/${datos.prestamo_id}/pagar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        monto: datos.monto,
                        metodo: datos.metodo,
                        fecha_pago: datos.fecha_pago,
                        referencia: datos.referencia || null
                    })
                });
                break;
                
            case 'gasto':
                response = await fetch('/movil/gasto', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        concepto: datos.concepto,
                        monto: datos.monto,
                        metodo: datos.metodo
                    })
                });
                break;
                
            case 'visita':
                response = await fetch('/movil/no-pago', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        prestamo_id: datos.prestamo_id,
                        motivo: datos.motivo,
                        observaciones: datos.observaciones || null
                    })
                });
                break;
        }
        
        if (!response || !response.ok) {
            throw new Error(`Error HTTP`);
        }
        
        // Marcar como sincronizado
        const db = await openDB();
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);
        registro.sincronizado = true;
        await new Promise((resolve, reject) => {
            const request = store.put(registro);
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
        
        return true;
    } catch (error) {
        registro.intentos = (registro.intentos || 0) + 1;
        registro.ultimo_intento = new Date().toISOString();
        const db = await openDB();
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);
        await store.put(registro);
        return false;
    }
}

// Actualizar el contador de pendientes en la UI
async function actualizarContadorPendientes() {
    const pendientes = await obtenerPendientes();
    const contador = document.getElementById('offline-count');
    const badge = document.getElementById('offline-badge');
    const syncBtn = document.getElementById('btn-sync-manual');
    
    if (contador) contador.textContent = pendientes.length;
    
    if (badge) {
        badge.style.display = pendientes.length > 0 ? 'flex' : 'none';
    }
    
    if (syncBtn) {
        syncBtn.style.display = pendientes.length > 0 ? 'flex' : 'none';
        const label = syncBtn.querySelector('.sync-label');
        if (label) label.textContent = `${pendientes.length} pendiente(s)`;
    }
}

// Sincronización manual (botón)
async function sincronizarManual() {
    const btn = document.getElementById('btn-sync-manual');
    if (!btn) return;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> <span>Sincronizando...</span>';
    
    const resultado = await sincronizarTodo();
    
    btn.disabled = false;
    if (resultado.sincronizados > 0) {
        btn.innerHTML = '<i class="bi bi-check-circle-fill" style="color:#10b981"></i> <span>¡Sincronizado!</span>';
        setTimeout(() => actualizarContadorPendientes(), 2000);
        setTimeout(() => window.location.reload(), 1500);
    } else {
        actualizarContadorPendientes();
    }
}

// Inicializar el sistema offline
function initOfflineSync() {
    console.log('🚀 Inicializando sistema offline v2...');
    
    // Sincronizar cuando vuelve la conexión
    window.addEventListener('online', async () => {
        console.log('📶 Conexión recuperada');
        const resultado = await sincronizarTodo();
        
        if (resultado.sincronizados > 0) {
            const banner = document.getElementById('banner-online');
            if (banner) {
                banner.innerHTML = `<i class="bi bi-check-circle-fill"></i> <strong>${resultado.sincronizados} operación(es) sincronizada(s) automáticamente</strong>`;
                banner.style.display = 'flex';
                setTimeout(() => { banner.style.display = 'none'; }, 6000);
            }
            setTimeout(() => window.location.reload(), 2000);
        }
    });
    
    // Actualizar contador al cargar
    actualizarContadorPendientes();
    
    console.log('✅ Sistema offline v2 listo');
}

// Inicializar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOfflineSync);
} else {
    initOfflineSync();
}