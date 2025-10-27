const API_BASE = 'https://backend.pages.local/api'; // замени при необходимости

function getAccessToken() {
    return localStorage.getItem('access_token');
}

function getRefreshToken() {
    return localStorage.getItem('refresh_token');
}

function saveTokens(data) {
    localStorage.setItem('access_token', data.token);
    if (data.refresh_token) {
        localStorage.setItem('refresh_token', data.refresh_token);
    }
}

function logout() {
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    window.location.href = '/login.html';
}

async function refreshToken() {
    const refresh = getRefreshToken();
    if (!refresh) {
        logout();
        return;
    }

    const res = await fetch(`${API_BASE}/token/refresh`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ refresh_token: refresh })
    });

    if (!res.ok) {
        logout();
        throw new Error('Не удалось обновить токен');
    }

    const data = await res.json();
    saveTokens(data);
    return data.token;
}

async function fetchWithAuth(url, options = {}) {
    let token = getAccessToken();
    if (!token) {
        logout();
        throw new Error('Нет access token');
    }

    let response = await fetch(url, {
        ...options,
        headers: {
            'Authorization': `Bearer ${token}`,
            ...(options.headers || {})
        }
    });

    if (response.status === 401) {
        try {
            const newToken = await refreshToken();
            response = await fetch(url, {
                ...options,
                headers: {
                    'Authorization': `Bearer ${newToken}`,
                    ...(options.headers || {})
                }
            });
        } catch (err) {
            console.error('Ошибка при обновлении токена', err);
            logout();
            throw err;
        }
    }

    if (!response.ok) {
        throw new Error(`Ошибка запроса: ${response.status}`);
    }

    return response.json();
}
