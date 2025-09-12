<?php
$dbfile = __DIR__ . "/../admin/sql/mywebsite.sqlite";

try {
    $conn = new PDO("sqlite:" . $dbfile);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Chyba připojení: " . $e->getMessage());
}
?>
