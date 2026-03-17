document.addEventListener("DOMContentLoaded", () => {
    const qtyValue = document.getElementById("qty-value");
    const qtyDecrease = document.getElementById("qty-decrease");
    const qtyIncrease = document.getElementById("qty-increase");
    const addToCartBtn = document.getElementById("add-to-cart");
    const addToWishlistBtn = document.getElementById("add-to-wishlist");
    const feedback = document.getElementById("pdp-feedback");
    const mainImage = document.getElementById("pdp-main-image");
    const thumbs = document.querySelectorAll(".pdp-thumb");
    const tabButtons = document.querySelectorAll(".pdp-tab");
    const infoPanel = document.getElementById("pdp-info-panel");
    const relatedButtons = document.querySelectorAll(".pdp-related-btn");

    if (!qtyValue || !qtyDecrease || !qtyIncrease || !feedback || !mainImage || !infoPanel) {
        return;
    }

    const tabContent = {
        description: `
            <p>
                Detailed product description goes here. This section contains comprehensive information
                about the product features, benefits, and use cases.
            </p>
            <p>
                Additional paragraphs providing more context about the product specifications, warranty
                information, and other relevant details.
            </p>
        `,
        specifications: `
            <p><strong>Display:</strong> 15.6-inch Full HD IPS, 250 nits</p>
            <p><strong>Memory:</strong> 16GB DDR4, upgradable to 32GB</p>
            <p><strong>Storage:</strong> 512GB NVMe SSD</p>
            <p><strong>Ports:</strong> USB-C, 2x USB-A, HDMI, audio jack</p>
            <p><strong>Battery:</strong> Up to 10 hours mixed use</p>
        `,
        reviews: `
            <p><strong>John D.</strong> - "Fast, reliable and perfect for office work."</p>
            <p><strong>Anna K.</strong> - "Great keyboard and screen quality for the price."</p>
            <p><strong>Martin P.</strong> - "Battery life is solid and build feels premium."</p>
        `
    };

    function showFeedback(message, variant) {
        feedback.textContent = message;
        feedback.classList.remove("success", "info");
        feedback.classList.add(variant);
    }

    function getQuantity() {
        return Number.parseInt(qtyValue.textContent, 10) || 1;
    }

    function setQuantity(value) {
        const safeValue = Math.max(1, value);
        qtyValue.textContent = String(safeValue);
    }

    qtyDecrease.addEventListener("click", () => {
        setQuantity(getQuantity() - 1);
    });

    qtyIncrease.addEventListener("click", () => {
        setQuantity(getQuantity() + 1);
    });

    thumbs.forEach((thumb) => {
        thumb.addEventListener("click", () => {
            thumbs.forEach((item) => item.classList.remove("is-active"));
            thumb.classList.add("is-active");
            const thumbImage = thumb.querySelector("img");
            const mainImageTag = mainImage.querySelector("img");
            if (thumbImage && mainImageTag) {
                mainImageTag.src = thumbImage.src;
                mainImageTag.alt = thumbImage.alt || "Product View";
            }
        });
    });

    tabButtons.forEach((button) => {
        button.addEventListener("click", () => {
            tabButtons.forEach((item) => item.classList.remove("active"));
            button.classList.add("active");
            const key = button.dataset.tab || "description";
            infoPanel.innerHTML = tabContent[key] || tabContent.description;
        });
    });

    addToCartBtn.addEventListener("click", () => {
        const quantity = getQuantity();
        showFeedback("Added " + quantity + " item(s) to cart.", "success");
    });

    addToWishlistBtn.addEventListener("click", () => {
        showFeedback("Saved to wishlist.", "info");
    });

    relatedButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const productName = button.dataset.relatedProduct || "Related product";
            showFeedback(productName + " added to cart.", "success");
        });
    });
});
