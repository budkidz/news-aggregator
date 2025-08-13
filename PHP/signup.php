<?php
// Ensure errors are visible during development (you may disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errors = [];
$success = false;

// Always include config using an absolute path from this file's directory
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and trim inputs
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

    // Validation rules
    // Username: 3-20 chars, letters, numbers, underscore, hyphen, dot
    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = 'Username must be between 3 and 20 characters.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]+$/', $username)) {
        $errors[] = 'Username can contain letters, numbers, underscores, hyphens, and dots only.';
    }

    // Email: valid format
    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // Password: 8-10 chars, at least 1 upper, 1 lower, 1 digit, 1 special, no spaces
    if ($password === '') {
        $errors[] = 'Password is required.';
    } else {
        if (strlen($password) < 8 || strlen($password) > 10) {
            $errors[] = 'Password must be between 8 and 10 characters.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }
        if (!preg_match('/\d/', $password)) {
            $errors[] = 'Password must contain at least one digit.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }
        if (preg_match('/\s/', $password)) {
            $errors[] = 'Password cannot contain spaces.';
        }
    }

    if (!$errors) {
        try {
            // Check if username exists
            $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE username = :username OR email = :email LIMIT 1');
            $stmt->execute([':username' => $username, ':email' => $email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if (strcasecmp($existing['username'], $username) === 0) {
                    $errors[] = 'Username is already taken.';
                }
                if (strcasecmp($existing['email'], $email) === 0) {
                    $errors[] = 'An account with this email already exists.';
                }
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, created_at) VALUES (:username, :email, :password_hash, NOW())');
                $stmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':password_hash' => $password_hash,
                ]);
                $success = true;
                // Optionally redirect to login after success
                header('Location: login.php?signup=success');
                exit;
            }
        } catch (Throwable $e) {
            // Log actual error on server; show generic message to user
            error_log('Signup error: ' . $e->getMessage());
            $errors[] = 'An unexpected error occurred while creating your account.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Sign Up</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
<h2>Sign Up</h2>
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
    <input type="text" name="username" placeholder="Username" minlength="3" maxlength="20" pattern="[A-Za-z0-9_.-]+" value="<?php echo isset($username) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>
    <input type="email" name="email" placeholder="Email" value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>" required><br>
    <input type="password" name="password" placeholder="Password" minlength="8" maxlength="64" required><br>
    <small>Password must have 8-64 chars, at least 1 uppercase, 1 lowercase, 1 digit, and 1 special character.</small><br><br>
    <button type="submit">Sign Up</button>
    <p><a href="login.php">Already have an account? Sign in</a></p>
    <p><a href="/index.php">Back to home</a></p>
</form>
</body>
</html>
