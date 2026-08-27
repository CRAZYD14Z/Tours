USE tours;

START TRANSACTION;

UPDATE companias SET rating = CASE id
    WHEN 1 THEN 4.6
    WHEN 2 THEN 4.8
    ELSE 4.2
END;

UPDATE tours SET rating = CASE id
    WHEN 1 THEN 4.9
    WHEN 2 THEN 4.7
    WHEN 3 THEN 4.5
    WHEN 4 THEN 4.8
    WHEN 5 THEN 4.4
    WHEN 6 THEN 4.6
    ELSE 4.0
END;

INSERT INTO users (name, email, password_hash)
VALUES ('Usuario Demo', 'demo@viajero.local', '$2y$10$xmFWhZw/6AV8975AQzQyYuBGHLhcQn6n9zwkqEC/U4aDy5bYS.tsm')
ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash);

SET @user_id = (SELECT id FROM users WHERE email = 'demo@viajero.local' LIMIT 1);
SET @departure_id = 4;
SET @tour_id = (SELECT tour_id FROM departures WHERE id = @departure_id);
SET @vehicle_id = (SELECT vehicle_id FROM departures WHERE id = @departure_id);
SET @agency_id = (SELECT company_id FROM tours WHERE id = @tour_id);

UPDATE seats
SET status = CASE WHEN seat_number IN (1, 2, 3, 4) THEN 'occupied' ELSE 'available' END
WHERE departure_id = @departure_id;

DELETE bs
FROM booking_seats bs
INNER JOIN bookings b ON b.id = bs.booking_id
WHERE b.user_id = @user_id AND b.departure_id = @departure_id;

DELETE FROM bookings
WHERE user_id = @user_id AND departure_id = @departure_id;

INSERT INTO bookings (
    user_id, agency_id, tour_id, departure_id, vehicle_id,
    adults, children, seniors, total_people, total, status
)
VALUES (
    @user_id, @agency_id, @tour_id, @departure_id, @vehicle_id,
    2, 1, 1, 4, 4286.00, 'confirmed'
);

SET @booking_id = LAST_INSERT_ID();

DELETE FROM booking_seats WHERE booking_id = @booking_id;

INSERT INTO booking_seats (booking_id, seat_id, passenger_type, passenger_number)
SELECT @booking_id, id, passenger_type, passenger_number
FROM (
    SELECT id, 'adult' AS passenger_type, 1 AS passenger_number
    FROM seats WHERE departure_id = @departure_id AND seat_number = 1
    UNION ALL
    SELECT id, 'adult', 2
    FROM seats WHERE departure_id = @departure_id AND seat_number = 2
    UNION ALL
    SELECT id, 'child', 1
    FROM seats WHERE departure_id = @departure_id AND seat_number = 3
    UNION ALL
    SELECT id, 'senior', 1
    FROM seats WHERE departure_id = @departure_id AND seat_number = 4
) AS passenger_seats;

COMMIT;

SELECT b.id AS booking_id, b.departure_id, b.adults, b.children, b.seniors, b.total_people, b.total,
       bs.seat_id, s.seat_number, bs.passenger_type, bs.passenger_number
FROM bookings b
INNER JOIN booking_seats bs ON bs.booking_id = b.id
INNER JOIN seats s ON s.id = bs.seat_id
WHERE b.id = @booking_id
ORDER BY s.seat_number;
