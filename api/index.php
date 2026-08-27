<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . env('API_CORS_ORIGIN', '*'));
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function respond(mixed $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function body(): array
{
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

function base64UrlEncode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function createToken(array $claims): string
{
    $header = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64UrlEncode(json_encode($claims));
    $signature = base64UrlEncode(hash_hmac('sha256', "$header.$payload", (string) env('JWT_SECRET'), true));
    return "$header.$payload.$signature";
}

function authenticated(): array
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
        respond(['error' => 'Token de autenticación requerido'], 401);
    }

    $parts = explode('.', $matches[1]);
    if (count($parts) !== 3) {
        respond(['error' => 'Token inválido'], 401);
    }

    [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
    $expected = base64UrlEncode(hash_hmac('sha256', "$encodedHeader.$encodedPayload", (string) env('JWT_SECRET'), true));
    $payload = json_decode(base64_decode(strtr($encodedPayload, '-_', '+/')), true);
    if (!hash_equals($expected, $encodedSignature) || !is_array($payload) || ($payload['exp'] ?? 0) < time()) {
        respond(['error' => 'Token inválido o expirado'], 401);
    }
    return $payload;
}

try {
    $resource = $_GET['resource'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    if ($resource === 'auth' && $method === 'POST') {
        $input = body();
        $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = (string) ($input['password'] ?? '');
        if (!$email || $password === '') {
            respond(['error' => 'Email y contraseña son obligatorios'], 422);
        }

        $query = database()->prepare('SELECT id, email, password_hash, name FROM users WHERE email = ? LIMIT 1');
        $query->execute([$email]);
        $user = $query->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            respond(['error' => 'Credenciales inválidas'], 401);
        }

        $now = time();
        $token = createToken(['sub' => (int) $user['id'], 'email' => $user['email'], 'iat' => $now, 'exp' => $now + (int) env('JWT_TTL', '3600')]);
        respond(['token' => $token, 'user' => ['id' => $user['id'], 'email' => $user['email'], 'name' => $user['name']]]);
    }

    $pdo = database();

    if ($resource === 'tours' && $method === 'GET') {
        $destination = trim((string) ($_GET['destination'] ?? ''));
        $query = $pdo->prepare('SELECT t.id, t.name, t.description, t.destination, t.price, t.duration, t.category, t.image_url, t.hero_image_url, t.max_group_size, c.trade_name AS company_name FROM tours t INNER JOIN companias c ON c.id = t.company_id WHERE (? = \'\' OR t.destination LIKE ?) AND t.active = 1 AND c.active = 1 ORDER BY t.id DESC');
        $query->execute([$destination, "%$destination%"]);
        respond(['data' => $query->fetchAll()]);
    }

    if ($resource === 'tour' && $method === 'GET') {
        $tourId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$tourId) respond(['error' => 'id de tour es obligatorio'], 422);

        $query = $pdo->prepare('SELECT t.*, c.trade_name AS company_name, c.email AS company_email, c.phone AS company_phone, c.website AS company_website FROM tours t INNER JOIN companias c ON c.id = t.company_id WHERE t.id = ? AND t.active = 1 AND c.active = 1 LIMIT 1');
        $query->execute([$tourId]);
        $tour = $query->fetch();
        if (!$tour) respond(['error' => 'Tour no encontrado'], 404);

        $relatedQueries = [
            'quick_details' => 'SELECT id, label, value, display_order FROM tour_quick_details WHERE tour_id = ? ORDER BY display_order, id',
            'highlights' => 'SELECT id, title, description, display_order FROM tour_highlights WHERE tour_id = ? ORDER BY display_order, id',
            'prices' => 'SELECT id, name, description, amount, currency, min_age, max_age, display_order FROM tour_prices WHERE tour_id = ? AND active = 1 ORDER BY display_order, id',
            'photos' => 'SELECT id, image_url, alt_text, is_cover, display_order FROM tour_photos WHERE tour_id = ? ORDER BY is_cover DESC, display_order, id',
            'meeting_points' => 'SELECT id, name, description, address, latitude, longitude, display_order FROM tour_meeting_points WHERE tour_id = ? ORDER BY display_order, id',
            'recommendations' => 'SELECT id, category, title, items, display_order FROM tour_recommendations WHERE tour_id = ? ORDER BY display_order, id',
            'inclusions' => 'SELECT id, included, item, display_order FROM tour_inclusions WHERE tour_id = ? ORDER BY included DESC, display_order, id',
            'itinerary' => 'SELECT id, day_number, title, location, description FROM tour_itinerary_days WHERE tour_id = ? ORDER BY day_number',
            'departures' => 'SELECT d.id, d.departure_date, d.capacity, d.capacity - COUNT(CASE WHEN s.status = \'occupied\' THEN 1 END) AS available_seats FROM departures d LEFT JOIN seats s ON s.departure_id = d.id WHERE d.tour_id = ? AND d.departure_date >= CURRENT_DATE GROUP BY d.id, d.departure_date, d.capacity ORDER BY d.departure_date'
        ];

        $sections = [];
        foreach ($relatedQueries as $section => $sql) {
            $related = $pdo->prepare($sql);
            $related->execute([$tourId]);
            $sections[$section] = $related->fetchAll();
        }

        respond(['data' => ['tour' => $tour, ...$sections]]);
    }

    if ($resource === 'departures' && $method === 'GET') {
        $tourId = filter_input(INPUT_GET, 'tour_id', FILTER_VALIDATE_INT);
        if (!$tourId) respond(['error' => 'tour_id es obligatorio'], 422);
        $query = $pdo->prepare('SELECT d.id, d.tour_id, d.departure_date, d.capacity, d.capacity - COUNT(CASE WHEN s.status = \'occupied\' THEN 1 END) AS available_seats FROM departures d LEFT JOIN seats s ON s.departure_id = d.id WHERE d.tour_id = ? AND d.departure_date >= CURRENT_DATE GROUP BY d.id, d.tour_id, d.departure_date, d.capacity ORDER BY d.departure_date');
        $query->execute([$tourId]);
        respond(['data' => $query->fetchAll()]);
    }

    authenticated();

    if ($resource === 'seats' && $method === 'GET') {
        $departureId = filter_input(INPUT_GET, 'departure_id', FILTER_VALIDATE_INT);
        if (!$departureId) respond(['error' => 'departure_id es obligatorio'], 422);
        $query = $pdo->prepare('SELECT id, seat_number, status FROM seats WHERE departure_id = ? ORDER BY seat_number');
        $query->execute([$departureId]);
        respond(['data' => $query->fetchAll()]);
    }

    if ($resource === 'bookings' && $method === 'POST') {
        $input = body();
        $departureId = (int) ($input['departure_id'] ?? 0);
        $seatIds = $input['seat_ids'] ?? [];
        if ($departureId < 1 || !is_array($seatIds) || count($seatIds) < 1) respond(['error' => 'Salida y asientos son obligatorios'], 422);

        $pdo->beginTransaction();
        $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
        $lock = $pdo->prepare("SELECT id FROM seats WHERE departure_id = ? AND id IN ($placeholders) AND status = 'available' FOR UPDATE");
        $lock->execute(array_merge([$departureId], array_map('intval', $seatIds)));
        $available = $lock->fetchAll();
        if (count($available) !== count(array_unique(array_map('intval', $seatIds)))) {
            $pdo->rollBack();
            respond(['error' => 'Uno o más asientos ya no están disponibles'], 409);
        }

        $insert = $pdo->prepare('INSERT INTO bookings (user_id, departure_id, total, status) VALUES (?, ?, ?, \'confirmed\')');
        $insert->execute([authenticated()['sub'], $departureId, count($seatIds) * 450]);
        $bookingId = (int) $pdo->lastInsertId();
        $bookingSeat = $pdo->prepare('INSERT INTO booking_seats (booking_id, seat_id) VALUES (?, ?)');
        $updateSeat = $pdo->prepare("UPDATE seats SET status = 'occupied' WHERE id = ?");
        foreach (array_map('intval', $seatIds) as $seatId) {
            $bookingSeat->execute([$bookingId, $seatId]);
            $updateSeat->execute([$seatId]);
        }
        $pdo->commit();
        respond(['data' => ['booking_id' => $bookingId]], 201);
    }

    respond(['error' => 'Recurso no encontrado'], 404);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    respond(['error' => env('APP_ENV') === 'local' ? $error->getMessage() : 'Error interno del servidor'], 500);
}
