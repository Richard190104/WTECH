(function () {
    const ADMIN_AUTH_KEY = "electrohub_admin_auth";
    const ADMIN_PRODUCTS_KEY = "electrohub_admin_products";
    const ADMIN_EMAIL = "admin";
    const ADMIN_PASSWORD = "admin";

    const initialProducts = [
        {
            id: "p-1001",
            title: "PlayDock 5 Console",
            price: 299.99,
            discount: 20,
            quantity: 25,
            description: "Latest gaming console with 4K support.",
            specifications: "4K output, 1TB SSD, Wi-Fi 6, DualSense controller",
            category: "Gaming",
            brand: "PlayDock",
            images: ["images/Playdock%205%20console.jpg", "images/silly%20cat.jpg"]
        },
        {
            id: "p-1002",
            title: "UltraBook Pro 15",
            price: 349.99,
            discount: 10,
            quantity: 16,
            description: "Slim performance laptop for work and study.",
            specifications: "15 inch display, 16GB RAM, 512GB SSD, Intel Core i7",
            category: "Computers",
            brand: "ElectroHub",
            images: ["images/ultrabook%20pro.jpg"]
        },
        {
            id: "p-1003",
            title: "SmartPhone X12",
            price: 395.99,
            discount: 15,
            quantity: 42,
            description: "Fast smartphone with a great OLED display.",
            specifications: "6.7 inch OLED, 128GB storage, 5G, 50MP camera",
            category: "Phones",
            brand: "XMobile",
            images: ["images/smartphone%20x12.jpg", "images/silly%20cat.jpg"]
        }
    ];

    function getSelectedImageNames(fileInput) {
        return Array.from(fileInput.files || []).map((file) => file.name);
    }

    function renderImagePreview(previewEl, images) {
        if (!previewEl) {
            return;
        }

        previewEl.innerHTML = "";
        if (!images.length) {
            const emptyItem = document.createElement("li");
            emptyItem.textContent = "No images selected";
            previewEl.appendChild(emptyItem);
            return;
        }

        images.forEach((imageName) => {
            const item = document.createElement("li");
            item.textContent = imageName;
            previewEl.appendChild(item);
        });
    }

    function safeParse(json, fallback) {
        try {
            const parsed = JSON.parse(json);
            return parsed || fallback;
        } catch (error) {
            return fallback;
        }
    }

    function getProducts() {
        return safeParse(localStorage.getItem(ADMIN_PRODUCTS_KEY), []);
    }

    function setProducts(products) {
        localStorage.setItem(ADMIN_PRODUCTS_KEY, JSON.stringify(products));
    }

    function ensureProducts() {
        if (!localStorage.getItem(ADMIN_PRODUCTS_KEY)) {
            setProducts(initialProducts);
        }
    }

    function getAdminAuth() {
        return safeParse(localStorage.getItem(ADMIN_AUTH_KEY), null);
    }

    function setAdminAuth(authPayload) {
        localStorage.setItem(ADMIN_AUTH_KEY, JSON.stringify(authPayload));
    }

    function clearAdminAuth() {
        localStorage.removeItem(ADMIN_AUTH_KEY);
    }

    function requireAuth() {
        const currentPage = window.location.pathname.split("/").pop();
        if ((currentPage === "admin-products.html" || currentPage === "admin-product-form.html") && !getAdminAuth()) {
            window.location.href = "admin-login.html";
            return false;
        }
        return true;
    }

    function setupLogoutButtons() {
        document.querySelectorAll(".js-admin-logout").forEach((btn) => {
            btn.addEventListener("click", () => {
                clearAdminAuth();
                window.location.href = "admin-login.html";
            });
        });
    }

    function setupAdminLogin() {
        const form = document.getElementById("admin-login-form");
        if (!form) {
            return;
        }

        const feedback = document.getElementById("admin-login-feedback");

        form.addEventListener("submit", (event) => {
            event.preventDefault();
            const email = document.getElementById("admin-email").value.trim();
            const password = document.getElementById("admin-password").value;

            feedback.textContent = "";
            feedback.className = "admin-feedback";

            if (!email || !password) {
                feedback.textContent = "Please enter both email and password.";
                feedback.classList.add("error");
                return;
            }

            if (email !== ADMIN_EMAIL || password !== ADMIN_PASSWORD) {
                feedback.textContent = "Invalid login credentials.";
                feedback.classList.add("error");
                return;
            }

            setAdminAuth({
                email,
                loggedAt: new Date().toISOString()
            });

            feedback.textContent = "Login successful. Redirecting...";
            feedback.classList.add("success");

            setTimeout(() => {
                window.location.href = "admin-products.html";
            }, 650);
        });
    }

    function renderAdminProducts() {
        const tableBody = document.getElementById("admin-products-body");
        const emptyState = document.getElementById("admin-empty-state");
        const countBadge = document.getElementById("admin-products-count");

        if (!tableBody || !emptyState || !countBadge) {
            return;
        }

        const products = getProducts();
        tableBody.innerHTML = "";
        countBadge.textContent = String(products.length);

        if (!products.length) {
            emptyState.hidden = false;
            return;
        }

        emptyState.hidden = true;

        products.forEach((product) => {
            const title = product.title || product.name || "Untitled";
            const category = product.category || "-";
            const brand = product.brand || "-";
            const price = Number(product.price);
            const discount = Number(product.discount || 0);
            const quantity = Number(product.quantity || 0);

            const row = document.createElement("tr");
            row.innerHTML = ""
                + "<td><strong>" + title + "</strong></td>"
                + "<td>" + category + "</td>"
                + "<td>" + brand + "</td>"
                + "<td>$" + (Number.isFinite(price) ? price.toFixed(2) : "0.00") + "</td>"
                + "<td>" + (Number.isFinite(discount) ? discount : 0) + "%</td>"
                + "<td>" + (Number.isFinite(quantity) ? quantity : 0) + "</td>"
                + "<td>"
                + "<a href='admin-product-form.html?id=" + encodeURIComponent(product.id) + "' class='admin-table-action'>Edit</a>"
                + "<button type='button' class='admin-table-action danger js-delete-product' data-id='" + product.id + "'>Delete</button>"
                + "</td>";
            tableBody.appendChild(row);
        });

        document.querySelectorAll(".js-delete-product").forEach((btn) => {
            btn.addEventListener("click", () => {
                const productId = btn.dataset.id;
                if (!window.confirm("Are you sure you want to delete this product?")) {
                    return;
                }

                const currentProducts = getProducts();
                const updatedProducts = currentProducts.filter((item) => item.id !== productId);
                setProducts(updatedProducts);
                renderAdminProducts();
            });
        });
    }

    function setupProductForm() {
        const form = document.getElementById("admin-product-form");
        if (!form) {
            return;
        }

        const params = new URLSearchParams(window.location.search);
        const productId = params.get("id");
        const title = document.getElementById("admin-form-title");
        const submitBtn = document.getElementById("admin-form-submit");
        const feedback = document.getElementById("admin-form-feedback");
        const imagesInput = document.getElementById("product-images");
        const imagesPreview = document.getElementById("product-images-preview");
        let existingImages = [];

        if (imagesInput) {
            imagesInput.addEventListener("change", () => {
                renderImagePreview(imagesPreview, getSelectedImageNames(imagesInput));
            });
        }

        if (productId) {
            const existing = getProducts().find((product) => product.id === productId);
            if (existing) {
                title.textContent = "Edit product";
                submitBtn.textContent = "Save changes";

                document.getElementById("product-title").value = existing.title || existing.name || "";
                document.getElementById("product-price").value = existing.price;
                document.getElementById("product-discount").value = Number(existing.discount || 0);
                document.getElementById("product-quantity").value = Number(existing.quantity || 0);
                document.getElementById("product-description").value = existing.description;
                document.getElementById("product-specifications").value = existing.specifications || "";
                document.getElementById("product-category").value = existing.category;
                document.getElementById("product-brand").value = existing.brand;
                existingImages = Array.isArray(existing.images) ? existing.images : [];
                renderImagePreview(imagesPreview, existingImages);
            }
        } else {
            renderImagePreview(imagesPreview, []);
        }

        form.addEventListener("submit", (event) => {
            event.preventDefault();
            feedback.textContent = "";
            feedback.className = "admin-feedback";

            const productData = {
                id: productId || "p-" + Date.now(),
                title: document.getElementById("product-title").value.trim(),
                price: Number(document.getElementById("product-price").value),
                discount: Number(document.getElementById("product-discount").value),
                quantity: Number(document.getElementById("product-quantity").value),
                description: document.getElementById("product-description").value.trim(),
                specifications: document.getElementById("product-specifications").value.trim(),
                category: document.getElementById("product-category").value.trim(),
                brand: document.getElementById("product-brand").value.trim(),
                images: []
            };

            const selectedImageNames = imagesInput ? getSelectedImageNames(imagesInput) : [];
            productData.images = selectedImageNames.length ? selectedImageNames : existingImages;

            const basicFieldsFilled = productData.title
                && Number.isFinite(productData.price)
                && Number.isFinite(productData.discount)
                && Number.isFinite(productData.quantity)
                && productData.description
                && productData.specifications
                && productData.category
                && productData.brand;

            if (!basicFieldsFilled) {
                feedback.textContent = "Please fill in all required fields.";
                feedback.classList.add("error");
                return;
            }

            if (productData.discount < 0 || productData.discount > 100) {
                feedback.textContent = "Discount must be between 0 and 100.";
                feedback.classList.add("error");
                return;
            }

            if (productData.quantity < 0) {
                feedback.textContent = "Quantity must be 0 or higher.";
                feedback.classList.add("error");
                return;
            }

            const products = getProducts();
            const existingIndex = products.findIndex((item) => item.id === productData.id);

            if (existingIndex >= 0) {
                products[existingIndex] = productData;
            } else {
                products.unshift(productData);
            }

            setProducts(products);
            feedback.textContent = "Product saved successfully.";
            feedback.classList.add("success");

            setTimeout(() => {
                window.location.href = "admin-products.html";
            }, 700);
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        ensureProducts();
        if (!requireAuth() && window.location.pathname.split("/").pop() !== "admin-login.html") {
            return;
        }

        if (getAdminAuth() && window.location.pathname.split("/").pop() === "admin-login.html") {
            window.location.href = "admin-products.html";
            return;
        }

        setupLogoutButtons();
        setupAdminLogin();
        renderAdminProducts();
        setupProductForm();
    });
})();
