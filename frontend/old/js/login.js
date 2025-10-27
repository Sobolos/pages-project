document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const response = await fetch('https://backend.pages.local/api/login_check', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username: username, password: password })
    });
    const data = await response.json();
    if (response.ok) {
        localStorage.setItem('access_token', data.token);
        localStorage.setItem('refresh_token', data.refresh_token);
        window.location.href = '/';
    } else {
        alert('Неверные учетные данные');
    }
});