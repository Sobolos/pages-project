// Загрузка Agile-доски
function loadBoard(statuses, authors, books) {
    try {
        agileStatuses = statuses.sort((a, b) => a.position - b.position); // Sort statuses by position
        agileAuthors = authors;
        const board = document.getElementById('kanbanBoard');
        const loading = document.getElementById('loading_kanban');
        loading.style.display = 'none';
        board.style.display = 'flex';
        board.innerHTML = '';

        statuses.forEach((status, index) => {
            const column = document.createElement('div');
            column.className = 'kanban-column';
            column.style.background = status.color;
            column.dataset.statusId = status.id;
            column.draggable = true; // Make entire column draggable

            // Create title container for header
            const titleContainer = document.createElement('div');
            titleContainer.className = 'd-flex align-items-center justify-content-between p-2 kanban-column-title';

            // Drag handle (centered)
            // const dragHandle = document.createElement('div');
            // dragHandle.className = 'drag-handle';
            // dragHandle.innerHTML = '☰';
            // titleContainer.appendChild(dragHandle);

            // Title
            const title = document.createElement('h5');
            title.textContent = status.name;
            title.className = 'status-title mb-0';
            titleContainer.appendChild(title);

            // Button group for edit and delete (aligned right)
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

            // Column drag-and-drop events
            column.addEventListener('dragstart', handleColumnDragStart);
            column.addEventListener('dragend', handleDragEnd);
            column.addEventListener('dragover', e => e.preventDefault());
            column.addEventListener('dragenter', () => column.classList.add('dropzone-highlight'));
            column.addEventListener('dragleave', () => column.classList.remove('dropzone-highlight'));
            column.addEventListener('drop', handleColumnDrop);

            // Book drag-and-drop events
            column.addEventListener('dragover', e => e.preventDefault());
            column.addEventListener('dragenter', () => column.classList.add('dropzone-highlight'));
            column.addEventListener('dragleave', () => column.classList.remove('dropzone-highlight'));
            column.addEventListener('drop', handleDrop);

            const booksInStatus = books.filter(book => book.status_id === status.id);
            console.log(booksInStatus)
            booksInStatus.forEach(book => {
                const card = document.createElement('div');
                card.className = 'kanban-card';
                card.draggable = true;
                card.dataset.bookId = book.id;
                card.innerHTML = `${book.cover ? `<img src="${book.cover}" alt="${book.title}">` : ''}<p>${book.title}</p>`;
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
                card.addEventListener('click', () => openBookModal(book.id));
                column.appendChild(card);
            });

            board.appendChild(column);
        });
    } catch (error) {
        console.error(error);
        alert('Не удалось загрузить доску');
    }
}

// Обработчики Drag-and-Drop для карточек
function handleDragStart(e) {
    e.stopPropagation(); // Prevent column dragstart from firing
    e.dataTransfer.setData('text/plain', e.target.dataset.bookId);
    e.dataTransfer.setData('type', 'book'); // Indicate this is a book drag
}

// Clean up visual states after drag ends (for both books and columns)
function handleDragEnd(e) {
    e.stopPropagation(); // Prevent bubbling
    // Remove dropzone-highlight from all columns
    document.querySelectorAll('.kanban-column').forEach(col => col.classList.remove('dropzone-highlight'));
    // Remove dragging class from the dragged element
    const column = e.target.closest('.kanban-column') || e.target.closest('.kanban-card');
    if (column) column.classList.remove('dragging');
}

async function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation(); // Prevent column drop handler from firing
    const type = e.dataTransfer.getData('type');
    if (type !== 'book') return; // Only handle book drops

    const bookId = e.dataTransfer.getData('text/plain');
    const target = e.target.closest('.kanban-column');
    if (!target) return; // Ensure drop target is a column
    const newStatusId = target.dataset.statusId;

    try {
        await fetchWithAuth(`${API_BASE}/book/${bookId}/status`, {
            method: 'PATCH',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({status_id: newStatusId})
        });
        const card = document.querySelector(`.kanban-card[data-book-id="${bookId}"]`);
        target.appendChild(card);
    } catch (error) {
        console.error(error);
        alert('Ошибка перемещения книги');
    }
    target.classList.remove('dropzone-highlight');
}

// Обработчики Drag-and-Drop для колонок
function handleColumnDragStart(e) {
    // Prevent column drag if initiated from a book card
    if (e.target.closest('.kanban-card')) {
        e.stopPropagation();
        return;
    }
    e.stopPropagation(); // Prevent other drag events
    const column = e.target.closest('.kanban-column');
    e.dataTransfer.setData('text/plain', column.dataset.statusId);
    e.dataTransfer.setData('type', 'column'); // Indicate this is a column drag
    column.classList.add('dragging'); // Add dragging class for visual feedback
}

async function handleColumnDrop(e) {
    e.preventDefault();
    e.stopPropagation(); // Prevent book drop handler from firing
    const type = e.dataTransfer.getData('type');
    if (type !== 'column') return; // Only handle column drops

    const draggedStatusId = e.dataTransfer.getData('text/plain');
    const target = e.target.closest('.kanban-column');
    if (!target) return; // Ensure drop target is a column
    const targetStatusId = target.dataset.statusId;

    if (draggedStatusId === targetStatusId) {
        target.classList.remove('dropzone-highlight');
        return; // No change if dropped on itself
    }

    try {
        // Reorder statuses array
        const draggedIndex = statuses.findIndex(s => s.id == draggedStatusId);
        const targetIndex = statuses.findIndex(s => s.id == targetStatusId);
        const [draggedStatus] = statuses.splice(draggedIndex, 1);
        statuses.splice(targetIndex, 0, draggedStatus);

        // Update positions in the backend
        const updatedPositions = statuses.map((status, index) => ({
            id: status.id,
            position: index
        }));

        await fetchWithAuth(`${API_BASE}/reorder-statuses`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(updatedPositions)
        });

        // Re-render the board
        init();
    } catch (error) {
        console.error(error);
        alert('Ошибка перемещения колонки');
    }
    target.classList.remove('dropzone-highlight');
    document.querySelectorAll('.kanban-column').forEach(col => col.classList.remove('dragging'));
}

// Open the status modal for editing
function openEditStatusModal(status) {
    const modal = new bootstrap.Modal(document.getElementById('add-status-modal'));
    const statusNameInput = document.getElementById('status-name');
    const statusColorInput = document.getElementById('status-color');
    const saveButton = document.getElementById('saveStatusBtn');

    // Populate modal fields
    statusNameInput.value = status.name;
    statusColorInput.value = status.color;

    // Store original button state
    const originalButtonText = saveButton.textContent;

    // Remove all existing event listeners to prevent duplication
    const newSaveButton = saveButton.cloneNode(true);
    saveButton.parentNode.replaceChild(newSaveButton, saveButton);

    // Add new click handler for updating
    newSaveButton.textContent = 'Сохранить';
    newSaveButton.addEventListener('click', async () => {
        const name = statusNameInput.value;
        const color = statusColorInput.value;
        if (!name) return alert('Название обязательно');

        try {
            await fetchWithAuth(`${API_BASE}/statuses/${status.id}`, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ name: name, color: color, hide_from_agile: false})
            });
            modal.hide();
            init();
        } catch (error) {
            console.error(error);
            alert('Ошибка обновления статуса');
        }
    });

    // Reset modal on close
    const modalElement = document.getElementById('add-status-modal');
    modalElement.addEventListener('hidden.bs.modal', () => {
        // Clear inputs
        statusNameInput.value = '';
        statusColorInput.value = '#FFFAAA';
        // Restore original button text
        newSaveButton.textContent = originalButtonText;
    }, { once: true });

    modal.show();
}

// Delete a status
async function deleteStatus(statusId, authors, books) {
    if (!confirm('Вы уверены, что хотите удалить этот статус?')) return;

    try {
        await fetchWithAuth(`${API_BASE}/statuses/${statusId}`, {
            method: 'DELETE'
        });

        init();
    } catch (error) {
        console.error(error);
        alert('Ошибка удаления статуса');
    }
}

document.getElementById('saveStatusBtn').addEventListener('click', async () => {
    const name = document.getElementById('status-name').value;
    const color = document.getElementById('status-color').value;
    if (!name) return alert('Название обязательно');

    const request = JSON.stringify({ name: name, color: color, hide_from_agile: false, position: statuses.length+1})

    try {
        await fetchWithAuth(`${API_BASE}/statuses`, {headers: { 'Content-Type': 'application/json' },method: 'POST', body: request});
        bootstrap.Modal.getInstance(document.getElementById('add-status-modal')).hide();
        init();
    } catch (error) {
        alert('Ошибка создания статуса\n' + error);
    }
});