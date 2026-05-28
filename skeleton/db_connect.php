<?php
require_once __DIR__ . '/paths.php';

$dbfile = __DIR__ . "/../admin/sql/mywebsite.sqlite";

try {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        http_response_code(500);
        die("Chyba připojení: PHP nemá dostupný SQLite PDO driver (pdo_sqlite).");
    }
    $conn = new PDO("sqlite:" . $dbfile);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    die("Chyba připojení: " . $e->getMessage());
}
?>
