import { saveOfflinePunch, getPendingPunches, removeSyncedPunch } from '../db';

const API_BASE = '/api';

export async function syncPendingPunches() {
  const pending = await getPendingPunches();
  if (pending.length === 0) return;

  const token = localStorage.getItem('token');
  if (!token) return;

  try {
    const response = await fetch(`${API_BASE}/sync`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ punches: pending })
    });

    if (response.ok) {
      const result = await response.json();
      // Eliminar los que fueron procesados correctamente o rechazados definitivamente (ej. fraude)
      // En un caso real, leeríamos "result.results" para eliminar uno por uno.
      for (const res of result.results) {
        if (res.status_code === 200 || res.status_code === 403 || res.status_code === 409) {
          await removeSyncedPunch(res.punch.id);
        }
      }
    }
  } catch (error) {
    console.error('Error durante sincronización masiva', error);
  }
}

export async function registerPunch(punchData) {
  const isOnline = navigator.onLine;
  const token = localStorage.getItem('token') || 'token_de_prueba_para_este_demo'; // hardcode fallback for demo
  
  if (isOnline && token) {
    try {
      const response = await fetch(`${API_BASE}/attendance`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(punchData)
      });

      if (response.ok) {
        return { success: true, synced: true };
      } else {
        const errorData = await response.json();
        return { success: false, error: errorData.message || 'Error del servidor' };
      }
    } catch (error) {
      // Error de red a pesar de onLine = true -> guardamos offline
      await saveOfflinePunch(punchData);
      return { success: true, synced: false, offline: true };
    }
  } else {
    // Sin conexión -> offline
    await saveOfflinePunch(punchData);
    return { success: true, synced: false, offline: true };
  }
}

window.addEventListener('online', () => {
  // Cuando vuelve la conexión, intentar sincronizar con un pequeño delay
  setTimeout(syncPendingPunches, 2000);
});
