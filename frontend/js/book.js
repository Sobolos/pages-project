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
            cover.setAttribute('src', `${BACKEND_BASE}` + coverUrl)
        }
    }

    try {
        // Загрузка данных книги
        let book = await fetchWithAuth(`${API_BASE}/books/${id}`, {
            method: 'GET'
        });

        book = book.data[0];

        console.log(book);

        // Заполнение названия книги
        const bookTitle = document.getElementById('book-detail-title');
        bookTitle.value = book.title;

        // Установка выбранных авторов
        selectedAuthors = book.authors.map(author => author.id);
        selectedAuthorsInput.value = JSON.stringify(selectedAuthors);

        // Установка выбранной полки (если есть)
        selectedShelf = book.shelf ? book.shelf.id : null;
        selectedShelfInput.value = selectedShelf || '';

        // Установка выбранного статуса (если есть)
        selectedStatus = book.status ? book.status.id : null;
        selectedStatusInput.value = selectedStatus || '';

        console.log(book.coverUrl)
        setBookCover(book.coverUrl);

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
                    const newAuthor = { id: tempIdCounter, name: query };
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

        // Обработчик сохранения
        document.getElementById('saveBook').onclick = async () => {
            try {
                let finalSelectedAuthors = [...selectedAuthors];

                // Если есть новые авторы, создаем их на backend
                if (newAuthors.length > 0) {
                    const newAuthorNames = newAuthors.map(author => author.name);
                    const createResponse = await fetchWithAuth(`${API_BASE}/authors/batch-create`, {
                        method: 'POST',
                        body: JSON.stringify({ names: newAuthorNames })
                    });

                    // Заменяем tempId на реальные ID
                    newAuthors.forEach((newAuthor, index) => {
                        const realId = createResponse[index].id;
                        const tempIdIndex = finalSelectedAuthors.indexOf(newAuthor.id);
                        if (tempIdIndex !== -1) {
                            finalSelectedAuthors[tempIdIndex] = realId;
                        }
                        // Обновляем authorOptionsData
                        const optionIndex = authorOptionsData.findIndex(opt => opt.id === newAuthor.id);
                        if (optionIndex !== -1) {
                            authorOptionsData[optionIndex].id = realId;
                        }
                    });
                    newAuthors = []; // Очищаем после создания
                }

                const formData = new FormData();
                const data = JSON.stringify(
                    {
                        title: bookTitle.value,
                        selectedAuthors: finalSelectedAuthors,
                        selectedShelf: selectedShelf,
                        selectedStatus: selectedStatus
                    }
                );

                formData.append('data', data);
                const fileInput = document.getElementById('book-cover-upload');
                if (fileInput && fileInput.files.length > 0) {
                    formData.append('file', fileInput.files[0]);
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
        };

        document.getElementById('deleteBook').onclick = async () => {
            await fetchWithAuth(`${API_BASE}/book/${id}`, {
                    method: 'DELETE'
                }
            );
            modal.hide();
            // Re-render the board
            init();
        };

        modal.show();
    } catch (e) {
        console.error('Ошибка при загрузке книги', e);
        alert('Не удалось загрузить данные книги');
    }
}