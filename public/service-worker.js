const CACHE_NAME = 'tecnicell-v5';
const CACHE_ASSETS = 'tecnicell-assets-v5';
const CACHE_PAGES = 'tecnicell-pages-v5';
const CACHE_API = 'tecnicell-api-v5';

// Recursos estáticos a precachear
const PRECACHE_URLS = [
  '/movil',
  '/movil/historial',
  '/offline.html',
  '/img/icons/icon.svg',
  '/js/offline-sync.js',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap'
];

// Instalación
self.addEventListener('install', e => {
  self.skipWaiting();
  e.waitUntil(
    caches.open(CACHE_ASSETS).then(cache => {
      return cache.addAll(PRECACHE_URLS).catch(err => {
        console.warn('Precaché parcial:', err);
      });
    })
  );
});

// Activación: limpiar caches antiguos
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== CACHE_NAME && key !== CACHE_ASSETS && key !== CACHE_PAGES && key !== CACHE_API)
          .map(key => caches.delete(key))
      );
    }).then(() => self.clients.claim())
  );
});

// Estrategia de fetch mejorada
self.addEventListener('fetch', e => {
  const { request } = e;
  const url = new URL(request.url);

  // Solo interceptar GET
  if (request.method !== 'GET') return;

  // Rutas de la app móvil - Stale-While-Revalidate (carga rápido, actualiza en background)
  if (url.pathname.startsWith('/movil')) {
    e.respondWith(staleWhileRevalidate(request, 3000));
    return;
  }

  // API de datos (clientes, préstamos, cuotas) - Network First
  if (url.pathname.startsWith('/api/') || url.pathname.includes('/buscar')) {
    e.respondWith(networkFirstWithCache(request));
    return;
  }

  // Recursos CDN - Cache First
  if (['cdn.jsdelivr.net', 'fonts.googleapis.com', 'fonts.gstatic.com'].includes(url.hostname)) {
    e.respondWith(cacheFirst(request));
    return;
  }

  // Assets locales - Cache First
  if (url.pathname.startsWith('/img/') || url.pathname.startsWith('/build/assets/') || 
      url.pathname.startsWith('/storage/') || url.pathname === '/manifest.json' ||
      url.pathname.startsWith('/js/') || url.pathname.startsWith('/css/')) {
    e.respondWith(cacheFirst(request));
    return;
  }

  // Para todo lo demás (páginas web), Network First
  e.respondWith(networkFirstWithCache(request));
});

// Stale-While-Revalidate: muestra la cacheada inmediatamente, actualiza en background
// Si no hay cache y no hay red, muestra offline.html
async function staleWhileRevalidate(request, timeoutMs = 3000) {
  // Primero devolver lo que haya en caché (rápido)
  const cachedResponse = await caches.match(request);
  
  // Intentar obtener nueva versión de la red
  try {
    const timeoutPromise = new Promise((_, reject) => 
      setTimeout(() => reject(new Error('Timeout')), timeoutMs)
    );
    
    const fetchPromise = fetch(request).then(async (response) => {
      if (response && response.ok) {
        const cache = await caches.open(CACHE_PAGES);
        cache.put(request, response.clone());
      }
      return response;
    });
    
    // Podemos obtener la respuesta de red
    const networkResponse = await Promise.race([fetchPromise, timeoutPromise]);
    return networkResponse;
  } catch (error) {
    // Si hay caché, devolverla (aunque esté desactualizada, es mejor que nada)
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Si es una ruta móvil, redirigir a offline.html
    if (request.destination === 'document' || request.mode === 'navigate') {
      const offlinePage = await caches.match('/offline.html');
      if (offlinePage) return offlinePage;
    }
    
    return new Response('Sin conexión', { status: 408 });
  }
}

// Network First con timeout
async function networkFirstWithTimeout(request, timeoutMs = 3000) {
  const timeoutPromise = new Promise((_, reject) => 
    setTimeout(() => reject(new Error('Timeout')), timeoutMs)
  );

  try {
    const response = await Promise.race([
      fetch(request),
      timeoutPromise
    ]);
    const cache = await caches.open(CACHE_PAGES);
    cache.put(request, response.clone());
    return response;
  } catch (error) {
    const cached = await caches.match(request);
    if (cached) return cached;
    
    if (request.destination === 'document') {
      const offlinePage = await caches.match('/offline.html');
      if (offlinePage) return offlinePage;
    }
    
    return new Response('Sin conexión', { status: 408 });
  }
}

// Network First clásico
async function networkFirstWithCache(request) {
  try {
    const response = await fetch(request);
    const cache = await caches.open(CACHE_PAGES);
    cache.put(request, response.clone());
    return response;
  } catch (error) {
    const cached = await caches.match(request);
    if (cached) return cached;
    return new Response('Sin conexión', { status: 408 });
  }
}

// Cache First
async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;
  try {
    const response = await fetch(request);
    const cache = await caches.open(CACHE_ASSETS);
    cache.put(request, response.clone());
    return response;
  } catch (error) {
    return new Response('Recurso no disponible offline', { status: 408 });
  }
}