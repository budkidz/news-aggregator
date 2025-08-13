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


function timeAgo(dateString) {
    const date = new Date(dateString.replace(/-/g, '/')); // Fix for cross-browser date parsing
    if (isNaN(date.getTime())) return ''; // Invalid date fallback
    const now = new Date();
    const seconds = Math.round((now - date) / 1000);

    if (seconds < 60) return `${seconds}s ago`;
    const minutes = Math.round(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.round(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.round(hours / 24);
    return `${days}d ago`;
}

function fetchNews(category = "") {
    console.log("Fetching news for category:", category);
    let url = "PHP/news.php";
    if (category) url += `?category=${encodeURIComponent(category)}`;
    
    fetch(url)
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            console.log("Data received:", data);
            const container = document.getElementById('newsContainer');
            container.innerHTML = ""; // Clear previous news
            const articles = Array.isArray(data) ? data : (data.articles || []);
            
            if (!articles.length) {
                container.innerHTML = "<p>No news found.</p>";
                return;
            }

            articles.forEach((article, index) => {
                console.log("Processing article", index, article.title);
                const card = document.createElement("div");
                card.className = "col mb-4";
                card.innerHTML = `
                    <div class="card h-100">
                        <img src="${article.image_url || 'assets/Images/placeholder.jpg'}" class="card-img-top" alt="${article.title}" onerror="this.onerror=null;this.src='assets/Images/placeholder.jpg';">
                        <div class="card-body">
                            <h5 class="card-title">${article.title}</h5>
                            <p class="card-text">${article.description || ''}</p>
                        </div>
                        <div class="card-footer d-flex justify-content-between small text-muted">
                            <span class="category">Category: ${article.category || 'Unknown'}</span>
                            <span class="time-ago">${timeAgo(article.published_at)}</span>
                        </div>
                        <a href="${article.url}" target="_blank" class="stretched-link"></a>
                    </div>
                `;
                container.appendChild(card);
            });
            document.getElementById('updateTime').textContent = new Date().toLocaleTimeString();
        })
        .catch(error => {
            console.error("Error fetching news:", error);
            document.getElementById('newsContainer').innerHTML = "<p>Error loading news. Please try again later.</p>";
        });
}

// If you have a category select, add this:
const select = document.getElementById('category-select');
if (select) {
    select.addEventListener('change', () => { fetchNews(select.value); });
}

// Load news when page loads
document.addEventListener('DOMContentLoaded', () => {
    fetchNews();
});
    </script>
</body>
</html>