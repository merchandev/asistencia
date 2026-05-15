import React, { useEffect, useState } from 'react';
import { getPendingPunches } from '../db';
import { syncPendingPunches } from '../services/api';

export default function Dashboard() {
  const [pending, setPending] = useState([]);
  const [loading, setLoading] = useState(false);

  const load = async () => {
    setLoading(true);
    const p = await getPendingPunches();
    setPending(p);
    setLoading(false);
  };

  useEffect(() => { load(); }, []);

  const handleSync = async () => {
    setLoading(true);
    await syncPendingPunches();
    await load();
    setLoading(false);
  };

  return (
    <div className="max-w-2xl w-full bg-white rounded-lg shadow p-6">
      <h2 className="text-xl font-bold mb-4">Dashboard - Pendientes</h2>
      <div className="mb-4">
        <button onClick={handleSync} disabled={loading} className="bg-blue-600 text-white px-4 py-2 rounded">
          {loading ? 'Sincronizando...' : 'Sincronizar pendientes'}
        </button>
      </div>

      {pending.length === 0 ? (
        <p className="text-sm text-gray-600">No hay fichajes pendientes.</p>
      ) : (
        <ul className="space-y-3">
          {pending.map(p => (
            <li key={p.id} className="p-3 border rounded flex justify-between items-center">
              <div>
                <div className="font-semibold">{p.branch_code} • {p.punch_type}</div>
                <div className="text-sm text-gray-500">{new Date(p.device_timestamp).toLocaleString()}</div>
              </div>
              <div className="text-sm text-gray-700">ID #{p.id}</div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
