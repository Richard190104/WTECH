document.addEventListener('DOMContentLoaded', async () => {
    const form = document.getElementById('login-form');
    const feedback = document.getElementById('login-feedback');
    const toggleBtn = document.getElementById('toggle-password');
    const pwInput = document.getElementById('login-password');
    const pwIcon = document.getElementById('toggle-pw-icon');

    if (!form || !feedback || !toggleBtn || !pwInput || !pwIcon || !window.electrohubApi) {
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');

    const showFeedback = (message, type) => {
        feedback.textContent = message;
        feedback.className = `login-feedback ${type}`;
    };

    const getCookie = (name) => {
        const value = document.cookie
            .split('; ')
            .find((row) => row.startsWith(`${name}=`));

        return value ? value.split('=').slice(1).join('=') : null;
    };

    const ensureCsrfToken = async () => {
        // Laravel / Sanctum nastaví XSRF-TOKEN cookie
        await fetch('http://localhost:8000/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'include',
            headers: {
                Accept: 'application/json',
            },
        });

        const token = getCookie('XSRF-TOKEN');

        if (!token) {
            throw new Error('CSRF token was not created.');
        }

        return decodeURIComponent(token);
    };

    try {
        const meResponse = await window.electrohubApi.get('/me');
        if (meResponse?.user) {
            window.location.href = 'index.html';
            return;
        }
    } catch (_) {
        // User is not logged in yet.
    }

    toggleBtn.addEventListener('click', () => {
        const isText = pwInput.type === 'text';
        pwInput.type = isText ? 'password' : 'text';
        pwIcon.classList.toggle('fa-eye', isText);
        pwIcon.classList.toggle('fa-eye-slash', !isText);
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const email = document.getElementById('login-email')?.value.trim() || '';
        const password = document.getElementById('login-password')?.value || '';
        const remember = document.getElementById('remember-me')?.checked || false;

        feedback.className = 'login-feedback';
        feedback.textContent = '';

        if (!email || !password) {
            showFeedback('Please fill in all fields.', 'error');
            return;
        }

        const originalButtonHtml = submitBtn?.innerHTML || 'Sign In';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
        }

        try {
            const csrfToken = await ensureCsrfToken();

            const response = await fetch('http://localhost:8000/login', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    email,
                    password,
                    remember,
                }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || !data?.user) {
                throw new Error(data?.message || 'Login failed.');
            }

            localStorage.removeItem('electrohub_user');
            window.location.href = 'index.html';
        } catch (error) {
            showFeedback(error.message || 'Login failed.', 'error');

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalButtonHtml;
            }
        }
    });
});