# Sistema de Registro e Inicio de Sesión de Clientes

## Descripción

Implementar un sistema completo de autenticación para clientes que quieran reservar tours.
Soporta 3 métodos: **correo + contraseña**, **Google OAuth**, **Facebook OAuth**.
El registro por correo requiere verificación de email (token de 24 h antes de bloquear cuenta).
Los parámetros SMTP se configuran en `.env`.

---

## Propuesta de arquitectura

- **Sin dependencias externas de Composer**: PHP puro con `PDO`, `mail()` o SMTP nativo, tokens con `random_bytes()`.
- **OAuth social**: flujo estándar OAuth 2.0 con redirect — se usa Google Identity / Facebook Login con sus SDKs JS para el popup y el servidor recibe el `id_token` o `access_token` que valida contra la API del proveedor.
- **Sesiones PHP nativas** para mantener al cliente autenticado (con cookie `HttpOnly`).
- **JWT** para el token de sesión almacenado en cookie (reutiliza `JWT_SECRET` del `.env`).

---

## Cambios propuestos

### 1. Base de datos — [NEW] `database/auth_migration.sql`

Nueva tabla `clientes`:
```sql
id, nombre, apellido, email (unique), password_hash (nullable para OAuth),
auth_provider ENUM('email','google','facebook'),
provider_id (nullable, ID externo del proveedor OAuth),
email_verified TINYINT(1),
verification_token VARCHAR(64),
verification_expires_at DATETIME,
avatar_url, active TINYINT(1), created_at, updated_at
```

---

### 2. Variables de entorno — `.env` y `.env.example`

Agregar sección `MAIL_*` y `OAUTH_*`:
```
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=Weekender

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost/tours/auth/google/callback

FACEBOOK_APP_ID=
FACEBOOK_APP_SECRET=
FACEBOOK_REDIRECT_URI=http://localhost/tours/auth/facebook/callback
```

---

### 3. Núcleo de autenticación — [NEW] `auth/`

| Archivo | Propósito |
|---|---|
| `auth/Mailer.php` | Envío de correos via SMTP (sin Composer) |
| `auth/Auth.php` | Lógica de sesión, JWT, helpers de cliente activo |
| `auth/ClienteRepository.php` | CRUD de clientes contra BD |

---

### 4. Endpoints de autenticación — [NEW] `auth/actions/`

| Archivo | Ruta | Propósito |
|---|---|---|
| `register.php` | `POST /auth/register` | Registrar con email |
| `login.php` | `POST /auth/login` | Login con email |
| `verify.php` | `GET /auth/verify?token=` | Verificar email |
| `logout.php` | `GET /auth/logout` | Cerrar sesión |
| `google-callback.php` | `GET /auth/google/callback` | OAuth Google |
| `facebook-callback.php` | `GET /auth/facebook/callback` | OAuth Facebook |
| `me.php` | `GET /auth/me` | Datos del cliente autenticado (JSON) |

---

### 5. Página de autenticación — [NEW] `pages/auth/index.php`

Modal / página con:
- Tabs: **Iniciar sesión** / **Registrarse**
- Botones sociales: Google, Facebook
- Formulario email + contraseña
- Vista de "verificar tu correo"
- Mensajes de error/éxito

---

### 6. Integración en el nav

Reemplazar el botón estático "Iniciar sesión" en los 4 `index.php` por un componente dinámico:
- Si hay sesión activa → muestra avatar/nombre + dropdown (Mi cuenta / Cerrar sesión)
- Si no hay sesión → botón "Iniciar sesión" que abre el modal/redirect

---

### 7. `.htaccess` — agregar rutas auth

```apache
RewriteRule ^auth/register/?$ auth/actions/register.php [L,QSA]
RewriteRule ^auth/login/?$ auth/actions/login.php [L,QSA]
RewriteRule ^auth/logout/?$ auth/actions/logout.php [L,QSA]
RewriteRule ^auth/verify/?$ auth/actions/verify.php [L,QSA]
RewriteRule ^auth/google/callback/?$ auth/actions/google-callback.php [L,QSA]
RewriteRule ^auth/facebook/callback/?$ auth/actions/facebook-callback.php [L,QSA]
RewriteRule ^auth/me/?$ auth/actions/me.php [L,QSA]
```

---

## Open Questions

> [!IMPORTANT]
> **OAuth social**: Para Google y Facebook OAuth es necesario tener **credenciales de app** registradas en Google Cloud Console y Facebook Developers. ¿Ya tienes esas credenciales o las dejamos como placeholders en `.env` para que las configures después?

> [!IMPORTANT]
> **Servidor SMTP**: ¿Qué proveedor usarás para enviar correos? (Gmail SMTP, Mailtrap para dev, SendGrid, etc.) Esto afecta los valores de `MAIL_*`.

> [!NOTE]
> La tabla `clientes` es independiente de la tabla `users` existente (que parece ser para operadores/admin). Si prefieres unificar en una sola tabla con un campo `role`, dímelo.

> [!NOTE]
> El modal de autenticación se mostrará en overlay sobre cualquier página. Si prefieres una **página separada** (`/auth/login`) en lugar de modal, indícalo.

---

## Plan de verificación

1. Correr la migración SQL y verificar que la tabla `clientes` se crea correctamente.
2. Registrar un cliente con email → verificar que llega el correo.
3. Intentar iniciar sesión sin verificar → debe rechazarlo.
4. Verificar el token → el cliente queda activo.
5. Intentar verificar un token expirado (+24h) → error.
6. Login exitoso → la sesión persiste, el nav muestra el nombre del cliente.
7. Logout → sesión destruida, vuelve el botón "Iniciar sesión".
8. Flujo Google OAuth → redirect → callback → sesión activa.
9. Flujo Facebook OAuth → idem.
