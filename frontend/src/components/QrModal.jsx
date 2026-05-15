import React, { useEffect, useState } from 'react';
import QRCode from 'qrcode';

export default function QrModal({ employeeId, onClose }) {
  const [dataUrl, setDataUrl] = useState('');
  useEffect(() => {
    if (!employeeId) return;
    const text = JSON.stringify({ employee_id: employeeId });
    QRCode.toDataURL(text, { width: 300 })
      .then(url => setDataUrl(url))
      .catch(() => setDataUrl(''));
  }, [employeeId]);

  if (!employeeId) return null;

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div className="bg-white p-6 rounded shadow max-w-sm w-full text-center">
        <h3 className="text-lg font-bold mb-4">QR de Empleado: {employeeId}</h3>
        {dataUrl ? (
          <img src={dataUrl} alt="QR" className="mx-auto mb-4" />
        ) : (
          <p className="text-sm text-gray-500 mb-4">Generando QR...</p>
        )}
        <div className="flex justify-center gap-2">
          <button onClick={onClose} className="px-4 py-2 bg-gray-200 rounded">Cerrar</button>
        </div>
      </div>
    </div>
  );
}
