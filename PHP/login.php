<?php
session_start();

// Show errors while developing (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load DB config using absolute path from this file's directory
require_once __DIR__ . '/config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1');
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                // Redirect to project landing page (from PHP/ to ../)
                header('Location: ../index.php');
                exit;
            } else {
                $errors[] = 'Invalid username or password.';
            }
        } catch (Throwable $e) {
            error_log('Login error: ' . $e->getMessage());
            $errors[] = 'An unexpected error occurred.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
<h2>Login</h2>
<?php if (!empty($errors)): ?>
    <div style="color:#b00020;">
        <ul>
            <?php foreach ($errors as $msg): ?>
                <li><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" novalidate>
    <input type="text" name="username" placeholder="Username" value="<?php echo isset($username) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Sign In</button>
</form>
<a href="signup.php">Don't have an account? Sign up</a>
</body>
</html>
