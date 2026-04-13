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
    const breadcrumb = document.querySelector('.pdp-breadcrumb');
    const titleEl = document.querySelector('.pdp-title');
    const ratingEl = document.querySelector('.pdp-rating');
    const priceEl = document.querySelector('.pdp-price');
    const descriptionEl = document.querySelector('.pdp-description');
    const specBox = document.querySelector('.pdp-spec-box');

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
        const lines = String(specifications || '')
            .split(/\n|,|;/)
            .map((line) => line.trim())
            .filter(Boolean);

        if (!lines.length) {
            return '<p>No specifications available.</p>';
        }

        return lines.map((line, index) => `<div class="pdp-spec-row"><span>Spec ${index + 1}:</span><strong>${escapeHtml(line)}</strong></div>`).join('');
    };

    const renderReviewContent = (reviews) => {
        if (!reviews.length) {
            return '<p>No reviews yet.</p>';
        }

        return reviews.map((review) => `<p><strong>${review.rating}/5</strong> - ${escapeHtml(review.text || 'No review text.')} <small>${review.created_at || ''}</small></p>`).join('');
    };

    const updateTabContent = (key, product, reviews) => {
        if (key === 'specifications') {
            infoPanel.innerHTML = renderSpecificationRows(product.specifications);
            return;
        }

        if (key === 'reviews') {
            infoPanel.innerHTML = renderReviewContent(reviews);
            return;
        }

        infoPanel.innerHTML = `
            <h3>About This Product</h3>
            <p>${escapeHtml(product.description || 'No description available.')}</p>
            <h4 style="margin-top: var(--spacing-lg);">Key Features:</h4>
            <p>${escapeHtml(product.specifications || 'No additional details available.')}</p>
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
        const images = Array.isArray(response.images) && response.images.length ? response.images : [{ image_path: product.image_path, alt_text: product.image_alt, is_title: true }];
        const reviews = Array.isArray(response.reviews) ? response.reviews : [];

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
            `;
        }

        if (priceEl) {
            priceEl.textContent = formatPrice(product.price);
        }

        if (descriptionEl) {
            descriptionEl.textContent = product.description || 'No description available.';
        }

        if (specBox) {
            specBox.innerHTML = `
                <h2>Key Specifications</h2>
                ${renderSpecificationRows(product.specifications)}
            `;
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
                tabButtons.forEach((item) => item.classList.remove('active'));
                button.classList.add('active');
                updateTabContent(button.dataset.tab || 'description', product, reviews);
            });
        });

        updateTabContent('description', product, reviews);

        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', () => {
                const quantity = getQuantity();
                showFeedback(`Added ${quantity} item(s) of ${product.title} to cart.`, 'success');
            });
        }

        const stockBadge = document.querySelector('.pdp-actions')?.previousElementSibling?.querySelector('span');
        if (stockBadge) {
            stockBadge.textContent = Number(product.qty || 0) > 0 ? 'IN STOCK' : 'OUT OF STOCK';
        }
    } catch (error) {
        infoPanel.innerHTML = '<p>Failed to load product details from the API.</p>';
        showFeedback('Failed to load product details.', 'info');
    }
});
