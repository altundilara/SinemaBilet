<?php
$servername = "localhost";
$username = "root";
$password = "1357910Dd";
$dbname = "SinemaBiletSistemi";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Öncelikle tüm salonları çekiyoruz (seçim için)
$salonlarResult = $conn->query("SELECT * FROM Salonlar ORDER BY SalonAdi");

// Salon seçimi için
$seciliSalonID = isset($_GET['SalonID']) ? (int)$_GET['SalonID'] : 0;

// Koltuk ekleme
if (isset($_POST['koltuk_ekle'])) {
    $salonID = (int)$_POST['SalonID'];
    $koltukNumarasi = $_POST['KoltukNumarasi'];
    $koltukNumarasiInt = (int)$koltukNumarasi; // string'i int'e çevir

    // Önce salonun kapasitesini alalım
    $kapasiteSorgu = $conn->query("SELECT Kapasite FROM Salonlar WHERE SalonID = $salonID");
    if ($kapasiteSorgu && $kapasiteSorgu->num_rows > 0) {
        $kapasite = (int)$kapasiteSorgu->fetch_assoc()['Kapasite'];
    } else {
        $mesaj = "Salon bilgisi bulunamadı.";
        $kapasite = 0;
    }

    // Toplam mevcut koltuk sayısını alalım
    $mevcutKoltukSayisi = 0;
    $sayimSorgu = $conn->query("SELECT COUNT(*) as sayi FROM Koltuklar WHERE SalonID = $salonID");
    if ($sayimSorgu && $sayimSorgu->num_rows > 0) {
        $mevcutKoltukSayisi = (int)$sayimSorgu->fetch_assoc()['sayi'];
    }

    // Kontroller

    if ($kapasite == 0) {
        // Salon bulunamadıysa ekleme yapma zaten mesaj verildi
    }
    else if ($koltukNumarasiInt > $kapasite) {
        $mesaj = "Koltuk numarası salon kapasitesinden yüksek olamaz (Maksimum koltuk numarası: $kapasite).";
    }
    else if ($mevcutKoltukSayisi >= $kapasite) {
        $mesaj = "Salon kapasitesi doldu, daha fazla koltuk eklenemez.";
    }
    else {
        // Aynı koltuk numarası salon içinde tekrar etmemeli
        $kontrol = $conn->query("SELECT * FROM Koltuklar WHERE SalonID=$salonID AND KoltukNumarasi='$koltukNumarasi'");
        if ($kontrol && $kontrol->num_rows > 0) {
            $mesaj = "Bu koltuk numarası zaten seçili salonda mevcut!";
        } else {
            $sql = "INSERT INTO Koltuklar (SalonID, KoltukNumarasi) VALUES ($salonID, '$koltukNumarasi')";
            if ($conn->query($sql)) {
                $mesaj = "Koltuk başarıyla eklendi.";
            } else {
                $mesaj = "Hata: " . $conn->error;
            }
        }
    }

    $seciliSalonID = $salonID; // seçili salonu koru

    
    // ... diğer kodlar değişmeden kalır ...
}


// Koltuk silme
if (isset($_GET['islem']) && $_GET['islem'] === 'sil' && isset($_GET['KoltukID'])) {
    $koltukID = (int)$_GET['KoltukID'];

    // Silmeden önce salon ID'yi alalım ki sonrasında aynı sayfaya dönelim
    $salonSorgu = $conn->query("SELECT SalonID FROM Koltuklar WHERE KoltukID=$koltukID");
    if ($salonSorgu && $salonSorgu->num_rows > 0) {
        $row = $salonSorgu->fetch_assoc();
        $seciliSalonID = $row['SalonID'];
    }

    $conn->query("DELETE FROM Koltuklar WHERE KoltukID=$koltukID");
    $mesaj = "Koltuk silindi.";
}

// Güncelleme için koltuk seçme
$guncellenecekKoltuk = null;
if (isset($_GET['islem']) && $_GET['islem'] === 'guncelle' && isset($_GET['KoltukID'])) {
    $koltukID = (int)$_GET['KoltukID'];
    $res = $conn->query("SELECT * FROM Koltuklar WHERE KoltukID=$koltukID");
    if ($res && $res->num_rows > 0) {
        $guncellenecekKoltuk = $res->fetch_assoc();
        $seciliSalonID = $guncellenecekKoltuk['SalonID'];
    }
}

// Seçili salonun koltuklarını listeleme
$koltuklarResult = null;
if ($seciliSalonID > 0) {
    $koltuklarResult = $conn->query("SELECT * FROM Koltuklar WHERE SalonID=$seciliSalonID ORDER BY KoltukNumarasi");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Koltuk Yönetimi</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
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

<h1>Koltuk Yönetimi</h1>

<?php if (isset($mesaj)): ?>
    <div class="mesaj <?= strpos($mesaj, 'Hata') === false ? 'basarili' : 'hata' ?>">
        <?= htmlspecialchars($mesaj) ?>
    </div>
<?php endif; ?>

<form method="get" action="">
    <label for="SalonID">Salon Seçiniz:</label>
    <select name="SalonID" id="SalonID" onchange="this.form.submit()">
        <option value="0">-- Salon Seç --</option>
        <?php if ($salonlarResult && $salonlarResult->num_rows > 0): ?>
            <?php while($row = $salonlarResult->fetch_assoc()): ?>
                <option value="<?= $row['SalonID'] ?>" <?= $seciliSalonID == $row['SalonID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['SalonAdi']) ?>
                </option>
            <?php endwhile; ?>
        <?php endif; ?>
    </select>
</form>

<?php if ($seciliSalonID == 0): ?>
    <p>Lütfen önce bir salon seçiniz.</p>
<?php else: ?>

    <h2>Seçili Salon: 
        <?php
        $salonAdiSorgu = $conn->query("SELECT SalonAdi FROM Salonlar WHERE SalonID=$seciliSalonID");
        if ($salonAdiSorgu && $salonAdiSorgu->num_rows > 0) {
            $salonAdiRow = $salonAdiSorgu->fetch_assoc();
            echo htmlspecialchars($salonAdiRow['SalonAdi']);
        }
        ?>
    </h2>

    <table>
        <thead>
        <tr>
            <th>Koltuk ID</th>
            <th>Koltuk Numarası</th>
            <th>İşlemler</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($koltuklarResult && $koltuklarResult->num_rows > 0): ?>
            <?php while($row = $koltuklarResult->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['KoltukID'] ?></td>
                    <td><?= htmlspecialchars($row['KoltukNumarasi']) ?></td>
                    <td>
                        <a href="?islem=guncelle&KoltukID=<?= $row['KoltukID'] ?>">Güncelle</a> |
                        <a href="?islem=sil&KoltukID=<?= $row['KoltukID'] ?>" onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">Bu salonda hiç koltuk yok.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <hr>

    <?php if ($guncellenecekKoltuk): ?>
        <h2>Koltuk Güncelle</h2>
        <form method="post" action="">
            <input type="hidden" name="KoltukID" value="<?= $guncellenecekKoltuk['KoltukID'] ?>">
            <input type="hidden" name="SalonID" value="<?= $seciliSalonID ?>">

            <label>Koltuk Numarası:</label>
            <input type="text" name="KoltukNumarasi" value="<?= htmlspecialchars($guncellenecekKoltuk['KoltukNumarasi']) ?>" required>

            <button type="submit" name="koltuk_guncelle">Güncelle</button>
            <a href="?SalonID=<?= $seciliSalonID ?>">İptal</a>
        </form>
    <?php else: ?>
        <h2>Yeni Koltuk Ekle</h2>
        <form method="post" action="">
            <input type="hidden" name="SalonID" value="<?= $seciliSalonID ?>">

            <label>Koltuk Numarası:</label>
            <input type="text" name="KoltukNumarasi" required>

            <button type="submit" name="koltuk_ekle">Ekle</button>
        </form>
    <?php endif; ?>

<?php endif; ?>

</body>
</html>