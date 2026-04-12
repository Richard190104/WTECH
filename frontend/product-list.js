document.addEventListener('DOMContentLoaded', () => {
    // Add to cart feedback
    document.querySelectorAll('.plp-add-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const name = btn.dataset.product;
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Added!';
            btn.disabled = true;
            setTimeout(() => {
                btn.innerHTML = original;
                btn.disabled = false;
            }, 1200);
        });
    });

    // Wishlist toggle
    document.querySelectorAll('.product-fav-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const icon = btn.querySelector('i');
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
        });
    });

    // Pagination active state
    document.querySelectorAll('.plp-page-num').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.plp-page-num').forEach(b => {
                b.classList.remove('active');
                b.removeAttribute('aria-current');
            });
            btn.classList.add('active');
            btn.setAttribute('aria-current', 'page');
            document.getElementById('reset-filters').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            const idx = [...document.querySelectorAll('.plp-page-num')].indexOf(btn);
            document.getElementById('result-count').textContent =
                'Showing ' + (idx * 12 + 1) + '–' + Math.min((idx + 1) * 12, 48) + ' of 48 products';
        });
    });

    // Reset filters
    document.getElementById('reset-filters').addEventListener('click', () => {
        document.querySelectorAll('.plp-sidebar input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        document.getElementById('price-min').value = 0;
        document.getElementById('price-max').value = 2000;
        document.getElementById('price-range').value = 2000;
    });

    // Sync range slider with max price input
    document.getElementById('price-range').addEventListener('input', e => {
        document.getElementById('price-max').value = e.target.value;
    });
    document.getElementById('price-max').addEventListener('input', e => {
        document.getElementById('price-range').value = e.target.value;
    });
});
