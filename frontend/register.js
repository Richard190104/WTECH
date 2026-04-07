document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('register-form');
    const feedback = document.getElementById('reg-feedback');
    const passwordInput1 = document.getElementById('register-password');
    const passwordInput2 = document.getElementById('confirm-password');
    const toggleBtn1 = document.getElementById('toggle-password-1');
    const toggleBtn2 = document.getElementById('toggle-password-2');

    // Password visibility toggles
    toggleBtn1.addEventListener('click', (e) => {
        e.preventDefault();
        const isPassword = passwordInput1.type === 'password';
        passwordInput1.type = isPassword ? 'text' : 'password';
        toggleBtn1.innerHTML = isPassword ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
    });

    toggleBtn2.addEventListener('click', (e) => {
        e.preventDefault();
        const isPassword = passwordInput2.type === 'password';
        passwordInput2.type = isPassword ? 'text' : 'password';
        toggleBtn2.innerHTML = isPassword ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
    });

    // Show feedback message
    function showFeedback(message, type) {
        feedback.textContent = message;
        feedback.className = 'login-feedback ' + type;
        feedback.style.display = 'block';
        setTimeout(() => {
            feedback.style.display = 'none';
        }, 3500);
    }

    // Form validation and submission
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const firstName = document.getElementById('first-name').value.trim();
        const lastName = document.getElementById('last-name').value.trim();
        const email = document.getElementById('register-email').value.trim();
        const password = passwordInput1.value;
        const confirmPassword = passwordInput2.value;
        const termsAgreed = document.getElementById('terms-agree').checked;

        // Validation
        if (!firstName) {
            showFeedback('Please enter your first name', 'error');
            return;
        }
        if (!lastName) {
            showFeedback('Please enter your last name', 'error');
            return;
        }
        if (!email) {
            showFeedback('Please enter a valid email address', 'error');
            return;
        }
        if (password.length < 8) {
            showFeedback('Password must be at least 8 characters', 'error');
            return;
        }
        if (password !== confirmPassword) {
            showFeedback('Passwords do not match', 'error');
            return;
        }
        if (!termsAgreed) {
            showFeedback('You must agree to the Terms &amp; Conditions', 'error');
            return;
        }

        // Simulate registration
        const btn = document.getElementById('register-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

        setTimeout(() => {
            showFeedback('Account created successfully! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 1500);
        }, 1200);
    });
});
