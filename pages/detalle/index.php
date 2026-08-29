<?php
require_once dirname(__DIR__, 2) . '/config.php';

function seoSlug(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

$seoTour = null;
$seoTourId = filter_var($_GET['tour_id'] ?? null, FILTER_VALIDATE_INT) ?: filter_input(INPUT_GET, 'tour_id', FILTER_VALIDATE_INT);
if ($seoTourId) {
    try {
        $seoQuery = database()->prepare('SELECT t.id, t.name, t.description, t.destination, t.price, t.duration, t.category, t.hero_image_url, t.image_url, t.badge, c.id AS company_id, c.trade_name AS company_name, c.legal_name AS company_legal_name, c.rating AS company_rating, c.description AS company_description, c.logo_url AS company_logo_url, c.city AS company_city, c.country AS company_country, c.phone AS company_phone, c.email AS company_email, c.website AS company_website FROM tours t INNER JOIN companias c ON c.id = t.company_id WHERE t.id = ? AND t.active = 1 AND c.active = 1 LIMIT 1');
        $seoQuery->execute([$seoTourId]);
        $seoTour = $seoQuery->fetch();
    } catch (Throwable $error) {
        $seoTour = null;
    }
}

$companyName = $seoTour['company_name'] ?? 'Explorando los Andes';
$companyId = (int) ($seoTour['company_id'] ?? 1);
$companySlug = seoSlug($companyName);
$companyUrl = "/tours/compania-de-tours/{$companySlug}/{$companyId}";
$companyRating = number_format((float) ($seoTour['company_rating'] ?? 4.8), 1);
$companyDesc = $seoTour['company_description'] ?? 'Operador turístico certificado con amplia trayectoria en salidas grupales, transporte de primera clase y guías locales expertos.';


$seoTitle = $seoTour ? $seoTour['name'] . ' | ' . $seoTour['company_name'] . ' | Viajero' : 'Tours y experiencias | Viajero';
$seoDescription = $seoTour ? $seoTour['description'] : 'Descubre tours y experiencias grupales con operadores especializados.';
$seoImage = $seoTour['hero_image_url'] ?? $seoTour['image_url'] ?? 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1200&h=630&fit=crop';
$seoPath = $seoTour ? seoSlug($seoTour['company_name']) . '/' . seoSlug($seoTour['name']) . '/' . $seoTour['id'] : '';
$seoUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/tours/' . $seoPath;
$seoStructuredData = $seoTour ? [
    '@context' => 'https://schema.org',
    '@type' => 'TouristTrip',
    'name' => $seoTour['name'],
    'description' => $seoTour['description'],
    'touristType' => $seoTour['category'],
    'itinerary' => $seoTour['destination'],
    'provider' => [
        '@type' => 'Organization',
        'name' => $seoTour['company_name']
    ],
    'image' => $seoImage,
    'offers' => [
        '@type' => 'Offer',
        'url' => $seoUrl,
        'price' => $seoTour['price'],
        'priceCurrency' => 'USD',
        'availability' => 'https://schema.org/InStock'
    ]
] : [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'Viajero',
    'url' => 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/tours/'
];
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($seoUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_ES">
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seoUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seoImage, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($seoImage, ENT_QUOTES, 'UTF-8') ?>">
    <script type="application/ld+json"><?= json_encode($seoStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/tours/assets/css/theme.css">
    <link rel="stylesheet" href="/tours/assets/css/detail.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="<?= htmlspecialchars($companyUrl, ENT_QUOTES, 'UTF-8') ?>" style="text-decoration: none; color: inherit;" title="Ver perfil de la compañía">
                    <h1><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></h1>
                </a>
            </div>
            <div class="nav-menu">
                <a href="/tours/" class="nav-link">Inicio</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle">TOURS</a>
                    <div class="dropdown-menu">
                        <a href="#gallery" class="dropdown-link">Galería</a>
                        <a href="#itinerary" class="dropdown-link">Itinerario</a>
                        <a href="#vehicle" class="dropdown-link">Transporte</a>
                        <a href="#operator" class="dropdown-link">Operador</a>
                        <a href="#reviews" class="dropdown-link">Reseñas</a>
                    </div>
                </div>
                <a href="#operator" class="nav-link">Operador</a>
                <a href="#reviews" class="nav-link">Reseñas</a>
                <a href="#recommendations" class="nav-link">FAQ</a>
                <a href="<?= htmlspecialchars($companyUrl, ENT_QUOTES, 'UTF-8') ?>" class="nav-link" style="color: var(--accent); font-weight: 600;">Ver Compañía</a>
            </div>
            <div class="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="hero-background"></div>
        <div class="hero-content">
            <div class="hero-badge">Experiencia Exclusiva</div>
            <div class="hero-rating" id="heroRating" aria-label="Calificación del tour"></div>
            <h1 class="hero-title">Explorando los Andes</h1>
            <p class="hero-subtitle">Un viaje inolvidable a través de las montañas más majestuosas de Sudamérica</p>
        </div>
        <div class="scroll-indicator">
            <div class="scroll-arrow"></div>
        </div>
    </div>

    <div class="content-section">
        <div class="container">
            <div class="content-booking-wrapper">
                <div class="main-content">
                    <section class="quick-details minimal" id="quick-details">
                        <h2 class="section-title">Detalle Rápido</h2>
                        <div class="details-grid">
                            <div class="detail-card minimal">
                                <span class="detail-value">7 días</span>
                                <span class="detail-label">Duración</span>
                            </div>
                            <div class="detail-card minimal">
                                <span class="detail-value">12 personas</span>
                                <span class="detail-label">Grupo máximo</span>
                            </div>
                            <div class="detail-card minimal">
                                <span class="detail-value">4,200 msnm</span>
                                <span class="detail-label">Altitud máxima</span>
                            </div>
                            <div class="detail-card minimal">
                                <span class="detail-value">Moderada</span>
                                <span class="detail-label">Dificultad</span>
                            </div>
                        </div>
                    </section>

                    <section class="description-section" id="description">
                        <h2 class="section-title">Descripción</h2>
                        <div class="description-content">
                            <p class="description-text">
                                Embárcate en una aventura épica a través de la cordillera de los Andes, donde la majestuosidad de las montañas se encuentra con la rica cultura ancestral. Este tour te llevará por senderos antiguos, pueblos tradicionales y paisajes que te dejarán sin aliento.
                            </p>
                            <p class="description-text">
                                Desde las vibrantes ciudades coloniales hasta los remotos pueblos andinos, cada día trae nuevas maravillas. Experimentarás la hospitalidad de las comunidades locales, degustarás la auténtica gastronomía peruana y te maravillarás con vistas panorámicas que parecen de otro planeta.
                            </p>
                        </div>
                    </section>

                    <section class="highlights-section minimal" id="highlights">
                        <h2 class="section-title">Destacados</h2>
                        <div class="highlights-grid minimal">
                            <div class="highlight-item minimal">
                                <h3>Machu Picchu al amanecer</h3>
                                <p>Experiencia exclusiva sin multitudes</p>
                            </div>
                            <div class="highlight-item minimal">
                                <h3>Comunidades andinas</h3>
                                <p>Interacción auténtica con locales</p>
                            </div>
                            <div class="highlight-item minimal">
                                <h3>Gastronomía ancestral</h3>
                                <p>Talleres de cocina tradicional</p>
                            </div>
                            <div class="highlight-item minimal">
                                <h3>Cielos estrellados</h3>
                                <p>Observación a 4,000 msnm</p>
                            </div>
                        </div>
                    </section>

                    <section class="spaces-section" id="spaces">
                        <h2 class="section-title">Espacios disponibles</h2>
                        <div class="availability-summary">
                            <div class="availability-main">
                                <span class="availability-label">Disponibilidad total</span>
                                <strong class="availability-total">15 / 30</strong>
                                <span class="availability-note">plazas disponibles en este tour</span>
                            </div>
                            <div class="availability-meter" aria-label="15 de 30 espacios disponibles">
                                <span style="width: 50%"></span>
                            </div>
                        </div>

                        <div class="extras-section">
                            <h3 class="extras-title">Tipos de experiencia</h3>
                            <div class="extras-list">
                                <label class="extra-option">
                                    <input type="radio" name="experience-tier" checked>
                                    <div>
                                        <strong>Turista</strong>
                                        <span>Traslado básico, guía compartido y acceso estándar.</span>
                                    </div>
                                </label>
                                <label class="extra-option">
                                    <input type="radio" name="experience-tier">
                                    <div>
                                        <strong>Ejecutiva</strong>
                                        <span>Asiento prioritario, snacks premium y atención personalizada.</span>
                                    </div>
                                </label>
                                <label class="extra-option">
                                    <input type="radio" name="experience-tier">
                                    <div>
                                        <strong>Primera clase</strong>
                                        <span>Servicio VIP, lounge, catering exclusivo y alojamiento premium.</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </section>

                    <section class="gallery-section" id="gallery">
                        <h2 class="section-title">Galería</h2>
                        <div class="gallery-grid">
                            <figure class="gallery-item gallery-item--large">
                                <img src="https://images.unsplash.com/photo-1526392060635-9d6019884377?w=900&h=700&fit=crop" alt="Vista panorámica de los Andes">
                            </figure>
                            <figure class="gallery-item">
                                <img src="https://images.unsplash.com/photo-1589308078059-be1415eab4c3?w=700&h=500&fit=crop" alt="Valle Sagrado">
                            </figure>
                            <figure class="gallery-item">
                                <img src="https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?w=700&h=500&fit=crop" alt="Laguna Humantay">
                            </figure>
                            <figure class="gallery-item">
                                <img src="https://images.unsplash.com/photo-1518509565194-3a8d63d0f4c3?w=700&h=500&fit=crop" alt="Comunidades andinas">
                            </figure>
                            <figure class="gallery-item">
                                <img src="https://images.unsplash.com/photo-1527631746610-bca00a040d60?w=700&h=500&fit=crop" alt="Senderos de la montaña">
                            </figure>
                        </div>
                    </section>

                    <section class="meeting-points minimal" id="meeting-points">
                        <h2 class="section-title">Puntos de encuentro</h2>
                        <div class="meeting-grid">
                            <div class="meeting-card">
                                <div class="meeting-icon">✈️</div>
                                <h3>Aeropuerto Cusco</h3>
                                <p>Recepción personalizada con cartel identificativo</p>
                            </div>
                            <div class="meeting-card">
                                <div class="meeting-icon">🏨</div>
                                <h3>Hotel Casa Andina</h3>
                                <p>Reunión previa al tour - 7:00 AM</p>
                            </div>
                            <div class="meeting-card">
                                <div class="meeting-icon">📍</div>
                                <h3>Plaza de Armas</h3>
                                <p>Punto alternativo - 7:30 AM</p>
                            </div>
                        </div>
                    </section>

                    <section class="recommendations minimal" id="recommendations">
                        <h2 class="section-title">Recomendaciones</h2>
                        <div class="recommendations-grid">
                            <div class="recommendation-card">
                                <div class="recommendation-icon">🎒</div>
                                <h3>Qué llevar</h3>
                                <ul>
                                    <li>Ropa en capas para cambios de temperatura</li>
                                    <li>Botas de trekking impermeables</li>
                                    <li>Bloqueador solar factor 50+</li>
                                    <li>Botella de agua reutilizable</li>
                                </ul>
                            </div>
                            <div class="recommendation-card">
                                <div class="recommendation-icon">💊</div>
                                <h3>Salud y seguridad</h3>
                                <ul>
                                    <li>Pastillas para la altura (consultar médico)</li>
                                    <li>Seguro de viaje obligatorio</li>
                                    <li>Copia digital de documentos</li>
                                    <li>Dinero en efectivo (soles peruanos)</li>
                                </ul>
                            </div>
                            <div class="recommendation-card">
                                <div class="recommendation-icon">🌡️</div>
                                <h3>Clima</h3>
                                <ul>
                                    <li>Estación secada: Abr - Oct</li>
                                    <li>Temperatura: 5°C a 20°C</li>
                                    <li>Lluvias: Nov - Mar</li>
                                    <li>Altitud: Adaptación necesaria</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <section class="includes-section minimal" id="includes">
                        <div class="includes-container">
                            <div class="includes-column">
                                <h2 class="section-title">Qué Incluye</h2>
                                <ul class="includes-list">
                                    <li>Transporte terrestre privado durante todo el tour</li>
                                    <li>Alojamiento en hoteles boutique 4 estrellas</li>
                                    <li>Desayunos y cenas gourmet</li>
                                    <li>Guía local certificado bilingüe</li>
                                    <li>Entradas a todos los sitios arqueológicos</li>
                                    <li>Equipo de seguridad y primeros auxilios</li>
                                    <li>Seguro de viaje internacional</li>
                                </ul>
                            </div>
                            <div class="includes-column">
                                <h2 class="section-title">Qué No Incluye</h2>
                                <ul class="includes-list no-list">
                                    <li>Vuelos internacionales y domésticos</li>
                                    <li>Almuerzos (para flexibilidad en la exploración)</li>
                                    <li>Bebidas alcohólicas</li>
                                    <li>Gastos personales y propinas</li>
                                    <li>Visa de turista (si aplica)</li>
                                    <li>Actividades no especificadas en el itinerario</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <section class="itinerary-section minimal" id="itinerary">
                        <h2 class="section-title">Itinerario</h2>
                        <div class="itinerary-tabs">
                            <button class="tab-button active" data-day="all">Todo</button>
                            <button class="tab-button" data-day="1-3">Días 1-3</button>
                            <button class="tab-button" data-day="4-7">Días 4-7</button>
                        </div>
                        
                        <div class="itinerary-accordion">
                            <div class="accordion-item active" data-day="1">
                                <div class="accordion-header">
                                    <div class="day-number">01</div>
                                    <div class="accordion-title">
                                        <h3>Llegada a Cusco</h3>
                                        <p class="day-location">📍 Cusco, Perú</p>
                                    </div>
                                    <div class="accordion-icon">
                                        <span class="chevron"></span>
                                    </div>
                                </div>
                                <div class="accordion-content">
                                    <p class="day-description">Recepción en el aeropuerto y aclimatación. Tarde libre para explorar la ciudadela inca. Nuestro equipo te estará esperando en el aeropuerto con un cartel personalizado. Traslado al hotel boutique donde tendrás la tarde libre para aclimatarte a la altitud y explorar el centro histórico de Cusco, declarado Patrimonio de la Humanidad por la UNESCO.</p>
                                </div>
                            </div>

                            <div class="accordion-item" data-day="2">
                                <div class="accordion-header">
                                    <div class="day-number">02</div>
                                    <div class="accordion-title">
                                        <h3>Valle Sagrado</h3>
                                        <p class="day-location">📍 Pisac - Ollantaytambo</p>
                                    </div>
                                    <div class="accordion-icon">
                                        <span class="chevron"></span>
                                    </div>
                                </div>
                                <div class="accordion-content">
                                    <p class="day-description">Exploración de mercados tradicionales y ruinas incas en el corazón del Valle Sagrado. Comenzaremos temprano rumbo al Valle Sagrado, donde visitaremos el mercado de Pisac, famoso por sus textiles y artesanías. Luego exploraremos las ruinas de Pisac con impresionantes terrazas agrícolas. Almuerzo en Urubamba antes de dirigirnos a Ollantaytambo, la última ciudadela inca antes de Machu Picchu.</p>
                                </div>
                            </div>

                            <div class="accordion-item" data-day="3">
                                <div class="accordion-header">
                                    <div class="day-number">03</div>
                                    <div class="accordion-title">
                                        <h3>Machu Picchu</h3>
                                        <p class="day-location">📍 Aguas Calientes</p>
                                    </div>
                                    <div class="accordion-icon">
                                        <span class="chevron"></span>
                                    </div>
                                </div>
                                <div class="accordion-content">
                                    <p class="day-description">Tren panorámico a Aguas Calientes y visita guiada a la ciudadela perdida de Machu Picchu. Temprano por la mañana tomaremos el tren panorámico con techo de cristal hacia Aguas Calientes. Subida en bus a Machu Picchu donde nuestro guía experto te revelará los secretos de esta maravilla del mundo. Tarde libre para explorar a tu ritmo y opción de subir al Huayna Picchu (sujeto a disponibilidad).</p>
                                </div>
                            </div>

                            <div class="accordion-item" data-day="4">
                                <div class="accordion-header">
                                    <div class="day-number">04</div>
                                    <div class="accordion-title">
                                        <h3>Caminata al Humantay</h3>
                                        <p class="day-location">📍 Soraypampa</p>
                                    </div>
                                    <div class="accordion-icon">
                                        <span class="chevron"></span>
                                    </div>
                                </div>
                                <div class="accordion-content">
                                    <p class="day-description">Trekking de 5 horas hacia la laguna turquesa de Humantay a 4,200 msnm. Después de un desayuno temprano, nos dirigiremos a Soraypampa para comenzar nuestra caminata hacia la laguna Humantay. Esta laguna glacial de color turquesa es considerada sagrada por los andinos. Regreso por la tarde con almuerzo en un restaurante local.</p>
                                </div>
                            </div>

                            <div class="accordion-item" data-day="5">
                                <div class="accordion-header">
                                    <div class="day-number">05</div>
                                    <div class="accordion-title">
                                        <h3>Comunidades Andinas</h3>
                                        <p class="day-location">📍 Chinchero - Maras</p>
                                    </div>
                                    <div class="accordion-icon">
                                        <span class="chevron"></span>
                                    </div>
                                </div>
                                <div class="accordion-content">
                                    <p class="day-description">Interacción con comunidades locales y visita a las misteriosas salineras de Maras. Hoy nos sumergiremos en la vida cotidiana de las comunidades andinas. Visitaremos Chinchero donde aprenderemos tejido tradicional con tintes naturales. Continuaremos a las salineras de Maras, miles de pozos de sal en terrazas que crean un paisaje lunar. Almuerzo en casa de una familia local.</p>
                                </div>
                            </div>

                            <div class="accordion-item" data-day="6">
                                <div class="accordion-header">
                                    <div class="day-number">06</div>
                                    <div class="accordion-title">
                                        <h3>Relax en Aguas Termales</h3>
                                        <p class="day-location">📍 Lares</p>
                                    </div>
                                    <div class="accordion-icon">
                                        <span class="chevron"></span>
                                    </div>
                                </div>
                                <div class="accordion-content">
                                    <p class="day-description">Día de descanso en aguas termales naturales y ceremonia de coca con chamán local. Viaje a las aguas termales de Lares, conocidas por sus propiedades curativas. Tarde de relajación y opción de participar en una ceremonia tradicional de hoja de coca con un chamán local quien compartirá sabiduría ancestral andina. Cena especial de despedida con show de música folclórica.</p>
                                </div>
                            </div>

                            <div class="accordion-item" data-day="7">
                                <div class="accordion-header">
                                    <div class="day-number">07</div>
                                    <div class="accordion-title">
                                        <h3>Despedida</h3>
                                        <p class="day-location">📍 Cusco</p>
                                    </div>
                                    <div class="accordion-icon">
                                        <span class="chevron"></span>
                                    </div>
                                </div>
                                <div class="accordion-content">
                                    <p class="day-description">Desayuno de despedida y traslado al aeropuerto para tu vuelo de regreso. Último desayuno en el hotel con tiempo para compartir experiencias y fotos del viaje. Traslado privado al aeropuerto de Cusco con asistencia para el check-in. Fin de nuestros servicios con la promesa de volver a encontrarnos pronto.</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="booking-sidebar">
                    <div class="booking-content">
                        <h3 class="booking-title">Reserva tu Aventura</h3>
                        
                        <div class="minimal-carousel">
                            <div class="minimal-carousel-container">
                                <div class="minimal-carousel-track">
                                    <div class="minimal-carousel-slide">
                                        <img src="https://images.unsplash.com/photo-1589308078059-be1415eab4c3?w=400&h=250&fit=crop" alt="Valle Sagrado" class="minimal-carousel-image">
                                    </div>
                                    <div class="minimal-carousel-slide">
                                        <img src="https://images.unsplash.com/photo-1526392060635-9d6019884377?w=400&h=250&fit=crop" alt="Machu Picchu" class="minimal-carousel-image">
                                    </div>
                                    <div class="minimal-carousel-slide">
                                        <img src="https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?w=400&h=250&fit=crop" alt="Laguna Humantay" class="minimal-carousel-image">
                                    </div>
                                    <div class="minimal-carousel-slide">
                                        <img src="https://images.unsplash.com/photo-1518509565194-3a8d63d0f4c3?w=400&h=250&fit=crop" alt="Comunidades Andinas" class="minimal-carousel-image">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        
                        <div class="price-list">
                            <div class="price-item">
                                <span class="price-label">Adulto</span>
                                <span class="price-value">$1,299</span>
                            </div>
                            <div class="price-item">
                                <span class="price-label">Niño (5-12 años)</span>
                                <span class="price-value">$649</span>
                            </div>
                            <div class="price-item">
                                <span class="price-label">Tercera Edad (60+)</span>
                                <span class="price-value">$1,039</span>
                            </div>
                        </div>
                        
                        <div class="calendar-section">
                            <h4 class="calendar-title">Fechas de Salida</h4>
                            <p class="departure-price" id="departurePrice">Selecciona una fecha para consultar el precio.</p>
                            <div class="calendar-container" id="calendar">
                                <!-- Calendar will be generated by JavaScript -->
                            </div>
                        </div>
<button class="btn-reserve minimal-btn">RESERVAR AHORA</button>
                      
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <div class="gallery-lightbox" id="galleryLightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Galería de fotografías">
        <button class="gallery-lightbox__close" type="button" aria-label="Cerrar galería">×</button>
        <button class="gallery-lightbox__prev" type="button" aria-label="Fotografía anterior">‹</button>
        <figure class="gallery-lightbox__figure">
            <img class="gallery-lightbox__image" src="" alt="">
            <figcaption class="gallery-lightbox__caption"></figcaption>
        </figure>
        <button class="gallery-lightbox__next" type="button" aria-label="Fotografía siguiente">›</button>
    </div>

    <div class="booking-modal-overlay" id="bookingModal">
        <div class="booking-modal">
            <button class="modal-close" id="modalClose">×</button>
            <h3 class="modal-title">Reserva tu Aventura</h3>
            <p class="modal-date" id="modalDate">Fecha seleccionada: <span></span></p>
            
            <div class="booking-form">
                <div class="passenger-selector">
                    <label>Adultos</label>
                    <div class="counter">
                        <button class="counter-btn" data-type="adults" data-action="decrease">-</button>
                        <span class="counter-value" data-type="adults">1</span>
                        <button class="counter-btn" data-type="adults" data-action="increase">+</button>
                    </div>
                    <span class="price-detail">$1,299 c/u</span>
                </div>
                
                <div class="passenger-selector">
                    <label>Niños (5-12 años)</label>
                    <div class="counter">
                        <button class="counter-btn" data-type="children" data-action="decrease">-</button>
                        <span class="counter-value" data-type="children">0</span>
                        <button class="counter-btn" data-type="children" data-action="increase">+</button>
                    </div>
                    <span class="price-detail">$649 c/u</span>
                </div>
                
                <div class="passenger-selector">
                    <label>Tercera Edad (60+)</label>
                    <div class="counter">
                        <button class="counter-btn" data-type="seniors" data-action="decrease">-</button>
                        <span class="counter-value" data-type="seniors">0</span>
                        <button class="counter-btn" data-type="seniors" data-action="increase">+</button>
                    </div>
                    <span class="price-detail">$1,039 c/u</span>
                </div>
                
                <div class="availability-status">
                    <span id="availabilityText">Disponibilidad: <span class="available">12 cupos disponibles</span></span>
                </div>
                
                <div class="booking-summary">
                    <div class="summary-item">
                        <span>Total de personas</span>
                        <span id="totalPeople">1</span>
                    </div>
                    <div class="summary-item total">
                        <span>Total General</span>
                        <span id="totalPrice">$1,299</span>
                    </div>
                </div>
                
                <button class="minimal-btn" id="confirmBooking">CONFIRMAR RESERVA</button>
            </div>
        </div>
    </div>

    <section class="vehicle-section" id="vehicle">
        <div class="container">
            <div class="vehicle-section-header">
                <p class="vehicle-eyebrow">Transporte incluido</p>
                <h2 class="section-title">Vehículo asignado</h2>
            </div>
            <div class="vehicle-list" id="vehicleList"></div>
        </div>
    </section>

    <section class="company-showcase-section" id="operator">
        <div class="container">
            <div class="company-showcase-card">
                <div class="company-showcase-grid">
                    <div class="company-showcase-main">
                        <div class="company-showcase-header">
                            <div class="company-showcase-logo" id="companyLogoContainer">
                                <?php if (!empty($seoTour['company_logo_url'])): ?>
                                    <img src="<?= htmlspecialchars($seoTour['company_logo_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?>">
                                <?php else: ?>
                                    <span id="companyLogoInitial"><?= strtoupper(substr($companyName, 0, 1)) ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="badge--verified">✓ Operador Verificado</span>
                                <h2 class="company-showcase-name" id="companyTradeName"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></h2>
                                <p class="company-showcase-legal" id="companyLegalName">
                                    <?= !empty($seoTour['company_legal_name']) && $seoTour['company_legal_name'] !== $companyName ? htmlspecialchars($seoTour['company_legal_name'], ENT_QUOTES, 'UTF-8') . ' &bull; Razón Social' : (!empty($seoTour['company_city']) ? htmlspecialchars($seoTour['company_city'] . ', ' . ($seoTour['company_country'] ?? ''), ENT_QUOTES, 'UTF-8') : 'Operador Local') ?>
                                </p>
                            </div>
                        </div>
                        <p class="company-showcase-desc" id="companyDescription">
                            <?= htmlspecialchars($companyDesc, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <div class="company-showcase-features">
                            <span class="company-feat-tag">🛡️ Protocolos de Seguridad</span>
                            <span class="company-feat-tag">🚌 Flota de Transporte Propia</span>
                            <span class="company-feat-tag">🧭 Guías Certificados Bilingües</span>
                            <span class="company-feat-tag">⭐ Atención Personalizada</span>
                        </div>
                    </div>
                    <div class="company-showcase-cta">
                        <div class="company-rating-box">
                            <div class="company-rating-num" id="companyRatingValue"><?= $companyRating ?></div>
                            <div class="company-rating-stars">★★★★★</div>
                            <div class="company-rating-label">Calificación del Operador</div>
                        </div>
                        <div class="company-action-buttons">
                            <a href="<?= htmlspecialchars($companyUrl, ENT_QUOTES, 'UTF-8') ?>" id="companyProfileLink" class="btn-company-profile">
                                <span>Ver perfil y todos los tours</span>
                                <span>→</span>
                            </a>
                            <?php if (!empty($seoTour['company_phone'])): ?>
                                <a href="tel:<?= htmlspecialchars($seoTour['company_phone'], ENT_QUOTES, 'UTF-8') ?>" id="companyCallLink" class="btn-company-contact">
                                    <span>📞 <?= htmlspecialchars($seoTour['company_phone'], ENT_QUOTES, 'UTF-8') ?></span>
                                </a>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($companyUrl, ENT_QUOTES, 'UTF-8') ?>#contact" id="companyCallLink" class="btn-company-contact">
                                    <span>✉️ Contactar operador</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="reviews-section" id="reviews">
        <div class="container">
            <div class="reviews-section-header">
                <p class="section-eyebrow" style="color: var(--accent); font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.4rem;">Experiencias Comprobadas</p>
                <h2 class="section-title">Reseñas y Opiniones</h2>
                <p class="reviews-section-subtitle">Conoce los testimonios reales de pasajeros que han vivido esta experiencia.</p>
            </div>
            <div class="reviews-grid">
                <div class="reviews-group">
                    <div class="reviews-group-header">
                        <h3>🎒 Reseñas del tour</h3>
                        <span class="tour-rating" style="color: #f59e0b; font-size: 0.9rem;">★★★★★</span>
                    </div>
                    <div id="tourReviews" class="reviews-list"></div>
                </div>
                <div class="reviews-group">
                    <div class="reviews-group-header">
                        <h3>🏢 Reseñas del operador</h3>
                        <span class="tour-rating" style="color: #f59e0b; font-size: 0.9rem;">★★★★★</span>
                    </div>
                    <div id="companyReviews" class="reviews-list"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="related-tours-section">
            <div class="container">
                
                <h2 class="section-title">Más experiencias</h2>
                
                <div class="tours-carousel">
                    <div class="carousel-container">
                        <div class="carousel-track" id="relatedToursTrack">
                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1533002535226-22da29157c0f?w=400&h=250&fit=crop" alt="Machu Picchu Express" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$899</span>
                                                <span class="tour-days">3 días</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Machu Picchu Express</h3>
                                        <p class="tour-description">Descubre la ciudadela inca en un tour exprés perfecto para quienes tienen poco tiempo.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1526392060635-9d6019884377?w=400&h=250&fit=crop" alt="Inca Trail Clásico" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$1,299</span>
                                                <span class="tour-days">4 días</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Inca Trail Clásico</h3>
                                        <p class="tour-description">La caminata más emblemática de Sudamérica hasta la puerta del sol de Machu Picchu.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?w=400&h=250&fit=crop" alt="Amazonía Peruana" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$1,599</span>
                                                <span class="tour-days">5 días</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Amazonía Peruana</h3>
                                        <p class="tour-description">Explora la selva amazónica peruana con alojamiento en eco-lodges de lujo.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1518509565194-3a8d63d0f4c3?w=400&h=250&fit=crop" alt="Lago Titicaca" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$799</span>
                                                <span class="tour-days">2 días</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Lago Titicaca</h3>
                                        <p class="tour-description">Navega por el lago navegable más alto del mundo y visita las islas flotantes.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1570654628422-93748b0c6a20?w=400&h=250&fit=crop" alt="Cañón del Colca" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$999</span>
                                                <span class="tour-days">3 días</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Cañón del Colca</h3>
                                        <p class="tour-description">Observa el vuelo majestuoso de los cóndores en uno de los cañones más profundos del mundo.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?w=400&h=250&fit=crop" alt="Rainbow Mountain" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$89</span>
                                                <span class="tour-days">Día completo</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Montaña de Colores</h3>
                                        <p class="tour-description">Descubre la montaña arcoíris más famosa del mundo con sus colores naturales vibrantes.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1570654628422-93748b0c4c3?w=400&h=250&fit=crop" alt="Cañón de Colca" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$129</span>
                                                <span class="tour-days">2 días</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Cañón de Colca</h3>
                                        <p class="tour-description">Observa el majestuoso vuelo de los cóndores en el segundo cañón más profundo del mundo.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1589308078059-be1415eab4c3?w=400&h=250&fit=crop" alt="Valle Sagrado Premium" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$299</span>
                                                <span class="tour-days">2 días</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Valle Sagrado Premium</h3>
                                        <p class="tour-description">Explora Pisac, Ollantaytambo y las salineras de Maras con almuerzo gourmet incluido.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1518509565194-3a8d63d0f4c3?w=400&h=250&fit=crop" alt="Lago Titicaca Express" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$199</span>
                                                <span class="tour-days">2 días</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Lago Titicaca Express</h3>
                                        <p class="tour-description">Navega por el lago más alto del mundo y visita las islas flotantes de los Uros.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1533002535226-22da29157c0f?w=400&h=250&fit=crop" alt="Cusco Nocturno" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$79</span>
                                                <span class="tour-days">Noche</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Cusco Nocturno</h3>
                                        <p class="tour-description">Descubre la magia de Cusco iluminado con cena y show de danzas tradicionales.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1544966503-7cc5ac882d5d?w=400&h=250&fit=crop" alt="Selva Amazónica" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$899</span>
                                                <span class="tour-days">3 días</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Selva Amazónica</h3>
                                        <p class="tour-description">Aventura en la selva peruana con alojamiento en eco-lodge y avistamiento de fauna.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>

                            <div class="carousel-slide">
                                <div class="tour-card">
                                    <div class="tour-image-container">
                                        <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=250&fit=crop" alt="Nazca y Paracas" class="tour-image">
                                        <div class="tour-overlay">
                                            <div class="tour-details">
                                                <span class="tour-price">$299</span>
                                                <span class="tour-days">2 días</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tour-info">
                                        <h3 class="tour-name">Nazca y Paracas</h3>
                                        <p class="tour-description">Vuela sobre las líneas de Nazca y visita la reserva de Paracas con sus playas rojas.</p>
                                        <button class="btn-more-info">ver más</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-controls">
                        <button class="carousel-btn prev" aria-label="Anterior">‹</button>
                        <button class="carousel-btn next" aria-label="Siguiente">›</button>
                    </div>
                </div>
            </div>
        </section>

    <footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-title">Explorando los Andes</h3>
                <p class="footer-description">
                    Experiencias auténticas en la cordillera más majestuosa de Sudamérica. 
                    Diseñamos viajes inolvidables con respeto por la cultura y el medio ambiente.
                </p>
            </div>
            
            <div class="footer-section">
                <h4 class="footer-heading">Contacto</h4>
                <ul class="footer-links">
                    <li><a href="tel:+51987654321">+51 987 654 321</a></li>
                    <li><a href="mailto:hola@explorandolosandes.com">hola@explorandolosandes.com</a></li>
                    <li>Calle Saphi 444, Cusco, Perú</li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4 class="footer-heading">Enlaces Rápidos</h4>
                <ul class="footer-links">
                    <li><a href="#itinerary">Itinerario</a></li>
                    <li><a href="#recommendations">Recomendaciones</a></li>
                    <li><a href="#includes">Incluye</a></li>
                    <li><a href="#meeting-points">Puntos de Encuentro</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4 class="footer-heading">Legal</h4>
                <ul class="footer-links">
                    <li><a href="#">Términos y Condiciones</a></li>
                    <li><a href="#">Política de Privacidad</a></li>
                    <li><a href="#">Seguro de Viaje</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="social-links">
                <a href="#" aria-label="Facebook">Facebook</a>
                <a href="#" aria-label="Instagram">Instagram</a>
                <a href="#" aria-label="WhatsApp">WhatsApp</a>
            </div>
            <p class="copyright">
                © 2024 Explorando los Andes. Todos los derechos reservados.
            </p>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/tours/assets/js/api.js"></script>
<script src="/tours/assets/js/detail.js"></script>
</body>
</html>