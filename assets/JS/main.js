// Define a global namespace to hold app functions
window.NewsApp = {
    allNews: [],
    currentCategory: 'all',
    fetchNews: function(category = 'all', query = '') {
        this.currentCategory = category;
        showLoading();

        // Construct the API URL with both category and search query
        const apiUrl = `PHP/news.php?category=${encodeURIComponent(category)}&q=${encodeURIComponent(query)}`;

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.statusText}`);
                }
                return response.text(); // Get response as text first to check for errors
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'error') {
                        throw new Error(data.message || 'API returned an error');
                    }
                    // Ensure articles is always an array
                    this.allNews = Array.isArray(data.articles) ? data.articles : [];
                    this.renderNews();
                } catch (e) {
                    // If JSON parsing fails, the response might be a PHP error message
                    console.error("Failed to parse JSON:", e);
                    console.error("Server response:", text); // Log the raw text for debugging
                    throw new Error("Invalid data format received from server.");
                }
            })
            .catch(error => {
                console.error('Failed to fetch news:', error);
                const container = document.getElementById('newsContainer');
                if (container) {
                    container.innerHTML = `<div class="text-danger text-center p-4">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                        <h4 class="mt-3">Error Loading News</h4>
                        <p>${escapeHtml(error.message)}</p>
                    </div>`;
                }
            })
            .finally(() => {
                hideLoading();
                updateTimestamp();
            });
    },
    renderNews: function() {
        const container = document.getElementById('newsContainer');
        const query = document.getElementById('searchInput').value.trim();

        if (!container) return;

        if (this.allNews.length === 0) {
            container.innerHTML = `
                <div class="no-results text-center p-4">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <h3 class="mt-3 text-muted">No Articles Found</h3>
                    <p class="text-muted">
                        ${query ? `Your search for "${escapeHtml(query)}" did not return any results.` : 'There are no articles available for this category.'}
                    </p>
                </div>`;
            return;
        }

        container.innerHTML = '';
        this.allNews.forEach((article, index) => {
            const articleElement = createNewsCard(article, query);
            articleElement.style.animationDelay = `${index * 0.05}s`;
            container.appendChild(articleElement);
        });
    }
};

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    // Initial load of all news
    window.NewsApp.fetchNews('all');
    setupEventListeners();
    updateTimestamp();
});

function createNewsCard(article, query) {
    const card = document.createElement('article');
    card.className = 'news-card fade-in';

    const timeAgo = getTimeAgo(new Date(article.published_at || new Date()));
    
    // Function to highlight search terms in text
    const highlight = (text, term) => {
        if (!term) return escapeHtml(text);
        const regex = new RegExp(`(${escapeRegExp(term)})`, 'gi');
        return escapeHtml(text).replace(regex, '<mark>$1</mark>');
    };

    card.innerHTML = `
        <img src="${escapeHtml(article.image_url || 'assets/Images/placeholder.jpg')}" alt="${escapeHtml(article.title)}" loading="lazy" onerror="this.src='assets/Images/placeholder.jpg';">
        <div class="card-body">
            <h5 class="card-title">${highlight(article.title, query)}</h5>
            <p class="card-text">${highlight(article.description, query)}</p>
        </div>
        <div class="card-footer">
            <span class="source-badge">${escapeHtml(article.source_id)}</span>
            <small class="text-muted">${timeAgo}</small>
            <a href="${escapeHtml(article.url)}" class="read-more" target="_blank" rel="noopener">
                Read more <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    `;
    return card;
}

function setupEventListeners() {
    // Header scroll effect
    window.addEventListener('scroll', () => {
        const header = document.querySelector('header');
        header.classList.toggle('header-scrolled', window.scrollY > 10);
    });

    // Category navigation
    document.querySelectorAll('.nav-pills .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.dataset.category;
            
            // Remove active class from all, then add to the clicked one
            document.querySelectorAll('.nav-pills .nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Fetch news for this category, preserving any search query
            const query = document.getElementById('searchInput').value.trim();
            window.NewsApp.fetchNews(category, query);
        });
    });

    // Make trending topics clickable to filter news
    document.getElementById('trendingList').addEventListener('click', function(e) {
        const target = e.target.closest('a');
        if (target && target.dataset.category) {
            e.preventDefault();
            const category = target.dataset.category;
            
            // Set the corresponding main nav link to active
            document.querySelectorAll('.nav-pills .nav-link').forEach(link => {
                link.classList.toggle('active', link.dataset.category === category);
            });

            // Clear search and fetch news for the category
            document.getElementById('searchInput').value = '';
            window.NewsApp.fetchNews(category);
        }
    });
}

function showLoading() {
    const container = document.getElementById('newsContainer');
    if (container) {
        container.innerHTML = `
            <div class="loading text-center p-5 col-12">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Fetching latest news...</p>
            </div>`;
    }
}

function hideLoading() {
    // The loading indicator is now part of the container, so it's
    // automatically removed when renderNews is called. No action needed.
}

function updateTimestamp() {
    const timeElement = document.getElementById('lastUpdatedTime');
    if (timeElement) {
        timeElement.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
}

function getTimeAgo(date) {
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    if (seconds < 60) return `${seconds}s ago`;
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    return `${days}d ago`;
}

// Utility to escape HTML special characters
function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, match => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[match]));
}

// Utility to escape characters for use in a regular expression
function escapeRegExp(string) {
  return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Auto-refresh timestamp every minute
setInterval(updateTimestamp, 60000);