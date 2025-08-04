// Global variables
let allNews = [];
let filteredNews = [];
let currentCategory = 'all';

// Sample news data
const sampleNews = [
function loadNews() {
    fetch('php/news.php')
    .then(response => response.json())
    .then(data => {
        allNews = data;
        filteredNews = allNews;
        renderNews();
        hideLoading();
    })
    .catch(error => {
        console.error('Failed to fetch news:', error);
    });
}
];

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    setupEventListeners();
});

function initializeApp() {
    loadNews();
    updateTimestamp();
    updateStats();
}

function loadNews(category = 'all') {
    showLoading();
    fetch(`php/news.php?category=${encodeURIComponent(category)}`)
        .then(response => response.json())
        .then(data => {
            allNews = data;
            filteredNews = allNews;
            renderNews();
            hideLoading();
            updateStats();
        })
        .catch(error => {
            console.error('Failed to fetch news:', error);
            hideLoading();
        });
}

function renderNews() {
    const container = document.getElementById('newsContainer');
    
    if (filteredNews.length === 0) {
        container.innerHTML = `
            <div class="no-results">
                <i class="bi bi-newspaper display-1 text-muted"></i>
                <h3 class="mt-3 text-muted">No articles found</h3>
                <p class="text-muted">Try adjusting your search or filter criteria.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = '';
    filteredNews.forEach((article, index) => {
        const articleElement = createNewsCard(article);
        articleElement.style.animationDelay = `${index * 0.1}s`;
        container.appendChild(articleElement);
    });
}

function createNewsCard(article) {
    const card = document.createElement('article');
    card.className = 'news-card fade-in';
    
    const timeAgo = getTimeAgo(new Date(article.publishedAt));
    
    card.innerHTML = `
        <img src="${article.image}" alt="${article.title}" loading="lazy" onerror="this.src='assets/images/placeholder.jpg'">
        <div class="card-body">
            <h5 class="card-title">${article.title}</h5>
            <p class="card-text">${article.description}</p>
        </div>
        <div class="card-footer">
            <div class="article-meta">
                <span class="source-badge">${article.source}</span>
                <small class="text-muted">${timeAgo}</small>
            </div>
            <a href="${article.url}" class="read-more" target="_blank" rel="noopener">
                Read more <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    `;
    
    return card;
}

function setupEventListeners() {
    // Header scroll effect
    window.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        if (window.scrollY > 10) {
            header.classList.add('header-scrolled');
        } else {
            header.classList.remove('header-scrolled');
        }
    });

    // Category navigation
    document.querySelectorAll('[data-category]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.dataset.category;
            setActiveCategory(this, category);
            filterByCategory(category);
        });
    });

    // Trending topics
    document.querySelectorAll('[data-trend]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const searchTerm = this.textContent.replace('#', '').trim();
            document.getElementById('searchInput').value = searchTerm;
            handleSearch();
        });
    });
}

function setActiveCategory(activeLink, category) {
    document.querySelectorAll('[data-category]').forEach(link => {
        link.classList.remove('active');
    });
    activeLink.classList.add('active');
    currentCategory = category;
}

function filterByCategory(category) {
    loadNews(category);
    currentCategory = category;
}

function hideLoading() {
    const loading = document.querySelector('.loading');
    if (loading) {
        loading.style.display = 'none';
    }
}

function updateTimestamp() {
    const now = new Date();
    const timeElement = document.getElementById('updateTime');
    if (timeElement) {
        timeElement.textContent = now.toLocaleTimeString();
    }
}

function updateStats() {
    const articlesCount = document.getElementById('articlesCount');
    const sourcesCount = document.getElementById('sourcesCount');
    
    if (articlesCount) {
        articlesCount.textContent = filteredNews.length;
    }
    
    if (sourcesCount) {
        const uniqueSources = [...new Set(allNews.map(article => article.source))];
        sourcesCount.textContent = uniqueSources.length;
    }
}

function getTimeAgo(date) {
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
    return `${Math.floor(seconds / 86400)}d ago`;
}

// Auto-refresh timestamp
setInterval(updateTimestamp, 60000);

// Export functions for use in other files
window.NewsApp = {
    filterByCategory,
    updateStats,
    renderNews,
    allNews: () => allNews,
    filteredNews: () => filteredNews
};