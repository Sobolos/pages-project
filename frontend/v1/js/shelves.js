const tabs = document.getElementById('shelfTabs');
const tabContent = document.getElementById('shelfTabContent');

function displayShelves(shelves, books) {
    loadShelves(shelves, books);
}

function loadShelvesDOM(shelves) {
    tabs.innerHTML = '';
    console.log(shelves);
    shelves.forEach((shelf, index) => {
        const isActive = index === 0 ? 'active' : '';
        const tabId = `shelf-${shelf.id}`;

        // Создание вкладки
        const tab = document.createElement('li');
        tab.className = `nav-item ${isActive ? 'active-tab' : ''} shelf-tab-item`;
        tab.innerHTML = `
            <a class="nav-link ${isActive} shelf-nav-link" id="${tabId}-tab" data-bs-toggle="tab" href="#${tabId}" role="tab">
                <div class="d-flex align-items-center">
                    <span>${shelf.name}</span>
                    <button class="btn btn-sm ms-2 rename-shelf" data-shelf-id="${shelf.id}" data-shelf-name="${shelf.name}">✏️</button>
                    <button class="btn btn-sm ms-2 delete-shelf" data-shelf-id="${shelf.id}">🗑️</button>
                </div>
            </a>
        `;
        tabs.appendChild(tab);

        // Создание контента вкладки
        const pane = document.createElement('div');
        pane.className = `tab-pane fade ${isActive ? 'show active' : ''}`;
        pane.id = tabId;
        pane.innerHTML = `
            <div class="row" id="books-${shelf.id}">
                <!-- Книги будут добавлены сюда -->
            </div>
        `;
        tabContent.appendChild(pane);

        // Загрузка книг для полки
        loadBooksForShelf(shelf.id, books);
    });

    // Добавление обработчиков для кнопок переименования и удаления
    document.querySelectorAll('.rename-shelf').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation(); // Предотвращаем переключение вкладки
            const shelfId = e.target.getAttribute('data-shelf-id');
            const shelfName = e.target.getAttribute('data-shelf-name');
            openRenameShelfModal(shelfId, shelfName, e);
        });
    });

    document.querySelectorAll('.delete-shelf').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation(); // Предотвращаем переключение вкладки
            const shelfId = e.target.getAttribute('data-shelf-id');
            deleteShelf(shelfId);
        });
    });

    // Обработчик переключения вкладок для управления z-index и видимости кнопок
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('shown.bs.tab', (e) => {
            // Удаляем класс active-tab у всех nav-item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active-tab');
            });
            // Добавляем active-tab к родительскому nav-item активной вкладки
            e.target.parentElement.classList.add('active-tab');
        });
    });
}

async function loadBooksForShelf(shelfId, books) {
    try {
        const filteredBooks = books.filter(book => book.shelf === shelfId);
        const container = document.getElementById(`books-${shelfId}`);
        container.innerHTML = '';

        filteredBooks.forEach(book => {
            const card = document.createElement('div');
            let authors = '';
            card.className = 'col-12 col-sm-6 col-md-4 col-lg-3 mb-4';

            book.authors.forEach(author => {
                authors = authors + author.name + ', ';
            });
            authors = authors.slice(0, -2); // Убираем последнюю запятую

            card.innerHTML = `
                <div class="card book-card">
                    ${book.cover ? `<img src="${book.cover}" class="card-img-top book-cover mx-auto mt-3" alt="${book.title}">` : ''}
                    <div class="card-body text-center" onclick="openBookModal(${book.id})">
                        <h5 class="card-title">${book.title}</h5>
                        <p class="card-text">${authors ? authors : 'Неизвестный автор'}</p>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });
    } catch (error) {
        console.error(`Ошибка загрузки книг для полки ${shelfId}:`, error);
    }
}

function openRenameShelfModal(shelfId, shelfName, editBtn) {
    const modal = document.getElementById('renameShelfModal');
    const input = document.getElementById('rename-shelf-name');
    const form = document.getElementById('rename-shelf-form');

    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();

    input.value = shelfName;
    form.onsubmit = async (e) => {
        e.preventDefault();
        const newName = input.value.trim();
        if (newName) {
            try {
                const response = await fetchWithAuth(`${API_BASE}/shelf/${shelfId}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name: newName })
                });
                if (response.success) {
                    // Обновляем название полки в DOM
                    const tabLink = document.getElementById(`shelf-${shelfId}-tab`);
                    tabLink.querySelector('span').textContent = newName;
                    editBtn.target.setAttribute('data-shelf-name', newName);
                    // Закрываем модальное окно
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance.hide();
                } else {
                    console.error('Ошибка при переименовании полки');
                }
            } catch (error) {
                console.error('Ошибка при отправке запроса:', error);
            }
        }
    };
}

async function deleteShelf(shelfId) {
    if (confirm('Вы уверены, что хотите удалить эту полку? Все книги на ней будут удалены.')) {
        try {
            const response = await fetchWithAuth(`${API_BASE}/shelf/${shelfId}`, {
                method: 'DELETE'
            });
            if (response.success) {
                // Удаляем вкладку и контент из DOM
                const tab = document.getElementById(`shelf-${shelfId}-tab`).parentElement;
                const pane = document.getElementById(`shelf-${shelfId}`);
                tab.remove();
                pane.remove();
                // Если удаленная полка была активной, активируем первую доступную
                const firstTab = document.querySelector('.nav-link');
                if (firstTab) {
                    firstTab.classList.add('active');
                    firstTab.parentElement.classList.add('active-tab');
                    const firstPaneId = firstTab.getAttribute('href').substring(1);
                    document.getElementById(firstPaneId).classList.add('show', 'active');
                }
            } else {
                console.error('Ошибка при удалении полки');
            }
        } catch (error) {
            console.error('Ошибка при отправке запроса:', error);
        }
    }
}

// Сохранение сущностей
document.getElementById('saveShelfBtn').addEventListener('click', async () => {
    const name = document.getElementById('shelf-name').value;
    if (!name) return alert('Название обязательно');

    try {
        await fetchWithAuth(`${API_BASE}/shelf`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name })
        });
        bootstrap.Modal.getInstance(document.getElementById('addShelfModal')).hide();
        init();
    } catch (error) {
        alert('Ошибка создания полки');
    }
});