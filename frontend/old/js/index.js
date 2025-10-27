let authors = [];
let shelves = [];
let statuses = [];
loadBoard();
loadShelves();

// Сохранение сущностей
document.getElementById('saveShelfBtn').addEventListener('click', async () => {
    const name = document.getElementById('shelfName').value;
    const iconFile = document.getElementById('shelfIconFile').files[0];
    if (!name) return alert('Название обязательно');

    const formData = new FormData();
    formData.append('name', name);
    if (iconFile) formData.append('iconFile', iconFile);

    try {
        await fetchWithAuth(`${API_BASE}/shelf`, {method: 'POST', body: formData});
        bootstrap.Modal.getInstance(document.getElementById('shelfModal')).hide();
        loadShelves();
    } catch (error) {
        alert('Ошибка создания полки');
    }
});

document.getElementById('saveBookBtn').addEventListener('click', async () => {
    const title = document.getElementById('bookTitle').value;
    const authorNames = document.getElementById('bookAuthor').value;
    const coverFile = document.getElementById('bookCoverFile').files[0];
    const statusId = document.getElementById('bookStatus').value;
    const shelfId = document.getElementById('bookShelf').value;
    if (!title) return alert('Название обязательно');

    const formData = new FormData();
    formData.append('title', title);
    formData.append('author_names', authorNames);
    if (coverFile) formData.append('coverFile', coverFile);
    formData.append('status_id', statusId);
    formData.append('shelf_id', shelfId);

    try {
        await fetchWithAuth(`${API_BASE}/book`, {method: 'POST', body: formData});
        bootstrap.Modal.getInstance(document.getElementById('bookModal')).hide();
        loadBoard();
        loadShelves();
    } catch (error) {
        alert('Ошибка создания книги');
    }
});

document.getElementById('saveStatusBtn').addEventListener('click', async () => {
    const name = document.getElementById('statusName').value;
    const color = document.getElementById('statusColor').value;
    if (!name) return alert('Название обязательно');

    const formData = new FormData();
    formData.append('name', name);
    formData.append('color', color);

    try {
        await fetchWithAuth(`${API_BASE}/status`, {method: 'POST', body: formData});
        bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
        loadBoard();
    } catch (error) {
        alert('Ошибка создания статуса');
    }
});

// Проверка авторизации
if (!localStorage.getItem('access_token')) {
    refreshToken();
}