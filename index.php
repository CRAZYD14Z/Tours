<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function seoSlug(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

$companies = [];
$tours = [];
$destinations = [];
$categories = [];
$globalReviews = [];

try {
    $pdo = database();

    // 1. Obtener todas las compañías activas
    $compStmt = $pdo->query('
        SELECT c.id, c.trade_name, c.legal_name, c.logo_url, c.city, c.country, c.rating, c.description,
               (SELECT COUNT(*) FROM tours t WHERE t.company_id = c.id AND t.active = 1) AS tours_count
        FROM companias c
        WHERE c.active = 1
        ORDER BY c.rating DESC, c.id ASC
    ');
    $companies = $compStmt->fetchAll();

    // 2. Obtener todos los tours activos con información de la compañía
    $toursStmt = $pdo->query('
        SELECT t.*, c.id AS company_id, c.trade_name AS company_name, c.rating AS company_rating
        FROM tours t
        INNER JOIN companias c ON c.id = t.company_id
        WHERE t.active = 1 AND c.active = 1
        ORDER BY t.rating DESC, t.id DESC
    ');
    $tours = $toursStmt->fetchAll();

    // 3. Obtener destinos y categorías únicos
    $destStmt = $pdo->query('SELECT DISTINCT destination FROM tours WHERE active = 1 ORDER BY destination ASC');
    $destinations = $destStmt->fetchAll(PDO::FETCH_COLUMN);

    $catStmt = $pdo->query('SELECT DISTINCT category FROM tours WHERE active = 1 AND category IS NOT NULL AND category <> \'\' ORDER BY category ASC');
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

    // 4. Obtener reseñas globales (de compañías y de tours)
    $revStmt = $pdo->query('
        (SELECT cr.id, cr.reviewer_name, cr.rating, cr.comment, cr.created_at, c.trade_name AS source_name, "company" AS review_type, c.id AS source_id
         FROM company_reviews cr
         INNER JOIN companias c ON c.id = cr.company_id
         WHERE c.active = 1)
        UNION
        (SELECT tr.id, tr.reviewer_name, tr.rating, tr.comment, tr.created_at, t.name AS source_name, "tour" AS review_type, t.id AS source_id
         FROM tour_reviews tr
         INNER JOIN tours t ON t.id = tr.tour_id
         INNER JOIN companias c ON c.id = t.company_id
         WHERE t.active = 1 AND c.active = 1)
        ORDER BY created_at DESC
        LIMIT 6
    ');
    $globalReviews = $revStmt->fetchAll();
} catch (Throwable $e) {
    // Si la BD no está disponible, se mantienen arrays vacíos
}

$totalTours = count($tours);
$totalCompanies = count($companies);
$totalDestinations = count($destinations);
$avgRating = 4.8;
if (!empty($tours)) {
    $ratings = array_filter(array_column($tours, 'rating'));
    if (!empty($ratings)) {
        $avgRating = round(array_sum($ratings) / count($ratings), 1);
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekender - Experiencias grupales y tours de fin de semana</title>
    <meta name="description" content="Descubre y reserva tours de fin de semana, escapadas grupales y excursiones con los mejores operadores turísticos locales.">
    <link rel="canonical" href="http://<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost', ENT_QUOTES, 'UTF-8') ?>/tours/">

    <!-- Fonts & Bootstrap -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/tours/assets/css/theme.css">
    <link rel="stylesheet" href="/tours/assets/css/styles.css">
</head>

<body>
    <header class="header">
        <nav class="nav">
            <div class="nav__brand">
                <a href="/tours/" style="text-decoration: none; color: inherit;">
                    <h1 style="display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                        <span>✨</span>
                        <span>Weekender</span>
                    </h1>
                </a>
            </div>

            <button class="nav__toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="nav__menu">
                <a href="#tours-catalog" class="btn btn--ghost">Explorar Tours</a>
                <a href="#operators" class="btn btn--ghost">Operadores</a>
                <a href="#global-reviews" class="btn btn--ghost">Reseñas</a>
                <a href="#how-it-works" class="btn btn--ghost">Cómo funciona</a>
                <button class="btn btn--primary" style="background: var(--primary); color: #fff; border-radius: 999px;">Iniciar sesión</button>
            </div>
        </nav>
    </header>

    <main class="main">
        <!-- Hero Search Section -->
        <section class="hero-search">
            <div class="hero-search__background">
                <div class="hero-search__bg-carousel">
                    <img src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1920&h=1080&fit=crop" alt="Travel background 1" class="hero-search__bg-image active">
                    <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&h=1080&fit=crop" alt="Travel background 2" class="hero-search__bg-image">
                    <img src="https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=1920&h=1080&fit=crop" alt="Travel background 3" class="hero-search__bg-image">
                    <img src="https://images.unsplash.com/photo-1519451241324-20b4ea2c4220?w=1920&h=1080&fit=crop" alt="Travel background 4" class="hero-search__bg-image">
                    <img src="https://images.unsplash.com/photo-1508672019048-805c876b67e2?w=1920&h=1080&fit=crop" alt="Travel background 5" class="hero-search__bg-image">
                </div>
                <div class="hero-search__overlay"></div>
            </div>

            <div class="hero-search__content">
                <div class="hero__content">
                    <h2 class="hero__title">Descubre experiencias inolvidables</h2>
                    <p class="hero__subtitle">Encuentra y reserva salidas grupales con los mejores operadores certificados</p>
                </div>

                <div class="search__container">
                    <form class="search__form">
                        <div class="search__group">
                            <label for="destination" class="search__label">Destino</label>
                            <input
                                type="text"
                                id="destination"
                                class="search__input"
                                placeholder="Ej: Cusco, Valle Sagrado..."
                                list="destinations"
                                autocomplete="off">
                            <datalist id="destinations">
                                <?php if (!empty($destinations)): ?>
                                    <?php foreach ($destinations as $dest): ?>
                                        <option value="<?= htmlspecialchars($dest, ENT_QUOTES, 'UTF-8') ?>">
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="Cusco y Machu Picchu">
                                        <option value="Valle Sagrado">
                                        <option value="Soraypampa">
                                        <option value="Vinicunca">
                                        <option value="Cañón del Colca">
                                        <option value="Chinchero">
                                        <?php endif; ?>
                            </datalist>
                        </div>

                        <div class="search__group">
                            <label for="category" class="search__label">Categoría</label>
                            <select id="category" class="search__select">
                                <option value="all">Todas las categorías</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($cat), ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="search__group">
                            <label for="company" class="search__label">Operador</label>
                            <select id="company" class="search__select">
                                <option value="all">Todos los operadores</option>
                                <?php foreach ($companies as $comp): ?>
                                    <option value="<?= (int) $comp['id'] ?>"><?= htmlspecialchars($comp['trade_name'] ?? $comp['legal_name'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="search__group">
                            <label for="dateRange" class="search__label">Fechas</label>
                            <input
                                type="text"
                                id="dateRange"
                                class="search__input search__input--date-range"
                                placeholder="Seleccionar fechas"
                                readonly>
                        </div>

                        <button type="submit" class="btn-hero-search">
                            <span>🔍</span>
                            <span>Buscar</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Global Platform Key Stats -->
        <section class="global-stats-section">
            <div class="global-stats-grid">
                <div class="global-stat-card">
                    <div class="global-stat-card__num"><?= max(1, $totalTours) ?>+</div>
                    <div class="global-stat-card__label">Tours & Experiencias</div>
                </div>

                <div class="global-stat-card">
                    <div class="global-stat-card__num"><?= max(1, $totalCompanies) ?></div>
                    <div class="global-stat-card__label">Operadores Verificados</div>
                </div>

                <div class="global-stat-card">
                    <div class="global-stat-card__num">★ <?= $avgRating ?></div>
                    <div class="global-stat-card__label">Calificación de la Comunidad</div>
                </div>

                <div class="global-stat-card">
                    <div class="global-stat-card__num"><?= max(1, $totalDestinations) ?>+</div>
                    <div class="global-stat-card__label">Destinos Increíbles</div>
                </div>
            </div>
        </section>

        <!-- Verified Operators Directory Section -->
        <section class="companies-section" id="operators">
            <div class="section-header-centered">
                <div class="section-eyebrow">Agencias Certificadas</div>
                <h2 class="section-heading-lg">Nuestros Operadores de Tours</h2>
                <p class="section-subtitle-text">Conoce a las agencias locales que hacen posible cada aventura con transportes propios y guías certificados.</p>
            </div>

            <div class="companies-grid">
                <?php if (!empty($companies)): ?>
                    <?php foreach ($companies as $company): ?>
                        <?php
                        $cName = $company['trade_name'] ?? $company['legal_name'];
                        $cSlug = seoSlug($cName);
                        $cUrl = "/tours/compania-de-tours/{$cSlug}/{$company['id']}";
                        $cRating = number_format((float) ($company['rating'] ?? 4.8), 1);
                        $cLocation = trim(($company['city'] ?? '') . ', ' . ($company['country'] ?? ''), ', ');
                        ?>
                        <article class="company-card-home">
                            <div class="company-card-home__top">
                                <div class="company-card-home__logo">
                                    <?php if (!empty($company['logo_url'])): ?>
                                        <img src="<?= htmlspecialchars($company['logo_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <?= strtoupper(substr($cName, 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="company-card-home__name"><?= htmlspecialchars($cName, ENT_QUOTES, 'UTF-8') ?></h3>
                                    <div class="company-card-home__meta">
                                        <span style="color: #f59e0b; font-weight: 600;">★ <?= $cRating ?></span>
                                        <?php if ($cLocation): ?>
                                            <span>&bull;</span>
                                            <span>📍 <?= htmlspecialchars($cLocation, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <p class="company-card-home__desc">
                                <?= htmlspecialchars($company['description'] ?? 'Especialistas en rutas de trekking, salidas culturales y experiencias de fin de semana.', ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <div class="company-card-home__footer">
                                <span style="font-size: 0.82rem; color: #666; font-weight: 500;">
                                    🎒 <?= (int) ($company['tours_count'] ?? 0) ?> tours activos
                                </span>
                                <a href="<?= htmlspecialchars($cUrl, ENT_QUOTES, 'UTF-8') ?>" class="company-card-home__btn">
                                    <span>Ver operador</span>
                                    <span>→</span>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Tours Catalog Section -->
        <section class="results" id="tours-catalog">
            <div class="results__header">
                <h3 class="results__title results__title--enhanced">
                    <span class="results__title-main">Vive la experiencia,</span>
                    <span class="results__title-sub">no solo el destino</span>
                </h3>
                <p class="results__location-label">
                    Destino actual: <span class="results__location">Todos los destinos</span>
                </p>
                <div class="results__filters">
                    <button class="filter-btn active" data-category="all">Todos</button>
                    <?php foreach ($categories as $cat): ?>
                        <button class="filter-btn" data-category="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($cat), ENT_QUOTES, 'UTF-8') ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="results__grid">
                <?php if (!empty($tours)): ?>
                    <?php foreach ($tours as $tour): ?>
                        <?php
                        $tCompSlug = seoSlug($tour['company_name']);
                        $tTourSlug = seoSlug($tour['name']);
                        $tTourUrl = "/tours/{$tCompSlug}/{$tTourSlug}/{$tour['id']}";
                        $tCompUrl = "/tours/compania-de-tours/{$tCompSlug}/{$tour['company_id']}";
                        $tImg = $tour['image_url'] ?? $tour['hero_image_url'] ?? 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=500&h=350&fit=crop';
                        $tRating = number_format((float) ($tour['rating'] ?? 4.8), 1);
                        ?>
                        <article class="tour-card" data-category="<?= htmlspecialchars($tour['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <div class="tour-image-container">
                                <img src="<?= htmlspecialchars($tImg, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?>" class="tour-image" loading="lazy">
                                <div class="tour-overlay">
                                    <div class="tour-details">
                                        <span class="tour-price">$<?= number_format((float) $tour['price'], 0) ?> USD</span>
                                        <span class="tour-days"><?= htmlspecialchars($tour['duration'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="tour-info">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <a href="<?= htmlspecialchars($tCompUrl, ENT_QUOTES, 'UTF-8') ?>" class="tour-company-link">🏢 <?= htmlspecialchars($tour['company_name'], ENT_QUOTES, 'UTF-8') ?></a>
                                    <span style="color: #f59e0b; font-size: 0.82rem; font-weight: 600;">★ <?= $tRating ?></span>
                                </div>
                                <h3 class="tour-name"><?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="tour-description"><?= htmlspecialchars($tour['description'], ENT_QUOTES, 'UTF-8') ?></p>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; border-top: 1px solid #f0ece7; padding-top: 0.75rem;">
                                    <span style="font-size: 0.78rem; color: #666;">📍 <?= htmlspecialchars($tour['destination'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <a class="btn-more-info" href="<?= htmlspecialchars($tTourUrl, ENT_QUOTES, 'UTF-8') ?>">ver más →</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Global Verified Reviews Section -->
        <section class="global-reviews-section" id="global-reviews">
            <div class="section-header-centered">
                <div class="section-eyebrow">Experiencias Verificadas</div>
                <h2 class="section-heading-lg">Opiniones de la Comunidad</h2>
                <p class="section-subtitle-text">Testimonios auténticos de viajeros que ya han disfrutado de salidas grupales con nuestros operadores.</p>
            </div>

            <div class="global-reviews-grid">
                <?php if (!empty($globalReviews)): ?>
                    <?php foreach ($globalReviews as $rev): ?>
                        <div class="global-review-card">
                            <div class="global-review-card__header">
                                <div class="global-reviewer">
                                    <div class="global-reviewer__avatar">
                                        <?= strtoupper(substr($rev['reviewer_name'] ?? 'V', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="global-reviewer__name"><?= htmlspecialchars($rev['reviewer_name'] ?? 'Viajero Verificado', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="global-review-card__stars"><?= str_repeat('★', (int) ($rev['rating'] ?? 5)) ?></div>
                                    </div>
                                </div>
                            </div>
                            <p class="global-review-card__text">"<?= htmlspecialchars($rev['comment'] ?? '', ENT_QUOTES, 'UTF-8') ?>"</p>
                            <div class="global-review-card__source">
                                <span><?= $rev['review_type'] === 'company' ? '🏢 Operador:' : '🎒 Tour:' ?></span>
                                <strong><?= htmlspecialchars($rev['source_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="global-review-card">
                        <div class="global-review-card__header">
                            <div class="global-reviewer">
                                <div class="global-reviewer__avatar">L</div>
                                <div>
                                    <div class="global-reviewer__name">Lucía Herrera</div>
                                    <div class="global-review-card__stars">★★★★★</div>
                                </div>
                            </div>
                        </div>
                        <p class="global-review-card__text">"La caminata hacia la laguna fue increíble. La organización y puntualidad del transporte fueron excepcionales."</p>
                        <div class="global-review-card__source">
                            <span>🎒 Tour:</span>
                            <strong>Trekking Laguna Humantay</strong>
                        </div>
                    </div>

                    <div class="global-review-card">
                        <div class="global-review-card__header">
                            <div class="global-reviewer">
                                <div class="global-reviewer__avatar">C</div>
                                <div>
                                    <div class="global-reviewer__name">Camila Paredes</div>
                                    <div class="global-review-card__stars">★★★★★</div>
                                </div>
                            </div>
                        </div>
                        <p class="global-review-card__text">"La agencia respondió rapidísimo y estuvo pendiente de cada detalle antes y durante el recorrido."</p>
                        <div class="global-review-card__source">
                            <span>🏢 Operador:</span>
                            <strong>Andes Raices</strong>
                        </div>
                    </div>

                    <div class="global-review-card">
                        <div class="global-review-card__header">
                            <div class="global-reviewer">
                                <div class="global-reviewer__avatar">M</div>
                                <div>
                                    <div class="global-reviewer__name">Mateo Rojas</div>
                                    <div class="global-review-card__stars">★★★★★</div>
                                </div>
                            </div>
                        </div>
                        <p class="global-review-card__text">"Los guías compartieron historias fascinantes y la selección de asientos en el bus hizo todo muy fácil."</p>
                        <div class="global-review-card__source">
                            <span>🏢 Operador:</span>
                            <strong>Horizonte Andino</strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Features & Process Section -->
        <section class="features">
            <div class="features__header">
                <p class="eyebrow">Por qué elegir viajero</p>
                <h3>Una experiencia clara, segura y memorable</h3>
            </div>

            <div class="features__grid">
                <article class="feature-card">
                    <div class="feature-card__icon">✦</div>
                    <h4>Curación premium</h4>
                    <p>Actividades seleccionadas por calidad, valor y autenticidad con operadores verificados.</p>
                </article>

                <article class="feature-card">
                    <div class="feature-card__icon">✓</div>
                    <h4>Mapa de asientos interactivo</h4>
                    <p>Elige tu asiento favorito directamente en el mapa del autobús antes de pagar.</p>
                </article>

                <article class="feature-card">
                    <div class="feature-card__icon">◎</div>
                    <h4>Grupos pequeños</h4>
                    <p>Más conexión, atención personalizada y una experiencia cercana con guías locales.</p>
                </article>
            </div>
        </section>

        <section class="process" id="how-it-works">
            <div class="process__header">
                <p class="eyebrow">Cómo funciona</p>
                <h3>Reserva en tres sencillos pasos</h3>
            </div>

            <div class="process__grid">
                <div class="process-step">
                    <span class="process-step__number">01</span>
                    <h4>Busca</h4>
                    <p>Explora destinos, filtra por categoría y encuentra a tu operador de confianza.</p>
                </div>

                <div class="process-step">
                    <span class="process-step__number">02</span>
                    <h4>Compara</h4>
                    <p>Revisa disponibilidad, precios, vehículos incluidos y reseñas antes de reservar.</p>
                </div>

                <div class="process-step">
                    <span class="process-step__number">03</span>
                    <h4>Confirma</h4>
                    <p>Guarda tu lugar con confirmación inmediata y prepárate para vivir tu aventura.</p>
                </div>
            </div>
        </section>

        <section class="cta-banner">
            <div class="cta-banner__content">
                <p class="eyebrow eyebrow--light">Próxima salida</p>
                <h3>Descubre tu próximo viaje con gente que comparte tu energía</h3>
                <a href="#tours-catalog" class="btn btn--primary btn--large" style="text-decoration: none;">Explorar experiencias</a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer__container">
            <div class="footer__brand">
                <h3>Weekender</h3>
                <p>Plataforma para descubrir escapadas de fin de semana, excursiones grupales y operadores turísticos certificados.</p>
            </div>

            <div class="footer__links">
                <div class="footer__section">
                    <h4>Explorar</h4>
                    <a href="#tours-catalog">Todos los Tours</a>
                    <a href="#operators">Operadores de Tours</a>
                    <a href="#global-reviews">Reseñas</a>
                </div>

                <div class="footer__section">
                    <h4>Soporte</h4>
                    <a href="#">Centro de ayuda</a>
                    <a href="#">Contacto</a>
                    <a href="#">Términos y condiciones</a>
                    <a href="#">Privacidad</a>
                </div>

                <div class="footer__section">
                    <h4>Sobre nosotros</h4>
                    <a href="#">Quiénes somos</a>
                    <a href="#how-it-works">Cómo funciona</a>
                    <a href="#">Publicar con nosotros</a>
                </div>
            </div>
        </div>

        <div class="footer__bottom">
            <p>&copy; <?= date('Y') ?> viajero. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/api.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>