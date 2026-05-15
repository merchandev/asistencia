import { precacheAndRoute } from 'workbox-precaching';
import { registerRoute } from 'workbox-routing';
import { NetworkFirst, StaleWhileRevalidate } from 'workbox-strategies';

// Pre-caché de archivos generados por Vite (será inyectado en build time)
precacheAndRoute(self.__WB_MANIFEST || []);

// Estrategia para API: primero red, si falla intenta cache (útil para consultas GET, no para POSTs de attendance)
registerRoute(
  ({ url }) => url.pathname.startsWith('/api/'),
  new NetworkFirst({
    cacheName: 'api-cache',
    networkTimeoutSeconds: 5
  })
);

// Archivos estáticos
registerRoute(
  ({ request }) => request.destination === 'document' ||
                   request.destination === 'script' ||
                   request.destination === 'style' ||
                   request.destination === 'image',
  new StaleWhileRevalidate({
    cacheName: 'static-resources'
  })
);

// Escuchar mensaje para forzar actualización
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
