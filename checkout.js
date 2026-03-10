function formatCurrency(value) {
    return "$" + value.toFixed(2);
}

function updateOptionCards(groupName) {
    const inputs = document.querySelectorAll("input[name='" + groupName + "']");
    inputs.forEach((input) => {
        const option = input.closest(".option-card");
        if (!option) {
            return;
        }
        option.classList.toggle("is-selected", input.checked);
    });
}

function initShippingPaymentPage() {
    const shippingInputs = document.querySelectorAll("input[name='shipping_method']");
    const paymentInputs = document.querySelectorAll("input[name='payment_method']");
    const summarySubtotal = document.getElementById("summary-subtotal");
    const summaryVat = document.getElementById("summary-vat");
    const summaryShipping = document.getElementById("summary-shipping");
    const summaryTotal = document.getElementById("summary-total");
    const continueButton = document.getElementById("continue-to-delivery");
    const feedback = document.getElementById("checkout-feedback");

    if (!shippingInputs.length || !paymentInputs.length || !summarySubtotal || !summaryVat || !summaryShipping || !summaryTotal || !continueButton || !feedback) {
        return;
    }

    const subtotal = 1079.96;
    const vat = 215.99;

    function getSelectedShipping() {
        return document.querySelector("input[name='shipping_method']:checked");
    }

    function getSelectedPayment() {
        return document.querySelector("input[name='payment_method']:checked");
    }

    function updateSummary() {
        const selectedShipping = getSelectedShipping();
        const shippingCost = Number.parseFloat(selectedShipping?.dataset.price || "0");
        const total = subtotal + vat + shippingCost;

        summarySubtotal.textContent = formatCurrency(subtotal);
        summaryVat.textContent = formatCurrency(vat);
        summaryShipping.textContent = formatCurrency(shippingCost);
        summaryTotal.textContent = formatCurrency(total);
        updateOptionCards("shipping_method");
        updateOptionCards("payment_method");
    }

    shippingInputs.forEach((input) => {
        input.addEventListener("change", updateSummary);
    });

    paymentInputs.forEach((input) => {
        input.addEventListener("change", () => updateOptionCards("payment_method"));
    });

    continueButton.addEventListener("click", () => {
        const selectedShipping = getSelectedShipping();
        const selectedPayment = getSelectedPayment();

        if (!selectedShipping || !selectedPayment) {
            feedback.textContent = "Please choose shipping and payment before continuing.";
            feedback.classList.add("is-error");
            return;
        }

        const shippingCost = Number.parseFloat(selectedShipping.dataset.price || "0");
        const total = subtotal + vat + shippingCost;
        const checkoutData = {
            shipping: selectedShipping.value,
            shippingCost,
            payment: selectedPayment.value,
            subtotal,
            vat,
            total
        };

        localStorage.setItem("electrohub_checkout", JSON.stringify(checkoutData));
        window.location.href = "delivery-details.html";
    });

    updateSummary();
}

function initDeliveryDetailsPage() {
    const form = document.getElementById("delivery-form");
    const recapShipping = document.getElementById("recap-shipping");
    const recapPayment = document.getElementById("recap-payment");
    const recapTotal = document.getElementById("recap-total");
    const feedback = document.getElementById("delivery-feedback");

    if (!form || !recapShipping || !recapPayment || !recapTotal || !feedback) {
        return;
    }

    const savedData = localStorage.getItem("electrohub_checkout");
    if (savedData) {
        try {
            const parsed = JSON.parse(savedData);
            if (parsed.shipping) {
                recapShipping.textContent = parsed.shipping + " (" + formatCurrency(parsed.shippingCost || 0) + ")";
            }
            if (parsed.payment) {
                recapPayment.textContent = parsed.payment;
            }
            if (typeof parsed.total === "number") {
                recapTotal.textContent = formatCurrency(parsed.total);
            }
        } catch (error) {
            console.error("Failed to parse checkout data", error);
        }
    }

    form.addEventListener("submit", (event) => {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            feedback.textContent = "Please fill in all required fields correctly.";
            feedback.classList.add("is-error");
            return;
        }

        const firstName = document.getElementById("first-name")?.value || "Customer";
        const orderId = "EH" + Math.floor(100000 + Math.random() * 900000);
        feedback.textContent = "Thank you, " + firstName + ". Your order " + orderId + " has been placed.";
        feedback.classList.remove("is-error");
        feedback.classList.add("is-success");
        localStorage.removeItem("electrohub_checkout");
        form.reset();
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initShippingPaymentPage();
    initDeliveryDetailsPage();
});
