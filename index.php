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
function fetchNews(category = "") {
    console.log("Fetching news for category:", category);
    let url = "PHP/news.php";
    if (category) url += `?category=${encodeURIComponent(category)}`;
    
    fetch(url)
        .then(res => {
            console.log("Response status:", res.status);
            return res.json();
        })
        .then(data => {
            console.log("Data received:", data);
            const container = document.getElementById('newsContainer');
            container.innerHTML = ""; // Clear previous news
            const articles = data.articles || data; // Support both formats
            console.log("Number of articles:", articles.length);
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
                        <img src="${article.image_url || 'https://via.placeholder.com/300x150'}" class="card-img-top" alt="${article.title}">
                        <div class="card-body">
                            <h5 class="card-title">${article.title}</h5>
                            <p class="card-text">${article.description}</p>
                        </div>
                        <div class="card-footer d-flex justify-content-between small text-muted">
                            <span class="source">Source: ${article.source || 'Unknown'}</span>
                            <a href="${article.url}" target="_blank" class="text-decoration-none read-more">Read more →</a>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
            document.getElementById('updateTime').textContent = new Date().toLocaleTimeString();
        })
        .catch(error => {
            console.error("Error fetching news:", error);
            document.getElementById('newsContainer').innerHTML = "<p>Error loading news.</p>";
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