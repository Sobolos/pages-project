async function openBookModal(id) {
    currentBookId = id;
    const modal = new bootstrap.Modal(document.getElementById('book-details-modal'));
    const cover = document.getElementById('book-cover-img');
    const authorSelectDisplay = document.getElementById('authorSelectDisplay');
    const authorSearchInput = document.getElementById('authorSearchInput');
    const authorOptionsList = document.getElementById('authorOptionsList');
    const selectedAuthorsInput = document.getElementById('selectedAuthors');
    const shelfSelectDisplay = document.getElementById('shelfSelectDisplay');
    const shelfSearchInput = document.getElementById('shelfSearchInput');
    const shelfOptionsList = document.getElementById('shelfOptionsList');
    const selectedShelfInput = document.getElementById('selectedShelf');
    const statusSelectDisplay = document.getElementById('statusSelectDisplay');
    const statusSearchInput = document.getElementById('statusSearchInput');
    const statusOptionsList = document.getElementById('statusOptionsList');
    const selectedStatusInput = document.getElementById('selectedStatus');

    // Данные из глобальных переменных
    let authorOptionsData = [...authors]; // Копируем, чтобы не мутировать оригинал
    let selectedAuthors = [];
    let newAuthors = []; // Массив для новых авторов {tempId: -N, name: 'Имя'}
    let tempIdCounter = -1; // Счетчик для временных ID
    let shelfOptionsData = [...shelves];
    let selectedShelf = null;
    let statusOptionsData = [...statuses];
    let selectedStatus = null;

    function setBookCover(coverUrl) {
        if (coverUrl === null) {
            cover.setAttribute('hidden', 'hidden')
        }

        if (coverUrl !== null) {
            cover.removeAttribute('hidden');
            cover.setAttribute('src', coverUrl)
        }
    }

    try {
        // Загрузка данных книги
        let book = await fetchWithAuth(`${API_BASE}/book/${id}`, {
            method: 'GET'
        });

        book = book.data[0];

        // Заполнение названия книги
        const bookTitle = document.getElementById('book-detail-title');
        bookTitle.value = book.title;

        // Установка выбранных авторов
        selectedAuthors = book.authors.map(author => author.id);
        selectedAuthorsInput.value = JSON.stringify(selectedAuthors);

        // Установка выбранной полки (если есть)
        selectedShelf = book.shelf_id ?? null;
        selectedShelfInput.value = selectedShelf || '';

        // Установка выбранного статуса (если есть)
        selectedStatus = book.status_id ?? null;
        selectedStatusInput.value = selectedStatus || '';

        if (book.cover_url !== null) {
            setBookCover(book.cover_url);
        }

        // Универсальная функция рендеринга опций
        function renderOptions(container, options, selectedItems, isMultiple, query = '') {
            container.innerHTML = '';
            let hasOptions = options.length > 0;

            if (container === authorOptionsList && !hasOptions && query.trim() !== '') {
                // Если ничего не найдено для авторов, добавить опцию "Добавить 'query'"
                const addItem = document.createElement('div');
                addItem.classList.add('dropdown-item');
                addItem.innerHTML = `<span>Добавить "${query}"</span>`;
                addItem.addEventListener('click', (e) => {
                    e.stopPropagation();
                    // Добавляем нового автора локально
                    const newAuthor = {id: tempIdCounter, name: query};
                    authorOptionsData.push(newAuthor);
                    newAuthors.push(newAuthor);
                    selectedAuthors.push(tempIdCounter);
                    tempIdCounter--;
                    handleAuthorSelectionChange();
                    authorSearchInput.value = ''; // Очищаем поиск
                    renderOptions(authorOptionsList, authorOptionsData, selectedAuthors, true);
                });
                container.appendChild(addItem);
                return;
            }

            options.forEach(option => {
                const isSelected = isMultiple ? selectedItems.includes(option.id) : selectedItems === option.id;
                const item = document.createElement('div');
                item.classList.add('dropdown-item');
                const inputType = isMultiple ? 'checkbox' : 'radio';
                const nameAttr = isMultiple ? '' : `name="${container.id}-radio"`;
                item.innerHTML = `
                    <input type="${inputType}" ${nameAttr} value="${option.id}" ${isSelected ? 'checked' : ''}>
                    <span>${option.name}</span>
                `;
                item.addEventListener('click', (e) => {
                    e.stopPropagation(); // Предотвращаем закрытие dropdown
                    if (e.target.type !== inputType) {
                        const input = item.querySelector('input');
                        if (isMultiple) {
                            input.checked = !input.checked;
                        } else {
                            // Для single: снимаем выбор с других и выбираем этот
                            container.querySelectorAll('input').forEach(inp => inp.checked = false);
                            input.checked = true;
                        }
                    }
                    if (container === authorOptionsList) {
                        handleAuthorSelectionChange();
                    } else if (container === shelfOptionsList) {
                        handleShelfSelectionChange();
                    } else {
                        handleStatusSelectionChange();
                    }
                });
                container.appendChild(item);
            });
        }

        // Обработка изменения выбора авторов (multiple)
        function handleAuthorSelectionChange() {
            selectedAuthors = Array.from(authorOptionsList.querySelectorAll('input:checked')).map(input => parseInt(input.value));
            selectedAuthorsInput.value = JSON.stringify(selectedAuthors);
            updateAuthorDisplay();
        }

        // Обновление отображаемого текста для авторов
        function updateAuthorDisplay() {
            const selectedNames = selectedAuthors
                .map(id => authorOptionsData.find(opt => opt.id === id)?.name)
                .filter(name => name)
                .join(', ');
            authorSelectDisplay.querySelector('.selected-items').textContent = selectedNames || 'Выберите авторов...';
        }

        // Обработка изменения выбора полок (single)
        function handleShelfSelectionChange() {
            const checkedInput = shelfOptionsList.querySelector('input:checked');
            selectedShelf = checkedInput ? parseInt(checkedInput.value) : null;
            selectedShelfInput.value = selectedShelf || '';
            updateShelfDisplay();
        }

        // Обновление отображаемого текста для полок
        function updateShelfDisplay() {
            const selectedName = selectedShelf
                ? shelfOptionsData.find(opt => opt.id === selectedShelf)?.name || ''
                : '';
            shelfSelectDisplay.querySelector('.selected-items').textContent = selectedName || 'Выберите полку...';
        }

        // Обработка изменения выбора статусов (single)
        function handleStatusSelectionChange() {
            const checkedInput = statusOptionsList.querySelector('input:checked');
            selectedStatus = checkedInput ? parseInt(checkedInput.value) : null;
            selectedStatusInput.value = selectedStatus || '';
            updateStatusDisplay();
        }

        // Обновление отображаемого текста для статусов
        function updateStatusDisplay() {
            const selectedName = selectedStatus
                ? statusOptionsData.find(opt => opt.id === selectedStatus)?.name || ''
                : '';
            statusSelectDisplay.querySelector('.selected-items').textContent = selectedName || 'Выберите статус...';
        }

        // Поиск по авторам с поддержкой добавления нового
        authorSearchInput.addEventListener('input', (e) => {
            e.stopPropagation();
            const query = authorSearchInput.value.toLowerCase();
            const filteredOptions = authorOptionsData.filter(option => option.name.toLowerCase().includes(query));
            renderOptions(authorOptionsList, filteredOptions, selectedAuthors, true, authorSearchInput.value);
        });

        // Поиск по полкам
        shelfSearchInput.addEventListener('input', (e) => {
            e.stopPropagation();
            const query = shelfSearchInput.value.toLowerCase();
            const filteredOptions = shelfOptionsData.filter(option => option.name.toLowerCase().includes(query));
            renderOptions(shelfOptionsList, filteredOptions, selectedShelf, false);
        });

        // Поиск по статусам
        statusSearchInput.addEventListener('input', (e) => {
            e.stopPropagation();
            const query = statusSearchInput.value.toLowerCase();
            const filteredOptions = statusOptionsData.filter(option => option.name.toLowerCase().includes(query));
            renderOptions(statusOptionsList, filteredOptions, selectedStatus, false);
        });

        // Предотвращение закрытия dropdown при клике внутри
        document.getElementById('authorDropdownMenu').addEventListener('click', (e) => {
            e.stopPropagation();
        });
        document.getElementById('shelfDropdownMenu').addEventListener('click', (e) => {
            e.stopPropagation();
        });
        document.getElementById('statusDropdownMenu').addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Инициализация dropdown'ов
        new bootstrap.Dropdown(authorSelectDisplay);
        new bootstrap.Dropdown(shelfSelectDisplay);
        new bootstrap.Dropdown(statusSelectDisplay);

        // Первичный рендеринг
        renderOptions(authorOptionsList, authorOptionsData, selectedAuthors, true);
        updateAuthorDisplay();
        renderOptions(shelfOptionsList, shelfOptionsData, selectedShelf, false);
        updateShelfDisplay();
        renderOptions(statusOptionsList, statusOptionsData, selectedStatus, false);
        updateStatusDisplay();

        // Функция для загрузки заметок
        async function loadNotes() {
            try {
                const response = await fetchWithAuth(`${API_BASE}/notes?book_id=${id}`, {
                    method: 'GET'
                });

                const notesList = document.querySelector('.notes-list');
                notesList.innerHTML = '';

                if (response.status === 'success' && response.data.length > 0) {
                    response.data.forEach(note => {
                        const noteItem = document.createElement('li');
                        noteItem.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-start');
                        noteItem.setAttribute('data-note-id', note.id);
                        // Используем data-атрибуты вместо onclick
                        noteItem.innerHTML = `
                            <div class="note-content">${note.content}</div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary edit-note-btn" data-note-id="${note.id}">Редактировать</button>
                                <button type="button" class="btn btn-outline-danger delete-note-btn" data-note-id="${note.id}">Удалить</button>
                            </div>
                        `;
                        notesList.appendChild(noteItem);
                    });
                }
            } catch (error) {
                console.error('Ошибка при загрузке заметок', error);
                alert('Не удалось загрузить заметки');
            }
        }



        // Функция для редактирования заметки
        async function editNote(noteId) {
            const noteItem = document.querySelector(`[data-note-id="${noteId}"]`);
            const noteContent = noteItem.querySelector('.note-content');
            const currentContent = noteContent.textContent;

            // Заменяем текст на textarea
            noteContent.innerHTML = `<textarea class="form-control form-control-sm" id="edit-note-${noteId}" rows="3">${currentContent}</textarea>`;

            // Меняем кнопки на Сохранить и Отмена
            const buttonGroup = noteItem.querySelector('.btn-group');
            buttonGroup.innerHTML = `
                <button type="button" class="btn btn-outline-primary save-note-btn" data-note-id="${noteId}">Сохранить</button>
                <button type="button" class="btn btn-outline-secondary cancel-edit-btn" data-note-id="${noteId}">Отмена</button>`;
        }

        // Функция для сохранения заметки
        async function saveNote(noteId) {
            const textarea = document.getElementById(`edit-note-${noteId}`);
            const content = textarea.value.trim();

            if (!content) {
                alert('Заметка не может быть пустой');
                return;
            }

            try {
                await fetchWithAuth(`${API_BASE}/notes/${noteId}`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        content: content
                    })
                });

                loadNotes(); // Перезагружаем список заметок
            } catch (error) {
                console.error('Ошибка при сохранении заметки', error);
                alert('Не удалось сохранить заметку');
            }
        }

        // Функция для отмены редактирования
        async function cancelEdit(noteId) {
            loadNotes(); // Просто перезагружаем список заметок
        }

        // Функция для удаления заметки
        async function deleteNote(noteId) {
            if (!confirm('Вы уверены, что хотите удалить эту заметку?')) return;

            try {
                await fetchWithAuth(`${API_BASE}/notes/${noteId}`, {
                    method: 'DELETE'
                });

                loadNotes(); // Перезагружаем список заметок
            } catch (error) {
                console.error('Ошибка при удалении заметки', error);
                alert('Не удалось удалить заметку');
            }
        }

        // Удаление старых обработчиков и добавление новых с делегированием
        const notesList = document.querySelector('.notes-list');
        
        // Удаляем существующие обработчики, если они есть
        notesList.removeEventListener('click', handleNotesListClick);
        
        // Добавляем новый обработчик с делегированием
        notesList.addEventListener('click', handleNotesListClick);
        
        // Обработчик для всех кнопок заметок
        function handleNotesListClick(event) {
            const editButton = event.target.closest('.edit-note-btn');
            const deleteButton = event.target.closest('.delete-note-btn');
            const saveButton = event.target.closest('.save-note-btn');
            const cancelButton = event.target.closest('.cancel-edit-btn');
            
            if (editButton) {
                const noteId = editButton.dataset.noteId;
                editNote(noteId);
            }
            
            if (deleteButton) {
                const noteId = deleteButton.dataset.noteId;
                deleteNote(noteId);
            }
            
            if (saveButton) {
                const noteId = saveButton.dataset.noteId;
                saveNote(noteId);
            }
            
            if (cancelButton) {
                const noteId = cancelButton.dataset.noteId;
                cancelEdit(noteId);
            }
        }
        
        // Обработчик кнопки добавления заметки
        document.querySelector('[data-action="add-note"]').addEventListener('click', async () => {
            const newNoteTextarea = document.getElementById('newNote');
            const content = newNoteTextarea.value.trim();

            if (!content) {
                alert('Введите текст заметки');
                return;
            }

            try {
                await fetchWithAuth(`${API_BASE}/notes`, {
                    method: 'POST',
                    body: JSON.stringify({
                        content: content,
                        book_id: id
                    })
                });

                newNoteTextarea.value = '';
                loadNotes(); // Перезагружаем список заметок
            } catch (error) {
                console.error('Ошибка при создании заметки', error);
                alert('Не удалось создать заметку');
            }
        });

        // Функция для загрузки цитат
        async function loadQuotes() {
            try {
                const response = await fetchWithAuth(`${API_BASE}/quotes?book_id=${id}`, {
                    method: 'GET'
                });

                const quotesList = document.querySelector('.quotes-list');
                quotesList.innerHTML = '';

                if (response.status === 'success' && response.data.length > 0) {
                    response.data.forEach(quote => {
                        const quoteItem = document.createElement('li');
                        quoteItem.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-start');
                        quoteItem.setAttribute('data-quote-id', quote.id);
                        // Используем data-атрибуты вместо onclick
                        quoteItem.innerHTML = `
                            <div class="quote-content">
                                <div>"${quote.content}"</div>
                                <div class="text-muted small">${quote.page_number} стр., ${quote.author || 'Автор не указан'}</div>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary edit-quote-btn" data-quote-id="${quote.id}">Редактировать</button>
                                <button type="button" class="btn btn-outline-danger delete-quote-btn" data-quote-id="${quote.id}">Удалить</button>
                            </div>
                        `;
                        quotesList.appendChild(quoteItem);
                    });
                }
            } catch (error) {
                console.error('Ошибка при загрузке цитат', error);
                alert('Не удалось загрузить цитаты');
            }
        }

        // Функция для редактирования цитаты
        async function editQuote(quoteId) {
            const quoteItem = document.querySelector(`[data-quote-id="${quoteId}"]`);
            const quoteContent = quoteItem.querySelector('.quote-content');
            const contentDiv = quoteContent.querySelector('div:first-child');
            const pageDiv = quoteContent.querySelector('div.text-muted');
            
            const content = contentDiv.textContent.trim().replace(/"/g, '');
            const pageMatch = pageDiv.textContent.match(/(\d+) стр/);
            const page = pageMatch ? pageMatch[1] : '';
            
            // Заменяем текст на форму редактирования
            quoteContent.innerHTML = `
                <div class="mb-2">
                    <textarea class="form-control form-control-sm" id="edit-quote-content-${quoteId}" rows="2">${content}</textarea>
                </div>
                <div class="mb-2">
                    <input type="text" class="form-control form-control-sm" id="edit-quote-author-${quoteId}" placeholder="Автор цитаты" value="">
                </div>
                <div>
                    <input type="number" class="form-control form-control-sm" id="edit-quote-page-${quoteId}" placeholder="Номер страницы" value="${page}">
                </div>
            `;

            // Меняем кнопки на Сохранить и Отмена
            const buttonGroup = quoteItem.querySelector('.btn-group');
            buttonGroup.innerHTML = `
                <button type="button" class="btn btn-outline-primary save-quote-btn" data-quote-id="${quoteId}">Сохранить</button>
                <button type="button" class="btn btn-outline-secondary cancel-edit-quote-btn" data-quote-id="${quoteId}">Отмена</button>`;
        }

        // Функция для сохранения цитаты
        async function saveQuote(quoteId) {
            const contentTextarea = document.getElementById(`edit-quote-content-${quoteId}`);
            const authorInput = document.getElementById(`edit-quote-author-${quoteId}`);
            const pageInput = document.getElementById(`edit-quote-page-${quoteId}`);
            
            const content = contentTextarea.value.trim();
            const author = authorInput.value.trim();
            const page = pageInput.value.trim();

            if (!content) {
                alert('Цитата не может быть пустой');
                return;
            }

            if (!page) {
                alert('Укажите номер страницы');
                return;
            }

            try {
                await fetchWithAuth(`${API_BASE}/quotes/${quoteId}`, {
                    method: 'POST',
                    body: JSON.stringify({
                        content: content,
                        page_number: parseInt(page),
                        author: author
                    })
                });

                loadQuotes(); // Перезагружаем список цитат
            } catch (error) {
                console.error('Ошибка при сохранении цитаты', error);
                alert('Не удалось сохранить цитату');
            }
        }

        // Функция для отмены редактирования цитаты
        async function cancelEditQuote(quoteId) {
            loadQuotes(); // Просто перезагружаем список цитат
        }

        // Функция для удаления цитаты
        async function deleteQuote(quoteId) {
            if (!confirm('Вы уверены, что хотите удалить эту цитату?')) return;

            try {
                await fetchWithAuth(`${API_BASE}/quotes/${quoteId}`, {
                    method: 'DELETE'
                });

                loadQuotes(); // Перезагружаем список цитат
            } catch (error) {
                console.error('Ошибка при удалении цитаты', error);
                alert('Не удалось удалить цитату');
            }
        }

        // Удаление старых обработчиков и добавление новых с делегированием для цитат
        const quotesList = document.querySelector('.quotes-list');
        
        // Удаляем существующие обработчики, если они есть
        quotesList.removeEventListener('click', handleQuotesListClick);
        
        // Добавляем новый обработчик с делегированием
        quotesList.addEventListener('click', handleQuotesListClick);
        
        // Обработчик для всех кнопок цитат
        function handleQuotesListClick(event) {
            const editButton = event.target.closest('.edit-quote-btn');
            const deleteButton = event.target.closest('.delete-quote-btn');
            const saveButton = event.target.closest('.save-quote-btn');
            const cancelButton = event.target.closest('.cancel-edit-quote-btn');
            
            if (editButton) {
                const quoteId = editButton.dataset.quoteId;
                editQuote(quoteId);
            }
            
            if (deleteButton) {
                const quoteId = deleteButton.dataset.quoteId;
                deleteQuote(quoteId);
            }
            
            if (saveButton) {
                const quoteId = saveButton.dataset.quoteId;
                saveQuote(quoteId);
            }
            
            if (cancelButton) {
                const quoteId = cancelButton.dataset.quoteId;
                cancelEditQuote(quoteId);
            }
        }
        
        // Обработчик кнопки добавления цитаты
        document.querySelector('[data-action="add-quote"]').addEventListener('click', async () => {
            const quoteTextarea = document.getElementById('newQuote');
            const authorInput = document.getElementById('quoteAuthor');
            const pageInput = document.getElementById('quotePage');
            
            const content = quoteTextarea.value.trim();
            const author = authorInput.value.trim();
            const page = pageInput.value.trim();

            if (!content) {
                alert('Введите текст цитаты');
                return;
            }

            if (!page) {
                alert('Укажите номер страницы');
                return;
            }

            try {
                await fetchWithAuth(`${API_BASE}/quotes`, {
                    method: 'POST',
                    body: JSON.stringify({
                        content: content,
                        book_id: id,
                        page_number: parseInt(page),
                        author: author
                    })
                });

                quoteTextarea.value = '';
                authorInput.value = '';
                pageInput.value = '';
                loadQuotes(); // Перезагружаем список цитат
            } catch (error) {
                console.error('Ошибка при создании цитаты', error);
                alert('Не удалось создать цитату');
            }
        });

        // Инициализация заметок и цитат
        loadNotes(); // Загружаем заметки при открытии модального окна
        loadQuotes(); // Загружаем цитаты при открытии модального окна

        // Обработчик кнопки сохранения книги
        const saveBookButton = document.getElementById('saveBook');
        saveBookButton.addEventListener('click', async () => {
            const bookTitle = document.getElementById('book-detail-title').value;
            const selectedShelf = selectedShelfInput.value ? parseInt(selectedShelfInput.value) : null;
            const selectedStatus = selectedStatusInput.value ? parseInt(selectedStatusInput.value) : null;

            if (!bookTitle) {
                alert('Название книги обязательно');
                return;
            }

            try {
                let finalSelectedAuthors = [...selectedAuthors];

                // Если есть новые авторы, создаем их
                if (newAuthors.length > 0) {
                    const newAuthorNames = newAuthors.map(author => author.name);
                    const createResponse = await fetchWithAuth(`${API_BASE}/batch-authors`, {
                        method: 'POST',
                        body: JSON.stringify({ names: newAuthorNames })
                    });

                    // Заменяем tempId на реальные ID
                    newAuthors.forEach((newAuthor, index) => {
                        const realId = createResponse['data'][index].id;
                        const tempIdIndex = finalSelectedAuthors.indexOf(newAuthor.id);
                        if (tempIdIndex !== -1) {
                            finalSelectedAuthors[tempIdIndex] = realId;
                        }
                        const optionIndex = authorOptionsData.findIndex(opt => opt.id === newAuthor.id);
                        if (optionIndex !== -1) {
                            authorOptionsData[optionIndex].id = realId;
                        }
                    });
                }

                const formData = new FormData();
                const data = JSON.stringify(
                    {
                        title: bookTitle,
                        selected_authors: finalSelectedAuthors,
                        shelf_id: selectedShelf,
                        status_id: selectedStatus
                    }
                );

                formData.append('data', data);
                const fileInput = document.getElementById('book-cover-upload');
                if (fileInput && fileInput.files.length > 0) {
                    formData.append('cover', fileInput.files[0]);
                }

                // Сохраняем книгу
                await fetchWithAuth(`${API_BASE}/book/${id}`, {
                    method: 'POST',
                    body: formData
                });

                // Re-render the board
                init();
            } catch (e) {
                console.error('Ошибка при сохранении книги', e);
                alert('Не удалось сохранить книгу');
            }
        });

        // Обработчик кнопки удаления книги
        const deleteBookButton = document.getElementById('deleteBook');
        deleteBookButton.addEventListener('click', async () => {
            if (!confirm('Вы уверены, что хотите удалить эту книгу?')) return;

            try {
                await fetchWithAuth(`${API_BASE}/book/${id}`, {
                    method: 'DELETE'
                });
                
                // Закрываем модальное окно
                modal.hide();
                
                // Перезагружаем данные на странице
                init();
                
            } catch (error) {
                console.error('Ошибка при удалении книги', error);
                alert('Не удалось удалить книгу');
            }
        });

        // Показ модального окна
        modal.show();
    }
    catch (e) {
        console.error('Ошибка при загрузке книги', e);
    }
}

