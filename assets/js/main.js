// Simple interactive functionality
document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.querySelector('.search__form');
    const destinationInput = document.querySelector('#destination');
    const dateRangeInput = document.querySelector('#dateRange');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const locationSpan = document.querySelector('.results__location');
    const resultsGrid = document.querySelector('.results__grid');

    // Calendar functionality
    let startDate = null;
    let endDate = null;

    function createCalendar() {
        const calendar = document.createElement('div');
        calendar.className = 'calendar';
        calendar.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-width: 320px;
            margin: 0 auto;
        `;

        const header = document.createElement('div');
        header.style.cssText = 'display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;';

        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '&larr;';
        prevBtn.style.cssText = 'background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #1a1a1a;';

        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '&rarr;';
        nextBtn.style.cssText = 'background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #1a1a1a;';

        const monthTitle = document.createElement('h4');
        monthTitle.style.cssText = 'margin: 0; font-size: 1rem; font-weight: 500; color: #1a1a1a;';

        header.appendChild(prevBtn);
        header.appendChild(monthTitle);
        header.appendChild(nextBtn);

        const grid = document.createElement('div');
        grid.style.cssText = `
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            text-align: center;
        `;

        const days = ['D', 'L', 'M', 'M', 'J', 'V', 'S'];
        days.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.textContent = day;
            dayHeader.style.cssText = 'font-size: 0.75rem; color: #666; padding: 0.5rem; font-weight: 500;';
            grid.appendChild(dayHeader);
        });

        calendar.appendChild(header);
        calendar.appendChild(grid);

        return { calendar, grid, monthTitle, prevBtn, nextBtn };
    }

    if (dateRangeInput) {
        const { calendar, grid, monthTitle, prevBtn, nextBtn } = createCalendar();
        dateRangeInput.parentElement.style.position = 'relative';
        dateRangeInput.parentElement.appendChild(calendar);

        // set up calendar behavior using the local variables
        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();

        function isValidRange(start, end) {
            return start && end && end >= start;
        }

        function renderCalendar() {
            grid.innerHTML = '';

            const days = ['D', 'L', 'M', 'M', 'J', 'V', 'S'];
            days.forEach(day => {
                const dayHeader = document.createElement('div');
                dayHeader.textContent = day;
                dayHeader.style.cssText = 'font-size: 0.75rem; color: #666; padding: 0.5rem; font-weight: 500;';
                grid.appendChild(dayHeader);
            });

            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

            monthTitle.textContent = new Date(currentYear, currentMonth).toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });

            for (let i = 0; i < firstDay; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.style.cssText = 'padding: 0.5rem;';
                grid.appendChild(emptyCell);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const cell = document.createElement('div');
                cell.style.cssText = `
                    padding: 0.5rem;
                    cursor: pointer;
                    font-size: 0.875rem;
                    transition: all 0.2s;
                    color: #1a1a1a;
                    border-radius: 0;
                    position: relative;
                `;
                cell.textContent = day;

                const date = new Date(currentYear, currentMonth, day);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (date < today) {
                    cell.style.color = '#ccc';
                    cell.style.cursor = 'not-allowed';
                    cell.style.pointerEvents = 'none';
                    grid.appendChild(cell);
                    continue;
                }

                if (startDate && endDate && date >= startDate && date <= endDate) {
                    cell.style.background = '#1a1a1a';
                    cell.style.color = 'white';
                } else if (startDate && date.getTime() === startDate.getTime()) {
                    cell.style.background = '#1a1a1a';
                    cell.style.color = 'white';
                } else if (endDate && date.getTime() === endDate.getTime()) {
                    cell.style.background = '#1a1a1a';
                    cell.style.color = 'white';
                }

                cell.addEventListener('click', () => {
                    if (date < today) return;

                    if (!startDate || (startDate && endDate)) {
                        startDate = date;
                        endDate = null;
                    } else if (date >= startDate) {
                        endDate = date;
                        if (isValidRange(startDate, endDate)) {
                            updateDateRangeDisplay();
                            if (startDate && endDate) {
                                setTimeout(() => {
                                    calendar.style.display = 'none';
                                }, 150);
                            }
                        }
                    } else {
                        startDate = date;
                        endDate = null;
                    }
                    renderCalendar();
                });

                cell.addEventListener('mouseenter', () => {
                    if (startDate && !endDate && date >= startDate) {
                        cell.style.background = '#f5f5f5';
                    }
                });

                cell.addEventListener('mouseleave', () => {
                    renderCalendar();
                });

                grid.appendChild(cell);
            }
        }

        function updateDateRangeDisplay() {
            if (startDate && endDate) {
                const startStr = startDate.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
                const endStr = endDate.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
                dateRangeInput.value = `${startStr} - ${endStr}`;
                setTimeout(() => {
                    calendar.style.display = 'none';
                }, 150);
            } else if (startDate) {
                dateRangeInput.value = startDate.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
            }
        }

        prevBtn.addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar();
        });

        nextBtn.addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar();
        });

        dateRangeInput.addEventListener('click', (e) => {
            e.stopPropagation();
            calendar.style.display = calendar.style.display === 'block' ? 'none' : 'block';
            renderCalendar();
        });

        document.addEventListener('click', (e) => {
            const isClickInsideCalendar = e.target.closest('.calendar');
            const isClickOnInput = e.target === dateRangeInput;

            if (!isClickInsideCalendar && !isClickOnInput) {
                if (startDate && endDate) {
                    calendar.style.display = 'none';
                }
            }
        });

        const today = new Date();
        const nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
        startDate = today;
        endDate = nextWeek;
        updateDateRangeDisplay();
        renderCalendar();
    }

    // Mobile menu toggle
    const navToggle = document.querySelector('.nav__toggle');
    const navMenu = document.querySelector('.nav__menu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');

            const spans = navToggle.querySelectorAll('span');
            if (navMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.nav') && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                const spans = navToggle.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
    }

    const categorySelect = document.querySelector('#category');
    const companySelect = document.querySelector('#company');
    let currentCategory = 'all';

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>'"]/g, character => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[character]));
    }

    function slugify(value) {
        return String(value ?? '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function renderTours(tours) {
        if (!resultsGrid) return;
        if (!tours || tours.length === 0) {
            resultsGrid.innerHTML = '<p class="results__empty" style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #666;">No encontramos tours para los filtros seleccionados.</p>';
            return;
        }

        resultsGrid.innerHTML = tours.map(tour => {
            const compSlug = slugify(tour.company_name);
            const tourSlug = slugify(tour.name);
            const tourUrl = `${compSlug}/${tourSlug}/${encodeURIComponent(tour.id)}`;
            const companyProfileUrl = `compania-de-tours/${compSlug}/${encodeURIComponent(tour.company_id)}`;
            const ratingScore = Number(tour.rating || 4.8).toFixed(1);

            return `
            <article class="tour-card" data-category="${escapeHtml(tour.category || '')}">
                <div class="tour-image-container">
                    <img src="${escapeHtml(tour.image_url || tour.hero_image_url || 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=500&h=350&fit=crop')}" alt="${escapeHtml(tour.name)}" class="tour-image" loading="lazy">
                    <div class="tour-overlay">
                        <div class="tour-details">
                            <span class="tour-price">$${Number(tour.price).toFixed(0)} USD</span>
                            <span class="tour-days">${escapeHtml(tour.duration)}</span>
                        </div>
                    </div>
                </div>
                <div class="tour-info">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <a href="${companyProfileUrl}" class="tour-company-link">🏢 ${escapeHtml(tour.company_name)}</a>
                        <span style="color: #f59e0b; font-size: 0.82rem; font-weight: 600;">★ ${ratingScore}</span>
                    </div>
                    <h3 class="tour-name">${escapeHtml(tour.name)}</h3>
                    <p class="tour-description">${escapeHtml(tour.description)}</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; border-top: 1px solid #f0ece7; padding-top: 0.75rem;">
                        <span style="font-size: 0.78rem; color: #666;">📍 ${escapeHtml(tour.destination)}</span>
                        <a class="btn-more-info" href="${tourUrl}">ver más →</a>
                    </div>
                </div>
            </article>
            `;
        }).join('');
    }

    async function loadTours(destination = '', category = '', companyId = '') {
        if (!resultsGrid) return;
        resultsGrid.style.opacity = '0.5';
        try {
            const response = await ToursApi.getTours(destination, category, companyId);
            resultsGrid.dataset.apiStatus = 'loaded';
            renderTours(response.data);
            if (locationSpan) {
                if (destination && category && category !== 'all') {
                    locationSpan.textContent = `${destination} (${category})`;
                } else if (destination) {
                    locationSpan.textContent = destination;
                } else if (category && category !== 'all') {
                    locationSpan.textContent = `Categoría: ${category}`;
                } else if (companyId && companyId !== 'all') {
                    locationSpan.textContent = 'Filtro por Operador';
                } else {
                    locationSpan.textContent = 'Todos los destinos';
                }
            }
        } catch (error) {
            resultsGrid.dataset.apiStatus = 'error';
            resultsGrid.innerHTML = '<p class="results__empty" style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #666;">No se pudieron cargar los tours.</p>';
            console.error(error);
        } finally {
            resultsGrid.style.opacity = '1';
        }
    }

    loadTours();

    if (searchForm) {
        searchForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const destination = destinationInput ? destinationInput.value.trim() : '';
            const category = categorySelect ? categorySelect.value : currentCategory;
            const companyId = companySelect ? companySelect.value : '';
            
            // Sync filter buttons
            filterBtns.forEach(btn => {
                const btnCat = btn.dataset.category || btn.textContent.trim().toLowerCase();
                btn.classList.toggle('active', btnCat === (category || 'all') || (btnCat === 'todos' && category === 'all'));
            });

            await loadTours(destination, category, companyId);
            
            // Smooth scroll to results
            const resultsSec = document.querySelector('.results');
            if (resultsSec) {
                resultsSec.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    // Handle category filter buttons
    filterBtns.forEach(btn => {
        btn.addEventListener('click', async () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const btnCategory = btn.dataset.category || btn.textContent.trim();
            currentCategory = (btnCategory.toLowerCase() === 'todos' || btnCategory.toLowerCase() === 'all') ? 'all' : btnCategory;
            
            if (categorySelect) {
                categorySelect.value = currentCategory;
            }

            const destination = destinationInput ? destinationInput.value.trim() : '';
            const companyId = companySelect ? companySelect.value : '';

            await loadTours(destination, currentCategory, companyId);
        });
    });

    // Add hover effect on cards
    const cards = document.querySelectorAll('.tour-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transition = 'all 0.3s ease';
        });
    });

    // Dynamic background carousel
    const bgImages = document.querySelectorAll('.hero-search__bg-image');
    let currentImageIndex = 0;

    function rotateBackgroundImages() {
        bgImages.forEach(img => img.classList.remove('active'));
        bgImages[currentImageIndex].classList.add('active');
        
        currentImageIndex = (currentImageIndex + 1) % bgImages.length;
    }

    // Start carousel
    setInterval(rotateBackgroundImages, 5000);
});