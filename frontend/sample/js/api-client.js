class ApiClient {
    constructor() {
        this.baseUrl = 'http://localhost/api';
        this.token = localStorage.getItem('access_token') || null;
    }

    async request(endpoint, method = 'GET', data = null) {
        const headers = {
            'Content-Type': 'application/json',
        };
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        const response = await fetch(`${this.baseUrl}${endpoint}`, {
            method,
            headers,
            body: data ? JSON.stringify(data) : null,
        });

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.error || `HTTP error! status: ${response.status}`);
        }

        return result;
    }

    async register(name, email, password) {
        const response = await this.request('/auth/register', 'POST', { name, email, password });
        this.token = response.data.access_token;
        localStorage.setItem('access_token', this.token);
        return response;
    }

    async login(email, password) {
        const response = await this.request('/auth/login', 'POST', { email, password });
        this.token = response.data.access_token;
        localStorage.setItem('access_token', this.token);
        return response;
    }

    async getBooks(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/books${query ? '?' + query : ''}`);
    }

    async createBook(bookData) {
        return this.request('/books', 'POST', bookData);
    }

    async updateBook(id, bookData) {
        return this.request(`/books/${id}`, 'PUT', bookData);
    }

    async deleteBook(id) {
        return this.request(`/books/${id}`, 'DELETE');
    }

    async updateBookProgress(bookId, progressData) {
        return this.request(`/books/${bookId}/progress`, 'PUT', JSON.stringify(progressData));
    }

    async getStatuses() {
        return this.request('/statuses');
    }

    async createStatus(statusData) {
        return this.request('/statuses', 'POST', statusData);
    }

    async updateStatus(id, statusData) {
        return this.request(`/statuses/${id}`, 'PUT', statusData);
    }

    async deleteStatus(id) {
        return this.request(`/statuses/${id}`, 'DELETE');
    }

    async getShelves() {
        return this.request('/shelves');
    }

    async createShelf(shelfData) {
        return this.request('/shelves', 'POST', shelfData);
    }

    async updateShelf(id, shelfData) {
        return this.request(`/shelves/${id}`, 'PUT', shelfData);
    }

    async deleteShelf(id) {
        return this.request(`/shelves/${id}`, 'DELETE');
    }

    async getAuthors() {
        return this.request('/authors');
    }

    async createAuthor(authorData) {
        return this.request('/authors', 'POST', authorData);
    }

    async updateAuthor(id, authorData) {
        return this.request(`/authors/${id}`, 'PUT', authorData);
    }

    async deleteAuthor(id) {
        return this.request(`/authors/${id}`, 'DELETE');
    }

    async getNotes(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/notes?${query}`);
    }

    async createNote(noteData) {
        return this.request('/notes', 'POST', noteData);
    }

    async updateNote(id, noteData) {
        return this.request(`/notes/${id}`, 'PUT', noteData);
    }

    async deleteNote(id) {
        return this.request(`/notes/${id}`, 'DELETE');
    }

    async getQuotes(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/quotes?${query}`);
    }

    async createQuote(quoteData) {
        return this.request('/quotes', 'POST', quoteData);
    }

    async updateQuote(id, quoteData) {
        return this.request(`/quotes/${id}`, 'PUT', quoteData);
    }

    async deleteQuote(id) {
        return this.request(`/quotes/${id}`, 'DELETE');
    }

    async getPublicProfile(userId, filters = {}) {
        return this.request(`/profile/${userId}`, 'POST', filters);
    }

    async getSettings() {
        return this.request('/settings', 'GET');
    }

    async updateSettings(settingsData) {
        return this.request('/settings', 'PUT', settingsData);
    }

    async getReadingProgress(bookId) {
        return this.request(`/reading-progress?book_id=${bookId}`, 'GET');
    }

    async updateReadingProgress(progressData) {
        return this.request('/reading-progress', 'POST', progressData);
    }

    async patchReadingProgress(progressData) {
        return this.request('/reading-progress', 'PATCH', progressData);
    }

    async syncPaperProgress(bookId, paperSyncOffset, deviceType) {
        return this.request(`/books/${bookId}/sync-paper`, 'POST', { paperSyncOffset, deviceType });
    }

    async suggestStatusChange(bookId) {
        return this.request(`/books/${bookId}/status`, 'PATCH');
    }
}

const apiClient = new ApiClient();