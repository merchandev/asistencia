# Sistema de Asistencia PWA (Offline-First)

Este es un sistema de control de asistencia moderno, diseñado como una Progressive Web App (PWA) con una arquitectura **Offline-First**. Permite a los empleados registrar sus entradas y salidas (fichajes) incluso cuando no tienen conexión a internet. El sistema guarda los datos localmente y los sincroniza automáticamente con el servidor cuando la conexión se restablece.

## 🚀 Características Principales

*   **Offline-First:** Funciona sin conexión a internet. Los fichajes se guardan en el dispositivo usando IndexedDB (mediante Dexie.js) y se sincronizan en segundo plano (Service Workers/Workbox) cuando hay conexión.
*   **Geolocalización:** Captura y requiere las coordenadas GPS exactas al momento de registrar la asistencia.
*   **Autenticación Segura:** Sistema de login seguro basado en JSON Web Tokens (JWT).
*   **Arquitectura en Contenedores:** Todo el entorno (Frontend, Backend, Base de Datos, Caché, Worker) está completamente contenerizado usando Docker y Docker Compose para facilitar su despliegue y escalabilidad.
*   **Cola de Tareas (Worker):** Procesamiento en segundo plano utilizando Redis para manejar la sincronización masiva de datos sin bloquear el servidor web.

## 🛠️ Stack Tecnológico

**Frontend:**
*   React 19 + Vite
*   Tailwind CSS v4 (Estilos)
*   Dexie.js (Manejo de base de datos local IndexedDB)
*   Workbox (Service Workers y caché PWA)

**Backend:**
*   PHP 8.2 (Vanilla PHP)
*   MySQL 8.0 (Base de datos relacional)
*   Redis (Gestor de colas y caché)

**Infraestructura:**
*   Docker & Docker Compose
*   Nginx (Servidor Web y Reverse Proxy)

## 📂 Estructura del Proyecto

```text
/
├── backend/            # Código fuente de la API PHP, Worker y script SQL inicial
├── frontend/           # Aplicación React PWA
├── nginx/              # Configuración del servidor Nginx (Reverse Proxy)
├── docker-compose.yml  # Orquestación de contenedores
└── .env.example        # Ejemplo de variables de entorno
```

## 💻 Desarrollo Local

Para correr este proyecto en tu entorno local, asegúrate de tener instalado **Docker** y **Docker Compose**.

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/merchandev/asistencia.git
    cd asistencia
    ```

2.  **Configurar variables de entorno:**
    Crea un archivo `.env` en la raíz del proyecto basándote en un `.env.example` (o define las variables manualmente). Necesitarás las siguientes variables:
    ```env
    DB_ROOT_PASS=tu_password_root
    DB_NAME=asistencia_db
    DB_USER=asistencia_user
    DB_PASS=tu_password_seguro
    JWT_SECRET=tu_secreto_jwt_super_seguro
    ```

3.  **Levantar los contenedores:**
    ```bash
    docker-compose up -d --build
    ```

4.  **Acceder a la aplicación:**
    *   Frontend y API (vía Nginx): `http://localhost:8090`
    *   La API responde bajo la ruta: `http://localhost:8090/api/...`

## 🌍 Despliegue en Producción (Hostinger Docker Manager)

El proyecto está preparado para ser desplegado fácilmente en un VPS con **Docker Manager** (como el de Hostinger).

1.  Ingresa a tu panel de control de Hostinger VPS y navega a la sección **Docker Manager**.
2.  Selecciona la opción para crear una nueva aplicación desde un repositorio de GitHub (Create from Repository).
3.  Conecta y selecciona este repositorio: `merchandev/asistencia`.
4.  **Paso Crítico:** En la sección de **Environment Variables** (Variables de Entorno) de la interfaz de Hostinger, debes configurar obligatoriamente las siguientes claves y sus valores:
    *   `DB_ROOT_PASS` (Contraseña root para MySQL)
    *   `DB_NAME` (Nombre de la base de datos, ej: `asistencia_db`)
    *   `DB_USER` (Usuario de la base de datos)
    *   `DB_PASS` (Contraseña del usuario de la BD)
    *   `JWT_SECRET` (Una cadena de texto larga y secreta para firmar los tokens de login)
5.  Inicia el proceso de construcción y despliegue. Docker Manager leerá automáticamente el archivo `docker-compose.yml`, construirá las imágenes del frontend (React) y backend (PHP), y levantará todos los servicios (Nginx, MySQL, Redis, Worker, Backend, Frontend).

## 🧑‍💻 Usuarios de Prueba Iniciales

El archivo `init.sql` creará la base de datos y unas tablas iniciales si la base de datos está vacía. *(Nota: Asegúrate de agregar usuarios de prueba directamente en la base de datos o implementar un endpoint de registro si es necesario para el flujo completo).*
