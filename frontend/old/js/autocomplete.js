const autocompleteSources = {
    author: `${API_BASE}/authors`,
    shelf: `${API_BASE}/shelves`,
    status: `${API_BASE}/statuses`
};

function showSuggestions(element, suggestions, onSelect, currentAuthorIndex) {
    const wrapper = element.parentElement.querySelector('.autocomplete-suggestions');
    wrapper.innerHTML = '';
    wrapper.classList.add('active');

    console.log('Showing suggestions:', suggestions);

    suggestions.slice(0, 10).forEach(item => {
        const div = document.createElement('div');
        div.classList.add('list-group-item', 'list-group-item-action', 'suggestion-item');
        div.style.padding = '8px';
        div.textContent = item.name;
        div.dataset.id = item.id;
        div.addEventListener('mousedown', () => {
            console.log('Selected suggestion:', item);
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

    let isComposing = false;

    const fetchSuggestions = async (query, currentAuthorIndex = 0) => {
        if (isComposing) {
            console.log('Skipping fetchSuggestions due to composition');
            return;
        }

        const isEmpty = query.length === 0;

        console.log(`Input query: ${query}, type: ${key}, isEmpty: ${isEmpty}`);

        const cache = key === 'author' ? authors : key === 'shelf' ? shelves : statuses;

        let suggestions = cache;
        if (query) {
            suggestions = cache.filter(item => {
                const itemName = item.name.toLowerCase();
                const queryLower = query.toLowerCase();
                return itemName.includes(queryLower);
            });
        }

        console.log(`Filtered suggestions:`, suggestions);

        if ((key === 'shelf' || key === 'status') && isEmpty) {
            console.log(`Showing suggestions for empty ${key}:`, suggestions.slice(0, 10));
            showSuggestions(element, suggestions, (item) => {
                element.textContent = item.name;
                element.dataset.relatedId = item.id;
                element.dataset.originalValue = item.name;
                element.dataset.originalId = item.id;
                updateField(element.id, item.name, item.id);
            });
            return;
        }

        if (suggestions.length > 0) {
            console.log(`Showing suggestions for ${key}:`, suggestions);
            showSuggestions(element, suggestions, (item) => {
                element.textContent = item.name;
                element.dataset.relatedId = item.id;
                element.dataset.originalValue = item.name;
                element.dataset.originalId = item.id;
                updateField(element.id, item.name, item.id);
            }, currentAuthorIndex);
            return;
        }

        if (query) {
            try {
                const encodedQuery = encodeURIComponent(query);
                console.log(`Fetching suggestions for ${key} with query: ${encodedQuery}`);
                const response = await fetchWithAuth(`${url}?q=${encodedQuery}`, {
                    headers: { 'Accept': 'application/json; charset=utf-8' }
                });
                const suggestions = await response;

                console.log(`Received suggestions:`, suggestions);

                if (suggestions.length > 0) {
                    showSuggestions(element, suggestions, (item) => {
                        const currentValue = element.textContent.trim();
                        let authors = currentValue ? currentValue.split(',').map(name => name.trim()) : [];
                        if (element.id === 'bookViewAuthor') {
                            if (currentAuthorIndex >= authors.length) {
                                authors.push(item.name);
                            } else {
                                authors[currentAuthorIndex] = item.name;
                            }
                        } else {
                            authors[currentAuthorIndex] = item.name;
                        }
                        element.textContent = authors.join(', ');
                        element.dataset.relatedId = authors.map(name => {
                            const author = suggestions.find(a => a.name === name);
                            return author ? author.id : '';
                        }).filter(id => id).join(',');
                        element.dataset.originalValue = authors.join(', ');
                        element.dataset.originalId = element.dataset.relatedId;
                        updateField(element.id, authors.join(', '));
                    }, currentAuthorIndex);
                }
            } catch (e) {
                console.error(`Autocomplete fetch error for ${key}:`, e);
                removeSuggestions(element);
                return;
            }
        }
    };

    const saveBtn = element.parentElement.querySelector('.btn-primary');
    const cancelBtn = element.parentElement.querySelector('.btn-secondary');

    saveBtn.addEventListener('click', async () => {
        const query = element.textContent.trim();
        if (!query) {
            removeButtons(element);
            removeSuggestions(element);
            return;
        }

        if (key !== 'author') {
            const cache = key === 'shelf' ? shelves : statuses;
            const existing = cache.find(item => item.name.toLowerCase() === query.toLowerCase());

            if (existing) {
                console.log('Found existing in cache:', existing);
                element.dataset.relatedId = existing.id;
                element.dataset.originalValue = query;
                element.dataset.originalId = existing.id;
                await updateField(element.id, query, existing.id);
                removeButtons(element);
                removeSuggestions(element);
                return;
            }

            try {
                let endpoint, payload;
                if (key === 'shelf') {
                    endpoint = '/shelf';
                    payload = { name: query };
                } else if (key === 'status') {
                    endpoint = '/status';
                    payload = { name: query, color: '#FFFAAA' };
                }

                console.log(`Sending POST to ${endpoint} with payload:`, payload);
                const res = await fetchWithAuth(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json; charset=utf-8' },
                    body: JSON.stringify(payload)
                });
                const data = await res;
                console.log(`Received POST response:`, data);
                element.dataset.relatedId = data.id;
                element.dataset.originalValue = query;
                element.dataset.originalId = data.id;

                cache.push({ id: data.id, name: query });
                await updateField(element.id, query, data.id);
                removeButtons(element);
                removeSuggestions(element);
            } catch (e) {
                console.error(`Error creating ${key}:`, e);
                alert(`Не удалось создать ${key}`);
            }
        } else {
            const authorNames = query.split(',').map(name => name.trim()).filter(name => name);
            const authorIds = [];
            for (const name of authorNames) {
                let author = authors.find(a => a.name.toLowerCase() === name.toLowerCase());
                if (!author) {
                    try {
                        const res = await fetchWithAuth('/author', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json; charset=utf-8' },
                            body: JSON.stringify({ name })
                        });
                        author = { id: res.id, name };
                        authors.push(author);
                    } catch (e) {
                        console.error(`Error creating author ${name}:`, e);
                        alert(`Не удалось создать автора ${name}`);
                        continue;
                    }
                }
                authorIds.push(author.id);
            }
            element.dataset.relatedId = authorIds.join(',');
            element.dataset.originalValue = query;
            element.dataset.originalId = authorIds.join(',');
            await updateField(element.id, query);
            removeButtons(element);
            removeSuggestions(element);
        }
    });

    cancelBtn.addEventListener('click', () => {
        element.textContent = element.dataset.originalValue || '';
        element.dataset.relatedId = element.dataset.originalId || '';
        removeButtons(element);
        removeSuggestions(element);
    });

    element.addEventListener('input', () => {
        if (!isComposing) {
            const query = element.textContent.trim();
            const authors = query ? query.split(',').map(name => name.trim()) : [];
            const lastAuthor = authors[authors.length - 1] || '';
            fetchSuggestions(lastAuthor, authors.length - 1);
        }
    });

    element.addEventListener('compositionstart', () => {
        isComposing = true;
        console.log('Composition started');
    });

    element.addEventListener('compositionend', () => {
        isComposing = false;
        console.log('Composition ended');
        const query = element.textContent.trim();
        const authors = query ? query.split(',').map(name => name.trim()) : [];
        const lastAuthor = authors[authors.length - 1] || '';
        fetchSuggestions(lastAuthor, authors.length - 1);
    });

    element.addEventListener('focus', () => {
        element.dataset.originalId = element.dataset.relatedId || '';
        element.dataset.originalValue = element.textContent.trim() || '';
        showButtons(element);
        const query = element.textContent.trim();
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