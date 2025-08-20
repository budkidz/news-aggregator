<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NewsBite - News Aggregator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/CSS/style.css">
    <link rel="stylesheet" href="assets/CSS/responsive.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Main Content -->
    <div class="main-container">
        <div class="main-grid">
            <!-- News Section -->
            <main id="main-content">
                <div class="news-header">
                    <h2>Latest News</h2>
                    <div class="update-time">
                        Last updated: <span id="updateTime">Loading...</span>
                    </div>
                </div>
                <section id="newsContainer" class="news-grid">
                    <!-- News cards will be dynamically added here -->
                </section>
            </main>
            
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="assets/js/search.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/animations.js"></script>
    <script>
// Human readable relative time
function timeAgo(isoString) {
    const date = new Date(isoString);
    if (isNaN(date.getTime())) return '';
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

function buildCard(article) {
    const div = document.createElement('div');
    div.className = 'col mb-4';
    const imgSrc = article.image_url || 'assets/Images/placeholder.jpg';
    div.innerHTML = `
    <div class="card h-100">
        <img src="${imgSrc}" class="card-img-top" alt="${article.title}" onerror="this.onerror=null;this.src='assets/Images/placeholder.jpg';">
        <div class="card-body">
            <h5 class="card-title">${article.title}</h5>
            <p class="card-text">${article.description || ''}</p>
        </div>
        <div class="card-footer d-flex justify-content-between small text-muted">
            <span class="source">${article.source || ''}</span>
            <span class="time-ago" title="${article.published_at}">${timeAgo(article.published_at)}</span>
        </div>
        <a href="${article.url}" target="_blank" class="stretched-link" rel="noopener"></a>
    </div>`;
    return div;
}

function setActiveCategory(cat) {
    document.querySelectorAll('nav .nav-link').forEach(a => {
        const match = !cat || cat === 'all' ? a.dataset.category === 'all' : a.dataset.category === cat;
        a.classList.toggle('active', match);
    });
}

function fetchNews(category = '') {
    const container = document.getElementById('newsContainer');
    container.innerHTML = '<p class="text-muted">Loading…</p>';
    let url = 'PHP/news.php';
    if (category && category !== 'all') url += `?category=${encodeURIComponent(category)}`;
    fetch(url)
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            const articles = Array.isArray(data) ? data : (data.articles || []);
            container.innerHTML = '';
            if (!articles.length) {
                container.innerHTML = '<p class="text-muted">No news found.</p>';
                return;
            }
            const frag = document.createDocumentFragment();
            articles.forEach(a => frag.appendChild(buildCard(a)));
            container.appendChild(frag);
            document.getElementById('updateTime').textContent = new Date().toLocaleTimeString();
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<p class="text-danger">Error loading news.</p>';
        });
}

// Persist chosen category across reloads
document.addEventListener('click', e => {
    const link = e.target.closest('a.nav-link[data-category]');
    if (link) {
        e.preventDefault();
        const cat = link.dataset.category || 'all';
        localStorage.setItem('nb_active_category', cat);
        setActiveCategory(cat);
        fetchNews(cat);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('nb_active_category') || 'all';
    setActiveCategory(saved);
    fetchNews(saved);
});
    </script>
</body>
</html>