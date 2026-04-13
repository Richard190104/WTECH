window.ELECTROHUB_API_BASE = window.ELECTROHUB_API_BASE || 'http://127.0.0.1:8000/api';

window.electrohubApi = {
    baseUrl: window.ELECTROHUB_API_BASE,

    buildUrl(path, params = {}) {
        const normalizedPath = path.startsWith('/') ? path : `/${path}`;
        const url = new URL(`${this.baseUrl}${normalizedPath}`);

        Object.entries(params).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                url.searchParams.set(key, value);
            }
        });

        return url.toString();
    },

    async get(path, params = {}) {
        const response = await fetch(this.buildUrl(path, params), {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            const error = new Error(`Request failed with status ${response.status}`);
            error.status = response.status;
            throw error;
        }

        return response.json();
    },

    imageUrl(path) {
        if (!path) {
            return 'images/Playdock 5 console.jpg';
        }

        if (/^https?:\/\//i.test(path)) {
            return path;
        }

        if (path.startsWith('/')) {
            return path;
        }

        return path;
    },
};
