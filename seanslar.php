<?php
$servername = "localhost";
$username = "root";
$password = "1357910Dd";
$dbname = "SinemaBiletSistemi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$filmler = $conn->query("SELECT * FROM Filmler ORDER BY FilmAdi");
$salonlar = $conn->query("SELECT * FROM Salonlar ORDER BY SalonAdi");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['seans_ekle'])) {
        $filmID = (int)$_POST['FilmID'];
        $salonID = (int)$_POST['SalonID'];
        $seansTarihi = $_POST['SeansTarihi'];
        $seansSaati = $_POST['SeansSaati'];


        if (!$filmID || !$salonID || !$seansTarihi || !$seansSaati) {
            $mesaj = "Lütfen tüm alanları doldurunuz.";
        } else {
            $sql = "INSERT INTO Seanslar (FilmID, SalonID, SeansTarihi, SeansSaati) VALUES ($filmID, $salonID, '$seansTarihi', '$seansSaati')";
            if ($conn->query($sql)) {
                $mesaj = "Seans başarıyla eklendi.";
            } else {
                $mesaj = "Hata: " . $conn->error;
            }
        }
    }


    if (isset($_POST['seans_guncelle'])) {
        $seansID = (int)$_POST['SeansID'];
        $filmID = (int)$_POST['FilmID'];
        $salonID = (int)$_POST['SalonID'];
        $seansTarihi = $_POST['SeansTarihi'];
        $seansSaati = $_POST['SeansSaati'];

        if (!$filmID || !$salonID || !$seansTarihi || !$seansSaati) {
            $mesaj = "Lütfen tüm alanları doldurunuz.";
        } else {
            $sql = "UPDATE Seanslar SET FilmID=$filmID, SalonID=$salonID, SeansTarihi='$seansTarihi', SeansSaati='$seansSaati' WHERE SeansID=$seansID";
            if ($conn->query($sql)) {
                $mesaj = "Seans başarıyla güncellendi.";
            } else {
                $mesaj = "Hata: " . $conn->error;
            }
        }
    }
}


if (isset($_GET['islem']) && $_GET['islem'] === 'sil' && isset($_GET['SeansID'])) {
    $seansID = (int)$_GET['SeansID'];
    $conn->query("DELETE FROM Seanslar WHERE SeansID=$seansID");
    $mesaj = "Seans silindi.";
}


$guncellenecekSeans = null;
if (isset($_GET['islem']) && $_GET['islem'] === 'guncelle' && isset($_GET['SeansID'])) {
    $seansID = (int)$_GET['SeansID'];
    $res = $conn->query("SELECT * FROM Seanslar WHERE SeansID=$seansID");
    if ($res && $res->num_rows > 0) {
        $guncellenecekSeans = $res->fetch_assoc();
    }
}


$seanslar = $conn->query("
    SELECT s.SeansID, f.FilmAdi, sa.SalonAdi, s.SeansTarihi, s.SeansSaati
    FROM Seanslar s
    JOIN Filmler f ON s.FilmID = f.FilmID
    JOIN Salonlar sa ON s.SalonID = sa.SalonID
    ORDER BY s.SeansTarihi DESC, s.SeansSaati DESC
");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Seans Yönetimi</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #666; padding: 8px; text-align: left; }
        form label { display: block; margin-top: 8px; }
        form input, form select { width: 100%; padding: 6px; box-sizing: border-box; }
        form button { margin-top: 10px; padding: 8px 15px; }
        a { text-decoration: none; color: blue; }
        a:hover { text-decoration: underline; }
        .mesaj { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .basarili { background-color: #c8e6c9; color: #256029; }
        .hata { background-color: #ffcdd2; color: #b71c1c; }
    </style>
</head>
<body>

<h1>Seans Yönetimi</h1>

<?php if (isset($mesaj)): ?>
    <div class="mesaj <?= strpos($mesaj, 'Hata') === false ? 'basarili' : 'hata' ?>">
        <?= htmlspecialchars($mesaj) ?>
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Seans ID</th>
            <th>Film</th>
            <th>Salon</th>
            <th>Seans Tarihi</th>
            <th>Seans Saati</th>
            <th>İşlemler</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($seanslar && $seanslar->num_rows > 0): ?>
            <?php while($row = $seanslar->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['SeansID'] ?></td>
                    <td><?= htmlspecialchars($row['FilmAdi']) ?></td>
                    <td><?= htmlspecialchars($row['SalonAdi']) ?></td>
                    <td><?= $row['SeansTarihi'] ?></td>
                    <td><?= $row['SeansSaati'] ?></td>
                    <td>
                        <a href="?islem=guncelle&SeansID=<?= $row['SeansID'] ?>">Güncelle</a> |
                        <a href="?islem=sil&SeansID=<?= $row['SeansID'] ?>" onclick="return confirm('Seansı silmek istediğinize emin misiniz?');">Sil</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Hiç seans bulunamadı.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<hr>

<?php if ($guncellenecekSeans): ?>
    <h2>Seans Güncelle</h2>
    <form method="post" action="">
        <input type="hidden" name="SeansID" value="<?= $guncellenecekSeans['SeansID'] ?>">

        <label>Film:</label>
        <select name="FilmID" required>
            <option value="">-- Film Seç --</option>
            <?php foreach ($filmler as $film): ?>
                <option value="<?= $film['FilmID'] ?>" <?= $film['FilmID'] == $guncellenecekSeans['FilmID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($film['FilmAdi']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Salon:</label>
        <select name="SalonID" required>
            <option value="">-- Salon Seç --</option>
            <?php foreach ($salonlar as $salon): ?>
                <option value="<?= $salon['SalonID'] ?>" <?= $salon['SalonID'] == $guncellenecekSeans['SalonID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($salon['SalonAdi']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Seans Tarihi:</label>
        <input type="date" name="SeansTarihi" value="<?= $guncellenecekSeans['SeansTarihi'] ?>" required>

        <label>Seans Saati:</label>
        <input type="time" name="SeansSaati" value="<?= $guncellenecekSeans['SeansSaati'] ?>" required>

        <button type="submit" name="seans_guncelle">Güncelle</button>
        <a href="seanslar.php">İptal</a>
    </form>
<?php else: ?>
    <h2>Yeni Seans Ekle</h2>
    <form method="post" action="">

        <label>Film:</label>
        <select name="FilmID" required>
            <option value="">-- Film Seç --</option>
            <?php foreach ($filmler as $film): ?>
                <option value="<?= $film['FilmID'] ?>">
                    <?= htmlspecialchars($film['FilmAdi']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Salon:</label>
        <select name="SalonID" required>
            <option value="">-- Salon Seç --</option>
            <?php foreach ($salonlar as $salon): ?>
                <option value="<?= $salon['SalonID'] ?>">
                    <?= htmlspecialchars($salon['SalonAdi']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Seans Tarihi:</label>
        <input type="date" name="SeansTarihi" required>

        <label>Seans Saati:</label>
        <input type="time" name="SeansSaati" required>

        <button type="submit" name="seans_ekle">Ekle</button>
    </form>
<?php endif; ?>

</body>
</html>