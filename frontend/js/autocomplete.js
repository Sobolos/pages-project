const autocompleteSources = {
    author: `${API_BASE}/authors`,
    shelf: `${API_BASE}/shelves`,
    status: `${API_BASE}/statuses`
};

function showSuggestions(element, suggestions, onSelect, currentAuthorIndex) {
    const wrapper = element.parentElement.querySelector('.autocomplete-suggestions');
    wrapper.innerHTML = '';
    wrapper.classList.add('active');

    suggestions.slice(0, 10).forEach(item => {
        const div = document.createElement('div');
        div.classList.add('list-group-item', 'list-group-item-action', 'suggestion-item');
        div.style.padding = '8px';
        div.textContent = item.name;
        div.dataset.id = item.id;
        div.addEventListener('mousedown', () => {
            const currentValue = element.textContent.trim();
            let authors = currentValue ? currentValue.split(',').map(name => name.trim()) : [];

            if (element.id === 'bookViewAuthor') {
                // В карточке редактирования добавляем нового автора
                if (currentAuthorIndex >= authors.length) {
                    authors.push(item.name);
                } else {
                    authors[currentAuthorIndex] = item.name;
                }
            } else {
                // В модальном окне создания заменяем текущего автора
                authors[currentAuthorIndex] = item.name;
            }

            element.textContent = authors.join(', ');
            element.dataset.relatedId = authors.map(name => {
                const author = suggestions.find(a => a.name === name);
                return author ? author.id : '';
            }).filter(id => id).join(',');
            element.dataset.originalValue = authors.join(', ');
            element.dataset.originalId = element.dataset.relatedId;
            onSelect(item);
            wrapper.classList.remove('active');
            element.parentElement.querySelector('.autocomplete-buttons').classList.add('active');
        });
        wrapper.appendChild(div);
    });
}

function removeSuggestions(element) {
    const wrapper = element.parentElement.querySelector('.autocomplete-suggestions');
    wrapper.classList.remove('active');
    wrapper.innerHTML = '';
}

function showButtons(element) {
    const buttonWrapper = element.parentElement.querySelector('.autocomplete-buttons');
    buttonWrapper.classList.add('active');
}

function removeButtons(element) {
    const buttonWrapper = element.parentElement.querySelector('.autocomplete-buttons');
    buttonWrapper.classList.remove('active');
}

function setupAutocomplete(element) {
    const key = element.getAttribute('data-autocomplete');
    const url = autocompleteSources[key];

    if (!url) return;

    const fetchSuggestions = async (query, currentAuthorIndex = 0) => {
        console.log(query)
    }

    element.addEventListener('input', () => {
        console.log(element.nodeName);
        let tag = element.nodeName;
        let query = '';

        if (tag === 'INPUT'){
            element.dataset.originalValue = element.value.trim() || '';
            query = element.value.trim();
        } else {
            element.dataset.originalValue = element.textContent.trim() || '';
            query = element.textContent.trim();
        }

        element.dataset.originalId = element.dataset.relatedId || '';

        const authors = query ? query.split(',').map(name => name.trim()) : [];
        const lastAuthor = authors[authors.length - 1] || '';
        fetchSuggestions(lastAuthor, authors.length - 1);
    });

    element.addEventListener('focus', () => {
        console.log(element.nodeName);
        let tag = element.nodeName;
        let query = '';

        if (tag === 'INPUT'){
            element.dataset.originalValue = element.value.trim() || '';
            query = element.value.trim();
        } else {
            element.dataset.originalValue = element.textContent.trim() || '';
            query = element.textContent.trim();
            showButtons(element);
        }

        element.dataset.originalId = element.dataset.relatedId || '';

        const authors = query ? query.split(',').map(name => name.trim()) : [];
        const lastAuthor = authors[authors.length - 1] || '';
        fetchSuggestions(lastAuthor, authors.length - 1);
    });

    element.addEventListener('blur', () => {
        setTimeout(() => {
            removeButtons(element);
            removeSuggestions(element);
        }, 200);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('[data-autocomplete]');
    elements.forEach(setupAutocomplete);
});