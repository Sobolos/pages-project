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