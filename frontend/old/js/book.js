// Основные переменные
let currentBookId = null;

// Создание элемента заметки или цитаты
function createItemElement(type, data, onDelete) {
    const isQuote = type === 'quote';
    const textKey = isQuote ? 'text' : 'content';

    const container = document.createElement('div');
    container.className = 'container my-3';
    container.innerHTML = `
        <div class="border rounded p-3">
            <p class="text-content mb-3 fs-5 ${type}-text">${data[textKey]}</p>
            <div class="d-flex flex-wrap align-items-center justify-content-between ${type}-footer">
                ${isQuote ? `<span class="text-muted ${type}-page">Стр. ${data.page}</span>` : '<div></div>'}
                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <button class="btn btn-outline-warning btn-sm edit-btn">✏️</button>
                    <button class="btn btn-outline-danger btn-sm delete-btn">🗑️</button>
                </div>
            </div>
        </div>
    `;

    const textEl = container.querySelector(`.${type}-text`);
    const footerEl = container.querySelector(`.${type}-footer`);
    const editBtn = container.querySelector('.edit-btn');
    const deleteBtn = container.querySelector('.delete-btn');
    const pageEl = isQuote ? container.querySelector(`.${type}-page`) : null;

    deleteBtn.addEventListener('click', async () => {
        await onDelete(data.id);
        container.remove();
    });

    const toggleEditMode = (isEditing) => {
        if (isEditing) {
            // Переход в режим редактирования
            const textarea = document.createElement('textarea');
            textarea.className = 'form-control mb-3';
            textarea.value = textEl.textContent;
            textEl.replaceWith(textarea);

            let pageInput, inputWrapper;
            if (isQuote && pageEl && pageEl.parentNode) {
                pageInput = document.createElement('input');
                pageInput.type = 'number';
                pageInput.className = 'form-control form-control-sm';
                pageInput.value = data.page;
                inputWrapper = document.createElement('div');
                inputWrapper.className = 'd-flex align-items-center gap-2';
                inputWrapper.appendChild(pageInput);
                pageEl.parentNode.replaceChild(inputWrapper, pageEl);
            }

            editBtn.innerText = '💾';
            editBtn.classList.replace('btn-outline-warning', 'btn-success');
            editBtn.dataset.mode = 'save';
        } else {
            // Выход из режима редактирования
            const activeTextarea = container.querySelector('textarea');
            const newText = activeTextarea?.value?.trim() || textEl.textContent;
            let newPage = data.page;

            if (isQuote) {
                const activePageInput = container.querySelector('input[type="number"]');
                newPage = activePageInput?.value ? parseInt(activePageInput.value) : data.page;
            }

            // Восстанавливаем текст
            textEl.textContent = newText;
            activeTextarea.replaceWith(textEl);

            if (isQuote && pageEl) {
                // Удаляем существующий pageEl, если он есть
                const existingPageEl = footerEl.querySelector(`.${type}-page`);
                if (existingPageEl && existingPageEl !== pageEl) {
                    existingPageEl.remove();
                }

                // Обновляем страницу
                data.page = newPage;
                pageEl.textContent = `Стр. ${newPage}`;

                // Восстанавливаем pageEl в правильное место
                const inputWrapper = container.querySelector('.d-flex.align-items-center.gap-2');
                if (inputWrapper && inputWrapper.parentNode) {
                    inputWrapper.parentNode.replaceChild(pageEl, inputWrapper);
                } else if (!footerEl.querySelector(`.${type}-page`)) {
                    // Добавляем pageEl только если его нет
                    footerEl.insertBefore(pageEl, footerEl.firstChild);
                }
            }

            editBtn.innerText = '✏️';
            editBtn.classList.replace('btn-success', 'btn-outline-warning');
            editBtn.dataset.mode = 'edit';
        }
    };

    editBtn.addEventListener('click', async () => {
        const isEditing = editBtn.dataset.mode === 'edit' || !editBtn.dataset.mode;

        if (isEditing) {
            toggleEditMode(true);
        } else {
            // Режим сохранения
            const activeTextarea = container.querySelector('textarea');
            const newText = activeTextarea?.value?.trim();
            let newPage = data.page;

            if (!newText) {
                alert('Текст не может быть пустым');
                toggleEditMode(false);
                return;
            }

            if (isQuote) {
                const activePageInput = container.querySelector('input[type="number"]');
                newPage = activePageInput?.value ? parseInt(activePageInput.value) : data.page;
                if (isNaN(newPage) || newPage <= 0) {
                    alert('Номер страницы должен быть положительным числом');
                    toggleEditMode(false);
                    return;
                }
            }

            const payload = {
                text: newText,
                book_id: currentBookId
            };
            if (isQuote) {
                payload.page_number = newPage;
            }

            try {
                editBtn.disabled = true;
                const response = await fetchWithAuth(`${API_BASE}/${type}/${data.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (response.update_status !== 'success') throw new Error('Save failed');

                toggleEditMode(false);
            } catch (e) {
                console.error(`Error updating ${type}:`, e);
                alert('Ошибка при сохранении');
                toggleEditMode(false);
            } finally {
                editBtn.disabled = false;
            }
        }
    });

    return container;
}

// Добавление заметки или цитаты
async function addItem(type, inputs, listId, clearForm) {
    const payload = {
        text: inputs.text.trim(),
        book_id: currentBookId
    };
    if (type === 'quote') {
        payload.page_number = parseInt(inputs.page);
    }

    try {
        const res = await fetchWithAuth(`${API_BASE}/${type}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const item = type === 'quote'
            ? { id: res.id, text: payload.text, page: payload.page_number }
            : { id: res.id, content: payload.text };

        const list = document.getElementById(listId);
        list.appendChild(createItemElement(type, item, id => deleteItem(type, id)));

        clearForm();
    } catch (e) {
        console.error(`Error adding ${type}:`, e);
        alert(`Не удалось добавить ${type}`);
    }
}

async function deleteItem(type, id) {
    try {
        await fetchWithAuth(`${API_BASE}/${type}/${id}`, { method: 'DELETE' });
    } catch (e) {
        console.error(`Error deleting ${type}:`, e);
        alert(`Не удалось удалить ${type}`);
    }
}

// Обработка загрузки обложки
document.getElementById('bookViewCoverFile')?.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('coverFile', file);

    try {
        const res = await fetchWithAuth(`${API_BASE}/book/${currentBookId}/cover`, {
            method: 'POST',
            body: formData
        });
        document.getElementById('bookCover').src = res.coverUrl;
        document.getElementById('bookCover').style.display = 'block';
        document.getElementById('coverUploadContainer').style.display = 'none';
    } catch (e) {
        console.error('Ошибка при загрузке обложки', e);
        alert('Не удалось загрузить обложку');
    }
});

// Обновление заголовка книги
document.getElementById('bookViewTitle')?.addEventListener('blur', async (e) => {
    const newTitle = e.target.textContent.trim();
    if (newTitle && newTitle !== e.target.dataset.originalValue) {
        try {
            await updateField('bookViewTitle', newTitle, null);
            e.target.dataset.originalValue = newTitle;
        } catch (e) {
            console.error('Ошибка при сохранении заголовка', e);
            alert('Не удалось сохранить заголовок');
        }
    }
});

// Кнопки добавления
document.getElementById('addNoteBtn')?.addEventListener('click', () => {
    const text = document.getElementById('newNote').value;
    if (!text.trim()) return;
    addItem('note', { text }, 'notesList', () => document.getElementById('newNote').value = '');
});

document.getElementById('addQuoteBtn')?.addEventListener('click', () => {
    const text = document.getElementById('newQuote').value;
    const page = document.getElementById('quotePage').value;
    if (!text.trim()) return;
    addItem('quote', { text, page }, 'quotesList', () => {
        document.getElementById('newQuote').value = '';
        document.getElementById('quotePage').value = '';
    });
});

// Открытие модального окна книги
async function openBookModal(id) {
    currentBookId = id;
    const modal = new bootstrap.Modal(document.getElementById('bookViewModal'));

    try {
        const response = await fetchWithAuth(`${API_BASE}/book/${id}`);
        const { book, notes = [], quotes = [] } = response;

        const title = document.getElementById('bookViewTitle');
        title.textContent = book.title || '';
        title.dataset.originalValue = book.title || '';

        const author = document.getElementById('bookViewAuthor');
        const authorNames = book.authors && Array.isArray(book.authors) && book.authors.length > 0
            ? book.authors.map(a => a.name).join(', ')
            : '';
        const authorIds = book.authors && Array.isArray(book.authors) && book.authors.length > 0
            ? book.authors.map(a => a.id).join(',')
            : '';
        author.textContent = authorNames;
        author.dataset.originalValue = authorNames;
        author.dataset.relatedId = authorIds;
        author.dataset.originalId = authorIds;

        document.getElementById('bookViewShelf').textContent = book.shelf_name || '';
        document.getElementById('bookViewStatus').textContent = book.status_name || '';

        const coverImg = document.getElementById('bookCover');
        const coverContainer = document.getElementById('coverUploadContainer');
        if (book.coverUrl) {
            coverImg.src = book.coverUrl;
            coverImg.style.display = 'block';
            coverContainer.style.display = 'none';
        } else {
            coverImg.style.display = 'none';
            coverContainer.style.display = 'block';
        }

        const notesList = document.getElementById('notesList');
        notesList.innerHTML = '';
        notes.forEach(note => notesList.appendChild(createItemElement('note', note, id => deleteItem('note', id))));

        const quotesList = document.getElementById('quotesList');
        quotesList.innerHTML = '';
        quotes.forEach(quote => quotesList.appendChild(createItemElement('quote', quote, id => deleteItem('quote', id))));

        modal.show();
    } catch (e) {
        console.error('Ошибка при загрузке книги', e);
        alert('Не удалось загрузить данные книги');
    }
}

// Обновление поля книги
async function updateField(fieldId, value, relatedId = null) {
    const fieldType = document.getElementById(fieldId).dataset.field;
    const data = {};

    switch (fieldType) {
        case 'title':
            data.title = value;
            break;
        case 'author_names':
            data.author_names = value;
            break;
        case 'shelf':
            data.shelf_id = relatedId;
            break;
        case 'status':
            data.status_id = relatedId;
            break;
    }

    try {
        await fetchWithAuth(`${API_BASE}/book/${currentBookId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        loadBoard?.();
        loadShelves?.();
    } catch (e) {
        console.error(`Ошибка при обновлении поля ${fieldType}`, e);
        alert('Не удалось сохранить изменения');
    }
}