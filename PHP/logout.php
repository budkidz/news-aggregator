<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    $path = (isset($params["path"]) && is_string($params["path"])) ? $params["path"] : '/';
    $domain = (isset($params["domain"]) && is_string($params["domain"])) ? $params["domain"] : '';
    $secure = (isset($params["secure"]) && ($params["secure"] === true || $params["secure"] === 1)) ? true : false;
    $httponly = (isset($params["httponly"]) && ($params["httponly"] === true || $params["httponly"] === 1)) ? true : false;
    $sess_name = (string) ini_get('session.name');
    if (!is_string($sess_name) || $sess_name === '') {
        $sess_name = 'PHPSESSID';
    }
    setcookie($sess_name, '', time() - 42000, $path, $domain, $secure, $httponly);
}

// Destroy the session
session_destroy();

// Redirect back to landing page
header('Location: ../index.php');
exit;
