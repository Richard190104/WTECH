document.addEventListener('DOMContentLoaded', async () => {
    const featuredGrid = document.getElementById('home-featured-grid');
    const trendingGrid = document.getElementById('home-trending-grid');
    const homeSearchInput = document.querySelector('.neutral-search');

    if (!featuredGrid || !trendingGrid || !window.electrohubApi) {
        return;
    }

    const starMarkup = (rating) => {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating - fullStars >= 0.5;
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

    const formatPrice = (value) => `$${Number(value || 0).toFixed(2)}`;

    const cardTemplate = (product) => {
        const imageSrc = window.electrohubApi.imageUrl(product.image_path);
        const description = product.description || product.specifications || 'Available now in our catalog.';
        const rating = Number(product.rating_avg || 0);
        const discount = Number(product.discount || 0);

        return `
            <div class="col-12 col-md-6 col-xl-4">
                <article class="product-card product-card-clickable js-product-card" data-product-url="product-detail.html?id=${product.id}" data-product-id="${product.id}" tabindex="0" role="button" aria-label="Open ${product.title} details">
                    <div style="position: relative;">
                        <img src="${imageSrc}" alt="${product.image_alt || product.title}" loading="lazy">
                        ${discount > 0 ? `<span class="product-discount-badge">-${discount}%</span>` : ''}
                    </div>
                    <div class="product-info">
                        <div class="product-rating">
                            <span class="stars">${starMarkup(rating)}</span>
                            <span>(${product.review_count || 0})</span>
                        </div>
                        <h3>${product.title}</h3>
                        <p>${description}</p>
                        <strong>${formatPrice(product.price)}</strong>
                        <div class="product-card-actions">
                            <button type="button" class="product-view-btn js-home-add-btn" data-product-id="${product.id}" data-product="${product.title}">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        `;
    };

    const renderCards = (grid, products) => {
        grid.innerHTML = products.length
            ? products.map(cardTemplate).join('')
            : '<div class="col-12"><div class="card p-4">No products found.</div></div>';
    };

    if (homeSearchInput) {
        homeSearchInput.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            const term = homeSearchInput.value.trim();
            const params = new URLSearchParams();

            if (term !== '') {
                params.set('q', term);
            }

            window.location.href = `product-list.html${params.toString() ? `?${params.toString()}` : ''}`;
        });
    }

    try {
        const response = await window.electrohubApi.get('/products', { per_page: 10, sort: 'newest' });
        const products = Array.isArray(response.products) ? response.products : [];

        renderCards(featuredGrid, products.slice(0, 6));
        renderCards(trendingGrid, products.slice(6, 10).length ? products.slice(6, 10) : products.slice(0, 4));

        document.querySelectorAll('.js-product-card').forEach((card) => {
            card.addEventListener('click', (event) => {
                if (event.target.closest('button')) {
                    return;
                }
                window.location.href = card.dataset.productUrl || 'product-detail.html';
            });

            card.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    window.location.href = card.dataset.productUrl || 'product-detail.html';
                }
            });
        });

        document.querySelectorAll('.js-home-add-btn').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const originalHtml = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Added!';
                button.disabled = true;

                window.setTimeout(() => {
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                }, 1200);
            });
        });
    } catch (error) {
        featuredGrid.innerHTML = '<div class="col-12"><div class="card p-4">Failed to load products from the API.</div></div>';
        trendingGrid.innerHTML = '';
    }
});
