<?php
session_start();
session_unset(); // Odstraní všechny hodnoty ze session
session_destroy(); // Ukončí session
if (isset($_COOKIE['rememberMe'])) {
    setcookie('rememberMe', '', time() - 3600, "/"); // Nastavení do minulosti pro smazání
}
header('Location: ../login/'); // Přesměrování na přihlašovací stránku
exit;
?>
