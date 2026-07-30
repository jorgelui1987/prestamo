<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <title>Sin conexión - Tecnicell</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .offline-container { max-width: 400px; width: 100%; text-align: center; }
        .offline-icon { font-size: 72px; margin-bottom: 20px; }
        .offline-title { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
        .offline-subtitle { font-size: 14px; color: #94a3b8; margin-bottom: 24px; line-height: 1.6; }
        .status-card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 20px; margin-bottom: 20px; }
        .status-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #334155; font-size: 14px; }
        .status-item:last-child { border-bottom: none; }
        .status-label { color: #94a3b8; }
        .status-value { font-weight: 600; }
        .status-value.online { color: #10b981; }
        .status-value.offline { color: #ef4444; }
        .btn-group { display: flex; flex-direction: column; gap: 10px; }
        .btn { padding: 14px; border-radius: 12px; font-size: 14px; font-weight: 700; border: none; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:active { background: #2563eb; }
        .btn-success { background: #10b981; color: white; }
        .btn-success:active { background: #059669; }
        .btn-secondary { background: #334155; color: #f8fafc; }
        .btn-secondary:active { background: #475569; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:active { background: #dc2626; }
        .btn:disabled { opacity: 0.5; pointer-events: none; }
        .sync-result { margin-top: 16px; padding: 12px; border-radius: 10px; font-size: 13px; display: none; }
        .sync-result.success { display: block; background: rgba(16,185,129,0.15); border: 1px solid #10b981; color: #34d399; }
        .sync-result.error { display: block; background: rgba(239,68,68,0.15); border: 1px solid #ef4444; color: #fca5a5; }
        .pending-badge { display: inline-flex; align-items: center; justify-content: center; background: #f59e0b; color: white; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 10px; min-width: 20px; }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">📡</div>
        <h1 class="offline-title">Sin conexión a Internet</h1>
        <p class="offline-subtitle">No tienes conexión en este momento. Puedes seguir trabajando y los datos se sincronizarán automáticamente cuando recuperes señal.</p>

        <div class="status-card">
            <div class="status-item">
                <span class="status-label"><i class="bi bi-wifi"></i> Estado de conexión</span>
                <span class="status-value offline" id="connection-status">
                    <i class="bi bi-wifi-off"></i> Desconectado
                </span>
            </div>
            <div class="status-item">
                <span class="status-label"><i class="bi bi-inbox"></i> Pendientes de sincronizar</span>
                <span class="status-value" id="pending-count">0</span>
            </div>
        </div>

        <div class="btn-group">
            <button class="btn btn-primary" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Reintentar conexión
            </button>
            <button class="btn btn-success" id="btn-sync" onclick="sincronizarAhora()">
                <i class="bi bi-cloud-arrow-up"></i> Sincronizar ahora
            </button>
            <a href="/movil" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Cobranzas
            </a>
        </div>

        <div id="sync-result" class="sync-result"></div>
    </div>

    <script>
        async function obtenerPendientes() {
            try {
                const db = await openDB();
                const tx = db.transaction('pending_sync', 'readonly');
                const store = tx.objectStore('pending_sync');
                const index = store.index('sincronizado');
                const pendientes = await new Promise((resolve, reject) => {
                    const request = index.getAll(false);
                    request.onsuccess = () => resolve(request.result);
                    request.onerror = () => reject(request.error);
                });
                return pendientes;
            } catch(e) {
                return [];
            }
        }

        async function actualizarEstado() {
            const pendientes = await obtenerPendientes();
            document.getElementById('pending-count').textContent = pendientes.length;
            
            if (navigator.onLine) {
                document.getElementById('connection-status').className = 'status-value online';
                document.getElementById('connection-status').innerHTML = '<i class="bi bi-wifi"></i> Conectado';
            } else {
                document.getElementById('connection-status').className = 'status-value offline';
                document.getElementById('connection-status').innerHTML = '<i class="bi bi-wifi-off"></i> Desconectado';
            }
        }

        async function sincronizarAhora() {
            const btn = document.getElementById('btn-sync');
            const result = document.getElementById('sync-result');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sincronizando...';
            result.className = 'sync-result';
            result.style.display = 'none';

            try {
                const response = await fetch('/movil/sync-batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    result.className = 'sync-result success';
                    result.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${data.mensaje}`;
                } else {
                    throw new Error(data.mensaje || 'Error al sincronizar');
                }
            } catch(error) {
                result.className = 'sync-result error';
                result.innerHTML = `<i class="bi bi-exclamation-circle"></i> ${error.message}`;
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Sincronizar ahora';
            await actualizarEstado();
        }

        // IndexedDB
        function openDB() {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open('tecnicell-offline', 1);
                request.onupgradeneeded = (event) => {
                    const db = event.target.result;
                    if (!db.objectStoreNames.contains('pending_sync')) {
                        const store = db.createObjectStore('pending_sync', { keyPath: 'id', autoIncrement: true });
                        store.createIndex('sincronizado', 'sincronizado', { unique: false });
                    }
                };
                request.onsuccess = (event) => resolve(event.target.result);
                request.onerror = (event) => reject(event.target.error);
            });
        }

        window.addEventListener('online', () => { actualizarEstado(); });
        window.addEventListener('offline', () => { actualizarEstado(); });
        actualizarEstado();
    </script>
</body>
</html>