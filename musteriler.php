<?php
// DB Bağlantısı
$servername = "localhost";
$username = "root";
$password = "1357910Dd";
$dbname = "SinemaBiletSistemi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Bağlantı hatası: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['musteri_ekle'])) {
        $adi = $conn->real_escape_string(trim($_POST['Adi']));
        $soyadi = $conn->real_escape_string(trim($_POST['Soyadi']));
        $telefon = $conn->real_escape_string(trim($_POST['Telefon']));
        $mail = $conn->real_escape_string(trim($_POST['Mail']));
        $sifre = password_hash($_POST['Sifre'], PASSWORD_DEFAULT);

        if (!$adi || !$soyadi || !$mail || !$_POST['Sifre']) {
            $mesaj = "Lütfen zorunlu alanları doldurun.";
        } else {
            $sql = "INSERT INTO Musteriler (Adi, Soyadi, Telefon, Mail, Sifre) VALUES ('$adi', '$soyadi', '$telefon', '$mail', '$sifre')";
            if ($conn->query($sql)) {
                $mesaj = "Müşteri eklendi.";
            } else {
                $mesaj = "Hata: " . $conn->error;
            }
        }
    }
    if (isset($_POST['musteri_guncelle'])) {
        $musteriID = (int)$_POST['MusteriID'];
        $adi = $conn->real_escape_string(trim($_POST['Adi']));
        $soyadi = $conn->real_escape_string(trim($_POST['Soyadi']));
        $telefon = $conn->real_escape_string(trim($_POST['Telefon']));
        $mail = $conn->real_escape_string(trim($_POST['Mail']));
        $sifreGuncelle = !empty($_POST['Sifre']);

        if (!$adi || !$soyadi || !$mail) {
            $mesaj = "Lütfen zorunlu alanları doldurun.";
        } else {
            if ($sifreGuncelle) {
                $sifre = password_hash($_POST['Sifre'], PASSWORD_DEFAULT);
                $sql = "UPDATE Musteriler SET Adi='$adi', Soyadi='$soyadi', Telefon='$telefon', Mail='$mail', Sifre='$sifre' WHERE MusteriID=$musteriID";
            } else {
                $sql = "UPDATE Musteriler SET Adi='$adi', Soyadi='$soyadi', Telefon='$telefon', Mail='$mail' WHERE MusteriID=$musteriID";
            }
            if ($conn->query($sql)) {
                $mesaj = "Müşteri güncellendi.";
            } else {
                $mesaj = "Hata: " . $conn->error;
            }
        }
    }
}

if (isset($_GET['islem']) && $_GET['islem'] === 'sil' && isset($_GET['MusteriID'])) {
    $musteriID = (int)$_GET['MusteriID'];
    $conn->query("DELETE FROM Musteriler WHERE MusteriID=$musteriID");
    $mesaj = "Müşteri silindi.";
}

$guncellenecekMusteri = null;
if (isset($_GET['islem']) && $_GET['islem'] === 'guncelle' && isset($_GET['MusteriID'])) {
    $musteriID = (int)$_GET['MusteriID'];
    $res = $conn->query("SELECT * FROM Musteriler WHERE MusteriID=$musteriID");
    if ($res && $res->num_rows > 0) {
        $guncellenecekMusteri = $res->fetch_assoc();
    }
}

$musteriler = $conn->query("SELECT MusteriID, Adi, Soyadi, Telefon, Mail FROM Musteriler ORDER BY Adi");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Müşteri Yönetimi</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #666; padding: 8px; }
        form label { display: block; margin-top: 8px; }
        form input { width: 100%; padding: 6px; box-sizing: border-box; }
        form button { margin-top: 10px; padding: 8px 15px; }
        a { color: blue; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .mesaj { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .basarili { background-color: #c8e6c9; color: #256029; }
        .hata { background-color: #ffcdd2; color: #b71c1c; }
    </style>
</head>
<body>

<h1>Müşteri Yönetimi</h1>

<?php if (isset($mesaj)): ?>
    <div class="mesaj <?= strpos($mesaj, 'Hata') === false ? 'basarili' : 'hata' ?>">
        <?= htmlspecialchars($mesaj) ?>
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th><th>Adı</th><th>Soyadı</th><th>Telefon</th><th>Mail</th><th>İşlemler</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($musteriler && $musteriler->num_rows > 0): ?>
            <?php while($row = $musteriler->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['MusteriID'] ?></td>
                    <td><?= htmlspecialchars($row['Adi']) ?></td>
                    <td><?= htmlspecialchars($row['Soyadi']) ?></td>
                    <td><?= htmlspecialchars($row['Telefon']) ?></td>
                    <td><?= htmlspecialchars($row['Mail']) ?></td>
                    <td>
                        <a href="?islem=guncelle&MusteriID=<?= $row['MusteriID'] ?>">Güncelle</a> |
                        <a href="?islem=sil&MusteriID=<?= $row['MusteriID'] ?>" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Hiç müşteri bulunamadı.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<hr>

<?php if ($guncellenecekMusteri): ?>
    <h2>Müşteri Güncelle</h2>
    <form method="post" action="">
        <input type="hidden" name="MusteriID" value="<?= $guncellenecekMusteri['MusteriID'] ?>">
        <label>Adı:</label>
        <input type="text" name="Adi" required value="<?= htmlspecialchars($guncellenecekMusteri['Adi']) ?>">
        <label>Soyadı:</label>
        <input type="text" name="Soyadi" required value="<?= htmlspecialchars($guncellenecekMusteri['Soyadi']) ?>">
        <label>Telefon:</label>
        <input type="text" name="Telefon" value="<?= htmlspecialchars($guncellenecekMusteri['Telefon']) ?>">
        <label>Mail:</label>
        <input type="email" name="Mail" required value="<?= htmlspecialchars($guncellenecekMusteri['Mail']) ?>">
        <label>Şifre (değiştirmek için yazınız):</label>
        <input type="password" name="Sifre" placeholder="Boş bırakılırsa değiştirilmez">
        <button type="submit" name="musteri_guncelle">Güncelle</button>
        <a href="musteriler.php">İptal</a>
    </form>
<?php else: ?>
    <h2>Yeni Müşteri Ekle</h2>
    <form method="post" action="">
        <label>Adı:</label>
        <input type="text" name="Adi" required>
        <label>Soyadı:</label>
        <input type="text" name="Soyadi" required>
        <label>Telefon:</label>
        <input type="text" name="Telefon">
        <label>Mail:</label>
        <input type="email" name="Mail" required>
        <label>Şifre:</label>
        <input type="password" name="Sifre" required>
        <button type="submit" name="musteri_ekle">Ekle</button>
    </form>
<?php endif; ?>

</body>
</html>
