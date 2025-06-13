<?php
// Bağlantı bilgileri
$servername = "localhost";
$username = "root";
$password = "1357910Dd";
$dbname = "SinemaBiletSistemi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Film ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['film_ekle'])) {
        $filmAdi = $conn->real_escape_string($_POST['FilmAdi']);
        $tur = $conn->real_escape_string($_POST['Tur']);
        $sure = (int)$_POST['Sure'];
        $aciklama = $conn->real_escape_string($_POST['Aciklama']);

        $sql = "INSERT INTO Filmler (FilmAdi, Tur, Sure, Aciklama) VALUES ('$filmAdi', '$tur', $sure, '$aciklama')";
        if ($conn->query($sql)) {
            $mesaj = "Film başarıyla eklendi.";
        } else {
            $mesaj = "Hata: " . $conn->error;
        }
    }

    // Film güncelleme
    if (isset($_POST['film_guncelle'])) {
        $filmID = (int)$_POST['FilmID'];
        $filmAdi = $conn->real_escape_string($_POST['FilmAdi']);
        $tur = $conn->real_escape_string($_POST['Tur']);
        $sure = (int)$_POST['Sure'];
        $aciklama = $conn->real_escape_string($_POST['Aciklama']);

        $sql = "UPDATE Filmler SET FilmAdi='$filmAdi', Tur='$tur', Sure=$sure, Aciklama='$aciklama' WHERE FilmID=$filmID";
        if ($conn->query($sql)) {
            $mesaj = "Film başarıyla güncellendi.";
        } else {
            $mesaj = "Hata: " . $conn->error;
        }
    }
}

// Film silme işlemi
if (isset($_GET['islem']) && $_GET['islem'] === 'sil' && isset($_GET['FilmID'])) {
    $filmID = (int)$_GET['FilmID'];
    $conn->query("DELETE FROM Filmler WHERE FilmID=$filmID");
    $mesaj = "Film silindi.";
}

// Güncelleme için film seçme
$guncellenecekFilm = null;
if (isset($_GET['islem']) && $_GET['islem'] === 'guncelle' && isset($_GET['FilmID'])) {
    $filmID = (int)$_GET['FilmID'];
    $res = $conn->query("SELECT * FROM Filmler WHERE FilmID=$filmID");
    if ($res && $res->num_rows > 0) {
        $guncellenecekFilm = $res->fetch_assoc();
    }
}

// Film listesi çekme
$sql = "SELECT * FROM Filmler ORDER BY FilmID DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Film Yönetimi</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #666; padding: 8px; text-align: left; }
        form label { display: block; margin-top: 8px; }
        form input, form textarea { width: 100%; padding: 6px; box-sizing: border-box; }
        form button { margin-top: 10px; padding: 8px 15px; }
        a { text-decoration: none; color: blue; }
        a:hover { text-decoration: underline; }
        .mesaj { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .basarili { background-color: #c8e6c9; color: #256029; }
        .hata { background-color: #ffcdd2; color: #b71c1c; }
    </style>
</head>
<body>

<h1>Film Yönetimi</h1>

<?php if (isset($mesaj)): ?>
    <div class="mesaj <?= strpos($mesaj, 'Hata') === false ? 'basarili' : 'hata' ?>">
        <?= htmlspecialchars($mesaj) ?>
    </div>
<?php endif; ?>

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Film Adı</th>
        <th>Tür</th>
        <th>Süre (dk)</th>
        <th>Açıklama</th>
        <th>İşlemler</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['FilmID'] ?></td>
                <td><?= htmlspecialchars($row['FilmAdi']) ?></td>
                <td><?= htmlspecialchars($row['Tur']) ?></td>
                <td><?= $row['Sure'] ?></td>
                <td><?= nl2br(htmlspecialchars($row['Aciklama'])) ?></td>
                <td>
                    <a href="?islem=guncelle&FilmID=<?= $row['FilmID'] ?>">Güncelle</a> |
                    <a href="?islem=sil&FilmID=<?= $row['FilmID'] ?>" onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">Hiç film bulunamadı.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<hr>

<?php if ($guncellenecekFilm): ?>
    <h2>Film Güncelle</h2>
    <form method="post" action="">
        <input type="hidden" name="FilmID" value="<?= $guncellenecekFilm['FilmID'] ?>">
        <label>Film Adı:</label>
        <input type="text" name="FilmAdi" value="<?= htmlspecialchars($guncellenecekFilm['FilmAdi']) ?>" required>

        <label>Tür:</label>
        <input type="text" name="Tur" value="<?= htmlspecialchars($guncellenecekFilm['Tur']) ?>">

        <label>Süre (dakika):</label>
        <input type="number" name="Sure" value="<?= $guncellenecekFilm['Sure'] ?>" min="0">

        <label>Açıklama:</label>
        <textarea name="Aciklama"><?= htmlspecialchars($guncellenecekFilm['Aciklama']) ?></textarea>

        <button type="submit" name="film_guncelle">Güncelle</button>
        <a href="?">İptal</a>
    </form>

<?php else: ?>
    <h2>Yeni Film Ekle</h2>
    <form method="post" action="">
        <label>Film Adı:</label>
        <input type="text" name="FilmAdi" required>

        <label>Tür:</label>
        <input type="text" name="Tur">

        <label>Süre (dakika):</label>
        <input type="number" name="Sure" min="0">

        <label>Açıklama:</label>
        <textarea name="Aciklama"></textarea>

        <button type="submit" name="film_ekle">Ekle</button>
    </form>
<?php endif; ?>

</body>
</html>