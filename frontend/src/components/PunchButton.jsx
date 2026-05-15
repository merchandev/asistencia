import React, { useState } from 'react';
import { useGeolocation } from '../hooks/useGeolocation';
import { useOnlineStatus } from '../hooks/useOnlineStatus';
import { registerPunch } from '../services/api';

export function PunchButton({ branchCode, employeeId, punchType }) {
  const { getCurrentPosition, loading: gpsLoading, error: gpsError } = useGeolocation();
  const isOnline = useOnlineStatus();
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState('');
  const [messageType, setMessageType] = useState('info'); // info, success, error

  const handlePunch = async () => {
    setSubmitting(true);
    setMessage('');
    try {
      const coords = await getCurrentPosition();
      
      const punchData = {
        branch_code: branchCode,
        punch_type: punchType,
        latitude: coords.latitude,
        longitude: coords.longitude,
        device_timestamp: new Date().toISOString() // ISO 8601
      };
      
      const result = await registerPunch(punchData);
      
      if (result.success) {
        if (!result.synced) {
          setMessageType('info');
          setMessage(`✅ Fichaje de ${punchType === 'in' ? 'Entrada' : 'Salida'} guardado localmente (Offline).`);
        } else {
          setMessageType('success');
          setMessage(`✅ Fichaje registrado correctamente a las ${new Date().toLocaleTimeString()}`);
        }
      } else {
        setMessageType('error');
        setMessage(`❌ Error: ${result.error}`);
      }
    } catch (err) {
      setMessageType('error');
      setMessage(`❌ Error de GPS: ${err.message}`);
    } finally {
      setSubmitting(false);
      setTimeout(() => setMessage(''), 7000);
    }
  };

  return (
    <div className="p-6 bg-white rounded-xl shadow-lg border border-gray-100 flex flex-col items-center max-w-sm w-full mx-auto">
      <h3 className="text-xl font-bold mb-4 text-gray-800">
        Sucursal: {branchCode}
      </h3>
      <button
        onClick={handlePunch}
        disabled={submitting || gpsLoading}
        className={`w-full py-4 rounded-lg text-white font-bold text-lg transition-transform transform active:scale-95 flex justify-center items-center gap-2
          ${punchType === 'in' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'}
          ${(submitting || gpsLoading) ? 'opacity-70 cursor-not-allowed' : ''}
        `}
      >
        {submitting || gpsLoading ? (
           <span className="animate-pulse">Procesando GPS...</span>
        ) : (
           <span>Marcar {punchType === 'in' ? 'Entrada' : 'Salida'}</span>
        )}
      </button>

      {message && (
        <div className={`mt-4 p-3 w-full rounded text-sm ${
          messageType === 'success' ? 'bg-green-100 text-green-800' :
          messageType === 'error' ? 'bg-red-100 text-red-800' :
          'bg-yellow-100 text-yellow-800'
        }`}>
          {message}
        </div>
      )}

      {!isOnline && (
        <div className="mt-4 flex items-center text-yellow-600 text-sm font-semibold">
          <span className="w-2 h-2 rounded-full bg-yellow-500 mr-2 animate-ping"></span>
          Modo Offline (Sin Internet)
        </div>
      )}
      {isOnline && (
        <div className="mt-4 flex items-center text-green-600 text-sm font-semibold">
          <span className="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
          Conectado
        </div>
      )}
    </div>
  );
}
