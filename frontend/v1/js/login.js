document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const response = await fetch(`${API_BASE}/auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, password: password })
    });
    const data = await response.json();

    if (response.ok) {
        localStorage.setItem('access_token', data.data.access_token);
        localStorage.setItem('refresh_token', data.data.refresh_token);
        window.location.href = '/index.html';
    } else {
        alert('Неверные учетные данные');
    }
});