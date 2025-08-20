<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<header>
<div class="container-fluid p-3">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center gap-3">
        <a href="index.php" class="navbar-brand d-flex align-items-center logo-wrapper">
            <img src="assets/Images/logo.png" alt="NewsBite logo" class="img-fluid logo-img">
        </a>
        <div class="input-group search-bar-lg">
            <input type="text" id="searchInput" class="form-control" placeholder="Search news..." aria-label="Search">
        </div>
        <div class="d-flex gap-2 align-items-center">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <div class="text-center me-2">
                    <i class="bi bi-person-circle fs-3 d-block"></i>
                    <div class="fw-semibold small"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <a href="PHP/logout.php" class="btn btn-outline-danger">Sign Out</a>
            <?php else: ?>
                <a href="PHP/login.php" class="btn btn-outline-primary">Sign In</a>
                <a href="PHP/signup.php" class="btn btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
    <nav class="mt-3">
    <ul class="nav nav-pills justify-content-center flex-wrap">
        <li class="nav-item"><a class="nav-link active" href="#" data-category="all">All</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-category="zambian">Zambia</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-category="world">World</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-category="business">Business</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-category="technology">Technology</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-category="sports">Sports</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-category="health">Health</a></li>
    </ul>
    </nav>
</div>
</header>
