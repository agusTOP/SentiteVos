# 🚀 Guía para Configurar Login y Registro con PHP + MySQL

## 📋 Paso 1: Crear la Base de Datos

1. **Abre XAMPP** y asegúrate de que **Apache** y **MySQL** estén corriendo (botones en verde).

2. **Abre phpMyAdmin:**
   - Ve a: `http://localhost/phpmyadmin/`

3. **Importa la base de datos:**
   - Haz clic en "Importar" (arriba)
   - Selecciona el archivo `database.sql` de tu proyecto
   - Haz clic en "Continuar"
   - ¡Listo! Ya tienes la base de datos `sentitevos` con las tablas creadas.

---

## 📋 Paso 2: Configurar tu Proyecto en XAMPP

1. **Copia tu proyecto completo** a la carpeta `htdocs` de XAMPP:
   - Ruta típica: `C:\xampp\htdocs\sentitevos\`
   - (O el nombre que prefieras para tu carpeta)

2. **Abre tu web en el navegador:**
   - Ve a: `http://localhost/sentitevos/`
   - (Reemplaza "sentitevos" por el nombre de tu carpeta)

---

## 📋 Paso 3: Probar el Sistema

### **Usuario de Prueba:**
- **Email:** `test@test.com`
- **Contraseña:** `123456`

### **Probar Registro:**
1. Ve a: `http://localhost/sentitevos/register.php`
2. Completa el formulario con un email nuevo
3. Si todo está bien, te redirige a login.php

### **Probar Login:**
1. Ve a: `http://localhost/sentitevos/login.php`
2. Usa el usuario de prueba o uno que hayas creado
3. Si es correcto, te redirige a index.html


## ✉️ Configurar Envío de Correos (.env)

Para que el sistema envíe mails (verificación, recuperación de contraseña, confirmación de reservas):

- Crea un archivo `.env` en la raíz del proyecto (c:\xampp\htdocs\sentitevos\.env) con:

```
APP_URL=http://localhost/sentitevos

MAIL_HOST=smtp.tu-proveedor.com
MAIL_PORT=465
MAIL_USERNAME=tu-email@dominio.com
MAIL_PASSWORD=tu-password
MAIL_FROM=tu-email@dominio.com
MAIL_FROM_NAME=Sentite Vos

# Email para notificar nuevas solicitudes de turno (dueña)
OWNER_EMAIL=lorena@sentitevos.site 
```

- Los correos se envían usando PHPMailer en modo SMTP seguro (465).
- Si usas Gmail, habilita “App Passwords” o usa un proveedor SMTP confiable.
- `APP_URL` se usa para construir enlaces en los mails.

Mailer centralizado:
- El envío de correos está centralizado en [config/mailer.php](config/mailer.php) para evitar repetir código.
- Usa `send_mail_simple(...)` para enviar al usuario y `notify_owner(...)` para avisar a la dueña (incluye correo secundario si está configurado).

---

## 📋 Paso 4: Actualizar Links en tu Navbar

**IMPORTANTE:** Cambia los links en `nav.html`:

```html
<!-- Cambiar de: -->
<a href="login.html" class="btn btn-login">Iniciar sesión</a>
<a href="register.html" class="btn btn-register">Registrarse</a>

<!-- A: -->
<a href="login.php" class="btn btn-login">Iniciar sesión</a>
<a href="register.php" class="btn btn-register">Registrarse</a>
```

---

## 🔧 Estructura de Archivos Creados

```
ProyectoFinal/
├── config/
│   └── database.php          (Configuración de BD)
├── php/
│   ├── login.php            (Procesa login)
│   ├── register.php         (Procesa registro)
│   └── logout.php           (Cierra sesión)
├── login.php                (Página de login)
├── register.php             (Página de registro)
└── database.sql             (Script SQL para crear BD)
```

---

## 🎯 Próximos Pasos (Sistema de Reservas)

Cuando quieras agregar el sistema de reservas, necesitarás:

1. **Crear `php/reservar.php`** - Para procesar nuevas reservas
2. **Crear `mis-reservas.php`** - Para que usuarios vean sus reservas
3. **Actualizar el formulario de contacto** para que guarde reservas en la BD

---

## ⚠️ Notas Importantes

- **Siempre accede por `http://localhost/...`** (nunca con `file:///`)
- **Las contraseñas están hasheadas** con `password_hash()` (seguro)
- **Las sesiones** mantienen al usuario logueado
- **Para deployar:** Solo sube todo a un hosting con PHP + MySQL (Hostinger, DonWeb, etc.)

---

## 🐛 Solución de Problemas

**Error de conexión a BD:**
- Verifica que MySQL esté corriendo en XAMPP
- Revisa `config/database.php` (usuario y contraseña)

**No aparecen mensajes de error/éxito:**
- Asegúrate de que los archivos `.html` se hayan renombrado a `.php`
- Verifica que `session_start()` esté al inicio de cada archivo PHP

**El nav no carga:**
- Debes acceder por `http://localhost/...` (servidor local)
- Nunca funciona con `file:///`

---

¡Listo! Ya tienes login y registro funcionando. 🎉

