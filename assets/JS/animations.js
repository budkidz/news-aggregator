// Animation effects using Anime.js

document.addEventListener('DOMContentLoaded', function() {
    initializeAnimations();
});

function initializeAnimations() {
    // Animate logo on load
    anime({
        targets: '.logo',
        translateY: [-20, 0],
        opacity: [0, 1],
        duration: 800,
        easing: 'easeOutElastic(1, .6)'
    });

    // Animate navigation items
    anime({
        targets: '.nav-link',
        translateY: [-10, 0],
        opacity: [0, 1],
        duration: 600,
        delay: anime.stagger(100),
        easing: 'easeOutQuart'
    });

    // Animate search bar
    anime({
        targets: '.search-container',
        scale: [0.9, 1],
        opacity: [0, 1],
        duration: 500,
        delay: 200,
        easing: 'easeOutBack'
    });
}

// Animate news cards when they appear
function animateNewsCards() {
    const cards = document.querySelectorAll('.news-card:not(.animated)');
    
    anime({
        targets: cards,
        translateY: [30, 0],
        opacity: [0, 1],
        duration: 600,
        delay: anime.stagger(100),
        easing: 'easeOutQuart',
        complete: function() {
            cards.forEach(card => card.classList.add('animated'));
        }
    });
}

// Animate sidebar elements
function animateSidebar() {
    anime({
        targets: '.trending-item',
        translateX: [-20, 0],
        opacity: [0, 1],
        duration: 500,
        delay: anime.stagger(50),
        easing: 'easeOutQuart'
    });
}

// Animate loading spinner
function animateSpinner() {
    anime({
        targets: '.spinner',
        rotate: '360deg',
        duration: 1000,
        loop: true,
        easing: 'linear'
    });
}

// Animate page transitions
function animatePageTransition() {
    anime({
        targets: '#main-content',
        opacity: [0, 1],
        duration: 800,
        easing: 'easeInOutQuad',
        complete: function() {
            // Trigger news card animations after transition
            animateNewsCards();
            animateSidebar();
            animateSpinner();   
        }
    });
}