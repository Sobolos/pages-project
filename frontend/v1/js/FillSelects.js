const authorSelectDisplay = document.getElementById('authorSelectDisplay');
const authorSearchInput = document.getElementById('authorSearchInput');
const authorOptionsList = document.getElementById('authorOptionsList');
const selectedAuthorsInput = document.getElementById('selectedAuthors');
const shelfSelectDisplay = document.getElementById('shelfSelectDisplay');
const shelfSearchInput = document.getElementById('shelfSearchInput');
const shelfOptionsList = document.getElementById('shelfOptionsList');
const selectedShelfInput = document.getElementById('selectedShelf');
const statusSelectDisplay = document.getElementById('statusSelectDisplay');
const statusSearchInput = document.getElementById('statusSearchInput');
const statusOptionsList = document.getElementById('statusOptionsList');
const selectedStatusInput = document.getElementById('selectedStatus');

// Данные из глобальных переменных
let authorOptionsData = [...authors]; // Копируем, чтобы не мутировать оригинал
let selectedAuthors = [];
let newAuthors = []; // Массив для новых авторов {tempId: -N, name: 'Имя'}
let tempIdCounter = -1; // Счетчик для временных ID
let shelfOptionsData = [...shelves];
let selectedShelf = null;
let statusOptionsData = [...statuses];
let selectedStatus = null;