<?php
try {
    $db = new PDO("mysql:host=localhost;dbname=SinemaBiletSistemi;charset=utf8", "root", "1357910Dd");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}
?>