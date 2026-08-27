# Viajero

Aplicacion web local para descubrir tours y experiencias grupales, consultar sus salidas y reservar asientos. El proyecto usa PHP sin framework, MySQL, JavaScript vanilla y Bootstrap 5. La carpeta se sirve desde XAMPP en `/tours`.

## Estado actual

- La portada vive en `index.php` y presenta busqueda, tarjetas de tours, filtros y secciones informativas.
- El detalle vive en `pages/detalle/index.php`; puede recibir `tour_id` para generar metadatos SEO desde la base de datos.
- La seleccion de asientos vive en `pages/bus/index.php` y usa `assets/js/bus.js` junto con la API.
- `assets/js/api.js` centraliza las peticiones HTTP y guarda el JWT en `localStorage` como `tours_jwt`.
- Las imagenes de la interfaz usan URLs externas de Unsplash y las librerias CSS/JS se cargan desde CDN.
- El contenido visual de algunas pantallas sigue siendo estatico; la API ya dispone de los datos necesarios para conectarlo progresivamente.

## Estructura

```text
config.php                 Carga .env y crea la conexion PDO a MySQL
index.php                  Portada y buscador de experiencias
api/index.php              API JSON, JWT y reservas
database/schema.sql        Esquema completo de la base de datos
pages/bus/index.php        Vista de seleccion de asientos
pages/detalle/index.php    Vista y metadatos SEO del detalle de un tour
assets/css/                Tema y estilos de portada, detalle y bus
assets/js/api.js           Cliente comun de la API
assets/js/main.js          Interacciones de portada y calendario
assets/js/detail.js        Interacciones del detalle
assets/js/bus.js           Carga y seleccion de asientos
```

## Requisitos

- XAMPP con Apache, PHP 8.1 o superior y MySQL/MariaDB.
- Extension PHP `pdo_mysql` habilitada.
- Navegador con `fetch`, `localStorage` y JavaScript habilitados.

## Puesta en marcha

1. Inicia Apache y MySQL desde XAMPP.
2. Crea `.env` en la raiz del proyecto. Las variables usadas son:

```dotenv
APP_ENV=local
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=tours
DB_USER=root
DB_PASSWORD=
JWT_SECRET=cambia-esta-clave-por-una-larga-y-aleatoria
JWT_TTL=3600
API_CORS_ORIGIN=*
```

3. Importa `database/schema.sql` en MySQL desde phpMyAdmin o con el cliente `mysql`.
4. Genera un hash para un usuario de prueba e insertalo en `users.password_hash`:

```bash
/Applications/XAMPP/xamppfiles/bin/php -r "echo password_hash('tu-clave', PASSWORD_DEFAULT), PHP_EOL;"
```

5. Abre [http://localhost/tours/](http://localhost/tours/).

La configuracion falla de forma intencional si no existe `.env`. No se deben subir credenciales reales al repositorio.

## API

Todas las peticiones usan `api/index.php?resource=...` y devuelven JSON.

| Metodo | Recurso | Auth | Parametros o cuerpo |
| --- | --- | --- | --- |
| `POST` | `auth` | No | `{ "email": "...", "password": "..." }` |
| `GET` | `tours` | No | `destination` opcional |
| `GET` | `tour` | No | `id` obligatorio; devuelve detalle y relaciones |
| `GET` | `departures` | No | `tour_id` obligatorio |
| `GET` | `vehicles` | No | `company_id` opcional |
| `GET` | `seats` | JWT | `departure_id` obligatorio |
| `POST` | `bookings` | JWT | `{ "departure_id": 1, "seat_ids": [1, 2] }` |

Ejemplo de autenticacion:

```bash
curl -X POST http://localhost/tours/api/?resource=auth \
  -H 'Content-Type: application/json' \
  -d '{"email":"usuario@ejemplo.com","password":"tu-clave"}'
```

El token de la respuesta debe enviarse como `Authorization: Bearer <token>`. La reserva se ejecuta dentro de una transaccion y bloquea los asientos disponibles con `FOR UPDATE` para evitar reservas simultaneas del mismo asiento.

## Modelo de datos

El esquema relaciona `companias`, `vehicles`, `tours`, `departures`, `seats` y `bookings`. Un tour puede tener detalles rapidos, destacados, precios, fotos, puntos de encuentro, recomendaciones, inclusiones e itinerario. Las claves foraneas y las restricciones `UNIQUE` protegen la consistencia de esas relaciones.

`vehicle_seat_layout` define la distribucion visual de cada vehiculo. Cada registro representa una posicion dentro de una fila: `position_type = 'seat'` muestra un asiento y `position_type = 'aisle'` muestra el pasillo. `display_order` controla el orden de izquierda a derecha y `seat_number` debe coincidir con `seats.seat_number`.

Ejemplo de una fila 2+2:

```sql
INSERT INTO vehicle_seat_layout
  (vehicle_id, row_number, position_type, seat_number, display_order)
VALUES
  (3, 1, 'seat', 1, 1),
  (3, 1, 'seat', 2, 2),
  (3, 1, 'aisle', NULL, 3),
  (3, 1, 'seat', 3, 4),
  (3, 1, 'seat', 4, 5);
```

La API obtiene el `vehicle_id` desde la salida y devuelve `layout` junto con `data` en `resource=seats`. El frontend agrupa esos registros por fila para construir `seatsGrid`; por ello cada vehiculo puede tener filas, cantidad de asientos y pasillos diferentes.

## Convenciones de desarrollo

- Mantener las credenciales y `JWT_SECRET` exclusivamente en `.env`.
- Usar consultas preparadas mediante PDO en la API.
- Devolver errores JSON con un estado HTTP adecuado.
- Reutilizar `ToursApi` desde el frontend en lugar de duplicar llamadas `fetch`.
- Mantener las rutas absolutas bajo `/tours` coherentes con XAMPP.
