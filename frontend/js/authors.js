/**
 * Управление авторами
 */

document.addEventListener('DOMContentLoaded', function() {
    // Получаем список авторов при открытии модального окна
    document.getElementById('authorsModal').addEventListener('show.bs.modal', function() {
        loadAuthorsList();
    });
});

// Показать форму добавления автора
function showAddAuthorForm() {
    const addRow = document.getElementById('add-author-row');
    const formRow = document.getElementById('add-author-form');
    if (addRow && formRow) {
        addRow.style.display = 'none';
        formRow.style.display = 'table-row';
        const input = document.getElementById('new-author-name');
        if (input) {
            input.value = '';
            input.focus();
        }
        
        // Убедимся, что tbody не перезаписывается при загрузке
        const tbody = document.getElementById('authorsList');
        if (tbody && !tbody.querySelector('#add-author-form')) {
            const formRowClone = formRow.cloneNode(true);
            formRowClone.style.display = 'table-row';
            tbody.appendChild(formRowClone);
        }
    }
}

// Скрыть форму добавления автора
function cancelAddAuthor() {
    document.getElementById('add-author-row').style.display = 'table-row';
    document.getElementById('add-author-form').style.display = 'none';
    document.getElementById('new-author-name').value = '';
}

// Добавление нового автора
async function addAuthor() {
    const newName = document.getElementById('new-author-name').value.trim();
    
    if (!newName) {
        return;
    }
    
    try {
        const response = await fetchWithAuth(`${API_BASE}/authors`, {
            method: 'POST',
            body: JSON.stringify({ name: newName })
        });
        
        if (response.status === 'success') {
            // Добавляем в глобальный массив authors
            authors.push(response.data);
            
            // Перезагружаем список авторов
            loadAuthorsList();
            
            // Сбрасываем форму
            cancelAddAuthor();
        } else {
            throw new Error(response.error || 'Не удалось добавить автора');
        }
    } catch (error) {
        console.error('Ошибка при добавлении автора', error);
    }
}

// Загрузка списка авторов
async function loadAuthorsList() {
    try {
        const response = await fetchWithAuth(`${API_BASE}/authors`, {
            method: 'GET'
        });
        
        if (response.status === 'success') {
            const authorsList = document.getElementById('authorsList');
            
            // Сохраняем форму добавления, если она существует
            const addAuthorForm = authorsList.querySelector('#add-author-form');
            
            // Очищаем содержимое, кроме строки добавления
            const addAuthorRow = authorsList.querySelector('#add-author-row');
            authorsList.innerHTML = '';
            
            // Восстанавливаем строку добавления
            if (addAuthorRow) {
                authorsList.appendChild(addAuthorRow);
            }
            
            // Восстанавливаем форму добавления
            if (addAuthorForm) {
                authorsList.appendChild(addAuthorForm);
            }
            
            response.data.forEach(author => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td id="author-name-${author.id}">${author.name}</td>
                    <td>
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="editAuthor(${author.id})">
                            <i class="bi bi-pencil"></i> Редактировать
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteAuthor(${author.id})">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </td>
                `;
                authorsList.appendChild(row);
            });
        }
    } catch (error) {
        console.error('Ошибка при загрузке авторов', error);
    }
}

// Редактирование автора
async function editAuthor(authorId) {
    const row = document.querySelector(`#author-name-${authorId}`).parentElement;
    const currentName = document.getElementById(`author-name-${authorId}`).textContent;
    
    // Заменяем текст на поле ввода
    document.getElementById(`author-name-${authorId}`).innerHTML = `
        <input type="text" class="form-control" id="edit-author-input" value="${currentName}" />
    `;
    
    // Меняем кнопки на Сохранить и Отмена
    row.querySelector('td:last-child').innerHTML = `
        <button type="button" class="btn btn-primary btn-sm me-2" onclick="saveAuthor(${authorId})">
            <i class="bi bi-save"></i> Сохранить
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit(${authorId}, '${currentName}')">
            <i class="bi bi-x"></i> Отмена
        </button>
    `;
}

// Отмена редактирования
function cancelEdit(authorId, originalName) {
    document.getElementById(`author-name-${authorId}`).textContent = originalName;
    loadAuthorsList(); // Перезагружаем список для восстановления кнопок
}

// Сохранение изменений автора
async function saveAuthor(authorId) {
    const newName = document.getElementById('edit-author-input').value.trim();
    
    if (!newName) {
        return;
    }
    
    try {
        const response = await fetchWithAuth(`${API_BASE}/authors/${authorId}`, {
            method: 'PUT',
            body: JSON.stringify({ name: newName })
        });
        
        if (response.status === 'success') {
            // Обновляем глобальный массив authors
            const authorIndex = authors.findIndex(a => a.id === authorId);
            if (authorIndex !== -1) {
                authors[authorIndex].name = newName;
            }
            
            // Перезагружаем список авторов
            loadAuthorsList();
            
            // Обновляем интерфейс выбора авторов в модальных окнах
            updateAuthorSelectDisplays(newName, authorId);
            // Re-render the board
            init();
        } else {
            throw new Error(response.error || 'Неизвестная ошибка');
        }
    } catch (error) {
        console.error('Ошибка при обновлении автора', error);
        loadAuthors(); // Восстанавливаем список в случае ошибки
    }
}

// Удаление автора
async function deleteAuthor(authorId) {
    if (!confirm('Вы уверены, что хотите удалить этого автора? Это действие нельзя отменить.')) {
        return;
    }
    
    try {
        const response = await fetchWithAuth(`${API_BASE}/authors/${authorId}`, {
            method: 'DELETE'
        });
        
        if (response.status === 'success') {
            // Удаляем из глобального массива authors
            authors = authors.filter(a => a.id !== authorId);
            
            // Перезагружаем список авторов
            loadAuthorsList();
            
            // Обновляем интерфейс выбора авторов
            updateAuthorSelectDisplays(null, authorId);
        } else {
            throw new Error(response.error || 'Неизвестная ошибка');
        }
    } catch (error) {
        console.error('Ошибка при удалении автора', error);
    }
}

// Обновление отображения выбора авторов в других модальных окнах
function updateAuthorSelectDisplays(newName = null, authorId) {
    // Обновляем отображение в модальном окне добавления книги
    const authorDisplayAdd = document.getElementById('authorSelectDisplayAdd');
    if (authorDisplayAdd) {
        const selectedItems = authorDisplayAdd.querySelector('.selected-items');
        if (selectedItems) {
            const currentText = selectedItems.textContent;
            if (currentText.includes(authors.find(a => a.id === authorId)?.name || '')) {
                if (newName) {
                    selectedItems.textContent = currentText.replace(authors.find(a => a.id === authorId)?.name, newName);
                } else {
                    selectedItems.textContent = 'Выберите авторов...';
                }
            }
        }
    }
    
    // Обновляем отображение в модальном окне редактирования книги
    const authorDisplay = document.getElementById('authorSelectDisplay');
    if (authorDisplay) {
        const selectedItems = authorDisplay.querySelector('.selected-items');
        if (selectedItems) {
            const currentText = selectedItems.textContent;
            if (currentText.includes(authors.find(a => a.id === authorId)?.name || '')) {
                if (newName) {
                    selectedItems.textContent = currentText.replace(authors.find(a => a.id === authorId)?.name, newName);
                } else {
                    selectedItems.textContent = 'Выберите авторов...';
                }
            }
        }
    }
}