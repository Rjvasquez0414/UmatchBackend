# 🚀 UMATCH - Guía Rápida

## 🍎 macOS - Comandos

```bash
# 1. Navegar al proyecto
cd /Users/rjvasquez/Desktop/Universidad/UMatchBackend/UmatchBackend

# 2. Iniciar MySQL (si no está corriendo)
brew services start mysql

# 3. Iniciar servidor Laravel
php artisan serve

# Abrir navegador en: http://localhost:8000
```

## 🪟 Windows - Comandos

```cmd
# 1. Navegar al proyecto
cd C:\Users\TU_USUARIO\Desktop\Universidad\UMatchBackend\UmatchBackend

# 2. Abrir XAMPP Control Panel y iniciar:
#    - Apache
#    - MySQL

# 3. Iniciar servidor Laravel
php artisan serve

# Abrir navegador en: http://localhost:8000
```

## 🔑 Credenciales

**Admin:**
```
admin@unab.edu.co
password
```

**Estudiante:**
```
juan.perez@unab.edu.co
password
```

## 🔧 Comandos Útiles

```bash
# Limpiar todos los cachés
php artisan optimize:clear

# Resetear base de datos
php artisan migrate:fresh --seed

# Ver rutas
php artisan route:list

# Detener servidor
Ctrl + C
```

## ⚠️ Problemas Comunes

**Error 404 en deportes:**
```bash
php artisan route:clear
php artisan optimize:clear
```

**Cambios no se ven:**
```
Navegador: Ctrl + Shift + R (Windows)
Navegador: Cmd + Shift + R (Mac)
```

**Base de datos:**
```bash
# Verificar que MySQL esté corriendo
# macOS:
brew services list

# Windows:
# Revisar XAMPP Control Panel
```

## 📁 Archivos Importantes

- `.env` - Configuración de base de datos
- `routes/web.php` - Rutas de la aplicación
- `public/css/umatch.css` - Estilos

## 📚 Documentación Completa

- **Instalación detallada:** Ver [INSTALACION.md](./INSTALACION.md)
- **Info del proyecto:** Ver [README.md](./README.md)
