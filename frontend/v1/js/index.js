if (!checkLogin()) {
    window.location.href = '/login.html';
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
            statuses,
            authors,
            books,
            shelves
        )
        loadBoard(statuses, authors, books);
        loadShelvesDOM(shelves, books);
    } catch (error) {
        console.error(error);
        alert('Не удалось загрузить доску');
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