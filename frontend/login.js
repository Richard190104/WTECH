document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    const feedback = document.getElementById('login-feedback');
    const toggleBtn = document.getElementById('toggle-password');
    const pwInput = document.getElementById('login-password');
    const pwIcon = document.getElementById('toggle-pw-icon');
    const submitBtn = form.querySelector('button[type="submit"]');
    const apiBaseUrl = 'http://127.0.0.1:8000/api';

    toggleBtn.addEventListener('click', () => {
        const isText = pwInput.type === 'text';
        pwInput.type = isText ? 'password' : 'text';
        pwIcon.classList.toggle('fa-eye', isText);
        pwIcon.classList.toggle('fa-eye-slash', !isText);
    });

    function showFeedback(message, type) {
        feedback.textContent = message;
        feedback.className = `login-feedback ${type}`;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = document.getElementById('login-email').value.trim();
        const password = document.getElementById('login-password').value;
        const remember = document.getElementById('remember-me').checked;

        feedback.className = 'login-feedback';
        feedback.textContent = '';

        if (!email || !password) {
            showFeedback('Please fill in all fields.', 'error');
            return;
        }

        const originalButtonHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

        try {
            const response = await fetch(`${apiBaseUrl}/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email, password, remember }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data?.message || 'Login failed.');
            }

            localStorage.setItem('electrohub_user', JSON.stringify(data.user));
            showFeedback('Login successful! Redirecting...', 'success');

            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
        } catch (error) {
            showFeedback(error.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalButtonHtml;
        }
    });
});