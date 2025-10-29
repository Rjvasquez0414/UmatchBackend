# 🏀 Guía de Instalación UMATCH - CSU UNAB

## 📋 Tabla de Contenidos
- [Requisitos Previos](#requisitos-previos)
- [Instalación en macOS](#instalación-en-macos)
- [Instalación en Windows](#instalación-en-windows)
- [Configuración del Proyecto](#configuración-del-proyecto)
- [Ejecutar el Proyecto](#ejecutar-el-proyecto)
- [Credenciales de Acceso](#credenciales-de-acceso)
- [Solución de Problemas](#solución-de-problemas)

---

## 📦 Requisitos Previos

Antes de comenzar, necesitas tener instalado:
- **PHP 8.1 o superior**
- **Composer** (Gestor de dependencias de PHP)
- **MySQL 8.0 o superior**
- **Git** (para clonar el proyecto)

---

## 🍎 Instalación en macOS

### 1. Instalar Homebrew (si no lo tienes)
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### 2. Instalar PHP 8.2
```bash
brew install php@8.2
brew link php@8.2 --force --overwrite

# Verificar instalación
php -v
```

### 3. Instalar Composer
```bash
brew install composer

# Verificar instalación
composer --version
```

### 4. Instalar MySQL
```bash
brew install mysql

# Iniciar MySQL
brew services start mysql

# Configurar contraseña root (opcional pero recomendado)
mysql_secure_installation
```

### 5. Clonar o Abrir el Proyecto
```bash
# Si el proyecto ya está en tu máquina:
cd /Users/rjvasquez/Desktop/Universidad/UMatchBackend/UmatchBackend

# Si necesitas clonarlo de un repositorio:
# git clone [URL_DEL_REPOSITORIO]
# cd UmatchBackend
```

### 6. Instalar Dependencias de PHP
```bash
composer install
```

### 7. Configurar Base de Datos

**Opción A: Usando Terminal**
```bash
# Conectar a MySQL
mysql -u root -p

# Crear base de datos
CREATE DATABASE umatch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Crear usuario (opcional)
CREATE USER 'umatch_user'@'localhost' IDENTIFIED BY 'tu_contraseña';
GRANT ALL PRIVILEGES ON umatch.* TO 'umatch_user'@'localhost';
FLUSH PRIVILEGES;

# Salir
EXIT;
```

**Opción B: Usando Aplicación GUI**
- Descarga [Sequel Pro](https://www.sequelpro.com/) (gratis) o [TablePlus](https://tableplus.com/)
- Conecta a `localhost:3306` con usuario `root`
- Crea una nueva base de datos llamada `umatch`

### 8. Configurar Variables de Entorno
```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar el archivo .env
nano .env
```

**Configuración mínima en .env:**
```env
APP_NAME=UMATCH
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=umatch
DB_USERNAME=root
DB_PASSWORD=tu_contraseña_mysql

# Azure Maps Weather API (opcional)
AZURE_MAPS_API_KEY=
WEATHER_LAT=7.116345247418024
WEATHER_LON=-73.10550121931915
```

### 9. Generar App Key
```bash
php artisan key:generate
```

### 10. Ejecutar Migraciones y Seeders
```bash
# Ejecutar migraciones (crear tablas)
php artisan migrate

# Llenar base de datos con datos de ejemplo
php artisan db:seed --class=SportsSeeder
php artisan db:seed --class=CourtsSeeder
php artisan db:seed --class=UsersSeeder
```

### 11. Iniciar Servidor de Desarrollo
```bash
php artisan serve
```

✅ **¡Listo!** Abre tu navegador en: http://localhost:8000

---

## 🪟 Instalación en Windows

### 1. Instalar XAMPP
1. Descarga [XAMPP](https://www.apachefriends.org/download.html) (incluye PHP y MySQL)
2. Instala XAMPP en `C:\xampp`
3. Inicia **Apache** y **MySQL** desde el panel de control de XAMPP

### 2. Instalar Composer
1. Descarga [Composer para Windows](https://getcomposer.org/Composer-Setup.exe)
2. Ejecuta el instalador
3. Asegúrate de seleccionar el PHP de XAMPP (`C:\xampp\php\php.exe`)
4. Verifica la instalación abriendo CMD:
```cmd
composer --version
```

### 3. Configurar PHP en Variables de Entorno
1. Presiona `Win + R` y escribe `sysdm.cpl`
2. Ve a la pestaña **Avanzado** > **Variables de entorno**
3. En **Variables del sistema**, busca `Path` y haz clic en **Editar**
4. Agrega: `C:\xampp\php`
5. Verifica en CMD:
```cmd
php -v
```

### 4. Abrir el Proyecto
```cmd
# Navega a la carpeta del proyecto
cd C:\Users\TU_USUARIO\Desktop\Universidad\UMatchBackend\UmatchBackend

# O usando el Explorador de Windows:
# Mantén presionada la tecla Shift
# Clic derecho en la carpeta del proyecto
# Selecciona "Abrir ventana de PowerShell aquí"
```

### 5. Instalar Dependencias de PHP
```cmd
composer install
```

### 6. Configurar Base de Datos

**Usando phpMyAdmin (más fácil):**
1. Abre http://localhost/phpmyadmin en tu navegador
2. Ve a la pestaña **Bases de datos**
3. Crea una nueva base de datos:
   - Nombre: `umatch`
   - Cotejamiento: `utf8mb4_unicode_ci`
4. Haz clic en **Crear**

**Usando Terminal MySQL:**
```cmd
# Navega a la carpeta de MySQL
cd C:\xampp\mysql\bin

# Conecta a MySQL
mysql.exe -u root -p

# Crear base de datos
CREATE DATABASE umatch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 7. Configurar Variables de Entorno
```cmd
# Copiar archivo de ejemplo
copy .env.example .env

# Editar con Notepad
notepad .env
```

**Configuración mínima en .env:**
```env
APP_NAME=UMATCH
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=umatch
DB_USERNAME=root
DB_PASSWORD=

# Azure Maps Weather API (opcional)
AZURE_MAPS_API_KEY=
WEATHER_LAT=7.116345247418024
WEATHER_LON=-73.10550121931915
```

**IMPORTANTE:** En XAMPP por defecto, la contraseña de MySQL root está vacía, deja `DB_PASSWORD=` sin valor.

### 8. Generar App Key
```cmd
php artisan key:generate
```

### 9. Ejecutar Migraciones y Seeders
```cmd
# Ejecutar migraciones (crear tablas)
php artisan migrate

# Llenar base de datos con datos de ejemplo
php artisan db:seed --class=SportsSeeder
php artisan db:seed --class=CourtsSeeder
php artisan db:seed --class=UsersSeeder
```

### 10. Iniciar Servidor de Desarrollo
```cmd
php artisan serve
```

✅ **¡Listo!** Abre tu navegador en: http://localhost:8000

---

## ⚙️ Configuración del Proyecto

### Estructura de Base de Datos

El proyecto crea automáticamente estas tablas:
- `users` - Usuarios del sistema
- `sports` - Deportes disponibles (7 deportes)
- `courts` - Canchas y mesas (13 instalaciones)
- `events` - Eventos deportivos
- `tournaments` - Torneos
- `court_reservations` - Reservas de canchas
- Tablas pivot para relaciones Many-to-Many

### Datos de Ejemplo Incluidos

**Deportes:**
- ⚽ Fútbol
- 🏀 Basketball
- 🎾 Tenis
- 🎾 Pádel
- 🏐 Volleyball
- 🎱 Billar
- 🏓 Ping Pong

**Canchas:**
- 3 Canchas Multiuso (Fútbol/Basketball)
- 1 Cancha de Volleyball
- 1 Cancha de Tenis
- 1 Cancha de Pádel
- 3 Mesas de Billar
- 3 Mesas de Ping Pong
- 1 Coliseo CSU (solo admin)

---

## 🚀 Ejecutar el Proyecto

### Cada vez que quieras trabajar en el proyecto:

**macOS:**
```bash
# 1. Navegar al proyecto
cd /Users/rjvasquez/Desktop/Universidad/UMatchBackend/UmatchBackend

# 2. Asegurarte de que MySQL esté corriendo
brew services start mysql

# 3. Iniciar servidor Laravel
php artisan serve
```

**Windows:**
```cmd
# 1. Abrir XAMPP Control Panel
# 2. Iniciar Apache y MySQL
# 3. Navegar al proyecto
cd C:\Users\TU_USUARIO\Desktop\Universidad\UMatchBackend\UmatchBackend

# 4. Iniciar servidor Laravel
php artisan serve
```

### Abrir en el Navegador
```
http://localhost:8000
```

---

## 🔑 Credenciales de Acceso

### Usuario Administrador
```
Email: admin@unab.edu.co
Contraseña: password
```

### Usuarios Estudiantes
```
Email: juan.perez@unab.edu.co
Contraseña: password

Email: maria.gonzalez@unab.edu.co
Contraseña: password
```

---

## 🔧 Solución de Problemas

### Error: "Could not find driver"
**Causa:** Extensión PDO MySQL no está habilitada

**Solución macOS:**
```bash
# Editar php.ini
php --ini
# Busca el archivo y descomenta estas líneas (quita el ;):
# extension=pdo_mysql
# extension=mysqli
```

**Solución Windows:**
```
1. Abrir: C:\xampp\php\php.ini
2. Buscar y descomentar (quitar ;):
   extension=pdo_mysql
   extension=mysqli
3. Reiniciar XAMPP
```

### Error: "Access denied for user 'root'@'localhost'"
**Solución:** Verifica tu contraseña de MySQL en el archivo `.env`

**macOS:**
```bash
# Si olvidaste la contraseña:
mysql.server stop
mysqld_safe --skip-grant-tables &
mysql -u root
# Cambiar contraseña
ALTER USER 'root'@'localhost' IDENTIFIED BY 'nueva_contraseña';
FLUSH PRIVILEGES;
```

**Windows:**
```
1. Detén MySQL en XAMPP
2. Haz clic en "Config" > "my.ini"
3. Agrega bajo [mysqld]:
   skip-grant-tables
4. Reinicia MySQL
5. Abre phpMyAdmin y cambia la contraseña
6. Elimina la línea skip-grant-tables
7. Reinicia MySQL
```

### Error: "419 Page Expired" al enviar formularios
**Solución:** Limpia el caché
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### El servidor no inicia en el puerto 8000
**Solución:** Usa otro puerto
```bash
php artisan serve --port=8080
```

### Errores de permisos en macOS/Linux
```bash
# Dar permisos a storage y bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Las migraciones fallan
```bash
# Resetear base de datos completamente
php artisan migrate:fresh

# Volver a llenar con datos
php artisan db:seed --class=SportsSeeder
php artisan db:seed --class=CourtsSeeder
php artisan db:seed --class=UsersSeeder
```

### Los estilos no se actualizan
**Solución:** Limpiar caché del navegador
- Chrome/Edge: `Ctrl + Shift + R` (Windows) o `Cmd + Shift + R` (Mac)
- Firefox: `Ctrl + F5` (Windows) o `Cmd + Shift + R` (Mac)

---

## 📝 Comandos Útiles

```bash
# Ver todas las rutas disponibles
php artisan route:list

# Limpiar todo el caché
php artisan optimize:clear

# Ver estado de las migraciones
php artisan migrate:status

# Ejecutar comandos de base de datos
php artisan tinker
>>> \App\Models\User::count()
>>> \App\Models\Sport::all()

# Detener el servidor
Ctrl + C
```

---

## 🆘 Soporte

Si tienes problemas:
1. Verifica que todos los servicios estén corriendo (MySQL, PHP)
2. Revisa el archivo `.env` (especialmente configuración de BD)
3. Limpia todos los cachés: `php artisan optimize:clear`
4. Verifica los logs en `storage/logs/laravel.log`

---

## 🎯 Próximos Pasos

Una vez que todo funcione:
1. **Explora el Dashboard:** http://localhost:8000
2. **Crea tu primer evento deportivo**
3. **Prueba la funcionalidad de torneos**
4. **Edita tu perfil de usuario**
5. **Configura Azure Maps API** (opcional, para el clima)

---

## ⚡ Tips de Desarrollo

### Recargar cambios automáticamente
```bash
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Watch de cambios (si usas Vite/Mix)
npm run dev
```

### Limpiar base de datos y empezar de nuevo
```bash
php artisan migrate:fresh --seed
```

### Ver emails en desarrollo (sin enviarlos)
```env
# En .env
MAIL_MAILER=log
```
Los emails se guardarán en `storage/logs/laravel.log`

---

## 📚 Documentación Adicional

- [Laravel 10 Docs](https://laravel.com/docs/10.x)
- [PHP Manual](https://www.php.net/manual/es/)
- [MySQL Documentation](https://dev.mysql.com/doc/)

---

**¡Listo para crear el mejor sistema de gestión deportiva universitaria! 🏆**
