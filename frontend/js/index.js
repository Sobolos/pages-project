if (!checkLogin()) {
    //window.location.href = '/login.html';
}

let statuses;
let books;
let authors;
let shelves;

async function init(){
    try {
        const [
            statuses,
            books,
            authors,
            shelves
        ] = await Promise.all([
            loadStatuses(),
            loadBooks(),
            loadAuthors(),
            loadShelves()
        ]);

        setVariables(
            statuses.data,
            authors.data,
            books.data,
            shelves.data
        )
        loadBoard(statuses.data, authors.data, books.data);
        loadShelvesDOM(shelves.data, books.data);
    } catch (error) {
        console.error(error);
    }
}

function loadStatuses() {
    return fetchWithAuth(`${API_BASE}/statuses`)
}

function loadBooks() {
    return fetchWithAuth(`${API_BASE}/books`)
}

function loadAuthors() {
    return fetchWithAuth(`${API_BASE}/authors`)
}

function loadShelves() {
    return fetchWithAuth(`${API_BASE}/shelves`)
}

init();

function setVariables(
    statusesArg,
    authorsArg,
    booksArg,
    shelvesArg
) {
    statuses = statusesArg;
    authors = authorsArg;
    books = booksArg;
    shelves = shelvesArg;
}

// Обновляем цвет статуса при выборе из выпадающего блока
document.addEventListener('DOMContentLoaded', function() {
    const colorPreview = document.getElementById('color-preview');
    const colorDropdown = document.getElementById('color-dropdown');
    const colorOptions = document.querySelectorAll('.color-option');
    const statusColor = document.getElementById('status-color');
    const selectedColor = document.getElementById('selected-color');
    
    // Показываем/скрываем выпадающий блок при клике на превью
    if (colorPreview) {
        colorPreview.addEventListener('click', function(e) {
            e.stopPropagation();
            colorDropdown.style.display = colorDropdown.style.display === 'none' ? 'block' : 'none';
        });
    }
    
    // Закрываем выпадающий блок при клике вне его
    document.addEventListener('click', function(e) {
        if (colorDropdown.style.display === 'block') {
            // Проверяем, что клик был не по превью и не по выпадающему блоку
            if (!colorPreview.contains(e.target) && !colorDropdown.contains(e.target)) {
                colorDropdown.style.display = 'none';
            }
        }
    });
    
    // Предотвращаем закрытие при клике на инпут цвета
    const colorInput = document.getElementById('status-color');
    if (colorInput) {
        colorInput.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Обработка выбора цвета из кирпичиков
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            statusColor.value = color;
            selectedColor.value = color;
            colorPreview.style.background = color;
            colorDropdown.style.display = 'none';
        });
    });
    
    // Обработка выбора цвета из инпута
    statusColor.addEventListener('input', function() {
        const color = this.value;
        selectedColor.value = color;
        colorPreview.style.background = color;
    });
    
    // Устанавливаем начальное значение цвета
    if (statusColor && selectedColor && colorPreview) {
        colorPreview.style.background = 'linear-gradient(45deg, #fcbeec, #a9dadf, #6cffce)';
    }
});