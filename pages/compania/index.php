<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config.php';

function seoSlug(string $value): string
{
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

$companyId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?? filter_input(INPUT_GET, 'Id', FILTER_VALIDATE_INT)
    ?? filter_input(INPUT_GET, 'company_id', FILTER_VALIDATE_INT)
    ?? 1;

$company = null;
$tours = [];
$reviews = [];
$vehicles = [];
$destinations = [];
$categories = [];

try {
    $pdo = database();

    // Consultar información de la compañía
    $stmt = $pdo->prepare('SELECT * FROM companias WHERE id = ? AND active = 1 LIMIT 1');
    $stmt->execute([$companyId]);
    $company = $stmt->fetch();

    if (!$company) {
        // Fallback a primera compañía activa si no se encuentra
        $fallbackStmt = $pdo->query('SELECT * FROM companias WHERE active = 1 ORDER BY id ASC LIMIT 1');
        $company = $fallbackStmt->fetch();
        if ($company) {
            $companyId = (int) $company['id'];
        }
    }

    if ($company) {
        // Consultar tours de la compañía
        $toursStmt = $pdo->prepare('SELECT * FROM tours WHERE company_id = ? AND active = 1 ORDER BY rating DESC, price ASC');
        $toursStmt->execute([$companyId]);
        $tours = $toursStmt->fetchAll();

        // Consultar reseñas de la compañía
        $revStmt = $pdo->prepare('SELECT * FROM company_reviews WHERE company_id = ? ORDER BY created_at DESC');
        $revStmt->execute([$companyId]);
        $reviews = $revStmt->fetchAll();

        // Consultar vehículos y fotos
        $vehStmt = $pdo->prepare('SELECT * FROM vehicles WHERE company_id = ? AND active = 1 ORDER BY brand, model, id');
        $vehStmt->execute([$companyId]);
        $vehicles = $vehStmt->fetchAll();

        foreach ($vehicles as &$veh) {
            $photoStmt = $pdo->prepare('SELECT * FROM vehicle_photos WHERE vehicle_id = ? ORDER BY is_cover DESC, display_order ASC, id ASC');
            $photoStmt->execute([$veh['id']]);
            $veh['photos'] = $photoStmt->fetchAll();
        }
        unset($veh);

        // Extraer destinos y categorías únicas
        $destinations = array_values(array_unique(array_filter(array_column($tours, 'destination'))));
        $categories = array_values(array_unique(array_filter(array_column($tours, 'category'))));
    }
} catch (Throwable $e) {
    // Si hay error en la base de datos, conservar valores vacíos
}

$companyName = $company['trade_name'] ?? $company['legal_name'] ?? 'Compañía de Tours';
$companyDescription = $company['description'] ?? 'Especialistas en tours, escapadas de fin de semana y experiencias inolvidables.';
$companyRating = number_format((float) ($company['rating'] ?? 4.8), 1);
$companyCity = $company['city'] ?? 'Cusco';
$companyCountry = $company['country'] ?? 'Perú';
$companyLocation = trim($companyCity . ', ' . $companyCountry, ', ');
$companyPhone = $company['phone'] ?? '+51 987 654 321';
$companyEmail = $company['email'] ?? 'contacto@' . seoSlug($companyName) . '.com';
$companyWebsite = $company['website'] ?? 'https://www.wekender.mx';
$companyLogo = $company['logo_url'] ?? '';

$seoTitle = $companyName . ' - Tours, Excursiones y Experiencias | Weekender';
$seoDescription = 'Conoce los mejores tours, itinerarios y flota de transporte de ' . $companyName . '. ' . substr($companyDescription, 0, 140) . '...';
$seoImage = !empty($tours[0]['hero_image_url']) ? $tours[0]['hero_image_url'] : 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=1200&h=630&fit=crop';
$seoUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/tours/compania-de-tours/' . seoSlug($companyName) . '/' . ($company['id'] ?? 1);

$seoStructuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'TravelAgency',
    'name' => $companyName,
    'legalName' => $company['legal_name'] ?? $companyName,
    'description' => $companyDescription,
    'url' => $seoUrl,
    'telephone' => $companyPhone,
    'email' => $companyEmail,
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $company['address'] ?? '',
        'addressLocality' => $companyCity,
        'addressCountry' => $companyCountry
    ],
    'aggregateRating' => [
        '@type' => 'AggregateRating',
        'ratingValue' => $companyRating,
        'reviewCount' => max(1, count($reviews))
    ]
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($seoUrl, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Open Graph & Social -->
    <meta property="og:type" content="business.business">
    <meta property="og:locale" content="es_ES">
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seoUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seoImage, ENT_QUOTES, 'UTF-8') ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($seoImage, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
        <?= json_encode($seoStructuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    </script>

    <!-- Google Fonts & Stylesheets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/tours/assets/css/theme.css">
    <link rel="stylesheet" href="/tours/assets/css/detail.css">
    <link rel="stylesheet" href="/tours/assets/css/company.css">
</head>

<body>



    <!-- Header & Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="/tours/" style="text-decoration: none; color: inherit;">
                    <h1 style="display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                        <span>✨</span>
                        <span>Weekender</span>
                    </h1>
                </a>
            </div>
            <div class="nav-menu">
                <a href="#tours" class="nav-link">Mejores Tours</a>
                <?php if (!empty($vehicles)): ?>
                    <a href="#fleet" class="nav-link">Flota</a>
                <?php endif; ?>
                <a href="#reviews" class="nav-link">Reseñas</a>
                <a href="#contact" class="nav-link">Contacto</a>
                <button class="btn btn--primary" style="background: var(--primary); color: #fff; border-radius: 999px;">Iniciar sesión</button>
            </div>
            <div class="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Company Hero Section -->
    <header class="company-hero">
        <div class="company-hero__overlay"></div>
        <div class="company-hero__container">
            <div class="company-hero__header">
                <div class="company-hero__logo-box">
                    <?php if (!empty($companyLogo)): ?>
                        <img src="<?= htmlspecialchars($companyLogo, ENT_QUOTES, 'UTF-8') ?>" alt="Logo <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?>">
                    <?php else: ?>
                        <div class="logo-placeholder">
                            <?= strtoupper(substr($companyName, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="company-hero__details">
                    <div class="company-hero__badges">
                        <span class="badge--verified">✓ Operador Verificado</span>
                        <span class="badge--rating">★ <?= $companyRating ?> / 5.0 (<?= count($reviews) ?> reseñas)</span>
                        <span class="badge--rating" style="color: #ffffff;"><?= count($tours) ?> Experiencias Activas</span>
                    </div>

                    <h1 class="company-hero__title"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></h1>

                    <?php if (!empty($company['legal_name']) && $company['legal_name'] !== $companyName): ?>
                        <p class="company-hero__legal"><?= htmlspecialchars($company['legal_name'], ENT_QUOTES, 'UTF-8') ?> &bull; Razón Social</p>
                    <?php endif; ?>

                    <div class="company-hero__contact-chips">
                        <?php if ($companyLocation): ?>
                            <span class="contact-chip">📍 <?= htmlspecialchars($companyLocation, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                        <?php if ($companyPhone): ?>
                            <a href="tel:<?= htmlspecialchars($companyPhone, ENT_QUOTES, 'UTF-8') ?>" class="contact-chip">📞 <?= htmlspecialchars($companyPhone, ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                        <?php if ($companyEmail): ?>
                            <a href="mailto:<?= htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') ?>" class="contact-chip">✉️ <?= htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Search & Filter Container for Company Tours -->
    <section class="company-search-wrapper">
        <div class="company-search-card">
            <div class="company-search-card__header">
                <div class="company-search-card__title">
                    <span>🔍</span>
                    <span>Explora las salidas de <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="company-search-card__subtitle" id="filteredToursCount">
                    <?= count($tours) ?> tours disponibles
                </div>
            </div>

            <form class="company-search-form" id="companySearchForm">
                <div class="search-field">
                    <label for="companySearchDestination">Destino o Nombre</label>
                    <input
                        type="text"
                        id="companySearchDestination"
                        placeholder="Ej. Machu Picchu, Valle Sagrado..."
                        list="companyDestinationsList"
                        autocomplete="off">
                    <datalist id="companyDestinationsList">
                        <?php foreach ($destinations as $dest): ?>
                            <option value="<?= htmlspecialchars($dest, ENT_QUOTES, 'UTF-8') ?>">
                            <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="search-field">
                    <label for="companySearchCategory">Categoría</label>
                    <select id="companySearchCategory">
                        <option value="all">Todas las categorías</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($cat), ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="search-field">
                    <label for="companySearchPrice">Precio Máximo</label>
                    <select id="companySearchPrice">
                        <option value="all">Cualquier presupuesto</option>
                        <option value="150">Hasta $150 USD</option>
                        <option value="500">Hasta $500 USD</option>
                        <option value="1000">Hasta $1,000 USD</option>
                        <option value="2000">Hasta $2,000 USD</option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn-search-apply">
                        <span>Buscar</span>
                    </button>
                    <button type="button" id="companySearchClear" class="btn btn-outline" style="border-radius: 12px; height: 46px; padding: 0 1rem;" title="Limpiar filtros">
                        ✕
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Company Key Stats Section -->
    <section class="company-stats-section">
        <div class="company-stats-grid">
            <div class="company-stat-card">
                <div class="company-stat-card__icon">🌟</div>
                <div class="company-stat-card__value"><?= $companyRating ?></div>
                <div class="company-stat-card__label">Calificación de Servicio</div>
            </div>

            <div class="company-stat-card">
                <div class="company-stat-card__icon">🎒</div>
                <div class="company-stat-card__value"><?= count($tours) ?></div>
                <div class="company-stat-card__label">Tours & Experiencias</div>
            </div>

            <div class="company-stat-card">
                <div class="company-stat-card__icon">🚌</div>
                <div class="company-stat-card__value"><?= max(1, count($vehicles)) ?></div>
                <div class="company-stat-card__label">Vehículos en Flota</div>
            </div>

            <div class="company-stat-card">
                <div class="company-stat-card__icon">📍</div>
                <div class="company-stat-card__value"><?= max(1, count($destinations)) ?></div>
                <div class="company-stat-card__label">Destinos Principales</div>
            </div>
        </div>
    </section>

    <!-- About Company Section -->
    <section class="company-about-section" id="about">
        <div class="about-card">
            <div class="section-eyebrow">Sobre el Operador</div>
            <h2 class="section-heading">Pasión por crear recuerdos que duran toda la vida</h2>
            <p class="about-text">
                <?= nl2br(htmlspecialchars($companyDescription, ENT_QUOTES, 'UTF-8')) ?>
            </p>

            <div class="values-grid">
                <div class="value-item">
                    <div class="value-item__icon">🧭</div>
                    <div>
                        <h4 class="value-item__title">Guías Locales Certificados</h4>
                        <p class="value-item__desc">Expertos bilingües con conocimiento profundo del territorio e historia.</p>
                    </div>
                </div>

                <div class="value-item">
                    <div class="value-item__icon">🛡️</div>
                    <div>
                        <h4 class="value-item__title">Seguridad & Confianza</h4>
                        <p class="value-item__desc">Protocolos de emergencia, botiquín médico y unidades con mantenimiento al día.</p>
                    </div>
                </div>

                <div class="value-item">
                    <div class="value-item__icon">✨</div>
                    <div>
                        <h4 class="value-item__title">Grupos Reducidos</h4>
                        <p class="value-item__desc">Atención personalizada con mayor confort y tiempo para disfrutar cada parada.</p>
                    </div>
                </div>

                <div class="value-item">
                    <div class="value-item__icon">🌿</div>
                    <div>
                        <h4 class="value-item__title">Turismo Responsable</h4>
                        <p class="value-item__desc">Compromiso activo con la sostenibilidad y las comunidades anfitrionas.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Best Tours of Company Section -->
    <section class="company-tours-section" id="tours">
        <div class="tours-section__header">
            <div>
                <div class="section-eyebrow">Catálogo Exclusivo</div>
                <h2 class="section-heading" style="margin-bottom: 0;">Mejores Tours de <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></h2>
            </div>

            <div class="category-filters">
                <button class="cat-filter-btn active" data-category="all">Todos (<?= count($tours) ?>)</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="cat-filter-btn" data-category="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($cat), ENT_QUOTES, 'UTF-8') ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="company-tours-grid" id="companyToursGrid">
            <?php if (!empty($tours)): ?>
                <?php foreach ($tours as $tour): ?>
                    <?php
                    $tourImg = !empty($tour['hero_image_url']) ? $tour['hero_image_url'] : (!empty($tour['image_url']) ? $tour['image_url'] : 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=600&h=400&fit=crop');
                    $tourSlug = seoSlug($tour['name']);
                    $compSlug = seoSlug($companyName);
                    $tourDetailUrl = "/tours/{$compSlug}/{$tourSlug}/{$tour['id']}";
                    $tourRating = number_format((float) ($tour['rating'] ?? 4.8), 1);
                    ?>
                    <article class="tour-card-item"
                        data-destination="<?= htmlspecialchars($tour['destination'], ENT_QUOTES, 'UTF-8') ?>"
                        data-category="<?= htmlspecialchars($tour['category'], ENT_QUOTES, 'UTF-8') ?>"
                        data-price="<?= htmlspecialchars((string) $tour['price'], ENT_QUOTES, 'UTF-8') ?>"
                        data-name="<?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="tour-card-item__media">
                            <img src="<?= htmlspecialchars($tourImg, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <?php if (!empty($tour['badge'])): ?>
                                <span class="tour-badge-tag"><?= htmlspecialchars($tour['badge'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                            <span class="tour-price-badge">$<?= number_format((float) $tour['price'], 0) ?> USD</span>
                        </div>

                        <div class="tour-card-item__body">
                            <div class="tour-card-meta">
                                <span class="tour-meta-item">📍 <?= htmlspecialchars($tour['destination'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span>&bull;</span>
                                <span class="tour-meta-item">⏱️ <?= htmlspecialchars($tour['duration'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if (!empty($tour['category'])): ?>
                                    <span>&bull;</span>
                                    <span class="tour-meta-item">🏷️ <?= htmlspecialchars(ucfirst($tour['category']), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </div>

                            <h3 class="tour-card-title"><?= htmlspecialchars($tour['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="tour-card-desc"><?= htmlspecialchars($tour['description'], ENT_QUOTES, 'UTF-8') ?></p>

                            <div class="tour-card-footer">
                                <div class="tour-rating-stars">
                                    <span>★</span>
                                    <span><?= $tourRating ?></span>
                                </div>
                                <a href="<?= htmlspecialchars($tourDetailUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-view-tour">
                                    <span>Ver detalles</span>
                                    <span>→</span>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="no-tours-found" id="noToursFound" style="display: <?= empty($tours) ? 'block' : 'none' ?>;">
                <div class="no-tours-found__icon">🔍</div>
                <h4>No se encontraron tours para esta búsqueda</h4>
                <p>Prueba ajustando el destino, la categoría o el rango de precio seleccionado.</p>
            </div>
        </div>
    </section>

    <!-- Company Fleet Section -->
    <?php if (!empty($vehicles)): ?>
        <section class="company-fleet-section" id="fleet">
            <div class="section-eyebrow">Flota & Confort</div>
            <h2 class="section-heading">Nuestras unidades de transporte</h2>
            <p style="color: var(--muted); font-size: 0.95rem;">Vehículos modernos equipados para garantizar un trayecto cómodo, panorámico y seguro en cada expedición.</p>

            <div class="fleet-grid">
                <?php foreach ($vehicles as $vehicle): ?>
                    <?php
                    $coverPhoto = !empty($vehicle['photos'][0]['image_url'])
                        ? $vehicle['photos'][0]['image_url']
                        : 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=600&h=400&fit=crop';
                    ?>
                    <div class="vehicle-card-modern">
                        <div class="vehicle-card-modern__img">
                            <img src="<?= htmlspecialchars($coverPhoto, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            <span class="vehicle-status-tag">Unidad Verificada</span>
                        </div>
                        <div class="vehicle-card-modern__body">
                            <h4 class="vehicle-card-modern__title"><?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <div style="font-size: 0.82rem; color: var(--muted); margin-bottom: 0.75rem;">
                                Año <?= htmlspecialchars((string) ($vehicle['vehicle_year'] ?? '2023'), ENT_QUOTES, 'UTF-8') ?> &bull; Placa <?= htmlspecialchars($vehicle['license_plate'], ENT_QUOTES, 'UTF-8') ?>
                            </div>

                            <div class="vehicle-features-list">
                                <span class="vehicle-feature-badge">💺 <?= htmlspecialchars((string) $vehicle['seat_capacity'], ENT_QUOTES, 'UTF-8') ?> Asientos reclinables</span>
                                <?php if (!empty($vehicle['accessible_seats']) && $vehicle['accessible_seats'] > 0): ?>
                                    <span class="vehicle-feature-badge">♿ <?= $vehicle['accessible_seats'] ?> Asientos accesibles</span>
                                <?php endif; ?>
                                <?php if (!empty($vehicle['luggage_capacity'])): ?>
                                    <span class="vehicle-feature-badge">🧳 <?= htmlspecialchars($vehicle['luggage_capacity'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                                <span class="vehicle-feature-badge">❄️ Aire Acondicionado</span>
                                <span class="vehicle-feature-badge">📶 Wi-Fi a bordo</span>
                            </div>

                            <?php if (!empty($vehicle['notes'])): ?>
                                <p style="font-size: 0.85rem; color: var(--muted); margin-top: 0.5rem; font-style: italic;">
                                    "<?= htmlspecialchars($vehicle['notes'], ENT_QUOTES, 'UTF-8') ?>"
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Company Reviews Section -->
    <section class="company-reviews-section" id="reviews">
        <div class="section-eyebrow">Opiniones Reales</div>
        <h2 class="section-heading">Lo que dicen los viajeros</h2>

        <div class="reviews-summary-card">
            <div class="reviews-score-block">
                <div class="reviews-score-big"><?= $companyRating ?></div>
                <div>
                    <div class="reviews-score-label">Experiencia Sobresaliente</div>
                    <div style="color: #ffde59; font-size: 1.1rem; letter-spacing: 2px; margin-bottom: 0.2rem;">★★★★★</div>
                    <div class="reviews-score-count">Basado en <?= max(1, count($reviews)) ?> opiniones verificadas de pasajeros</div>
                </div>
            </div>
            <div>
                <a href="#contact" class="btn" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: #fff;">
                    Contactar con la agencia
                </a>
            </div>
        </div>

        <div class="reviews-cards-grid">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="review-item-card">
                        <div class="review-item-header">
                            <div class="reviewer-profile">
                                <div class="reviewer-avatar">
                                    <?= strtoupper(substr($rev['reviewer_name'] ?? 'V', 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="reviewer-name"><?= htmlspecialchars($rev['reviewer_name'] ?? 'Viajero Anónimo', ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="review-stars">
                                        <?= str_repeat('★', (int) ($rev['rating'] ?? 5)) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="review-comment">"<?= htmlspecialchars($rev['comment'] ?? '', ENT_QUOTES, 'UTF-8') ?>"</p>
                        <?php if (!empty($rev['created_at'])): ?>
                            <div class="review-date">Publicado el <?= date('d/m/Y', strtotime($rev['created_at'])) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Reseñas por defecto si aún no hay en la BD -->
                <div class="review-item-card">
                    <div class="review-item-header">
                        <div class="reviewer-profile">
                            <div class="reviewer-avatar">C</div>
                            <div>
                                <div class="reviewer-name">Camila Paredes</div>
                                <div class="review-stars">★★★★★</div>
                            </div>
                        </div>
                    </div>
                    <p class="review-comment">"La agencia respondió de inmediato y estuvo al pendiente de cada detalle antes y durante el recorrido. ¡100% recomendados!"</p>
                    <div class="review-date">Viajero Verificado</div>
                </div>

                <div class="review-item-card">
                    <div class="review-item-header">
                        <div class="reviewer-profile">
                            <div class="reviewer-avatar">J</div>
                            <div>
                                <div class="reviewer-name">Jorge Salazar</div>
                                <div class="review-stars">★★★★★</div>
                            </div>
                        </div>
                    </div>
                    <p class="review-comment">"Personal sumamente amable, transporte puntual y unidades muy cómodas. Los guías compartieron historias fascinantes."</p>
                    <div class="review-date">Viajero Verificado</div>
                </div>

                <div class="review-item-card">
                    <div class="review-item-header">
                        <div class="reviewer-profile">
                            <div class="reviewer-avatar">E</div>
                            <div>
                                <div class="reviewer-name">Elena Vargas</div>
                                <div class="review-stars">★★★★★</div>
                            </div>
                        </div>
                    </div>
                    <p class="review-comment">"Excelente organización de principio a fin. El mapa de asientos del bus facilitó elegir nuestro lugar preferido."</p>
                    <div class="review-date">Viajero Verificado</div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Company Contact & Info Section -->
    <section class="company-contact-section" id="contact">
        <div class="contact-card-box">
            <div>
                <div class="section-eyebrow">Atención al Cliente</div>
                <h2 class="section-heading" style="font-size: 1.85rem;">Ponte en contacto directo</h2>
                <p style="color: var(--muted); font-size: 0.95rem; line-height: 1.6;">
                    ¿Tienes dudas sobre una salida grupal, itinerario a medida o reservas corporativas? Nuestro equipo de asesores de viaje está disponible para asistirte.
                </p>

                <div class="contact-details-list">
                    <?php if (!empty($company['address']) || $companyLocation): ?>
                        <div class="contact-item-row">
                            <div class="contact-item-icon">📍</div>
                            <div class="contact-item-info">
                                <h5>Ubicación</h5>
                                <p><?= htmlspecialchars($company['address'] ? $company['address'] . ', ' . $companyLocation : $companyLocation, ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($companyPhone): ?>
                        <div class="contact-item-row">
                            <div class="contact-item-icon">📞</div>
                            <div class="contact-item-info">
                                <h5>Teléfono / Central</h5>
                                <a href="tel:<?= htmlspecialchars($companyPhone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($companyPhone, ENT_QUOTES, 'UTF-8') ?></a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($companyEmail): ?>
                        <div class="contact-item-row">
                            <div class="contact-item-icon">✉️</div>
                            <div class="contact-item-info">
                                <h5>Correo Electrónico</h5>
                                <a href="mailto:<?= htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') ?></a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($companyWebsite): ?>
                        <div class="contact-item-row">
                            <div class="contact-item-icon">🌐</div>
                            <div class="contact-item-info">
                                <h5>Sitio Web</h5>
                                <a href="<?= htmlspecialchars($companyWebsite, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($companyWebsite, ENT_QUOTES, 'UTF-8') ?></a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="contact-cta-box">
                <h4>¿Listo para tu próxima escapada?</h4>
                <p>Escríbenos directamente y te ayudamos a reservar tu asiento en la próxima fecha programada.</p>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $companyPhone) ?>?text=Hola,%20me%20gustaría%20solicitar%20información%20sobre%20los%20tours%20de%20<?= urlencode($companyName) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-direct">
                    <span>💬</span>
                    <span>Mensaje por WhatsApp</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title"><?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="footer-description">
                        <?= htmlspecialchars(substr($companyDescription, 0, 160), ENT_QUOTES, 'UTF-8') ?>...
                    </p>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Contacto</h4>
                    <ul class="footer-links">
                        <li><a href="tel:<?= htmlspecialchars($companyPhone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($companyPhone, ENT_QUOTES, 'UTF-8') ?></a></li>
                        <li><a href="mailto:<?= htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($companyEmail, ENT_QUOTES, 'UTF-8') ?></a></li>
                        <li><?= htmlspecialchars($companyLocation, ENT_QUOTES, 'UTF-8') ?></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Enlaces Rápidos</h4>
                    <ul class="footer-links">
                        <li><a href="/tours/">Inicio</a></li>
                        <li><a href="#about">Sobre Nosotros</a></li>
                        <li><a href="#tours">Mejores Tours</a></li>
                        <?php if (!empty($vehicles)): ?>
                            <li><a href="#fleet">Flota de Vehículos</a></li>
                        <?php endif; ?>
                        <li><a href="#reviews">Reseñas</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4 class="footer-heading">Garantía</h4>
                    <ul class="footer-links">
                        <li>Operador Certificado</li>
                        <li>Términos y Condiciones</li>
                        <li>Política de Privacidad</li>
                        <li>Seguro de Pasajeros</li>
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
                    &copy; <?= date('Y') ?> <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?>. Operado a través de Weekender.
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/tours/assets/js/api.js"></script>
    <script src="/tours/assets/js/company.js"></script>
</body>

</html>