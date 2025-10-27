const API_BASE = 'http://localhost/api'; // замени при необходимости
const BACKEND_BASE = 'http://localhost'; // замени при необходимости

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
            alert('Ошибка при обновлении токена \n' + err);
            throw err;
        }
    }

    if (!response.ok) {
        throw new Error(`Ошибка запроса: ${response.status}`);
    }

    return response.json();
}