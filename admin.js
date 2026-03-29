(function () {
    const ADMIN_AUTH_KEY = "electrohub_admin_auth";
    const ADMIN_PRODUCTS_KEY = "electrohub_admin_products";
    const ADMIN_EMAIL = "admin";
    const ADMIN_PASSWORD = "admin";

    const initialProducts = [
        {
            id: "p-1001",
            name: "PlayDock 5 Console",
            description: "Latest gaming console with 4K support.",
            category: "Gaming",
            brand: "PlayDock",
            color: "White",
            price: 299.99,
            stock: 25,
            images: ["images/Playdock%205%20console.jpg", "images/silly%20cat.jpg"]
        },
        {
            id: "p-1002",
            name: "UltraBook Pro 15",
            description: "Slim performance laptop for work and study.",
            category: "Computers",
            brand: "ElectroHub",
            color: "Gray",
            price: 349.99,
            stock: 16,
            images: ["images/ultrabook%20pro.jpg", "images/USB%20C%20HUB.jpg"]
        },
        {
            id: "p-1003",
            name: "SmartPhone X12",
            description: "Fast smartphone with a great OLED display.",
            category: "Phones",
            brand: "XMobile",
            color: "Black",
            price: 395.99,
            stock: 42,
            images: ["images/smartphone%20x12.jpg", "images/silly%20cat.jpg"]
        }
    ];

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
            const row = document.createElement("tr");
            row.innerHTML = ""
                + "<td><img src='" + product.images[0] + "' alt='" + product.name + "' class='admin-thumb'></td>"
                + "<td><strong>" + product.name + "</strong><div class='admin-table-sub'>" + product.category + "</div></td>"
                + "<td>" + product.brand + "</td>"
                + "<td>$" + Number(product.price).toFixed(2) + "</td>"
                + "<td>" + Number(product.stock) + "</td>"
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

        if (productId) {
            const existing = getProducts().find((product) => product.id === productId);
            if (existing) {
                title.textContent = "Edit product";
                submitBtn.textContent = "Save changes";

                document.getElementById("product-name").value = existing.name;
                document.getElementById("product-description").value = existing.description;
                document.getElementById("product-category").value = existing.category;
                document.getElementById("product-brand").value = existing.brand;
                document.getElementById("product-color").value = existing.color;
                document.getElementById("product-price").value = existing.price;
                document.getElementById("product-stock").value = existing.stock;
                document.getElementById("product-image-1").value = existing.images[0] || "";
                document.getElementById("product-image-2").value = existing.images[1] || "";
            }
        }

        form.addEventListener("submit", (event) => {
            event.preventDefault();
            feedback.textContent = "";
            feedback.className = "admin-feedback";

            const productData = {
                id: productId || "p-" + Date.now(),
                name: document.getElementById("product-name").value.trim(),
                description: document.getElementById("product-description").value.trim(),
                category: document.getElementById("product-category").value.trim(),
                brand: document.getElementById("product-brand").value.trim(),
                color: document.getElementById("product-color").value.trim(),
                price: Number(document.getElementById("product-price").value),
                stock: Number(document.getElementById("product-stock").value),
                images: [
                    document.getElementById("product-image-1").value.trim(),
                    document.getElementById("product-image-2").value.trim()
                ]
            };

            const basicFieldsFilled = productData.name
                && productData.description
                && productData.category
                && productData.brand
                && productData.color
                && Number.isFinite(productData.price)
                && Number.isFinite(productData.stock);

            if (!basicFieldsFilled) {
                feedback.textContent = "Please fill in all required fields.";
                feedback.classList.add("error");
                return;
            }

            if (productData.images.some((path) => !path)) {
                feedback.textContent = "A product must include at least 2 photos.";
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
