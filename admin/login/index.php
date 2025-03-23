<?php
session_start();

// Nastavení časových limitů
define('INACTIVITY_LIMIT', 30 * 60); // 30 minut v sekundách
define('TEMPORARY_LOGIN_LIMIT', 6 * 60 * 60); // 6 hodin v sekundách
define('REMEMBER_ME_LIMIT', 30 * 24 * 60 * 60); // 30 dní v sekundách

// Funkce pro kontrolu nečinnosti a odhlášení
function checkInactivity()
{
    if (isset($_SESSION['last_activity'])) {
        $inactivityTime = time() - $_SESSION['last_activity'];
        if ($inactivityTime > INACTIVITY_LIMIT) {
            session_unset();
            session_destroy();
            setcookie('rememberMe', '', time() - 3600, "/"); // Smazání cookie
            header('Location: ./?message=inactive'); // Přesměrování na přihlášení s varováním
            exit;
        }
    }
    $_SESSION['last_activity'] = time(); // Aktualizace času poslední aktivity
}

// Kontrola trvání přihlášení
if (!empty($_SESSION['logged_in']) || (!empty($_COOKIE['rememberMe']) && $_COOKIE['rememberMe'] === 'true')) {
    if (!empty($_COOKIE['rememberMe']) && $_COOKIE['rememberMe'] === 'true') {
        $_SESSION['logged_in'] = true; // Obnovení session z cookies
    }
    checkInactivity(); // Zkontroluje nečinnost

    // Pokud uživatel nemá "Zůstat přihlášen" a uplynuly 2 hodiny, odhlásit
    if (empty($_COOKIE['rememberMe']) && isset($_SESSION['login_time'])) {
        $loggedTime = time() - $_SESSION['login_time'];
        if ($loggedTime > TEMPORARY_LOGIN_LIMIT) {
            session_unset();
            session_destroy();
            header('Location: ./'); // Přesměrování bez zprávy
            exit;
        }
    }

    header('Location: ../'); // Přesměrování do administrace
    exit;
}

// Pevně dané přihlašovací údaje
define('USERNAME', 'SAFEadmin');
define('PASSWORD', 'Admin25');

// Zpracování formuláře
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['rememberMe']); // Zkontrolovat zaškrtnutí

    if ($username === USERNAME && $password === PASSWORD) {
        $_SESSION['logged_in'] = true; // Uložení přihlášení
        $_SESSION['login_time'] = time(); // Uložení času přihlášení
        $_SESSION['last_activity'] = time(); // Uložení času aktivity

        if ($rememberMe) {
            setcookie('rememberMe', 'true', time() + REMEMBER_ME_LIMIT, "/"); // 7 dní
        }
        header('Location: ../'); // Přesměrování do administrace
        exit;
    } else {
        $error = 'Nesprávné uživatelské jméno nebo heslo.';
    }
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAFE | Admin Přihlášení</title>
    <style>
        * {
            font-family: "Poppins", sans-serif !important;
        }
    </style>
</head>
<?php include '../../skeleton/head.php' ?>
<body class="login-body">
    <main class="adminMain">
        <img src="../../images/SAFE-logo.png" alt="">
        <div class="loginForm">
            <p>Přihlášení do administrační části</p>
            <?php
            // Zobrazení varování na základě přesměrování
            if (!empty($_GET['message']) && $_GET['message'] === 'inactive') {
                echo '
                <div id="overBackground" class="invisible"></div>
                <div id="alertWarning" class="alert-warning noExist">
                    <center>
                        <img src="/images/zzz.png" alt="Spící obrázek" class="warning-img">
                    </center>
                    <div class="warning-content">
                        <p>Byli jste odhlášeni kvůli neaktivitě.</p>
                        <button class="closeButton" onclick="closeWarning()">OK</button>
                    </div>
                </div>';
            }
            ?>
            <script>
                function logoutUser() {
                    window.location.href = "./?message=inactive"; // Přesměrování s varováním
                }

                function closeWarning() {
                    const overlayBackground = document.getElementById('overBackground');
                    const alertWarning = document.getElementById('alertWarning');

                    if (overlayBackground && alertWarning) {
                        // Skryje overlay a varovné okno
                        overlayBackground.classList.add('invisible');
                        alertWarning.classList.add('noExist');

                        // Přesměrování zpět na "login/" bez "?message=inactive"
                        window.location.href = './';
                    } else {
                        console.error('Elementy #overBackground nebo #alertWarning nebyly nalezeny.');
                    }
                }

                // Kontrola existence a přidání event listeneru pouze tehdy, když element existuje
                document.addEventListener('DOMContentLoaded', () => {
                    const overlayBackground = document.getElementById('overBackground');
                    if (overlayBackground) {
                        overlayBackground.addEventListener('click', closeWarning);
                    }
                });
            </script>
            <form method="POST" action="" class="styleForm"  autocomplete>
                <?php if (!empty($error)): ?>
                    <center>
                        <div class="alert-email loginFail">
                        <?php echo "Špatné uživatelské jméno nebo heslo!" ?>
                        </div>
                    </center>
                <?php endif; ?>
                <div class="input-container">
                    <i class='bx bxs-envelope'></i>
                    <input type="text" name="username" placeholder="Uživatelské jméno" required>
                </div>
                <div class="input-container lastChild">
                    <i class='bx bxs-lock-alt'></i>
                    <input type="password" name="password" placeholder="Heslo" required>
                </div>
                <div class="input-container rememberMe">
                    <input type="checkbox" name="rememberMe">
                    <label>Zůstat přihlášen</label>
                </div>
                <button type="submit">Přihlásit se</button>
            </form>
        </div>
    </main>
</body>
</html>
