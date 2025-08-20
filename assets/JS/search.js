document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');

    // Debounce function to limit the rate at which a function gets called.
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }

    if (searchInput) {
        // Use 'input' event to trigger search on every keystroke, debounced.
        searchInput.addEventListener('input', debounce(function() {
            const query = searchInput.value.trim();
            // Access the globally available fetchNews function from main.js
            if (window.NewsApp && typeof window.NewsApp.fetchNews === 'function') {
                // Fetch news with the current search query.
                // The category filtering is handled by the active nav link.
                const activeCategory = document.querySelector('.nav-pills .nav-link.active').dataset.category || 'all';
                window.NewsApp.fetchNews(activeCategory, query);
            }
        }, 300)); // 300ms delay

        // Optional: Trigger search on Enter key press immediately
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Prevent form submission
                const query = searchInput.value.trim();
                if (window.NewsApp && typeof window.NewsApp.fetchNews === 'function') {
                    const activeCategory = document.querySelector('.nav-pills .nav-link.active').dataset.category || 'all';
                    window.NewsApp.fetchNews(activeCategory, query);
                }
            }
        });
    }
});
