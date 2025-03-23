<?php 
session_start();

// Zpracování cookies pro obnovení přihlášení
if (empty($_SESSION['logged_in']) && !empty($_COOKIE['rememberMe']) && $_COOKIE['rememberMe'] === 'true') {
    $_SESSION['logged_in'] = true; // Obnovení session z cookies
}

// Nastavení časového limitu pro odhlášení
define('INACTIVITY_LIMIT', 30 * 60); // 30 minut
define('TEMPORARY_LOGIN_LIMIT', 6 * 60 * 60); // 6 hodin v sekundách
define('REMEMBER_ME_LIMIT', 30 * 24 * 60 * 60); // 30 dní v sekundách

// Funkce pro kontrolu nečinnosti
function checkInactivity()
{
    if (isset($_SESSION['last_activity'])) {
        $inactivityTime = time() - $_SESSION['last_activity'];
        if ($inactivityTime > INACTIVITY_LIMIT) {
            session_unset();
            session_destroy();
            setcookie('rememberMe', '', time() - 3600, "/"); // Smazání cookie
            header('Location: /admin/login/?message=inactive'); // Přesměrování s důvodem odhlášení
            exit;
        }
    }
    $_SESSION['last_activity'] = time(); // Aktualizace času poslední aktivity
}

// Kontrola, zda je uživatel přihlášen
if (empty($_SESSION['logged_in'])) {
    header('Location: /admin/login/'); // Přesměrování na přihlášení, pokud není přihlášen
    exit;
}

// Kontrola, zda je zaškrtnuto "Zůstat přihlášen"
if (empty($_COOKIE['rememberMe'])) {
    // Kontrola nečinnosti
    checkInactivity();

    // Pokud uživatel nemá "Zůstat přihlášen" a uplynul časový limit, odhlásit
    if (isset($_SESSION['logged_in']) && (time() - $_SESSION['login_time'] > TEMPORARY_LOGIN_LIMIT)) {
        session_unset();
        session_destroy();
        setcookie('rememberMe', '', time() - 3600, "/"); // Smazání cookie
        header('Location: /admin/login/'); // Přesměrování bez zprávy
        exit;
    }
}

// Aktualizace času nečinnosti při každé stránce
$_SESSION['last_activity'] = time();
$_SESSION['login_time'] = $_SESSION['login_time'] ?? time(); // Nastavení počátečního času přihlášení
?>
