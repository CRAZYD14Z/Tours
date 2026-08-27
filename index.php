<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viajero - Experiencias grupales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="nav__brand">
                <h1>viajero</h1>
            </div>
            
            <button class="nav__toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <div class="nav__menu">
                <button class="btn btn--ghost">Explorar</button>
                <button class="btn btn--ghost">Iniciar sesión</button>
                <button class="btn btn--primary">Crear cuenta</button>
            </div>
        </nav>
    </header>

    <main class="main">
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
                    <p class="hero__subtitle">Encuentra tours y actividades grupales</p>
                </div>

                <div class="search__container">
                    <form class="search__form">
                        <div class="search__group">
                            <label for="destination" class="search__label">Destino</label>
                            <input 
                                type="text" 
                                id="destination" 
                                class="search__input" 
                                placeholder="Ej: París, Tokio"
                                list="destinations"
                            >
                            <datalist id="destinations">
                                <option value="París">
                                <option value="Tokio">
                                <option value="Barcelona">
                                <option value="Madrid">
                                <option value="Roma">
                                <option value="Londres">
                                <option value="Nueva York">
                                <option value="Amsterdam">
                                <option value="Berlín">
                                <option value="Lisboa">
                                <option value="Praga">
                                <option value="Budapest">
                                <option value="Viena">
                                <option value="Seúl">
                                <option value="Bangkok">
                            </datalist>
                        </div>
                        
                        <div class="search__group">
                            <label for="dateRange" class="search__label">Fechas</label>
                            <input 
                                type="text" 
                                id="dateRange" 
                                class="search__input search__input--date-range" 
                                placeholder="Selecciona rango de fechas"
                                readonly
                            >
                        </div>

                        <button type="submit" class="btn btn--primary">
                            <span>Buscar</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section class="results">
            <div class="results__header">
                <h3 class="results__title results__title--enhanced">
                    <span class="results__title-main">Vive la experiencia,</span>
                    <span class="results__title-sub">no solo el destino</span>
                </h3>
                <p class="results__location-label">
                    Destino actual: <span class="results__location">Barcelona</span>
                </p>
                <div class="results__filters">
                    <button class="filter-btn active">Todos</button>
                    <button class="filter-btn">Cultura</button>
                    <button class="filter-btn">Aventura</button>
                    <button class="filter-btn">Gastronomía</button>
                </div>
            </div>

            <div class="results__grid">
                <div class="tour-card">
                    <div class="tour-image-container">
                        <img src="https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=400&h=250&fit=crop" alt="Tour Gaudí" class="tour-image">
                        <div class="tour-overlay">
                            <div class="tour-details">
                                <span class="tour-price">45 €</span>
                                <span class="tour-days">4h</span>
                            </div>
                        </div>
                    </div>
                    <div class="tour-info">
                        <h3 class="tour-name">Tour privado Gaudí</h3>
                        <p class="tour-description">Explora las obras maestras de Gaudí con guía experto local</p>
                        <button class="btn-more-info" onclick="location.href='pages/detalle/index.php'">ver más</button>
                    </div>
                </div>

                <div class="tour-card">
                    <div class="tour-image-container">
                        <img src="https://images.unsplash.com/photo-1551218808-94e220e084d2?w=400&h=250&fit=crop" alt="Clase de paella" class="tour-image">
                        <div class="tour-overlay">
                            <div class="tour-details">
                                <span class="tour-price">65 €</span>
                                <span class="tour-days">3.5h</span>
                            </div>
                        </div>
                    </div>
                    <div class="tour-info">
                        <h3 class="tour-name">Clase paella y tapas</h3>
                        <p class="tour-description">Aprende a cocinar auténtica paella valenciana</p>
                        <button class="btn-more-info">ver más</button>
                    </div>
                </div>

                <div class="tour-card">
                    <div class="tour-image-container">
                        <img src="https://images.unsplash.com/photo-1579952363873-27d3bfad9c0d?w=400&h=250&fit=crop" alt="Montserrat" class="tour-image">
                        <div class="tour-overlay">
                            <div class="tour-details">
                                <span class="tour-price">89 €</span>
                                <span class="tour-days">Día completo</span>
                            </div>
                        </div>
                    </div>
                    <div class="tour-info">
                        <h3 class="tour-name">Excursión Montserrat</h3>
                        <p class="tour-description">Visita el monasterio y disfruta de vistas espectaculares</p>
                        <button class="btn-more-info">ver más</button>
                    </div>
                </div>

                <div class="tour-card">
                    <div class="tour-image-container">
                        <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=400&h=250&fit=crop" alt="Bike tour" class="tour-image">
                        <div class="tour-overlay">
                            <div class="tour-details">
                                <span class="tour-price">35 €</span>
                                <span class="tour-days">2.5h</span>
                            </div>
                        </div>
                    </div>
                    <div class="tour-info">
                        <h3 class="tour-name">Bike tour Barrio Gótico</h3>
                        <p class="tour-description">Recorre los rincones más emblemáticos en bicicleta</p>
                        <button class="btn-more-info">ver más</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="features__header">
                <p class="eyebrow">Por qué elegir viajero</p>
                <h3>Una experiencia clara, segura y memorable</h3>
            </div>

            <div class="features__grid">
                <article class="feature-card">
                    <div class="feature-card__icon">✦</div>
                    <h4>Curación premium</h4>
                    <p>Actividades seleccionadas por calidad, valor y autenticidad.</p>
                </article>

                <article class="feature-card">
                    <div class="feature-card__icon">✓</div>
                    <h4>Reservas sencillas</h4>
                    <p>Compare fechas, elige tu experiencia y confirma en minutos.</p>
                </article>

                <article class="feature-card">
                    <div class="feature-card__icon">◎</div>
                    <h4>Grupos pequeños</h4>
                    <p>Más conexión, atención personalizada y una experiencia cercana.</p>
                </article>
            </div>
        </section>

        <section class="process">
            <div class="process__header">
                <p class="eyebrow">Cómo funciona</p>
                <h3>Reserva en tres pasos</h3>
            </div>

            <div class="process__grid">
                <div class="process-step">
                    <span class="process-step__number">01</span>
                    <h4>Busca</h4>
                    <p>Explora destinos y filtra por estilo de viaje, fecha y vibe.</p>
                </div>

                <div class="process-step">
                    <span class="process-step__number">02</span>
                    <h4>Compara</h4>
                    <p>Revisa disponibilidad, precios y servicios incluidos antes de reservar.</p>
                </div>

                <div class="process-step">
                    <span class="process-step__number">03</span>
                    <h4>Confirma</h4>
                    <p>Guarda tu lugar y prepárate para vivir una experiencia inolvidable.</p>
                </div>
            </div>
        </section>

        <section class="cta-banner">
            <div class="cta-banner__content">
                <p class="eyebrow eyebrow--light">Próxima salida</p>
                <h3>Descubre tu próximo viaje con gente que comparte tu energía</h3>
                <button class="btn btn--primary btn--large">Explorar experiencias</button>
            </div>
        </section>

        <section class="reviews">
            <div class="reviews__container">
                <h3 class="reviews__title">Lo que dicen nuestros viajeros</h3>
                
                <div class="reviews__grid">
                    <div class="review-card">
                        <div class="review-card__content">
                            <p class="review-card__text">"Una experiencia inolvidable en Barcelona. El tour de Gaudí fue perfecto y el grupo muy divertido."</p>
                            <div class="review-card__author">
                                <span class="review-card__name">María García</span>
                                <span class="review-card__date">Enero 2024</span>
                            </div>
                        </div>
                    </div>

                    <div class="review-card">
                        <div class="review-card__content">
                            <p class="review-card__text">"La clase de paella superó mis expectativas. Aprendí mucho y conocí gente maravillosa."</p>
                            <div class="review-card__author">
                                <span class="review-card__name">Carlos López</span>
                                <span class="review-card__date">Diciembre 2023</span>
                            </div>
                        </div>
                    </div>

                    <div class="review-card">
                        <div class="review-card__content">
                            <p class="review-card__text">"Montserrat fue espectacular. Las vistas impresionantes y la organización impecable."</p>
                            <div class="review-card__author">
                                <span class="review-card__name">Ana Martínez</span>
                                <span class="review-card__date">Noviembre 2023</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="footer__container">
            <div class="footer__brand">
                <h3>viajero</h3>
                <p>Descubre experiencias inolvidables en todo el mundo con grupos de viajeros como tú.</p>
            </div>
            
            <div class="footer__links">
                <div class="footer__section">
                    <h4>Explorar</h4>
                    <a href="#">Destinos</a>
                    <a href="#">Tours</a>
                    <a href="#">Experiencias</a>
                    <a href="#">Grupos</a>
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
                    <a href="#">Cómo funciona</a>
                    <a href="#">Hazte anfitrión</a>
                    <a href="#">Blog</a>
                </div>
            </div>
        </div>
        
        <div class="footer__bottom">
            <p>&copy; 2024 viajero. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/api.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>