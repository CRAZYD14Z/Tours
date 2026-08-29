/**
 * Company Detail Page Script
 */
document.addEventListener('DOMContentLoaded', () => {
    // Navigation toggle for mobile
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }

    // Interactive Tour Filtering
    const searchForm = document.getElementById('companySearchForm');
    const destinationInput = document.getElementById('companySearchDestination');
    const categorySelect = document.getElementById('companySearchCategory');
    const priceSelect = document.getElementById('companySearchPrice');
    const clearBtn = document.getElementById('companySearchClear');
    const categoryFilterBtns = document.querySelectorAll('.cat-filter-btn');
    const tourCards = document.querySelectorAll('.tour-card-item');
    const noToursState = document.getElementById('noToursFound');
    const toursCounter = document.getElementById('filteredToursCount');

    let activeCategory = 'all';

    function filterTours() {
        const destQuery = (destinationInput?.value || '').trim().toLowerCase();
        const selectedCat = (categorySelect?.value || 'all').toLowerCase();
        const maxPrice = priceSelect?.value ? parseFloat(priceSelect.value) : Infinity;

        let visibleCount = 0;

        tourCards.forEach(card => {
            const cardDest = (card.dataset.destination || '').toLowerCase();
            const cardCat = (card.dataset.category || '').toLowerCase();
            const cardPrice = parseFloat(card.dataset.price || '0');

            const matchesDest = !destQuery || cardDest.includes(destQuery) || (card.dataset.name || '').toLowerCase().includes(destQuery);
            
            // Category can match either form select or filter buttons
            const effectiveCategory = activeCategory !== 'all' ? activeCategory.toLowerCase() : selectedCat;
            const matchesCat = effectiveCategory === 'all' || cardCat.includes(effectiveCategory);

            const matchesPrice = isNaN(maxPrice) || cardPrice <= maxPrice;

            if (matchesDest && matchesCat && matchesPrice) {
                card.style.display = 'flex';
                card.style.animation = 'fadeIn 0.3s ease forwards';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noToursState) {
            noToursState.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        if (toursCounter) {
            toursCounter.textContent = `${visibleCount} tour${visibleCount === 1 ? '' : 's'} disponible${visibleCount === 1 ? '' : 's'}`;
        }
    }

    // Category filter pills
    categoryFilterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            categoryFilterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeCategory = btn.dataset.category || 'all';
            
            if (categorySelect) {
                categorySelect.value = activeCategory;
            }
            
            filterTours();
        });
    });

    // Form submission & inputs
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            filterTours();
            
            // Smooth scroll to tours section
            const toursSec = document.getElementById('tours');
            if (toursSec) {
                toursSec.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    if (destinationInput) {
        destinationInput.addEventListener('input', filterTours);
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', () => {
            activeCategory = categorySelect.value;
            categoryFilterBtns.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.category === activeCategory);
            });
            filterTours();
        });
    }

    if (priceSelect) {
        priceSelect.addEventListener('change', filterTours);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (destinationInput) destinationInput.value = '';
            if (categorySelect) categorySelect.value = 'all';
            if (priceSelect) priceSelect.value = 'all';
            activeCategory = 'all';
            categoryFilterBtns.forEach(b => b.classList.toggle('active', b.dataset.category === 'all'));
            filterTours();
        });
    }

    // Smooth scroll for nav anchors
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});
