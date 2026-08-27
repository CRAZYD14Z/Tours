// Navigation functionality
document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navLinks = document.querySelectorAll('.nav-link');
    const dropdownItems = document.querySelectorAll('.nav-item.dropdown');
    
    // Toggle mobile menu
    navToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        
        // Animate hamburger
        const spans = navToggle.querySelectorAll('span');
        spans[0].style.transform = navMenu.classList.contains('active') ? 'rotate(45deg) translate(5px, 5px)' : 'none';
        spans[1].style.opacity = navMenu.classList.contains('active') ? '0' : '1';
        spans[2].style.transform = navMenu.classList.contains('active') ? 'rotate(-45deg) translate(7px, -6px)' : 'none';
    });
    
    // Handle dropdown toggles on mobile
    dropdownItems.forEach(item => {
        const toggle = item.querySelector('.dropdown-toggle');
        toggle.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                item.classList.toggle('active');
            }
        });
    });
    
    // Close mobile menu on link click
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth > 768 || !link.classList.contains('dropdown-toggle')) {
                navMenu.classList.remove('active');
                const spans = navToggle.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
    });
    
    // Smooth scroll for navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            if (!link.classList.contains('dropdown-toggle')) {
                e.preventDefault();
                const targetId = link.getAttribute('href');
                const targetSection = document.querySelector(targetId);
                
                if (targetSection) {
                    const offsetTop = targetSection.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    
    // Handle dropdown links
    document.querySelectorAll('.dropdown-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
            
            // Close mobile menu if open
            if (window.innerWidth <= 768) {
                navMenu.classList.remove('active');
                const spans = navToggle.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
    });
    
    // Navbar scroll effect
    let lastScrollTop = 0;
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            navbar.style.transform = 'translateY(-100%)';
        } else {
            navbar.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });
});

// Update existing smooth scroll to account for navbar
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        if (!this.classList.contains('nav-link')) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offsetTop = target.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        }
    });
});

// Enhanced scroll indicator with progress
const updateScrollIndicator = () => {
    const scrolled = window.pageYOffset;
    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
    const progress = (scrolled / maxScroll) * 100;
    
    const scrollIndicator = document.querySelector('.scroll-indicator');
    if (scrollIndicator) {
        const opacity = Math.max(0, 1 - (scrolled / 300));
        scrollIndicator.style.opacity = opacity;
    }
};

// Smoother intersection observer with staggered animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            entry.target.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
        }
    });
}, observerOptions);

// Observe all sections for scroll animations
document.querySelectorAll('section').forEach(section => {
    section.style.opacity = '0';
    section.style.transform = 'translateY(30px)';
    section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(section);
});

// Enhanced parallax with performance optimization
let ticking = false;
const updateParallax = () => {
    if (!ticking) {
        requestAnimationFrame(() => {
            const scrolled = window.pageYOffset;
            const heroBackground = document.querySelector('.hero-background');
            if (heroBackground) {
                const rate = scrolled * -0.3;
                heroBackground.style.transform = `translateY(${rate}px)`;
            }
            ticking = false;
        });
        ticking = true;
    }
};

// Improved hover effects with subtle animations
document.querySelectorAll('.detail-card, .highlight-item').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px) scale(1.02)';
        this.style.transition = 'all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// Enhanced carousel functionality for more slides
class TourCarousel {
    constructor() {
        this.container = document.querySelector('.carousel-container');
        this.track = document.querySelector('.carousel-track');
        this.slides = document.querySelectorAll('.carousel-slide');
        this.prevBtn = document.querySelector('.carousel-btn.prev');
        this.nextBtn = document.querySelector('.carousel-btn.next');
        this.currentIndex = 0;
        this.slidesPerView = this.getSlidesPerView();
        
        this.init();
    }

    getSlidesPerView() {
        if (window.innerWidth <= 768) return 1;
        if (window.innerWidth <= 1024) return 2;
        return 3;
    }

    init() {
        this.updateCarousel();
        this.bindEvents();
        this.addResizeHandler();
    }

    bindEvents() {
        this.prevBtn.addEventListener('click', () => this.prev());
        this.nextBtn.addEventListener('click', () => this.next());
    }

    addResizeHandler() {
        window.addEventListener('resize', () => {
            const newSlidesPerView = this.getSlidesPerView();
            if (newSlidesPerView !== this.slidesPerView) {
                this.slidesPerView = newSlidesPerView;
                this.currentIndex = 0;
                this.updateCarousel();
            }
        });
    }

    next() {
        const maxIndex = Math.max(0, this.slides.length - this.slidesPerView);
        if (this.currentIndex < maxIndex) {
            this.currentIndex++;
            this.updateCarousel();
        }
    }

    prev() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.updateCarousel();
        }
    }

    updateCarousel() {
        const translateX = -(this.currentIndex * (100 / this.slidesPerView));
        this.track.style.transform = `translateX(${translateX}%)`;
        
        // Update button states
        this.prevBtn.style.opacity = this.currentIndex === 0 ? '0.5' : '1';
        this.prevBtn.style.cursor = this.currentIndex === 0 ? 'not-allowed' : 'pointer';
        
        const maxIndex = Math.max(0, this.slides.length - this.slidesPerView);
        this.nextBtn.style.opacity = this.currentIndex >= maxIndex ? '0.5' : '1';
        this.nextBtn.style.cursor = this.currentIndex >= maxIndex ? 'not-allowed' : 'pointer';
    }
}

// Minimal Carousel for booking sidebar
class MinimalCarousel {
    constructor() {
        this.container = document.querySelector('.minimal-carousel-container');
        if (!this.container) return;
        
        this.track = this.container.querySelector('.minimal-carousel-track');
        this.slides = this.container.querySelectorAll('.minimal-carousel-slide');
        this.dots = this.container.querySelectorAll('.minimal-dot');
        this.currentIndex = 0;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateSlide(0);
        this.startAutoPlay();
    }

    bindEvents() {
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                this.updateSlide(index);
            });
        });
    }

    updateSlide(index) {
        if (index < 0 || index >= this.slides.length) return;
        
        this.currentIndex = index;
        
        // Update track position with proper transform
        this.track.style.transform = `translateX(-${index * 100}%)`;
        
        // Update active dot
        this.dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }

    startAutoPlay() {
        this.autoPlayInterval = setInterval(() => {
            this.currentIndex = (this.currentIndex + 1) % this.slides.length;
            this.updateSlide(this.currentIndex);
        }, 4000);
    }

    stopAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
        }
    }
}

// Initialize the carousel when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new TourCarousel();
    new MinimalCarousel();
    new BookingModal();
});

// Initialize calendar when DOM is loaded
let calendarInstance;

document.addEventListener('DOMContentLoaded', () => {
    calendarInstance = new TourCalendar();
    
    const reserveBtn = document.querySelector('.booking-sidebar .btn-reserve');
    if (reserveBtn) {
        reserveBtn.addEventListener('click', () => {
            if (calendarInstance.selectedDate) {
                alert(`¡Reserva confirmada para el ${calendarInstance.selectedDate}! Nos pondremos en contacto contigo pronto.`);
            } else {
                alert('Por favor selecciona una fecha de salida disponible.');
            }
        });
    }
});

function bindItineraryInteractions() {
    const accordionItems = document.querySelectorAll('.accordion-item');
    const tabButtons = document.querySelectorAll('.tab-button');
    
    // Handle accordion clicks
    accordionItems.forEach(item => {
        const header = item.querySelector('.accordion-header');
        header.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Close all items
            accordionItems.forEach(accordion => {
                accordion.classList.remove('active');
            });
            
            // Open clicked item if it wasn't active
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
    
    // Handle tab filtering
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.dataset.day;
            
            // Update active tab
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Filter accordion items
            accordionItems.forEach(item => {
                const dayNumber = parseInt(item.dataset.day);
                
                if (filter === 'all') {
                    item.classList.remove('hidden');
                } else if (filter === '1-3') {
                    item.classList.toggle('hidden', dayNumber < 1 || dayNumber > 3);
                } else if (filter === '4-7') {
                    item.classList.toggle('hidden', dayNumber < 4 || dayNumber > 7);
                }
            });
            
            // Close all open accordions when switching tabs
            accordionItems.forEach(item => item.classList.remove('active'));
        });
    });
    
    // Set initial state - close all accordion items
    accordionItems.forEach(item => item.classList.remove('active'));
    const allTab = document.querySelector('.tab-button[data-day="all"]');
    if (allTab) allTab.click();
}

// Accordion functionality
document.addEventListener('DOMContentLoaded', bindItineraryInteractions);

// Calendar functionality
class TourCalendar {
    constructor() {
        this.container = document.getElementById('calendar');
        this.currentDate = new Date();
        this.availableDates = [
            '2026-12-31'
        ];
        this.departuresByDate = {};
        this.selectedDate = null;
        this.init();
    }

    init() {
        this.renderCalendar();
    }

    renderCalendar() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        
        const monthNames = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        
        const calendarHTML = `
            <div class="calendar-header">
                <button type="button" class="calendar-nav" id="prevMonth">‹</button>
                <h3 class="calendar-month">${monthNames[month]} ${year}</h3>
                <button type="button" class="calendar-nav" id="nextMonth">›</button>
            </div>
            <table class="calendar">
                <thead>
                    <tr>
                        <th>Dom</th>
                        <th>Lun</th>
                        <th>Mar</th>
                        <th>Mié</th>
                        <th>Jue</th>
                        <th>Vie</th>
                        <th>Sáb</th>
                    </tr>
                </thead>
                <tbody>
                    ${this.generateCalendarDays(firstDay, lastDay)}
                </tbody>
            </table>
        `;
        
        this.container.innerHTML = calendarHTML;
        this.bindCalendarEvents();
        this.bindNavigationEvents();
    }

    generateCalendarDays(firstDay, lastDay) {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        let html = '<tr>';
        
        // Empty cells for days before month starts
        for (let i = 0; i < firstDay.getDay(); i++) {
            html += '<td class="disabled"></td>';
        }
        
        // Calendar days
        for (let day = 1; day <= lastDay.getDate(); day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isAvailable = this.availableDates.includes(dateStr);
            const isPast = new Date(year, month, day) < new Date();
            
            let className = 'disabled';
            let clickHandler = '';
            
            if (isAvailable) {
                className = 'available';
                clickHandler = `data-date="${dateStr}"`;
            } else if (!isPast) {
                className = 'unavailable';
            }
            
            const departure = this.departuresByDate[dateStr];
            const priceMarkup = departure ? `<small class="calendar-price">${escapeDetailHtml(departure.price)} ${escapeDetailHtml(departure.currency)}</small>` : '';
            html += `<td class="${className}" ${clickHandler}><span class="calendar-day">${day}</span>${priceMarkup}</td>`;
            
            // Close row after Sunday
            if ((firstDay.getDay() + day) % 7 === 0) {
                html += '</tr><tr>';
            }
        }
        
        html += '</tr>';
        return html;
    }

    bindCalendarEvents() {
        const availableDays = this.container.querySelectorAll('.available');
        availableDays.forEach(day => {
            day.addEventListener('click', (e) => {
                // Remove previous selection
                const previousSelected = this.container.querySelector('.available.selected');
                if (previousSelected) {
                    previousSelected.classList.remove('selected');
                }
                
                // Set new selection
                this.selectedDate = e.currentTarget.dataset.date;
                const departure = this.departuresByDate[this.selectedDate];
                if (departure) updateDepartureSummary(departure);
                e.currentTarget.classList.add('selected');
            });
        });
    }

    bindNavigationEvents() {
        const prevBtn = this.container.querySelector('#prevMonth');
        const nextBtn = this.container.querySelector('#nextMonth');
        
        prevBtn.addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.renderCalendar();
        });
        
        nextBtn.addEventListener('click', () => {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.renderCalendar();
        });
    }
}

// Dynamic background for itinerary timeline
const timeline = document.querySelector('.itinerary-timeline');
if (timeline) {
    const days = timeline.querySelectorAll('.day-card');
    days.forEach((day, index) => {
        day.style.animationDelay = `${index * 0.15}s`;
        day.classList.add('fade-in-left');
    });
}

// Add CSS for fade-in animation
const style = document.createElement('style');
style.textContent = `
    .fade-in-left {
        animation: fadeInLeft 0.6s ease forwards;
    }
    
    @keyframes fadeInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
`;
document.head.appendChild(style);

function escapeDetailHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;'
    }[character]));
}

function initGalleryLightbox() {
    const lightbox = document.getElementById('galleryLightbox');
    if (!lightbox || lightbox.dataset.bound === 'true') return;

    const image = lightbox.querySelector('.gallery-lightbox__image');
    const caption = lightbox.querySelector('.gallery-lightbox__caption');
    let currentImages = [];
    let currentIndex = 0;

    const showImage = index => {
        currentIndex = (index + currentImages.length) % currentImages.length;
        const selected = currentImages[currentIndex];
        image.src = selected.src;
        image.alt = selected.alt;
        caption.textContent = selected.alt;
    };

    const open = clickedImage => {
        const gallery = clickedImage.closest('.gallery-grid, .vehicle-card__gallery');
        if (!gallery) return;

        currentImages = [...gallery.querySelectorAll('img')].map(galleryImage => ({
            src: galleryImage.src,
            alt: galleryImage.alt
        }));
        if (!currentImages.length) return;

        showImage(currentImages.findIndex(selected => selected.src === clickedImage.src));
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    document.addEventListener('click', event => {
        const clickedImage = event.target.closest('.gallery-grid img, .vehicle-card__gallery img');
        if (clickedImage) open(clickedImage);
        if (event.target === lightbox || event.target.closest('.gallery-lightbox__close')) close();
        if (event.target.closest('.gallery-lightbox__prev')) {
            event.stopPropagation();
            showImage(currentIndex - 1);
        }
        if (event.target.closest('.gallery-lightbox__next')) {
            event.stopPropagation();
            showImage(currentIndex + 1);
        }
    });
    document.addEventListener('keydown', event => {
        if (!lightbox.classList.contains('is-open')) return;
        if (event.key === 'Escape') close();
        if (event.key === 'ArrowLeft') showImage(currentIndex - 1);
        if (event.key === 'ArrowRight') showImage(currentIndex + 1);
    });
    lightbox.dataset.bound = 'true';
}

document.addEventListener('DOMContentLoaded', initGalleryLightbox);

function updateDepartureSummary(departure) {
    const maxGroupSize = Number(departure.max_group_size || departure.capacity || 0);
    const availableSeats = Math.min(Number(departure.available_seats || 0), maxGroupSize);
    const departurePrice = document.getElementById('departurePrice');
    const availabilityTotal = document.querySelector('.availability-total');
    const availabilityNote = document.querySelector('.availability-note');
    const availabilityMeter = document.querySelector('.availability-meter span');

    if (departurePrice) {
        departurePrice.textContent = `Precio por persona: ${departure.price} ${departure.currency}`;
    }
    if (availabilityTotal) availabilityTotal.textContent = `${availableSeats} / ${maxGroupSize}`;
    if (availabilityNote) availabilityNote.textContent = `plazas disponibles · ${departure.departure_date}`;
    if (availabilityMeter) availabilityMeter.style.width = maxGroupSize ? `${(availableSeats / maxGroupSize) * 100}%` : '0%';
}

function renderTourDetail(payload) {
    const { tour, quick_details, highlights, prices, photos, meeting_points, recommendations, inclusions, itinerary, departures, vehicles } = payload;
    document.title = `${tour.name} - Viajero`;
    document.querySelector('.nav-brand h1').textContent = tour.company_name;
    document.querySelector('.hero-badge').textContent = tour.badge || tour.category;
    document.querySelector('.hero-title').textContent = tour.name;
    document.querySelector('.hero-subtitle').textContent = `${tour.destination} · ${tour.company_name}`;
    document.querySelector('.hero-background').style.backgroundImage = `url("${tour.hero_image_url || tour.image_url}")`;

    document.querySelector('.details-grid').innerHTML = quick_details.map(detail => `
        <div class="detail-card minimal">
            <span class="detail-value">${escapeDetailHtml(detail.value)}</span>
            <span class="detail-label">${escapeDetailHtml(detail.label)}</span>
        </div>
    `).join('');

    document.querySelector('.description-content').innerHTML = `<p class="description-text">${escapeDetailHtml(tour.description)}</p>`;
    document.querySelector('.highlights-grid').innerHTML = highlights.map(highlight => `
        <div class="highlight-item minimal">
            <h3>${escapeDetailHtml(highlight.title)}</h3>
            <p>${escapeDetailHtml(highlight.description)}</p>
        </div>
    `).join('');

    const firstDeparture = departures[0];
    if (firstDeparture) {
        updateDepartureSummary(firstDeparture);
    } else {
        document.querySelector('.availability-total').textContent = '0 / 0';
        document.querySelector('.availability-note').textContent = 'sin salidas disponibles';
    }

    document.querySelector('.gallery-grid').innerHTML = photos.map(photo => `
        <figure class="gallery-item${photo.is_cover ? ' gallery-item--large' : ''}">
            <img src="${escapeDetailHtml(photo.image_url)}" alt="${escapeDetailHtml(photo.alt_text || tour.name)}">
        </figure>
    `).join('');

    document.querySelector('.meeting-grid').innerHTML = meeting_points.map(point => `
        <div class="meeting-card">
            <div class="meeting-icon">📍</div>
            <h3>${escapeDetailHtml(point.name)}</h3>
            <p>${escapeDetailHtml(point.description || point.address)}</p>
        </div>
    `).join('');

    document.querySelector('.recommendations-grid').innerHTML = recommendations.map(recommendation => `
        <div class="recommendation-card">
            <div class="recommendation-icon">✦</div>
            <h3>${escapeDetailHtml(recommendation.title)}</h3>
            <ul>${recommendation.items.split(';').map(item => `<li>${escapeDetailHtml(item.trim())}</li>`).join('')}</ul>
        </div>
    `).join('');

    const includedItems = inclusions.filter(item => Number(item.included) === 1);
    const excludedItems = inclusions.filter(item => Number(item.included) === 0);
    document.querySelector('.includes-container').innerHTML = [
        ['Qué Incluye', includedItems],
        ['Qué No Incluye', excludedItems]
    ].map(([title, items]) => `
        <div class="includes-column">
            <h2 class="section-title">${title}</h2>
            <ul class="includes-list${title === 'Qué No Incluye' ? ' no-list' : ''}">
                ${items.map(item => `<li>${escapeDetailHtml(item.item)}</li>`).join('')}
            </ul>
        </div>
    `).join('');

    document.querySelector('.itinerary-accordion').innerHTML = itinerary.map((day, index) => `
        <div class="accordion-item${index === 0 ? ' active' : ''}" data-day="${day.day_number}">
            <div class="accordion-header">
                <div class="day-number">${String(day.day_number).padStart(2, '0')}</div>
                <div class="accordion-title">
                    <h3>${escapeDetailHtml(day.title)}</h3>
                    <p class="day-location">📍 ${escapeDetailHtml(day.location)}</p>
                </div>
                <div class="accordion-icon"><span class="chevron"></span></div>
            </div>
            <div class="accordion-content"><p class="day-description">${escapeDetailHtml(day.description)}</p></div>
        </div>
    `).join('');

    document.querySelector('.minimal-carousel-track').innerHTML = photos.map(photo => `
        <div class="minimal-carousel-slide"><img src="${escapeDetailHtml(photo.image_url)}" alt="${escapeDetailHtml(photo.alt_text || tour.name)}" class="minimal-carousel-image"></div>
    `).join('');
    document.querySelector('.price-list').innerHTML = prices.map(price => `
        <div class="price-item"><span class="price-label">${escapeDetailHtml(price.name)}</span><span class="price-value">${escapeDetailHtml(price.amount)} ${escapeDetailHtml(price.currency)}</span></div>
    `).join('');

    document.querySelector('#vehicleList').innerHTML = vehicles.length ? vehicles.map(vehicle => `
        <article class="vehicle-card">
            <div class="vehicle-card__details">
                <p class="vehicle-card__eyebrow">${escapeDetailHtml(vehicle.status)}</p>
                <h3>${escapeDetailHtml(vehicle.brand)} ${escapeDetailHtml(vehicle.model)}</h3>
                <p class="vehicle-card__plate">Placa: ${escapeDetailHtml(vehicle.license_plate)}</p>
                <dl class="vehicle-specs">
                    <div><dt>Asientos</dt><dd>${escapeDetailHtml(vehicle.seat_capacity)}</dd></div>
                    <div><dt>Accesibles</dt><dd>${escapeDetailHtml(vehicle.accessible_seats)}</dd></div>
                    <div><dt>Equipaje</dt><dd>${escapeDetailHtml(vehicle.luggage_capacity || 'No especificado')}</dd></div>
                    <div><dt>Año</dt><dd>${escapeDetailHtml(vehicle.vehicle_year || 'No especificado')}</dd></div>
                </dl>
                <p>${escapeDetailHtml(vehicle.notes || '')}</p>
            </div>
            <div class="vehicle-card__gallery">
                ${(vehicle.photos || []).map(photo => `<img src="${escapeDetailHtml(photo.image_url)}" alt="${escapeDetailHtml(photo.alt_text || `${vehicle.brand} ${vehicle.model}`)}">`).join('')}
            </div>
        </article>
    `).join('') : '<p>No hay un vehículo asignado para las próximas salidas.</p>';
    initGalleryLightbox();

    const defaultPrice = prices[0] || { amount: tour.price, currency: 'USD' };
    const modalPrices = document.querySelectorAll('.price-detail');
    modalPrices.forEach(priceDetail => {
        priceDetail.textContent = `${defaultPrice.amount} ${defaultPrice.currency} c/u`;
    });
    window.currentTourPrices = prices;
    window.currentTourBookingData = {
        agencyId: tour.company_id,
        agencyName: tour.company_name,
        tourId: tour.id,
        tourName: tour.name,
        departure: firstDeparture,
        vehicleId: firstDeparture?.vehicle_id || '',
        price: defaultPrice.amount,
        currency: defaultPrice.currency
    };
    bindItineraryInteractions();

    if (calendarInstance) {
        calendarInstance.availableDates = departures.map(departure => departure.departure_date);
        calendarInstance.departuresByDate = Object.fromEntries(departures.map(departure => [departure.departure_date, departure]));
        if (departures.length) {
            calendarInstance.currentDate = new Date(`${departures[0].departure_date}T00:00:00`);
            updateDepartureSummary(firstDeparture);
        }
        calendarInstance.renderCalendar();
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const queryTourId = new URLSearchParams(window.location.search).get('tour_id');
    const friendlyTourId = window.location.pathname.match(/\/([^/]+)\/([^/]+)\/(\d+)\/?$/)?.[3]
        || window.location.pathname.match(/\/tour\/(\d+)\/?$/)?.[1];
    const tourId = queryTourId || friendlyTourId;
    if (!tourId || typeof ToursApi === 'undefined') return;

    try {
        const response = await ToursApi.getTour(tourId);
        renderTourDetail(response.data);
    } catch (error) {
        document.querySelector('.hero-title').textContent = 'Tour no encontrado';
        document.querySelector('.hero-subtitle').textContent = error.message;
        console.error(error);
    }
});

// Enhanced scroll event handling
window.addEventListener('scroll', () => {
    updateScrollIndicator();
    updateParallax();
}, { passive: true });

class BookingModal {
    constructor() {
        this.modal = document.getElementById('bookingModal');
        this.reserveBtn = document.querySelector('.btn-reserve');
        this.closeBtn = document.getElementById('modalClose');
        this.confirmBtn = document.getElementById('confirmBooking');
        this.dateSpan = document.querySelector('#modalDate span');
        
        this.passengers = {
            adults: 1,
            children: 0,
            seniors: 0
        };
        
        this.prices = {
            adults: 1299,
            children: 649,
            seniors: 1039
        };
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateDisplay();
    }

    bindEvents() {
        this.reserveBtn.addEventListener('click', () => this.open());
        this.closeBtn.addEventListener('click', () => this.close());
        this.confirmBtn.addEventListener('click', () => this.confirm());
        
        // Handle counter buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('counter-btn')) {
                e.preventDefault();
                const type = e.target.dataset.type;
                const action = e.target.dataset.action;
                this.updatePassenger(type, action);
            }
        });

        // Close on backdrop click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.close();
            }
        });
    }

    open() {
        if (!calendarInstance || !calendarInstance.selectedDate) {
            alert('Por favor selecciona una fecha de salida disponible');
            return;
        }

        this.dateSpan.textContent = new Date(calendarInstance.selectedDate).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        this.modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    updatePassenger(type, action) {
        const current = this.passengers[type];
        const newValue = action === 'increase' ? current + 1 : Math.max(0, current - 1);
        
        // Check if adding would exceed available slots
        const totalPeople = Object.values(this.passengers).reduce((sum, count, passengerType) => {
            const countToAdd = passengerType === type ? newValue : count;
            return sum + countToAdd;
        }, 0);

        if (action === 'increase' && totalPeople > 12) {
            alert('¡Atención! Has excedido el cupo máximo disponible de 12 personas.');
            return;
        }
        
        // Prevent negative values
        if (newValue < 0) return;
        
        this.passengers[type] = newValue;
        this.updateDisplay();
    }

    updateDisplay() {
        // Update counter displays
        Object.keys(this.passengers).forEach(type => {
            const display = document.querySelector(`.counter-value[data-type="${type}"]`);
            if (display) {
                display.textContent = this.passengers[type];
            }
        });

        // Update total people
        const totalPeople = Object.values(this.passengers).reduce((sum, count) => sum + count, 0);
        document.getElementById('totalPeople').textContent = totalPeople;

        // Update total price
        const totalPrice = Object.keys(this.passengers).reduce((sum, type) => {
            return sum + (this.passengers[type] * this.prices[type]);
        }, 0);

        document.getElementById('totalPrice').textContent = `$${totalPrice.toLocaleString()}`;

        // Update availability
        const availableSlots = Math.max(0, 12 - totalPeople);
        document.getElementById('availabilityText').innerHTML = 
            `Disponibilidad: <span class="${availableSlots > 0 ? 'available' : 'unavailable'}">${availableSlots} cupo${availableSlots !== 1 ? 's' : ''} restante${availableSlots !== 1 ? 's' : ''}</span>`;

        // Update confirm button state
        this.confirmBtn.disabled = totalPeople === 0;
        this.confirmBtn.style.opacity = totalPeople === 0 ? '0.5' : '1';
    }

    confirm() {
        const totalPeople = Object.values(this.passengers).reduce((sum, count) => sum + count, 0);
        if (totalPeople === 0) {
            alert('Por favor selecciona al menos un pasajero');
            return;
        }

        const departure = calendarInstance.departuresByDate[calendarInstance.selectedDate];
        const tourData = window.currentTourBookingData || {};
        if (!departure || !tourData.tourId) {
            alert('No se pudo preparar la información de la reserva.');
            return;
        }

        const bookingData = {
            agency_id: tourData.agencyId || '',
            agency_name: tourData.agencyName || '',
            tour_id: tourData.tourId,
            tour_name: tourData.tourName || '',
            departure_id: departure.id,
            departure_date: departure.departure_date,
            vehicle_id: departure.vehicle_id || '',
            adults: this.passengers.adults,
            children: this.passengers.children,
            seniors: this.passengers.seniors,
            total_people: totalPeople,
            total: Object.keys(this.passengers).reduce((sum, type) => sum + (this.passengers[type] * this.prices[type]), 0)
        };
        const query = new URLSearchParams(bookingData);
        window.location.href = `../../bus/index.php?${query.toString()}`;
    }
}