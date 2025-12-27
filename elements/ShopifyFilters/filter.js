document.addEventListener('DOMContentLoaded', function() {
    const filterForms = document.querySelectorAll('.bde-shopify-filters-form');
    if (!filterForms.length) return;

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Main Update Function
    function updateFilters(form) {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        // Remove empty parameters
        const keys = Array.from(params.keys());
        for (const key of keys) {
            const val = params.get(key);
            if (val === '' || val === null) {
                params.delete(key);
            }
        }

        // Check for active params (excluding potentially hidden fields if any, but assume all form data matters)
        // Actually, we cleaned 'params' already.
        const hasFilters = Array.from(params.keys()).length > 0;
        
        const clearBtn = form.querySelector('.bde-shopify-filter-clear');
        
        console.log('UpdateFilters:', {
            paramCount: Array.from(params.keys()).length,
            hasFilters: hasFilters,
            btnFound: !!clearBtn
        });

        if (clearBtn) {
            clearBtn.style.display = hasFilters ? 'inline-block' : 'none';
        }

        // Construct new URL
        const newUrl = window.location.pathname + '?' + params.toString();

        // Push State
        window.history.pushState({ path: newUrl }, '', newUrl);

        // Fetch
        fetchPage(newUrl);
    }

    // Fetch and Replace
    function fetchPage(url) {
        const containerSelector = '.bde-shopify-page-grid, .bde-shopify-products-grid, .breakdance-swiper-wrapper, .bde-shopify-products-empty, .bde-shopify-products-error';
        const currentContainer = document.querySelector(containerSelector);
        
        if (!currentContainer) return; // Nothing to update
        
        // Add loading state
        currentContainer.style.opacity = '0.5';
        currentContainer.style.pointerEvents = 'none';

        fetch(url)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContainer = doc.querySelector(containerSelector);

                if (newContainer && currentContainer.parentNode) {
                    currentContainer.parentNode.replaceChild(newContainer, currentContainer);
                    
                    // Re-init swiper if needed? 
                    // If it was a slider, we might need to manually trigger events.
                    // For Grid, it's just CSS.
                } else {
                    console.error('Could not find product grid in response');
                     // Remove loading state if failed
                    currentContainer.style.opacity = '1';
                    currentContainer.style.pointerEvents = 'auto';
                }
            })
            .catch(err => {
                console.error('Filter fetch error:', err);
                currentContainer.style.opacity = '1';
                currentContainer.style.pointerEvents = 'auto';
            });
    }

    // Bind Events
    filterForms.forEach(form => {
        // Prevent standard submit
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            updateFilters(form);
        });

        // Inputs
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.type === 'text' || input.type === 'number') {
                input.addEventListener('input', debounce(() => updateFilters(form), 500));
            } else {
                input.addEventListener('change', () => updateFilters(form));
            }
        });

        // Clear Button
        const clearBtn = form.querySelector('.bde-shopify-filter-clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Reset form inputs manually or just fetch clear URL
                form.reset();
                this.style.display = 'none';
                
                // We also need to clear URL params manually for the pushState
                // Best way: use the href of the link which is already clean path
                const cleanUrl = this.getAttribute('href');
                window.history.pushState({ path: cleanUrl }, '', cleanUrl);
                fetchPage(cleanUrl);
                
                // Also uncheck inputs visually if not resetting automatically (form.reset works for form inputs usually)
                // But Selects might need manual reset if default wasn't empty? Usually reset() works to initial value.
                // However, initial value might be the filtered value if rendered by PHP!
                // So we should manually clear values.
                form.querySelectorAll('input[type="text"], input[type="number"]').forEach(i => i.value = '');
                form.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(i => i.checked = false);
                form.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
            });
        }
    });

    // Handle Back/Forward
    window.addEventListener('popstate', function(event) {
        fetchPage(window.location.href);
    });
});
