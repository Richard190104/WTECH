document.addEventListener("DOMContentLoaded", () => {
    const productCards = document.querySelectorAll(".js-product-card");
    const addButtons = document.querySelectorAll(".js-home-add-btn");

    function goToDetail(card) {
        const productUrl = card.dataset.productUrl || "product-detail.html";
        window.location.href = productUrl;
    }

    productCards.forEach((card) => {
        card.addEventListener("click", (event) => {
            if (event.target.closest("button")) {
                return;
            }
            goToDetail(card);
        });

        card.addEventListener("keydown", (event) => {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                goToDetail(card);
            }
        });
    });

    addButtons.forEach((button) => {
        button.addEventListener("click", (event) => {
            event.stopPropagation();
            const originalHtml = button.innerHTML;
            button.innerHTML = "<i class=\"fas fa-check\"></i> Added!";
            button.disabled = true;

            window.setTimeout(() => {
                button.innerHTML = originalHtml;
                button.disabled = false;
            }, 1200);
        });
    });
});
