document.addEventListener('DOMContentLoaded', async () => {
    const qtyValue = document.getElementById('qty-value');
    const qtyDecrease = document.getElementById('qty-decrease');
    const qtyIncrease = document.getElementById('qty-increase');
    const addToCartBtn = document.getElementById('add-to-cart');
    const feedback = document.getElementById('pdp-feedback');
    const mainImage = document.getElementById('pdp-main-image');
    const thumbsContainer = document.querySelector('.pdp-thumbs');
    const tabButtons = document.querySelectorAll('.pdp-tab');
    const infoPanel = document.getElementById('pdp-info-panel');
    const breadcrumb = document.getElementById('pdp-breadcrumb');
    const titleEl = document.querySelector('.pdp-title');
    const ratingEl = document.getElementById('pdp-rating');
    const priceEl = document.querySelector('.pdp-price');
    const basePriceEl = document.getElementById('pdp-base-price');
    const discountBadgeEl = document.getElementById('pdp-discount-badge');
    const descriptionEl = document.querySelector('.pdp-description');
    const specBox = document.querySelector('.pdp-spec-box');
    const stockBadge = document.getElementById('pdp-stock-badge');
    const relatedGrid = document.getElementById('related-products-grid');

    if (!qtyValue || !qtyDecrease || !qtyIncrease || !feedback || !mainImage || !infoPanel || !window.electrohubApi) {
        return;
    }

    const productId = new URLSearchParams(window.location.search).get('id');

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

    const renderRichTextLines = (value) => {
        const lines = String(value || '')
            .split(/\n|,|;/)
            .map((line) => line.trim())
            .filter(Boolean);

        return lines;
    };

    function showFeedback(message, variant) {
        feedback.textContent = message;
        feedback.classList.remove('success', 'info');
        feedback.classList.add(variant);
    }

    function getQuantity() {
        return Number.parseInt(qtyValue.textContent, 10) || 1;
    }

    function setQuantity(value) {
        qtyValue.textContent = String(Math.max(1, value));
    }

    const renderSpecificationRows = (specifications) => {
        const lines = renderRichTextLines(specifications);

        if (!lines.length) {
            return '<p>No specifications available.</p>';
        }

        return lines.map((line, index) => `<div class="pdp-spec-row"><span>Spec ${index + 1}:</span><strong>${escapeHtml(line)}</strong></div>`).join('');
    };

    const renderReviewContent = (reviews) => {
        if (!reviews.length) {
            return '<p>No reviews yet.</p>';
        }

        return reviews.map((review) => `
            <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                    <strong>${review.rating}/5</strong>
                    <small class="text-muted">${escapeHtml(review.created_at || '')}</small>
                </div>
                <p class="mb-0">${escapeHtml(review.text || 'No review text.')}</p>
            </div>
        `).join('');
    };

    const renderDescription = (product) => {
        const paragraphs = [
            product.description,
            product.specifications,
        ].filter(Boolean);

        if (!paragraphs.length) {
            return '<p>No description available.</p>';
        }

        return `
            <h3>About This Product</h3>
            ${paragraphs.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join('')}
        `;
    };

    const setActiveTab = (tabName, product, reviews) => {
        tabButtons.forEach((button) => {
            button.classList.toggle('active', button.dataset.tab === tabName);
        });

        if (tabName === 'specifications') {
            infoPanel.innerHTML = renderSpecificationRows(product.specifications);
            return;
        }

        if (tabName === 'reviews') {
            infoPanel.innerHTML = renderReviewContent(reviews);
            return;
        }

        infoPanel.innerHTML = renderDescription(product);
    };

    const renderRelatedProductCard = (item) => {
        const imageSrc = window.electrohubApi.imageUrl(item.image_path);
        return `
            <div class="col-12 col-sm-6 col-lg-3">
                <article class="product-card product-card-clickable js-related-product-card" data-product-url="product-detail.html?id=${item.id}" tabindex="0" role="button" aria-label="Open ${escapeHtml(item.title)} details">
                    <div style="position: relative;">
                        <img src="${imageSrc}" alt="${escapeHtml(item.image_alt || item.title)}" loading="lazy">
                    </div>
                    <div class="product-info">
                        <div class="product-rating">
                            <span class="stars">${renderStars(item.rating_avg)}</span>
                            <span>(${item.review_count || 0})</span>
                        </div>
                        <h3>${escapeHtml(item.title)}</h3>
                        <p>${escapeHtml(item.category_name)} · ${escapeHtml(item.brand_name)}</p>
                        <strong>${formatPrice(item.price)}</strong>
                    </div>
                </article>
            </div>
        `;
    };

    if (!productId) {
        infoPanel.innerHTML = '<p>Missing product id.</p>';
        return;
    }

    qtyDecrease.addEventListener('click', () => {
        setQuantity(getQuantity() - 1);
    });

    qtyIncrease.addEventListener('click', () => {
        setQuantity(getQuantity() + 1);
    });

    try {
        const response = await window.electrohubApi.get(`/products/${productId}`);
        const product = response.product;
        const pricing = response.pricing || {};
        const stock = response.stock || {};
        const images = Array.isArray(response.images) && response.images.length
            ? response.images
            : [{ image_path: product.image_path, alt_text: product.image_alt, is_title: true }];
        const reviews = Array.isArray(response.reviews) ? response.reviews : [];
        const relatedProducts = Array.isArray(response.related_products) ? response.related_products : [];

        document.title = `${product.title} - ElectroHub`;

        if (breadcrumb) {
            breadcrumb.innerHTML = `
                <a href="index.html"><i class="fas fa-home"></i> Home</a>
                <span>&gt;</span>
                <a href="product-list.html?category_id=${product.category_id}">${escapeHtml(product.category_name)}</a>
                <span>&gt;</span>
                <a href="product-list.html?brand_id=${product.brand_id}">${escapeHtml(product.brand_name)}</a>
                <span>&gt;</span>
                <span>${escapeHtml(product.title)}</span>
            `;
        }

        if (titleEl) {
            titleEl.textContent = product.title;
        }

        if (ratingEl) {
            ratingEl.innerHTML = `
                <span class="stars">${renderStars(product.rating_avg)}</span>
                ${Number(product.rating_avg || 0).toFixed(1)} out of 5 (${product.review_count || 0} reviews)
                <a href="#" id="pdp-see-reviews" style="margin-left: var(--spacing-md); color: var(--primary);">See all reviews</a>
            `;
        }

        if (priceEl) {
            priceEl.textContent = formatPrice(pricing.final_price ?? product.price);
        }

        if (basePriceEl) {
            if (pricing.discount_percent > 0) {
                basePriceEl.textContent = formatPrice(pricing.base_price);
                basePriceEl.style.display = 'inline';
            } else {
                basePriceEl.style.display = 'none';
            }
        }

        if (discountBadgeEl) {
            if (pricing.discount_percent > 0) {
                discountBadgeEl.textContent = `Save ${Number(pricing.discount_percent).toFixed(0)}%`;
                discountBadgeEl.style.display = 'inline-block';
            } else {
                discountBadgeEl.style.display = 'none';
            }
        }

        if (descriptionEl) {
            descriptionEl.textContent = product.description || 'No description available.';
        }

        if (specBox) {
            specBox.innerHTML = `
                <h2>Key Specifications</h2>
                <div id="pdp-spec-rows"></div>
            `;
        }

        const specRowsContainer = document.getElementById('pdp-spec-rows');
        if (specRowsContainer) {
            specRowsContainer.innerHTML = renderSpecificationRows(product.specifications);
        }

        if (stockBadge) {
            stockBadge.textContent = stock.in_stock ? 'IN STOCK' : 'OUT OF STOCK';
            stockBadge.style.background = stock.in_stock ? 'var(--success)' : 'var(--danger)';
        }

        const mainImageTag = mainImage.querySelector('img');
        if (mainImageTag) {
            mainImageTag.src = window.electrohubApi.imageUrl(images[0]?.image_path);
            mainImageTag.alt = images[0]?.alt_text || product.title;
        }

        if (thumbsContainer) {
            thumbsContainer.innerHTML = images.map((image, index) => `
                <button type="button" class="pdp-thumb${index === 0 ? ' is-active' : ''}" data-image="${escapeHtml(image.alt_text || product.title)}" aria-label="${escapeHtml(image.alt_text || product.title)}">
                    <img src="${window.electrohubApi.imageUrl(image.image_path)}" alt="${escapeHtml(image.alt_text || product.title)}" style="width: 100%; height: 100%; object-fit: cover;">
                </button>
            `).join('');

            thumbsContainer.querySelectorAll('.pdp-thumb').forEach((thumb) => {
                thumb.addEventListener('click', () => {
                    thumbsContainer.querySelectorAll('.pdp-thumb').forEach((item) => item.classList.remove('is-active'));
                    thumb.classList.add('is-active');
                    const thumbImage = thumb.querySelector('img');
                    const mainImageTag = mainImage.querySelector('img');

                    if (thumbImage && mainImageTag) {
                        mainImageTag.src = thumbImage.src;
                        mainImageTag.alt = thumbImage.alt || product.title;
                    }
                });
            });
        }

        tabButtons.forEach((button) => {
            if (button.dataset.tab === 'reviews') {
                button.textContent = `Reviews (${reviews.length})`;
            }

            button.addEventListener('click', () => {
                setActiveTab(button.dataset.tab || 'description', product, reviews);
            });
        });

        const seeReviewsLink = document.getElementById('pdp-see-reviews');
        if (seeReviewsLink) {
            seeReviewsLink.addEventListener('click', (event) => {
                event.preventDefault();
                setActiveTab('reviews', product, reviews);
            });
        }

        setActiveTab('description', product, reviews);

        if (relatedGrid) {
            relatedGrid.innerHTML = relatedProducts.length
                ? relatedProducts.map(renderRelatedProductCard).join('')
                : '<div class="col-12"><div class="card p-4">No related products found.</div></div>';

            relatedGrid.querySelectorAll('.js-related-product-card').forEach((card) => {
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
        }

        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', () => {
                const quantity = getQuantity();
                showFeedback(`Added ${quantity} item(s) of ${product.title} to cart.`, 'success');
            });
        }
    } catch (error) {
        infoPanel.innerHTML = '<p>Failed to load product details from the API.</p>';
        showFeedback('Failed to load product details.', 'info');
    }
});
