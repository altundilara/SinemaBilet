<?php
$servername = "localhost";
$username = "root";
$password = "1357910Dd";
$dbname = "SinemaBiletSistemi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['odeme_ekle'])) {
        $tarih = $conn->real_escape_string($_POST['OdemeTarihi']);
        $tutar = (float)$_POST['OdemeTutari'];
        $tur = $conn->real_escape_string($_POST['OdemeTuru']);

        $turler = ['Nakit', 'Kredi Kartı', 'Banka Ödemesi'];

        if (!$tarih || $tutar <= 0 || !in_array($tur, $turler)) {
            $mesaj = "Lütfen geçerli ödeme bilgilerini giriniz.";
        } else {
            $sql = "INSERT INTO Odemeler (OdemeTarihi, OdemeTutari, OdemeTuru) VALUES ('$tarih', $tutar, '$tur')";
            if ($conn->query($sql)) {
                $mesaj = "Ödeme eklendi.";
            } else {
                $mesaj = "Hata: " . $conn->error;
            }
        }
    }

    if (isset($_POST['odeme_guncelle'])) {
        $odemeID = (int)$_POST['OdemeID'];
        $tarih = $conn->real_escape_string($_POST['OdemeTarihi']);
        $tutar = (float)$_POST['OdemeTutari'];
        $tur = $conn->real_escape_string($_POST['OdemeTuru']);

        $turler = ['Nakit', 'Kredi Kartı', 'Banka Ödemesi'];

        if (!$tarih || $tutar <= 0 || !in_array($tur, $turler)) {
            $mesaj = "Lütfen geçerli ödeme bilgilerini giriniz.";
        } else {
            $sql = "UPDATE Odemeler SET OdemeTarihi='$tarih', OdemeTutari=$tutar, OdemeTuru='$tur' WHERE OdemeID=$odemeID";
            if ($conn->query($sql)) {
                $mesaj = "Ödeme güncellendi.";
            } else {
                $mesaj = "Hata: " . $conn->error;
            }
        }
    }
}

if (isset($_GET['islem']) && $_GET['islem'] === 'sil' && isset($_GET['OdemeID'])) {
    $odemeID = (int)$_GET['OdemeID'];
    $conn->query("DELETE FROM Odemeler WHERE OdemeID=$odemeID");
    $mesaj = "Ödeme silindi.";
}

$guncellenecekOdeme = null;
if (isset($_GET['islem']) && $_GET['islem'] === 'guncelle' && isset($_GET['OdemeID'])) {
    $odemeID = (int)$_GET['OdemeID'];
    $res = $conn->query("SELECT * FROM Odemeler WHERE OdemeID=$odemeID");
    if ($res && $res->num_rows > 0) {
        $guncellenecekOdeme = $res->fetch_assoc();
    }
}

$odemeler = $conn->query("SELECT * FROM Odemeler ORDER BY OdemeTarihi DESC");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ödeme Yönetimi</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #666; padding: 8px; }
        form label { display: block; margin-top: 8px; }
        form input, form select { width: 100%; padding: 6px; box-sizing: border-box; }
        form button { margin-top: 10px; padding: 8px 15px; }
        a { color: blue; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .mesaj { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .basarili { background-color: #c8e6c9; color: #256029; }
        .hata { background-color: #ffcdd2; color: #b71c1c; }
    </style>
</head>
<body>

<h1>Ödeme Yönetimi</h1>

<?php if (isset($mesaj)): ?>
    <div class="mesaj <?= strpos($mesaj, 'Hata') === false ? 'basarili' : 'hata' ?>">
        <?= htmlspecialchars($mesaj) ?>
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th><th>Tarih</th><th>Tutar</th><th>Tür</th><th>İşlemler</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($odemeler && $odemeler->num_rows > 0): ?>
            <?php while($row = $odemeler->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['OdemeID'] ?></td>
                    <td><?= htmlspecialchars($row['OdemeTarihi']) ?></td>
                    <td><?= number_format($row['OdemeTutari'], 2) ?> ₺</td>
                    <td><?= htmlspecialchars($row['OdemeTuru']) ?></td>
                    <td>
                        <a href="?islem=guncelle&OdemeID=<?= $row['OdemeID'] ?>">Güncelle</a> |
                        <a href="?islem=sil&OdemeID=<?= $row['OdemeID'] ?>" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Hiç ödeme bulunamadı.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<hr>

<?php if ($guncellenecekOdeme): ?>
    <h2>Ödeme Güncelle</h2>
    <form method="post" action="">
        <input type="hidden" name="OdemeID" value="<?= $guncellenecekOdeme['OdemeID'] ?>">
        <label>Ödeme Tarihi:</label>
        <input type="date" name="OdemeTarihi" required value="<?= htmlspecialchars($guncellenecekOdeme['OdemeTarihi']) ?>">
        <label>Ödeme Tutarı (₺):</label>
        <input type="number" name="OdemeTutari" step="0.01" min="0.01" required value="<?= $guncellenecekOdeme['OdemeTutari'] ?>">
        <label>Ödeme Türü:</label>
        <select name="OdemeTuru" required>
            <?php
            $turler = ['Nakit', 'Kredi Kartı', 'Banka Ödemesi'];
            foreach ($turler as $tur) {
                $sec = ($guncellenecekOdeme['OdemeTuru'] === $tur) ? 'selected' : '';
                echo "<option value='$tur' $sec>$tur</option>";
            }
            ?>
        </select>
        <button type="submit" name="odeme_guncelle">Güncelle</button>
        <a href="odemeler.php">İptal</a>
    </form>
<?php else: ?>
    <h2>Yeni Ödeme Ekle</h2>
    <form method="post" action="">
        <label>Ödeme Tarihi:</label>
        <input type="date" name="OdemeTarihi" required>
        <label>Ödeme Tutarı (₺):</label>
        <input type="number" name="OdemeTutari" step="0.01" min="0.01" required>
        <label>Ödeme Türü:</label>
        <select name="OdemeTuru" required>
            <option value="Nakit">Nakit</option>
            <option value="Kredi Kartı">Kredi Kartı</option>
            <option value="Banka Ödemesi">Banka Ödemesi</option>
        </select>
        <button type="submit" name="odeme_ekle">Ekle</button>
    </form>
<?php endif; ?>

</body>
</html>
