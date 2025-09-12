<?php 
session_start();

if (empty($_SESSION['logged_in']) && !empty($_COOKIE['rememberMe']) && $_COOKIE['rememberMe'] === 'true') {
    $_SESSION['logged_in'] = true;
}

define('INACTIVITY_LIMIT', 30 * 60);
define('TEMPORARY_LOGIN_LIMIT', 6 * 60 * 60);
define('REMEMBER_ME_LIMIT', 30 * 24 * 60 * 60);

function checkInactivity()
{
    if (isset($_SESSION['last_activity'])) {
        $inactivityTime = time() - $_SESSION['last_activity'];
        if ($inactivityTime > INACTIVITY_LIMIT) {
            session_unset();
            session_destroy();
            setcookie('rememberMe', '', time() - 3600, "/");
            header('Location: /admin/login/?message=inactive');
            exit;
        }
    }
    $_SESSION['last_activity'] = time();
}

if (empty($_SESSION['logged_in'])) {
    header('Location: /admin/login/');
    exit;
}

if (empty($_COOKIE['rememberMe'])) {
    checkInactivity();

    if (isset($_SESSION['logged_in']) && (time() - $_SESSION['login_time'] > TEMPORARY_LOGIN_LIMIT)) {
        session_unset();
        session_destroy();
        setcookie('rememberMe', '', time() - 3600, "/");
        header('Location: /admin/login/');
        exit;
    }
}

$_SESSION['last_activity'] = time();
$_SESSION['login_time'] = $_SESSION['login_time'] ?? time();
?>
