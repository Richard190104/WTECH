document.addEventListener('DOMContentLoaded', async () => {
    const grid = document.getElementById('product-grid');
    const resultCount = document.getElementById('result-count');
    const sortSelect = document.getElementById('sort-select');
    const resetFiltersBtn = document.getElementById('reset-filters');
    const priceMinInput = document.getElementById('price-min');
    const priceMaxInput = document.getElementById('price-max');
    const priceRangeInput = document.getElementById('price-range');
    const searchInput = document.querySelector('.neutral-search');
    const sidebar = document.querySelector('.plp-sidebar');
    const brandCard = document.querySelectorAll('.plp-filter-card')[1];
    const inStockInput = document.getElementById('availability-in-stock');
    const ratingSelect = document.getElementById('rating-filter-select');

    if (!grid || !resultCount || !sortSelect || !resetFiltersBtn || !window.electrohubApi) {
        return;
    }

    const state = {
        page: Number(new URLSearchParams(window.location.search).get('page') || 1),
        sort: new URLSearchParams(window.location.search).get('sort') || 'newest',
        priceMin: new URLSearchParams(window.location.search).get('price_min') || '',
        priceMax: new URLSearchParams(window.location.search).get('price_max') || '',
        brandId: new URLSearchParams(window.location.search).get('brand_id') || '',
        categoryId: new URLSearchParams(window.location.search).get('category_id') || '',
        inStock: new URLSearchParams(window.location.search).get('in_stock') === '1',
        ratingMin: new URLSearchParams(window.location.search).get('rating_min') || '',
        q: new URLSearchParams(window.location.search).get('q') || '',
        perPage: 12,
    };

    const placeholderImage = 'images/Playdock 5 console.jpg';

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const formatPrice = (value) => `$${Number(value || 0).toFixed(2)}`;

    const renderStars = (rating) => {
        const rounded = Number(rating || 0);
        const fullStars = Math.floor(rounded);
        const hasHalfStar = rounded - fullStars >= 0.5;
        let html = '';

        for (let index = 0; index < 5; index += 1) {
            if (index < fullStars) {
                html += '<i class="fas fa-star"></i>';
            } else if (index === fullStars && hasHalfStar) {
                html += '<i class="fas fa-star-half-alt"></i>';
            } else {
                html += '<i class="far fa-star"></i>';
            }
        }

        return html;
    };

    const getSelectedBrandId = () => document.querySelector('input[name="brand_id"]:checked')?.value || '';

    const syncUrl = () => {
        const params = new URLSearchParams();

        if (state.page > 1) params.set('page', String(state.page));
        if (state.sort && state.sort !== 'newest') params.set('sort', state.sort);
        if (state.priceMin !== '') params.set('price_min', state.priceMin);
        if (state.priceMax !== '') params.set('price_max', state.priceMax);
        if (state.brandId !== '') params.set('brand_id', state.brandId);
        if (state.categoryId !== '') params.set('category_id', state.categoryId);
        if (state.inStock) params.set('in_stock', '1');
        if (state.ratingMin !== '') params.set('rating_min', state.ratingMin);
        if (state.q !== '') params.set('q', state.q);

        const nextUrl = `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}`;
        window.history.replaceState({}, '', nextUrl);
    };

    const buildQuery = () => ({
        page: state.page,
        per_page: state.perPage,
        sort: state.sort,
        price_min: state.priceMin,
        price_max: state.priceMax,
        brand_id: state.brandId,
        category_id: state.categoryId,
        in_stock: state.inStock ? 1 : '',
        rating_min: state.ratingMin,
        q: state.q,
    });

    const parsePrice = (value) => {
        const parsed = Number.parseFloat(String(value).replace(',', '.'));
        return Number.isFinite(parsed) ? parsed : null;
    };

    let priceFilterTimer = null;
    const schedulePriceReload = () => {
        if (priceFilterTimer) {
            window.clearTimeout(priceFilterTimer);
        }

        priceFilterTimer = window.setTimeout(() => {
            state.page = 1;
            loadProducts();
        }, 200);
    };

    const cardMarkup = (product) => {
        const imageSrc = window.electrohubApi.imageUrl(product.image_path) || placeholderImage;
        const discount = Number(product.discount || 0);
        const description = product.description || product.specifications || 'Available in the catalog.';

        return `
            <div class="col-12 col-sm-6 col-xl-3">
                <article class="product-card product-card-clickable js-list-product-card" data-product-id="${product.id}" data-product-url="product-detail.html?id=${product.id}" tabindex="0" role="button" aria-label="Open ${escapeHtml(product.title)} details">
                    ${discount > 0 ? `<div class="product-discount-badge">-${discount}%</div>` : ''}
                    <a href="product-detail.html?id=${product.id}" class="product-card-link">
                        <div class="product-image-placeholder">
                            <img src="${imageSrc}" alt="${escapeHtml(product.image_alt || product.title)}">
                        </div>
                    </a>
                    <div class="product-info">
                        <h3><a href="product-detail.html?id=${product.id}" class="product-name-link">${escapeHtml(product.title)}</a></h3>
                        <p>${escapeHtml(description)}</p>
                        <div class="product-rating">
                            <div class="stars">${renderStars(product.rating_avg)}</div>
                            <span>(${product.review_count || 0})</span>
                        </div>
                        <strong>${formatPrice(product.price)}</strong>
                        <div class="product-card-actions">
                            <button type="button" class="product-view-btn plp-add-btn" data-product-id="${product.id}" data-product="${escapeHtml(product.title)}">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        `;
    };

    const renderProducts = (products) => {
        grid.innerHTML = products.length
            ? products.map(cardMarkup).join('')
            : '<div class="col-12"><div class="card p-4">No products match the selected filters.</div></div>';

        grid.querySelectorAll('.js-list-product-card').forEach((card) => {
            card.addEventListener('click', (event) => {
                if (event.target.closest('button')) {
                    return;
                }
                window.location.href = card.dataset.productUrl;
            });

            card.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    window.location.href = card.dataset.productUrl;
                }
            });
        });

        grid.querySelectorAll('.plp-add-btn').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const original = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Added!';
                button.disabled = true;

                window.setTimeout(() => {
                    button.innerHTML = original;
                    button.disabled = false;
                }, 1200);
            });
        });
    };

    const renderPagination = (meta) => {
        const existing = document.querySelector('.plp-pagination');
        if (!existing) {
            return;
        }

        const lastPage = Number(meta.last_page || 1);
        const currentPage = Number(meta.current_page || 1);
        const pageNumbers = [];
        const startPage = Math.max(1, currentPage - 1);
        const endPage = Math.min(lastPage, startPage + 3);

        for (let page = startPage; page <= endPage; page += 1) {
            pageNumbers.push(`
                <button type="button" class="plp-page-btn plp-page-num${page === currentPage ? ' active' : ''}" data-page="${page}" ${page === currentPage ? 'aria-current="page"' : ''}>${page}</button>
            `);
        }

        existing.innerHTML = `
            <button type="button" class="plp-page-btn plp-page-prev" data-page="${Math.max(1, currentPage - 1)}" ${currentPage === 1 ? 'disabled' : ''} aria-label="Previous page">
                <i class="fas fa-chevron-left"></i>
            </button>
            ${pageNumbers.join('')}
            <button type="button" class="plp-page-btn plp-page-next" data-page="${Math.min(lastPage, currentPage + 1)}" ${currentPage === lastPage ? 'disabled' : ''} aria-label="Next page">
                <i class="fas fa-chevron-right"></i> Next
            </button>
        `;

        existing.querySelectorAll('[data-page]').forEach((button) => {
            button.addEventListener('click', () => {
                state.page = Number(button.dataset.page || 1);
                loadProducts();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    };

    const ensureCategoryFilter = (categories) => {
        if (!sidebar || document.getElementById('category-filter-card')) {
            return;
        }

        const categoryCard = document.createElement('div');
        categoryCard.className = 'plp-filter-card';
        categoryCard.id = 'category-filter-card';
        categoryCard.innerHTML = `
            <h2 class="plp-filter-heading">Category</h2>
            <select id="category-filter-select" class="plp-sort-select" style="width: 100%;">
                <option value="">All categories</option>
                ${categories.map((category) => `<option value="${category.id}">${escapeHtml(category.name)}</option>`).join('')}
            </select>
        `;

        if (brandCard && brandCard.parentElement) {
            brandCard.insertAdjacentElement('afterend', categoryCard);
        } else {
            sidebar.insertBefore(categoryCard, resetFiltersBtn);
        }

        const categorySelect = document.getElementById('category-filter-select');
        if (categorySelect) {
            categorySelect.value = state.categoryId;
            categorySelect.addEventListener('change', () => {
                state.categoryId = categorySelect.value;
                state.page = 1;
                loadProducts();
            });
        }
    };

    const ensureBrandFilter = (brands) => {
        const brandList = brandCard?.querySelector('.plp-check-list');

        if (!brandList) {
            return;
        }

        brandList.innerHTML = [
            `<label class="plp-check-item"><input type="radio" name="brand_id" value="" ${state.brandId === '' ? 'checked' : ''}> All brands</label>`,
            ...brands.map((brand) => `<label class="plp-check-item"><input type="radio" name="brand_id" value="${brand.id}" ${String(state.brandId) === String(brand.id) ? 'checked' : ''}> ${escapeHtml(brand.name)}</label>`),
        ].join('');

        brandList.querySelectorAll('input[name="brand_id"]').forEach((input) => {
            input.addEventListener('change', () => {
                state.brandId = getSelectedBrandId();
                state.page = 1;
                loadProducts();
            });
        });
    };

    const syncAvailabilityAndRatingControls = () => {
        if (inStockInput) {
            inStockInput.checked = state.inStock;
            inStockInput.addEventListener('change', () => {
                state.inStock = inStockInput.checked;
                state.page = 1;
                loadProducts();
            });
        }

        if (ratingSelect) {
            ratingSelect.value = state.ratingMin;
            ratingSelect.addEventListener('change', () => {
                state.ratingMin = ratingSelect.value;
                state.page = 1;
                loadProducts();
            });
        }
    };

    const updateToolbarText = (meta) => {
        const total = Number(meta.total || 0);
        const perPage = Number(meta.per_page || state.perPage);
        const currentPage = Number(meta.current_page || 1);
        const start = total === 0 ? 0 : ((currentPage - 1) * perPage) + 1;
        const end = Math.min(total, currentPage * perPage);

        resultCount.textContent = `Showing ${start}–${end} of ${total} products`;
    };

    async function loadProducts() {
        state.brandId = getSelectedBrandId();
        state.sort = sortSelect.value || 'newest';
        const parsedMin = parsePrice(priceMinInput.value.trim());
        const parsedMax = parsePrice(priceMaxInput.value.trim());

        let normalizedMin = parsedMin;
        let normalizedMax = parsedMax;

        // Keep range valid even if user inputs min > max.
        if (normalizedMin !== null && normalizedMax !== null && normalizedMin > normalizedMax) {
            const temp = normalizedMin;
            normalizedMin = normalizedMax;
            normalizedMax = temp;
        }

        state.priceMin = normalizedMin === null ? '' : String(normalizedMin);
        state.priceMax = normalizedMax === null ? '' : String(normalizedMax);

        priceMinInput.value = state.priceMin;
        priceMaxInput.value = state.priceMax;
        if (priceRangeInput && state.priceMax !== '') {
            priceRangeInput.value = state.priceMax;
        }

        state.q = searchInput ? searchInput.value.trim() : state.q;

        syncUrl();

        const response = await window.electrohubApi.get('/products', buildQuery());
        const products = Array.isArray(response.products) ? response.products : [];
        const meta = response.meta || {};

        ensureBrandFilter(response.filter_options?.brands || []);
        ensureCategoryFilter(response.filter_options?.categories || []);
        renderProducts(products);
        renderPagination(meta);
        updateToolbarText(meta);
    }

    if (searchInput) {
        searchInput.value = state.q;
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                state.page = 1;
                loadProducts();
            }
        });
    }

    sortSelect.value = state.sort;
    priceMinInput.value = state.priceMin;
    priceMaxInput.value = state.priceMax || priceMaxInput.value || '';
    if (priceRangeInput && priceMaxInput.value) {
        priceRangeInput.value = priceMaxInput.value;
    }

    sortSelect.addEventListener('change', () => {
        state.page = 1;
        loadProducts();
    });

    priceMinInput.addEventListener('input', () => {
        schedulePriceReload();
    });
    priceMinInput.addEventListener('change', () => {
        schedulePriceReload();
    });

    priceMaxInput.addEventListener('input', () => {
        if (priceRangeInput) {
            priceRangeInput.value = priceMaxInput.value || priceRangeInput.min || '0';
        }
        schedulePriceReload();
    });
    priceMaxInput.addEventListener('change', () => {
        if (priceRangeInput) {
            priceRangeInput.value = priceMaxInput.value;
        }
        schedulePriceReload();
    });

    if (priceRangeInput) {
        priceRangeInput.addEventListener('input', (event) => {
            priceMaxInput.value = event.target.value;
            schedulePriceReload();
        });
        priceRangeInput.addEventListener('change', (event) => {
            priceMaxInput.value = event.target.value;
            schedulePriceReload();
        });
    }

    resetFiltersBtn.addEventListener('click', () => {
        state.page = 1;
        state.sort = 'newest';
        state.priceMin = '';
        state.priceMax = '';
        state.brandId = '';
        state.categoryId = '';
        state.inStock = false;
        state.ratingMin = '';
        state.q = '';

        sortSelect.value = 'newest';
        priceMinInput.value = '';
        priceMaxInput.value = '';
        if (priceRangeInput) {
            priceRangeInput.value = priceRangeInput.max || '2000';
        }
        if (searchInput) {
            searchInput.value = '';
        }
        if (inStockInput) {
            inStockInput.checked = false;
        }
        if (ratingSelect) {
            ratingSelect.value = '';
        }

        loadProducts();
    });

    try {
        syncAvailabilityAndRatingControls();
        await loadProducts();
    } catch (error) {
        grid.innerHTML = '<div class="col-12"><div class="card p-4">Failed to load products from the API.</div></div>';
        resultCount.textContent = 'Failed to load products.';
    }
});
