(() => {
    const initFaqWidget = (container) => {
        const searchInput = container.querySelector('[data-faq-search]');
        const tabsContainer = container.querySelector('[data-faq-tabs]');
        const faqList = container.querySelector('[data-faq-list]');
        const noResults = container.querySelector('[data-faq-no-results]');
        const sortSelect = container.querySelector('[data-faq-sort]');
        const pageUids = container.dataset.faqPageUids || '';

        let activeCategory = 'all';
        let sortField = 'sorting';
        let sortOrder = 'asc';
        let searchQuery = '';
        let currentItems = [];

        const normalize = (str) => str.toLowerCase().replace(/\s+/g, ' ').trim();

        const matchesSearch = (item) => {
            if (!searchQuery) return true;
            const question = normalize(item.question ?? '');
            const answer = normalize(item.answer ?? '');
            return question.includes(searchQuery) || answer.includes(searchQuery);
        };

        const matchesCategory = (item) => {
            if (activeCategory === 'all') return true;
            const cats = (item.categories ?? []).map((c) => String(c.uid || c));
            return cats.includes(activeCategory);
        };

        const buildItemElement = (item) => {
            const details = document.createElement('details');
            details.className = 'mai-faq__item';
            details.dataset.faqItem = '1';
            details.dataset.faqUid = String(item.uid);
            details.dataset.faqQuestion = item.question;
            details.dataset.faqAnswer = (item.answer || '').replace(/<[^>]*>/g, '');
            details.dataset.faqCategories = (item.categories ?? [])
                .map((c) => c.uid || c)
                .join(',');

            const summary = document.createElement('summary');
            summary.className = 'mai-faq__question';
            summary.textContent = item.question;

            const answerDiv = document.createElement('div');
            answerDiv.className = 'mai-faq__answer';
            answerDiv.innerHTML = item.answer;

            details.appendChild(summary);
            details.appendChild(answerDiv);
            return details;
        };

        const renderItems = (items) => {
            faqList.innerHTML = '';
            let visibleCount = 0;

            items.forEach((item) => {
                if (!(matchesSearch(item) && matchesCategory(item))) return;
                visibleCount++;
                faqList.appendChild(buildItemElement(item));
            });

            if (noResults) noResults.hidden = visibleCount > 0;
        };

        const loadItems = async (categoryUid) => {
            const params = new URLSearchParams();
            if (categoryUid > 0) params.set('categoryUid', String(categoryUid));
            if (pageUids) params.set('pageUids', pageUids);
            params.set('sort', sortField);
            params.set('order', sortOrder);

            try {
                const response = await fetch(`/api/faq/items?${params.toString()}`);
                const data = await response.json();
                currentItems = (data.items ?? []).map((item) => ({
                    uid: item.uid,
                    question: item.question,
                    answer: item.answer,
                    categories: item.categories ?? [],
                }));
                renderItems(currentItems);
            } catch (e) {
                console.error('Failed to load FAQ items:', e);
            }
        };

        const applyFilters = () => {
            renderItems(currentItems);
        };

        const handleTabClick = async (tab) => {
            activeCategory = tab.dataset.faqTab;

            tabsContainer.querySelectorAll('[data-faq-tab]').forEach((t) => {
                const isActive = t === tab;
                t.classList.toggle('mai-faq__tab--active', isActive);
                t.setAttribute('aria-selected', String(isActive));
            });

            const categoryUid = activeCategory === 'all' ? 0 : parseInt(activeCategory, 10);
            await loadItems(categoryUid);
        };

        if (tabsContainer) {
            tabsContainer.addEventListener('click', async (e) => {
                const tab = e.target.closest('[data-faq-tab]');
                if (!tab) return;
                await handleTabClick(tab);
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', async () => {
                const [field, order] = sortSelect.value.split(',');
                sortField = field || 'sorting';
                sortOrder = order || 'asc';

                const categoryUid = activeCategory === 'all' ? 0 : parseInt(activeCategory, 10);
                await loadItems(categoryUid);
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                searchQuery = normalize(searchInput.value);
                applyFilters();
            });
        }

        const itemElements = container.querySelectorAll('[data-faq-item]');
        currentItems = Array.from(itemElements).map((el) => ({
            uid: parseInt(el.dataset.faqUid || '0', 10),
            question: el.dataset.faqQuestion || '',
            answer: el.dataset.faqAnswer || '',
            categories: (el.dataset.faqCategories || '')
                .split(',')
                .map((c) => c.trim())
                .filter(Boolean),
        }));
    };

    document.querySelectorAll('[data-faq-container]').forEach(initFaqWidget);
})();
