# 🏀 UMATCH - Sistema de Gestión Deportiva CSU UNAB

Sistema web para la gestión de eventos deportivos y torneos del Centro de Servicios Universitarios de la Universidad Autónoma de Bucaramanga.

## ⚡ Inicio Rápido

### Requisitos
- PHP 8.1+
- MySQL 8.0+
- Composer

### Instalación

```bash
# 1. Instalar dependencias
composer install

# 2. Configurar entorno
cp .env.example .env
php artisan key:generate

# 3. Configurar base de datos en .env
DB_DATABASE=umatch
DB_USERNAME=root
DB_PASSWORD=tu_contraseña

# 4. Ejecutar migraciones y seeders
php artisan migrate
php artisan db:seed --class=SportsSeeder
php artisan db:seed --class=CourtsSeeder
php artisan db:seed --class=UsersSeeder

# 5. Iniciar servidor
php artisan serve
```

Abre: http://localhost:8000

## 🔑 Credenciales de Prueba

**Admin:**
- Email: `admin@unab.edu.co`
- Password: `password`

**Estudiantes:**
- Email: `juan.perez@unab.edu.co` / `maria.gonzalez@unab.edu.co`
- Password: `password`

## 📖 Documentación Completa

Para guía detallada de instalación en Mac y Windows, ver: **[INSTALACION.md](./INSTALACION.md)**

## 🚀 Características

- ✅ Gestión de eventos deportivos
- ✅ Sistema de torneos (oficiales y amistosos)
- ✅ Reserva de canchas automática
- ✅ 7 deportes disponibles (Fútbol, Basketball, Tenis, Pádel, Volleyball, Billar, Ping Pong)
- ✅ 13 instalaciones deportivas
- ✅ Perfiles de usuario con estadísticas
- ✅ Sistema de autenticación completo
- ✅ Recuperación de contraseña
- ✅ Integración con Azure Maps Weather API
- ✅ Diseño responsive con paleta UNAB

## 🛠️ Stack Tecnológico

- **Backend:** Laravel 10
- **Base de Datos:** MySQL 8
- **Frontend:** Blade Templates + Alpine.js
- **Estilos:** CSS3 (Custom Design System)
- **Iconos:** Feather Icons
- **API Externa:** Azure Maps Weather

## 📁 Estructura del Proyecto

```
UmatchBackend/
├── app/
│   ├── Http/Controllers/     # Controladores
│   ├── Models/               # Modelos Eloquent
│   └── Services/             # Servicios (Weather, etc.)
├── database/
│   ├── migrations/           # 13 migraciones
│   └── seeders/              # Datos iniciales
├── resources/views/          # Vistas Blade
│   ├── dashboard.blade.php
│   ├── events/
│   ├── tournaments/
│   ├── profile/
│   └── auth/
├── public/css/
│   └── umatch.css           # 3,500+ líneas de CSS
├── routes/web.php           # Rutas principales
└── .env                     # Configuración
```

## 🎯 Funcionalidades Principales

### Eventos Deportivos
- Crear eventos por deporte
- Unirse/Abandonar eventos
- Ver participantes
- Reserva automática de canchas
- Filtros por deporte, fecha, nivel

### Torneos
- Torneos oficiales (solo admin)
- Torneos amistosos (cualquier usuario)
- Inscripción de participantes
- Estados: Abierto, En Progreso, Finalizado
- Formatos: Eliminación Simple, Doble Eliminación, Round Robin

### Perfil de Usuario
- Estadísticas personales
- Eventos próximos
- Torneos activos
- Edición de perfil
- Cambio de contraseña

### Dashboard
- Estadísticas generales
- Widget de clima con Azure Maps
- Listado de deportes disponibles
- Próximos torneos

## 🔧 Comandos Útiles

```bash
# Limpiar cachés
php artisan optimize:clear

# Ver rutas
php artisan route:list

# Resetear base de datos
php artisan migrate:fresh --seed

# Ejecutar tinker
php artisan tinker
```

## 📊 Base de Datos

### Modelos Principales
- `User` - Usuarios (estudiantes y administradores)
- `Sport` - Deportes disponibles
- `Court` - Canchas y mesas
- `Event` - Eventos deportivos
- `Tournament` - Torneos
- `CourtReservation` - Reservas de instalaciones

### Relaciones
- Many-to-Many: Sport ↔ Court, Event ↔ User, Tournament ↔ User
- One-to-Many: Sport → Events, Sport → Tournaments
- Belongs-To: Event → Sport, Event → Organizer

## 🌦️ Configuración Azure Maps (Opcional)

```env
AZURE_MAPS_API_KEY=tu_api_key_aqui
WEATHER_LAT=7.116345247418024
WEATHER_LON=-73.10550121931915
```

El widget del clima se actualiza automáticamente cada 15 minutos con caché.

## 🐛 Solución de Problemas

**Error 404 en deportes:**
```bash
php artisan route:clear
```

**Estilos no se actualizan:**
- Limpiar caché del navegador: `Ctrl + Shift + R`

**Error de base de datos:**
```bash
php artisan migrate:fresh --seed
```

## 📝 Licencia

Proyecto académico - UNAB 2025

## 👥 Autor

Desarrollado para el Centro de Servicios Universitarios de la UNAB

---

**Para instrucciones detalladas de instalación, consulta [INSTALACION.md](./INSTALACION.md)**
