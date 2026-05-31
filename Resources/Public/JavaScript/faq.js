(() => {
    let faqPopstateBound = false;

    const initFaqWidget = (container) => {
        const searchInput = container.querySelector('[data-faq-search]');
        const tabsContainer = container.querySelector('[data-faq-tabs]');
        const faqList = container.querySelector('[data-faq-list]');
        const noResults = container.querySelector('[data-faq-no-results]');
        const sortSelect = container.querySelector('[data-faq-sort]');
        const statusRegion = container.querySelector('[data-faq-status]');
        const pageUids = container.dataset.faqPageUids || '';
        const msgResultsTemplate = container.dataset.faqMsgResults || '{count} FAQs shown.';
        const msgNoResults = container.dataset.faqMsgNoResults || 'No FAQs found.';

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

        const formatResultsMessage = (count) =>
            msgResultsTemplate.replace('{count}', String(count));

        const announceResults = (visibleCount) => {
            if (!statusRegion) return;
            statusRegion.textContent =
                visibleCount > 0
                    ? formatResultsMessage(visibleCount)
                    : msgNoResults;
        };

        const updateTabRovingFocus = (activeTab) => {
            if (!tabsContainer) return;
            tabsContainer.querySelectorAll('[data-faq-tab]').forEach((t) => {
                const isActive = t === activeTab;
                t.setAttribute('tabindex', isActive ? '0' : '-1');
            });
        };

        const linkTabPanelToTab = (tab) => {
            if (!faqList || !tab?.id) return;
            faqList.setAttribute('aria-labelledby', tab.id);
        };

        const focusTabPanel = () => {
            if (!faqList) return;
            faqList.focus({ preventScroll: true });
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

        const renderItems = (items, options = {}) => {
            const { announce = true, focusPanel = false } = options;
            faqList.innerHTML = '';
            let visibleCount = 0;

            items.forEach((item) => {
                if (!(matchesSearch(item) && matchesCategory(item))) return;
                visibleCount++;
                faqList.appendChild(buildItemElement(item));
            });

            if (noResults) noResults.hidden = visibleCount > 0;
            if (announce) announceResults(visibleCount);
            if (focusPanel) focusTabPanel();
        };

        const loadItems = async (categoryUid, options = {}) => {
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
                renderItems(currentItems, options);
            } catch (e) {
                console.error('Failed to load FAQ items:', e);
            }
        };

        const applyFilters = () => {
            renderItems(currentItems, { announce: true, focusPanel: false });
        };

        const getFilterState = () => ({
            cat: activeCategory,
            sort: sortField,
            order: sortOrder,
            q: searchQuery,
        });

        const encodeStateToHash = (state) => {
            const params = new URLSearchParams();
            if (state.cat && state.cat !== 'all') params.set('cat', state.cat);
            if (state.sort && (state.sort !== 'sorting' || state.order !== 'asc')) {
                params.set('sort', state.sort);
                params.set('order', state.order || 'asc');
            }
            if (state.q) params.set('q', state.q);
            return params.toString();
        };

        const decodeHashToState = (hash) => {
            if (!hash) return null;
            const params = new URLSearchParams(hash);
            const cat = params.get('cat');
            if (!cat && !params.has('sort') && !params.has('q')) return null;
            return {
                cat: cat || 'all',
                sort: params.get('sort') || 'sorting',
                order: params.get('order') || 'asc',
                q: params.get('q') || '',
            };
        };

        const pushURLState = () => {
            const state = getFilterState();
            const hash = encodeStateToHash(state);
            const url = window.location.pathname + (hash ? '#' + hash : '');
            history.pushState(state, '', url);
        };

        const replaceURLState = () => {
            const state = getFilterState();
            const hash = encodeStateToHash(state);
            const url = window.location.pathname + (hash ? '#' + hash : '');
            history.replaceState(state, '', url);
        };

        const applyStateFromURL = (state) => {
            if (!state) return;

            const prevCategory = activeCategory;
            const prevSortField = sortField;
            const prevSortOrder = sortOrder;

            activeCategory = state.cat;
            sortField = state.sort;
            sortOrder = state.order;
            searchQuery = state.q;

            if (tabsContainer) {
                tabsContainer.querySelectorAll('[data-faq-tab]').forEach((t) => {
                    const isActive = t.dataset.faqTab === activeCategory;
                    t.classList.toggle('mai-faq__tab--active', isActive);
                    t.setAttribute('aria-selected', String(isActive));
                    t.setAttribute('tabindex', isActive ? '0' : '-1');
                    if (isActive) linkTabPanelToTab(t);
                });
            }

            if (sortSelect) {
                sortSelect.value = sortField + ',' + sortOrder;
            }

            if (searchInput) {
                searchInput.value = searchQuery;
            }

            const categoryChanged = prevCategory !== activeCategory;
            const sortChanged = prevSortField !== sortField || prevSortOrder !== sortOrder;
            if (categoryChanged || sortChanged) {
                const categoryUid = activeCategory === 'all' ? 0 : parseInt(activeCategory, 10);
                loadItems(categoryUid, { announce: true, focusPanel: true });
                return;
            }

            renderItems(currentItems, { announce: true, focusPanel: false });
        };

        if (!faqPopstateBound) {
            window.addEventListener('popstate', (e) => {
                if (e.state) {
                    applyStateFromURL(e.state);
                }
            });
            faqPopstateBound = true;
        }

        const handleTabClick = async (tab) => {
            activeCategory = tab.dataset.faqTab;

            tabsContainer.querySelectorAll('[data-faq-tab]').forEach((t) => {
                const isActive = t === tab;
                t.classList.toggle('mai-faq__tab--active', isActive);
                t.setAttribute('aria-selected', String(isActive));
            });

            updateTabRovingFocus(tab);
            linkTabPanelToTab(tab);

            const categoryUid = activeCategory === 'all' ? 0 : parseInt(activeCategory, 10);
            await loadItems(categoryUid, { announce: true, focusPanel: true });
            pushURLState();
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
                await loadItems(categoryUid, { announce: true, focusPanel: true });
                pushURLState();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                searchQuery = normalize(searchInput.value);
                applyFilters();
                replaceURLState();
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

        const initialHash = window.location.hash.replace(/^#/, '');
        const initialState = decodeHashToState(initialHash);
        if (initialState) {
            applyStateFromURL(initialState);
        }
        replaceURLState();
    };

    document.querySelectorAll('[data-faq-container]').forEach(initFaqWidget);
})();
