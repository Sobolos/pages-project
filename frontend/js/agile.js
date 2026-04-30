// Глобальные переменные (предполагается, что они объявлены где-то выше)
let agileStatuses = [];
let agileAuthors = [];

// Загрузка Agile-доски
function loadBoard(statuses, authors, books) {
    try {
        agileStatuses = statuses.sort((a, b) => a.position - b.position);
        agileAuthors = authors;
        const board = document.getElementById('kanbanBoard');
        const loading = document.getElementById('loading_kanban');
        loading.style.display = 'none';
        board.style.display = 'flex';
        board.innerHTML = '';

        statuses.forEach((status) => {
            const column = document.createElement('div');
            column.className = 'kanban-column';
            column.style.background = status.color;
            column.dataset.statusId = status.id;
            column.draggable = true;

            const titleContainer = document.createElement('div');
            titleContainer.className = 'd-flex align-items-center justify-content-between p-2 kanban-column-title';

            const title = document.createElement('h5');
            title.textContent = status.name;
            title.className = 'status-title mb-0';
            titleContainer.appendChild(title);

            const buttonGroup = document.createElement('div');
            buttonGroup.className = 'd-flex';

            const editButton = document.createElement('button');
            editButton.className = 'btn btn-sm edit-status-btn';
            editButton.innerHTML = '✏️';
            editButton.addEventListener('click', () => openEditStatusModal(status));
            buttonGroup.appendChild(editButton);

            const deleteButton = document.createElement('button');
            deleteButton.className = 'btn btn-sm delete-status-btn';
            deleteButton.innerHTML = '🗑️';
            deleteButton.addEventListener('click', () => deleteStatus(status.id));
            buttonGroup.appendChild(deleteButton);

            titleContainer.appendChild(buttonGroup);
            column.appendChild(titleContainer);

            // События перетаскивания колонок
            column.addEventListener('dragstart', handleColumnDragStart);
            column.addEventListener('dragend', handleDragEnd);
            column.addEventListener('dragover', e => e.preventDefault());
            column.addEventListener('dragenter', () => column.classList.add('dropzone-highlight'));
            column.addEventListener('dragleave', () => column.classList.remove('dropzone-highlight'));
            column.addEventListener('drop', handleColumnDrop);

            // События перетаскивания книг
            column.addEventListener('dragover', e => e.preventDefault());
            column.addEventListener('dragenter', () => column.classList.add('dropzone-highlight'));
            column.addEventListener('dragleave', () => column.classList.remove('dropzone-highlight'));
            column.addEventListener('drop', handleDrop);

            const booksInStatus = books.filter(book => book.status_id === status.id);

            booksInStatus.forEach(book => {
                const card = document.createElement('div');
                card.className = 'kanban-card';
                card.draggable = true;
                card.dataset.bookId = book.id;

                if (book.cover_url) {
                    card.innerHTML = `<div class="row">
                                        <div class="col-3 p-2"><img src="${book.cover_url}" alt="${book.title}"></div>
                                        <div class="col-9 p-2"><p>${book.title}</p></div>
                                      </div>`;
                } else {
                    card.innerHTML = `<div class="row">
                                        <div class="col-12"><p>${book.title}</p></div>
                                      </div>`;
                }

                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
                card.addEventListener('click', () => openBookModal(book.id));
                column.appendChild(card);
            });

            board.appendChild(column);
        });
    } catch (error) {
        console.error(error);
    }
}

// ────────────────────────────────────────────────
// Обработчики Drag-and-Drop для карточек книг
// ────────────────────────────────────────────────

function handleDragStart(e) {
    e.stopPropagation();
    e.dataTransfer.setData('text/plain', e.target.dataset.bookId);
    e.dataTransfer.setData('type', 'book');
}

function handleDragEnd(e) {
    e.stopPropagation();
    document.querySelectorAll('.kanban-column').forEach(col => col.classList.remove('dropzone-highlight'));
    const el = e.target.closest('.kanban-column') || e.target.closest('.kanban-card');
    if (el) el.classList.remove('dragging');
}

async function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();

    const type = e.dataTransfer.getData('type');
    if (type !== 'book') return;

    const bookId = e.dataTransfer.getData('text/plain');
    const targetColumn = e.target.closest('.kanban-column');
    if (!targetColumn) return;

    const newStatusId = targetColumn.dataset.statusId;

    try {
        await fetchWithAuth(`${API_BASE}/book-status/${bookId}`, {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({status_id: newStatusId})
        });

        const card = document.querySelector(`.kanban-card[data-book-id="${bookId}"]`);
        if (card) targetColumn.appendChild(card);
    } catch (error) {
        console.error(error);
    }

    targetColumn.classList.remove('dropzone-highlight');
}

// ────────────────────────────────────────────────
// Обработчики Drag-and-Drop для колонок (статусов)
// ────────────────────────────────────────────────

function handleColumnDragStart(e) {
    if (e.target.closest('.kanban-card')) {
        e.stopPropagation();
        return;
    }
    e.stopPropagation();

    const column = e.target.closest('.kanban-column');
    if (!column) return;

    e.dataTransfer.setData('text/plain', column.dataset.statusId);
    e.dataTransfer.setData('type', 'column');
    column.classList.add('dragging');
}

async function handleColumnDrop(e) {
    e.preventDefault();
    e.stopPropagation();

    const type = e.dataTransfer.getData('type');
    if (type !== 'column') return;

    const draggedStatusId = e.dataTransfer.getData('text/plain');
    const targetColumn = e.target.closest('.kanban-column');
    if (!targetColumn) return;

    const targetStatusId = targetColumn.dataset.statusId;

    if (draggedStatusId === targetStatusId) {
        targetColumn.classList.remove('dropzone-highlight');
        return;
    }

    try {
        const draggedIndex = agileStatuses.findIndex(s => s.id == draggedStatusId);
        const targetIndex = agileStatuses.findIndex(s => s.id == targetStatusId);

        const [draggedStatus] = agileStatuses.splice(draggedIndex, 1);
        agileStatuses.splice(targetIndex, 0, draggedStatus);

        const updatedPositions = agileStatuses.map((status, index) => ({
            id: status.id,
            position: index
        }));

        await fetchWithAuth(`${API_BASE}/reorder-statuses`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(updatedPositions)
        });

        init();
    } catch (error) {
        console.error(error);
    }

    targetColumn.classList.remove('dropzone-highlight');
    document.querySelectorAll('.kanban-column').forEach(col => col.classList.remove('dragging'));
}

// ────────────────────────────────────────────────
// Модальное окно статуса — создание / редактирование
// ────────────────────────────────────────────────

// Один общий обработчик на кнопку "Сохранить"
document.getElementById('saveStatusBtn').addEventListener('click', async () => {
    const name = document.getElementById('status-name').value.trim();
    const color = document.getElementById('status-color').value;

    if (!name) {
        return;
    }

    const btn = document.getElementById('saveStatusBtn');
    const mode = btn.dataset.mode || 'create';
    const statusId = btn.dataset.statusId || null;

    let url = `${API_BASE}/statuses`;
    let method = 'POST';
    let body = { name, color, hide_from_agile: false };

    if (mode === 'edit' && statusId) {
        url += `/${statusId}`;
        method = 'PUT';
    } else {
        body.position = agileStatuses.length + 1;
    }

    try {
        await fetchWithAuth(url, {
            method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(body)
        });

        const modal = bootstrap.Modal.getInstance(document.getElementById('add-status-modal'));
        if (modal) modal.hide();

        init();
    } catch (error) {
        console.error(error);
    }
});

// Очистка модалки при закрытии (чтобы поля и data-атрибуты сбрасывались всегда)
const statusModalElement = document.getElementById('add-status-modal');
statusModalElement.addEventListener('hidden.bs.modal', () => {
    document.getElementById('status-name').value = '';
    document.getElementById('status-color').value = '#FFFAAA';
    document.getElementById('color-preview').style.background = 'linear-gradient(45deg, #fcbeec, #a9dadf, #6cffce)';

    const saveBtn = document.getElementById('saveStatusBtn');
    saveBtn.textContent = 'Создать';
    saveBtn.dataset.mode = 'create';
    saveBtn.dataset.statusId = '';
});

function openEditStatusModal(status) {
    // Заполняем поля
    document.getElementById('status-name').value = status.name;
    document.getElementById('status-color').value = status.color;
    document.getElementById('color-preview').style.background = status.color;

    // Настройка кнопки
    const saveBtn = document.getElementById('saveStatusBtn');
    saveBtn.textContent = 'Сохранить';
    saveBtn.dataset.mode = 'edit';
    saveBtn.dataset.statusId = status.id;

    const modal = new bootstrap.Modal(statusModalElement);
    modal.show();
}

function openCreateStatusModal() {
    // Заполняем поля (хотя и очистка при hidden.bs.modal сделает это, но для верности)
    document.getElementById('status-name').value = '';
    document.getElementById('status-color').value = '#FFFAAA';
    document.getElementById('color-preview').style.background = '#FFFAAA';

    // Настройка кнопки
    const saveBtn = document.getElementById('saveStatusBtn');
    saveBtn.textContent = 'Создать';
    saveBtn.dataset.mode = 'create';
    saveBtn.dataset.statusId = '';

    const modal = new bootstrap.Modal(statusModalElement);
    modal.show();
}

// ────────────────────────────────────────────────
// Удаление статуса
// ────────────────────────────────────────────────

async function deleteStatus(statusId) {
    if (!confirm('Вы уверены, что хотите удалить этот статус? Книги в этом статусе останутся без статуса.')) {
        return;
    }

    try {
        await fetchWithAuth(`${API_BASE}/statuses/${statusId}`, {
            method: 'DELETE'
        });
        init();
    } catch (error) {
        console.error(error);
    }
}

// ────────────────────────────────────────────────
// Остальные функции (предполагается, что они уже есть в проекте)
// ────────────────────────────────────────────────
// openBookModal(bookId)
// fetchWithAuth(url, options)
// init() — функция инициализации / загрузки данных