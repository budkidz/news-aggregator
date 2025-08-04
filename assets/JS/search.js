// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSearch();
            }
        });
    }
});

function handleSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    const query = searchInput.value.toLowerCase().trim();
    
    if (query === '') {
        window.NewsApp.filterByCategory(currentCategory);
        return;
    }

    const allNews = window.NewsApp.allNews();
    const filteredResults = allNews.filter(article => {
        const matchesCategory = currentCategory === 'all' || article.category === currentCategory;
        const matchesSearch = 
            article.title.toLowerCase().includes(query) ||
            article.description.toLowerCase().includes(query) ||
            article.source.toLowerCase().includes(query) ||
            article.category.toLowerCase().includes(query);
        
        return matchesCategory && matchesSearch;
    });
    
    // Update global filtered news
    filteredNews = filteredResults;
    window.NewsApp.renderNews();
    window.NewsApp.updateStats();
    
    // Add search highlight
    highlightSearchTerms(query);
}

function highlightSearchTerms(query) {
    if (!query) return;
    
    const articles = document.querySelectorAll('.news-card');
    articles.forEach(article => {
        const title = article.querySelector('.card-title');
        const description = article.querySelector('.card-text');
        
        if (title) {
            title.innerHTML = highlightText(title.textContent, query);
        }
        if (description) {
            description.innerHTML = highlightText(description.textContent, query);
        }
    });
}

function highlightText(text, query) {
    if (!query) return text;
    
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
}

function clearSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = '';
        handleSearch();
    }
}

// Debounce function to limit search frequency
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Advanced search filters
function setupAdvancedSearch() {
    const filterButtons = document.querySelectorAll('[data-filter]');
    filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.dataset.filter;
            applyAdvancedFilter(filter);
        });
    });
}

function applyAdvancedFilter(filter) {
    const allNews = window.NewsApp.allNews();
    let filtered;
    
    switch(filter) {
        case 'recent':
            filtered = allNews.filter(article => {
                const articleDate = new Date(article.publishedAt);
                const oneDayAgo = new Date(Date.now() - 24 * 60 * 60 * 1000);
                return articleDate > oneDayAgo;
            });
            break;
        case 'popular':
            // Simulate popularity based on article content length
            filtered = allNews.filter(article => article.description.length > 100);
            break;
        case 'trending':
            // Simulate trending based on recent articles
            filtered = allNews.slice(0, 3);
            break;
        default:
            filtered = allNews;
    }
    
    filteredNews = filtered;
    window.NewsApp.renderNews();
    window.NewsApp.updateStats();
}

// Initialize advanced search on page load
document.addEventListener('DOMContentLoaded', setupAdvancedSearch);