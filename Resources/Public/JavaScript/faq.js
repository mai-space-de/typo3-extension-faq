(() => {
    const initFaqWidget = (container) => {
        const searchInput = container.querySelector('[data-faq-search]');
        const tabsContainer = container.querySelector('[data-faq-tabs]');
        const items = Array.from(container.querySelectorAll('[data-faq-item]'));
        const noResults = container.querySelector('[data-faq-no-results]');

        let activeCategory = 'all';
        let searchQuery = '';

        const normalize = (str) => str.toLowerCase().replace(/\s+/g, ' ').trim();

        const matchesSearch = (item) => {
            if (!searchQuery) return true;
            const question = normalize(item.dataset.faqQuestion ?? '');
            const answer = normalize(item.dataset.faqAnswer ?? '');
            return question.includes(searchQuery) || answer.includes(searchQuery);
        };

        const matchesCategory = (item) => {
            if (activeCategory === 'all') return true;
            const cats = (item.dataset.faqCategories ?? '').split(',').map((c) => c.trim());
            return cats.includes(activeCategory);
        };

        const applyFilters = () => {
            let visibleCount = 0;
            items.forEach((item) => {
                const visible = matchesSearch(item) && matchesCategory(item);
                item.hidden = !visible;
                if (visible) visibleCount++;
            });
            if (noResults) noResults.hidden = visibleCount > 0;
        };

        if (tabsContainer) {
            tabsContainer.addEventListener('click', (e) => {
                const tab = e.target.closest('[data-faq-tab]');
                if (!tab) return;

                activeCategory = tab.dataset.faqTab;

                tabsContainer.querySelectorAll('[data-faq-tab]').forEach((t) => {
                    const isActive = t === tab;
                    t.classList.toggle('mai-faq__tab--active', isActive);
                    t.setAttribute('aria-selected', String(isActive));
                });

                applyFilters();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                searchQuery = normalize(searchInput.value);
                applyFilters();
            });
        }
    };

    document.querySelectorAll('[data-faq-container]').forEach(initFaqWidget);
})();
