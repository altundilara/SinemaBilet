<?php
// Bağlantı kodu (aynı şekilde her dosyada olacak, istersen tek dosyada tutabiliriz)
$servername = "localhost";
$username = "root";
$password = "1357910Dd";
$dbname = "SinemaBiletSistemi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Salon ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['salon_ekle'])) {
        $salonAdi = $conn->real_escape_string($_POST['SalonAdi']);
        $kapasite = (int)$_POST['Kapasite'];

        $sql = "INSERT INTO Salonlar (SalonAdi, Kapasite) VALUES ('$salonAdi', $kapasite)";
        if ($conn->query($sql)) {
            $mesaj = "Salon başarıyla eklendi.";
        } else {
            $mesaj = "Hata: " . $conn->error;
        }
    }

    // Salon güncelleme
    if (isset($_POST['salon_guncelle'])) {
        $salonID = (int)$_POST['SalonID'];
        $salonAdi = $conn->real_escape_string($_POST['SalonAdi']);
        $kapasite = (int)$_POST['Kapasite'];

        $sql = "UPDATE Salonlar SET SalonAdi='$salonAdi', Kapasite=$kapasite WHERE SalonID=$salonID";
        if ($conn->query($sql)) {
            $mesaj = "Salon başarıyla güncellendi.";
        } else {
            $mesaj = "Hata: " . $conn->error;
        }
    }
}

// Salon silme
if (isset($_GET['islem']) && $_GET['islem'] === 'sil' && isset($_GET['SalonID'])) {
    $salonID = (int)$_GET['SalonID'];
    $conn->query("DELETE FROM Salonlar WHERE SalonID=$salonID");
    $mesaj = "Salon silindi.";
}

// Güncelleme için salon seçme
$guncellenecekSalon = null;
if (isset($_GET['islem']) && $_GET['islem'] === 'guncelle' && isset($_GET['SalonID'])) {
    $salonID = (int)$_GET['SalonID'];
    $res = $conn->query("SELECT * FROM Salonlar WHERE SalonID=$salonID");
    if ($res && $res->num_rows > 0) {
        $guncellenecekSalon = $res->fetch_assoc();
    }
}

// Salon listesi
$sql = "SELECT * FROM Salonlar ORDER BY SalonID DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Salon Yönetimi</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #666; padding: 8px; text-align: left; }
        form label { display: block; margin-top: 8px; }
        form input { width: 100%; padding: 6px; box-sizing: border-box; }
        form button { margin-top: 10px; padding: 8px 15px; }
        a { text-decoration: none; color: blue; }
        a:hover { text-decoration: underline; }
        .mesaj { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .basarili { background-color: #c8e6c9; color: #256029; }
        .hata { background-color: #ffcdd2; color: #b71c1c; }
    </style>
</head>
<body>

<h1>Salon Yönetimi</h1>

<?php if (isset($mesaj)): ?>
    <div class="mesaj <?= strpos($mesaj, 'Hata') === false ? 'basarili' : 'hata' ?>">
        <?= htmlspecialchars($mesaj) ?>
    </div>
<?php endif; ?>

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Salon Adı</th>
        <th>Kapasite</th>
        <th>İşlemler</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['SalonID'] ?></td>
                <td><?= htmlspecialchars($row['SalonAdi']) ?></td>
                <td><?= $row['Kapasite'] ?></td>
                <td>
                    <a href="?islem=guncelle&SalonID=<?= $row['SalonID'] ?>">Güncelle</a> |
                    <a href="?islem=sil&SalonID=<?= $row['SalonID'] ?>" onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="4">Hiç salon bulunamadı.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<hr>

<?php if ($guncellenecekSalon): ?>
    <h2>Salon Güncelle</h2>
    <form method="post" action="">
        <input type="hidden" name="SalonID" value="<?= $guncellenecekSalon['SalonID'] ?>">
        <label>Salon Adı:</label>
        <input type="text" name="SalonAdi" value="<?= htmlspecialchars($guncellenecekSalon['SalonAdi']) ?>" required>

        <label>Kapasite:</label>
        <input type="number" name="Kapasite" value="<?= $guncellenecekSalon['Kapasite'] ?>" min="1" required>

        <button type="submit" name="salon_guncelle">Güncelle</button>
        <a href="?">İptal</a>
    </form>

<?php else: ?>
    <h2>Yeni Salon Ekle</h2>
    <form method="post" action="">
        <label>Salon Adı:</label>
        <input type="text" name="SalonAdi" required>

        <label>Kapasite:</label>
        <input type="number" name="Kapasite" min="1" required>

        <button type="submit" name="salon_ekle">Ekle</button>
    </form>
<?php endif; ?>

</body>
</html>