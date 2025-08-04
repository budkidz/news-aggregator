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
                    <article id="news-template" style="display:none;">
                        <div class="col">
                            <div class="card h-100">
                                <img src="https://via.placeholder.com/300x150" class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title">Sample Headline</h5>
                                    <p class="card-text">Short description of the news article...</p>
                                </div>
                                <div class="card-footer d-flex justify-content-between small text-muted">
                                    <span class="source">Source: BBC</span>
                                    <a href="#" class="text-decoration-none read-more">Read more →</a>
                                </div>
                            </div>
                        </div>
                    </article>
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
    <script src="assets/js/main.js"></script>
    <script src="assets/js/search.js"></script>
    <script src="assets/js/animations.js"></script>
    <script>
function fetchNews(category = "") {
    let url = "PHP/news.php";
    if (category) url += `?category=${encodeURIComponent(category)}`;
    fetch(url)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('newsContainer'); // Correct ID
            container.innerHTML = ""; // Clear previous news
            const template = document.getElementById('news-template');
            const articles = data.articles || data; // Support both formats
            if (!articles.length) {
                container.innerHTML = "<p>No news found.</p>";
                return;
            }
            articles.forEach(article => {
                const clone = template.cloneNode(true);
                clone.style.display = "";
                clone.removeAttribute("id");
                clone.querySelector(".card-img-top").src = article.image_url || "https://via.placeholder.com/300x150";
                clone.querySelector(".card-title").textContent = article.title;
                clone.querySelector(".card-text").textContent = article.description;
                clone.querySelector(".source").textContent = "Source: " + (article.source || "Unknown");
                clone.querySelector(".read-more").href = article.url;
                container.appendChild(clone);
            });
        })
        .catch(() => {
            document.getElementById('newsContainer').innerHTML = "<p>Error loading news.</p>";
        });
}

// If you have a category select, add this:
document.getElementById('category-select').addEventListener('change', function() {
    fetchNews(this.value);
});
fetchNews();
</script>
</body>
</html>