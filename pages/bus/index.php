<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Selección de Asientos - Bus Express</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/theme.css">
    <link rel="stylesheet" href="../../assets/css/bus.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Bus Express</h1>
            <p class="route">Ciudad de México → Guadalajara</p>
            <p class="time">Salida: 14:30 | Llegada estimada: 22:15</p>
        </header>

        <div class="bus-container">
            <div class="bus-visual">
                <div class="bus-front">
                    <div class="windshield"></div>
                    <div class="headlights"></div>
                </div>
                
                <div class="bus-body">
                    <div class="legend">
                        <div class="legend-item">
                            <div class="seat-indicator available"></div>
                            <span>Disponible</span>
                        </div>
                        <div class="legend-item">
                            <div class="seat-indicator selected"></div>
                            <span>Seleccionado</span>
                        </div>
                        <div class="legend-item">
                            <div class="seat-indicator occupied"></div>
                            <span>Ocupado</span>
                        </div>
                    </div>

                    <div class="seats-container">
                        <div class="driver-seat">
                            <div class="steering-wheel">🚌</div>
                        </div>
                        <div class="seats-grid" id="seatsGrid"></div>
                        <div class="bus-aisle"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="selection-info">
            <div class="info-card">
                <h3>Asientos Seleccionados</h3>
                <p id="selectedSeats">Ningún asiento seleccionado</p>
                <div class="price-info">
                    <span>Precio por asiento: $<span id="seatPrice">450</span></span>
                    <span class="total">Total: $<span id="totalPrice">0</span></span>
                </div>
            </div>
            
            <button id="confirmBtn" class="confirm-btn" disabled>
                Confirmar Selección
                <span class="ripple"></span>
            </button>
        </div>
    </div>

    <div class="success-modal" id="successModal">
        <div class="modal-content">
            <div class="success-icon">✓</div>
            <h2>¡Reserva Exitosa!</h2>
            <p>Tus asientos han sido reservados exitosamente.</p>
            <button id="closeModal" class="close-btn">Cerrar</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/bus.js"></script>
</body>
</html>