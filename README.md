# Viajero: API local

Toda operación de datos usa `api/index.php`; las credenciales de MySQL y la clave JWT sólo viven en `.env`.

## Puesta en marcha

1. Copia `.env.example` como `.env` y cambia `JWT_SECRET` por una clave aleatoria larga.
2. Importa `database/schema.sql` en MySQL desde phpMyAdmin o con el cliente `mysql`.
3. Crea un usuario con una contraseña generada por PHP:

```bash
/Applications/XAMPP/xamppfiles/bin/php -r "echo password_hash('tu-clave', PASSWORD_DEFAULT), PHP_EOL;"
```

Inserta el hash resultante en `users.password_hash`.

## Autenticación

Solicita un token JWT:

```bash
curl -X POST http://localhost/tours/api/?resource=auth \
  -H 'Content-Type: application/json' \
  -d '{"email":"usuario@ejemplo.com","password":"tu-clave"}'
```

Guarda el valor `token` en `localStorage` bajo `tours_jwt`. Las rutas `tours`, `seats` y `bookings` rechazan peticiones sin `Authorization: Bearer <token>`.

La selección de asientos acepta `?departure_id=1`; la reserva usa una transacción y bloquea los asientos para evitar dobles reservas.
