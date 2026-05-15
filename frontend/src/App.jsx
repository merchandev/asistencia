import React, { useState, useEffect } from 'react';
import { PunchButton } from './components/PunchButton';
import './index.css';

function App() {
  const [employeeId, setEmployeeId] = useState('');
  const [pin, setPin] = useState('');
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [loginError, setLoginError] = useState('');

  useEffect(() => {
    // Verificar si ya hay token
    const token = localStorage.getItem('token');
    const empId = localStorage.getItem('employeeId');
    if (token && empId) {
      setEmployeeId(empId);
      setIsLoggedIn(true);
    }
  }, []);

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoginError('');
    try {
      const response = await fetch('/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ employee_id: employeeId, pin })
      });
      if (response.ok) {
        const data = await response.json();
        localStorage.setItem('token', data.token);
        localStorage.setItem('employeeId', data.employee_id);
        setIsLoggedIn(true);
      } else {
        const err = await response.json();
        setLoginError(err.message);
      }
    } catch (error) {
      setLoginError('Error de red. Intenta nuevamente.');
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('employeeId');
    setIsLoggedIn(false);
    setEmployeeId('');
    setPin('');
  };

  if (!isLoggedIn) {
    return (
      <div className="min-h-screen flex items-center justify-center p-4">
        <form onSubmit={handleLogin} className="bg-white p-8 rounded-xl shadow-lg max-w-sm w-full">
          <h2 className="text-2xl font-bold text-center mb-6 text-gray-800">Control de Asistencia</h2>
          
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-1">ID Empleado</label>
            <input 
              type="text" 
              value={employeeId} 
              onChange={e => setEmployeeId(e.target.value)}
              className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-3 border"
              placeholder="Ej: EMP-001"
              required 
            />
          </div>
          
          <div className="mb-6">
            <label className="block text-sm font-medium text-gray-700 mb-1">PIN</label>
            <input 
              type="password" 
              value={pin} 
              onChange={e => setPin(e.target.value)}
              className="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-3 border"
              placeholder="Ej: 1234"
              required 
            />
          </div>

          {loginError && <p className="text-red-500 text-sm mb-4 text-center">{loginError}</p>}
          
          <button type="submit" className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
            Iniciar Sesión
          </button>
        </form>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 pb-12">
      <header className="bg-blue-800 text-white p-4 shadow-md flex justify-between items-center mb-8">
        <div>
          <h1 className="text-xl font-bold">Asistencia PWA</h1>
          <p className="text-sm text-blue-200">Empleado: {employeeId}</p>
        </div>
        <button onClick={handleLogout} className="text-sm bg-blue-700 hover:bg-blue-600 px-3 py-1 rounded">
          Salir
        </button>
      </header>

      <main className="container mx-auto px-4 space-y-6 flex flex-col items-center">
        {/* Usamos SUC-01 para fines de demostración */}
        <PunchButton branchCode="SUC-01" employeeId={employeeId} punchType="in" />
        <PunchButton branchCode="SUC-01" employeeId={employeeId} punchType="out" />
      </main>
    </div>
  );
}

export default App;
