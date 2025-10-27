
// Загрузка полок
async function loadShelves() {
    try {
        const shelves = await fetchWithAuth(`${API_BASE}/shelves`);
        const tabs = document.getElementById('shelfTabs');
        const tabContent = document.getElementById('shelfTabContent');
        tabs.innerHTML = '';
        tabContent.innerHTML = '';

        shelves.forEach((shelf, index) => {
            const isActive = index === 0 ? 'active' : '';
            const tabId = `shelf-${shelf.id}`;

            // Создание вкладки
            const tab = document.createElement('li');
            tab.className = 'nav-item';
            tab.innerHTML = `
                <a class="nav-link ${isActive}" id="${tabId}-tab" data-bs-toggle="tab" href="#${tabId}" role="tab">${shelf.name}</a>
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
            loadBooksForShelf(shelf.id);
        });

        document.getElementById('bookShelf').innerHTML = shelves.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
    } catch (error) {
        console.error('Ошибка загрузки полок:', error);
    }
}


// Загрузка книг для конкретной полки
async function loadBooksForShelf(shelfId) {
    try {
        const books = await fetchWithAuth(`${API_BASE}/shelf/${shelfId}/books`);
        const container = document.getElementById(`books-${shelfId}`);
        container.innerHTML = '';

        books.forEach(book => {
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