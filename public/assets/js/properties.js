/**
 * Properties AJAX Search
 * Automatikus szűrés az ingatlanok listájában
 */

document.addEventListener('DOMContentLoaded', function() {
    const filtersForm = document.getElementById('property-filters-form');
    const resultsContainer = document.getElementById('property-results');
    const heroForm = document.getElementById('hero-search-form');
    const clearFiltersBtn = document.getElementById('clear-filters-btn');
    const ingatlanokSection = document.getElementById('ingatlanok-section');

    if (!filtersForm || !resultsContainer) {
        console.log('Properties filters or results container not found');
        return;
    }

    /**
     * Fetch properties with current filter values
     */
    function fetchProperties() {
        const formData = new FormData(filtersForm);
        const params = new URLSearchParams();

        // Convert FormData to URLSearchParams
        for (const [key, value] of formData.entries()) {
            if (value) {
                params.append(key, value);
            }
        }

        // Show loading state
        resultsContainer.style.opacity = '0.5';
        resultsContainer.style.pointerEvents = 'none';

        // Fetch from API
        fetch(`/api/properties-search.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultsContainer.innerHTML = data.html;
                } else {
                    resultsContainer.innerHTML = data.html || '<div class="no-results"><p>Hiba történt.</p></div>';
                }

                // Restore state
                resultsContainer.style.opacity = '1';
                resultsContainer.style.pointerEvents = 'auto';
            })
            .catch(error => {
                console.error('Fetch error:', error);
                resultsContainer.innerHTML = '<div class="no-results"><p>Hiba történt a keresés során.</p></div>';
                resultsContainer.style.opacity = '1';
                resultsContainer.style.pointerEvents = 'auto';
            });
    }

    /**
     * Attach event listeners to all filter inputs
     */
    function attachFilterListeners() {
        // All inputs, selects, checkboxes
        const inputs = filtersForm.querySelectorAll('input, select');

        inputs.forEach(input => {
            if (input.type === 'checkbox') {
                input.addEventListener('change', fetchProperties);
            } else {
                // Text inputs and selects - debounce for better UX
                let timeout;
                input.addEventListener('input', () => {
                    clearTimeout(timeout);
                    timeout = setTimeout(fetchProperties, 500);
                });

                // Also trigger on change for select dropdowns
                input.addEventListener('change', fetchProperties);
            }
        });
    }

    /**
     * Clear all filters
     */
    function clearFilters() {
        // Reset form
        filtersForm.reset();

        // Trigger search with empty filters
        fetchProperties();
    }

    /**
     * Handle hero search form submission
     */
    function handleHeroSearch(e) {
        e.preventDefault();

        const heroFormData = new FormData(heroForm);

        // Map hero form fields to filter form fields
        const fieldMapping = {
            'location': 'location',
            'type': 'type',
            'price_min': 'price_min',
            'price_max': 'price_max',
            'bedrooms': 'bedrooms'
        };

        // Copy values from hero to filters
        for (const [heroField, filterField] of Object.entries(fieldMapping)) {
            const heroValue = heroFormData.get(heroField);
            const filterInput = filtersForm.querySelector(`[name="${filterField}"]`);

            if (filterInput && heroValue) {
                if (filterInput.tagName === 'SELECT') {
                    filterInput.value = heroValue;
                } else {
                    filterInput.value = heroValue;
                }
            }
        }

        // Scroll to properties section
        if (ingatlanokSection) {
            ingatlanokSection.scrollIntoView({ behavior: 'smooth' });
        }

        // Trigger search
        setTimeout(() => {
            fetchProperties();
        }, 300); // Wait for scroll animation
    }

    // Attach listeners
    attachFilterListeners();

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearFilters);
    }

    if (heroForm) {
        heroForm.addEventListener('submit', handleHeroSearch);
    }

    // Initial load with current filters (if any)
    // Uncomment if you want to auto-load on page load
    // fetchProperties();
});
