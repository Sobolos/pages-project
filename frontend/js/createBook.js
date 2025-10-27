let newAuthors = []; // Новые авторы {tempId: -N, name: 'Имя'}

document.getElementById('add-book-modal').addEventListener('show.bs.modal', () => {
    const authorSelectDisplay = document.getElementById('authorSelectDisplayAdd');
    const authorSearchInput = document.getElementById('authorSearchInputAdd');
    const authorOptionsList = document.getElementById('authorOptionsListAdd');
    const selectedAuthorsInput = document.getElementById('selectedAuthorsAdd');
    const shelfSelectDisplay = document.getElementById('shelfSelectDisplayAdd');
    const shelfSearchInput = document.getElementById('shelfSearchInputAdd');
    const shelfOptionsList = document.getElementById('shelfOptionsListAdd');
    const selectedShelfInput = document.getElementById('selectedShelfAdd');
    const statusSelectDisplay = document.getElementById('statusSelectDisplayAdd');
    const statusSearchInput = document.getElementById('statusSearchInputAdd');
    const statusOptionsList = document.getElementById('statusOptionsListAdd');
    const selectedStatusInput = document.getElementById('selectedStatusAdd');

    // Данные из глобальных переменных
    let authorOptionsData = [...authors]; // Копируем, чтобы не мутировать
    let selectedAuthors = [];
    let tempIdCounter = -1; // Счетчик для временных ID
    let shelfOptionsData = [...shelves];
    let selectedShelf = null;
    let statusOptionsData = [...statuses];
    let selectedStatus = null;

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
                e.stopPropagation();
                if (e.target.type !== inputType) {
                    const input = item.querySelector('input');
                    if (isMultiple) {
                        input.checked = !input.checked;
                    } else {
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
        authorSelectDisplay.querySelector('.selected-items').textContent = selectedNames || 'Выберите авторов';
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
        shelfSelectDisplay.querySelector('.selected-items').textContent = selectedName || 'Выберите полку';
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
        statusSelectDisplay.querySelector('.selected-items').textContent = selectedName || 'Выберите статус';
    }

    // Поиск по авторам
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

    // Предотвращение закрытия dropdown
    document.getElementById('authorDropdownMenuAdd').addEventListener('click', (e) => {
        e.stopPropagation();
    });
    document.getElementById('shelfDropdownMenuAdd').addEventListener('click', (e) => {
        e.stopPropagation();
    });
    document.getElementById('statusDropdownMenuAdd').addEventListener('click', (e) => {
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
});

document.getElementById('saveBookBtn').addEventListener('click', async () => {
    const title = document.getElementById('book-title').value;
    const selectedAuthorsInput = document.getElementById('selectedAuthorsAdd');
    const selectedShelfInput = document.getElementById('selectedShelfAdd');
    const selectedStatusInput = document.getElementById('selectedStatusAdd');

    if (!title) {
        alert('Название обязательно');
        return;
    }

    // Данные из глобальных переменных и полей
    let authorOptionsData = [...authors];
    let selectedAuthors = selectedAuthorsInput.value ? JSON.parse(selectedAuthorsInput.value) : [];
    let selectedShelf = selectedShelfInput.value ? parseInt(selectedShelfInput.value) : null;
    let selectedStatus = selectedStatusInput.value ? parseInt(selectedStatusInput.value) : null;

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
                const realId = createResponse[index].id;
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

        //нужно чтобы сначала грузилась книга а потом к ней обложка и епаб
        //const formData = new FormData();
        const data = JSON.stringify({
            title: title,
            shelf_id: selectedShelf,
            status_id: selectedStatus,
            selected_authors: finalSelectedAuthors,
            author_names: [], // Для совместимости
            physical_page_count: 0
        });
        //formData.append('data', data);

        // const fileInput = document.getElementById('book-cover');
        // if (fileInput && fileInput.files.length > 0) {
        //     formData.append('file', fileInput.files[0]);
        // }

        await fetchWithAuth(`${API_BASE}/books`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: data
        });
        bootstrap.Modal.getInstance(document.getElementById('add-book-modal')).hide();
        init(); // Перезагружаем данные
    } catch (error) {
        console.error('Ошибка создания книги', error);
        alert('Ошибка создания книги');
    }
});