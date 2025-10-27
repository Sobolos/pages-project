function checkLogin() {
    return !!getAccessToken();
}

function logout() {
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    window.location.href = '/login.html';
}

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