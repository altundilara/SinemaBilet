<?php
require 'db.php';

// Veritabanı bağlantısı
try {
    $db = new PDO("mysql:host=localhost;dbname=SinemaBiletSistemi;charset=utf8", "root", "1357910Dd");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Bilet Silme
if (isset($_GET['sil'])) {
    $biletID = (int)$_GET['sil'];

    try {
        $db->beginTransaction();

        $odemeSorgu = $db->prepare("SELECT OdemeID FROM Biletler WHERE BiletID = ?");
        $odemeSorgu->execute([$biletID]);
        $odeme = $odemeSorgu->fetch(PDO::FETCH_ASSOC);

        if (!$odeme) throw new Exception("Bilet bulunamadı.");

        $db->prepare("DELETE FROM Biletler WHERE BiletID = ?")->execute([$biletID]);
        $db->prepare("DELETE FROM Odemeler WHERE OdemeID = ?")->execute([$odeme['OdemeID']]);

        $db->commit();
        $silMesaj = "<p style='color:green;'>Bilet başarıyla silindi.</p>";
    } catch (Exception $e) {
        $db->rollBack();
        $silMesaj = "<p style='color:red;'>Hata: " . $e->getMessage() . "</p>";
    }
}

// Bilet Güncelleme
if (isset($_POST['bilet_guncelle'])) {
    $biletID = (int)$_POST['bilet_id'];
    $odemeID = (int)$_POST['odeme_id'];
    $yeniKoltuk = $_POST['yeni_koltuk'];
    $yeniTutar = (float)$_POST['yeni_tutar'];
    $yeniTur = $_POST['yeni_tur'];

    try {
        $db->beginTransaction();

        $koltukSorgu = $db->prepare("SELECT KoltukID FROM Koltuklar WHERE KoltukNumarasi = ?");
        $koltukSorgu->execute([$yeniKoltuk]);
        $koltuk = $koltukSorgu->fetch(PDO::FETCH_ASSOC);

        if (!$koltuk) throw new Exception("Koltuk bulunamadı.");

        $db->prepare("UPDATE Biletler SET KoltukID = ? WHERE BiletID = ?")->execute([$koltuk['KoltukID'], $biletID]);
        $db->prepare("UPDATE Odemeler SET OdemeTutari = ?, OdemeTuru = ? WHERE OdemeID = ?")->execute([$yeniTutar, $yeniTur, $odemeID]);

        $db->commit();
        $guncelleMesaj = "<p style='color:green;'>Bilet başarıyla güncellendi.</p>";
    } catch (Exception $e) {
        $db->rollBack();
        $guncelleMesaj = "<p style='color:red;'>Hata: " . $e->getMessage() . "</p>";
    }
}

// Bilet Kaydetme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bilet_kaydet'])) {
    $musteriID = (int)$_POST['musteri'];
    $seansID = (int)$_POST['seans'];
    $koltukNo = $_POST['koltuk'];
    $odemeTutari = (float)$_POST['odeme_tutari'];
    $odemeTuru = $_POST['odeme_turu'];

    try {
        $db->beginTransaction();

        $odemeEkle = $db->prepare("INSERT INTO Odemeler (OdemeTarihi, OdemeTutari, OdemeTuru) VALUES (NOW(), ?, ?)");
        $odemeEkle->execute([$odemeTutari, $odemeTuru]);
        $odemeID = $db->lastInsertId();

        $salonSorgu = $db->prepare("SELECT SalonID FROM Seanslar WHERE SeansID = ?");
        $salonSorgu->execute([$seansID]);
        $salon = $salonSorgu->fetch(PDO::FETCH_ASSOC);
        if (!$salon) throw new Exception("Seans bulunamadı.");

        $koltukSorgu = $db->prepare("SELECT KoltukID FROM Koltuklar WHERE SalonID = ? AND KoltukNumarasi = ?");
        $koltukSorgu->execute([$salon['SalonID'], $koltukNo]);
        $koltuk = $koltukSorgu->fetch(PDO::FETCH_ASSOC);
        if (!$koltuk) throw new Exception("Koltuk bulunamadı.");

        $biletEkle = $db->prepare("INSERT INTO Biletler (MusteriID, SeansID, KoltukID, OdemeID) VALUES (?, ?, ?, ?)");
        $biletEkle->execute([$musteriID, $seansID, $koltuk['KoltukID'], $odemeID]);

        $db->commit();
        $ekleMesaj = "<p style='color:green;'>Bilet başarıyla kaydedildi!</p>";
    } catch (Exception $e) {
        $db->rollBack();
        $ekleMesaj = "<p style='color:red;'>Hata: " . $e->getMessage() . "</p>";
    }
}

// Listeleme
$biletler = $db->query("
    SELECT b.BiletID, m.Adi, m.Soyadi, s.SeansTarihi, s.SeansSaati, k.KoltukNumarasi, o.OdemeTutari, o.OdemeTuru 
    FROM Biletler b
    JOIN Musteriler m ON b.MusteriID = m.MusteriID
    JOIN Seanslar s ON b.SeansID = s.SeansID
    JOIN Koltuklar k ON b.KoltukID = k.KoltukID
    JOIN Odemeler o ON b.OdemeID = o.OdemeID
")->fetchAll(PDO::FETCH_ASSOC);

$musteriler = $db->query("SELECT MusteriID, CONCAT(Adi, ' ', Soyadi) AS AdSoyad FROM Musteriler")->fetchAll(PDO::FETCH_ASSOC);
$seanslar = $db->query("SELECT SeansID, SeansTarihi, SeansSaati FROM Seanslar ORDER BY SeansTarihi, SeansSaati")->fetchAll(PDO::FETCH_ASSOC);

$seciliSeans = $_POST['seans'] ?? null;
$bosKoltuklar = [];

if ($seciliSeans) {
    $seansBilgi = $db->prepare("SELECT SalonID FROM Seanslar WHERE SeansID = ?");
    $seansBilgi->execute([$seciliSeans]);
    $salon = $seansBilgi->fetch(PDO::FETCH_ASSOC);

    if ($salon) {
        $koltuklar = $db->prepare("SELECT KoltukNumarasi FROM Koltuklar WHERE SalonID = ?");
        $koltuklar->execute([$salon['SalonID']]);
        $tumKoltuklar = $koltuklar->fetchAll(PDO::FETCH_COLUMN);

        $doluKoltuklarSorgu = $db->prepare("SELECT k.KoltukNumarasi FROM Biletler b JOIN Koltuklar k ON b.KoltukID = k.KoltukID WHERE b.SeansID = ?");
        $doluKoltuklarSorgu->execute([$seciliSeans]);
        $doluKoltuklar = $doluKoltuklarSorgu->fetchAll(PDO::FETCH_COLUMN);

        $bosKoltuklar = array_diff($tumKoltuklar, $doluKoltuklar);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bilet Yönetimi</title>
</head>
<body>

<h2>Bilet Yönetimi</h2>

<?php
if (isset($silMesaj)) echo $silMesaj;
if (isset($guncelleMesaj)) echo $guncelleMesaj;
if (isset($ekleMesaj)) echo $ekleMesaj;
?>

<h3>Mevcut Biletler</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Müşteri</th>
        <th>Seans</th>
        <th>Koltuk</th>
        <th>Ödeme Tutarı</th>
        <th>Ödeme Türü</th>
        <th>İşlemler</th>
    </tr>
    <?php foreach ($biletler as $bilet): ?>
        <tr>
            <td><?= htmlspecialchars($bilet['Adi'] . ' ' . $bilet['Soyadi']) ?></td>
            <td><?= htmlspecialchars($bilet['SeansTarihi'] . ' ' . $bilet['SeansSaati']) ?></td>
            <td><?= htmlspecialchars($bilet['KoltukNumarasi']) ?></td>
            <td><?= htmlspecialchars($bilet['OdemeTutari']) ?> TL</td>
            <td><?= htmlspecialchars($bilet['OdemeTuru']) ?></td>
            <td>
                <a href="?guncelle=<?= $bilet['BiletID'] ?>">Güncelle</a> | 
                <a href="?sil=<?= $bilet['BiletID'] ?>" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php
// Güncelleme Formu
if (isset($_GET['guncelle'])) {
    $biletID = (int)$_GET['guncelle'];

    $bilet = $db->prepare("SELECT b.BiletID, b.OdemeID, o.OdemeTutari, o.OdemeTuru, k.KoltukNumarasi FROM Biletler b JOIN Odemeler o ON b.OdemeID = o.OdemeID JOIN Koltuklar k ON b.KoltukID = k.KoltukID WHERE b.BiletID = ?");
    $bilet->execute([$biletID]);
    $b = $bilet->fetch(PDO::FETCH_ASSOC);

    if ($b): ?>
        <h3>Bilet Güncelle</h3>
        <form method="post" action="">
            <input type="hidden" name="bilet_id" value="<?= $biletID ?>">
            <input type="hidden" name="odeme_id" value="<?= $b['OdemeID'] ?>">

            <label>Yeni Koltuk Numarası:</label>
            <input type="text" name="yeni_koltuk" value="<?= htmlspecialchars($b['KoltukNumarasi']) ?>" required><br><br>

            <label>Yeni Ödeme Tutarı:</label>
            <input type="number" step="0.01" name="yeni_tutar" value="<?= $b['OdemeTutari'] ?>" required><br><br>

            <label>Yeni Ödeme Türü:</label>
            <select name="yeni_tur" required>
                <option value="Nakit" <?= $b['OdemeTuru'] == 'Nakit' ? 'selected' : '' ?>>Nakit</option>
                <option value="Kredi Kartı" <?= $b['OdemeTuru'] == 'Kredi Kartı' ? 'selected' : '' ?>>Kredi Kartı</option>
                <option value="Banka Ödemesi" <?= $b['OdemeTuru'] == 'Banka Ödemesi' ? 'selected' : '' ?>>Banka Ödemesi</option>
            </select><br><br>

            <button type="submit" name="bilet_guncelle">Güncelle</button>
        </form>
    <?php endif;
}
?>

<h3>Yeni Bilet Ekle</h3>
<form method="post" action="">
    <label>Müşteri Seç:</label>
    <select name="musteri" required>
        <option value="">Seçiniz</option>
        <?php foreach ($musteriler as $m): ?>
            <option value="<?= $m['MusteriID'] ?>" <?= (isset($_POST['musteri']) && $_POST['musteri'] == $m['MusteriID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['AdSoyad']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Seans Seç:</label>
    <select name="seans" required onchange="this.form.submit()">
        <option value="">Seçiniz</option>
        <?php foreach ($seanslar as $s): ?>
            <option value="<?= $s['SeansID'] ?>" <?= ($seciliSeans == $s['SeansID']) ? 'selected' : '' ?>>
                <?= $s['SeansTarihi'] . ' ' . $s['SeansSaati'] ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <?php if ($seciliSeans): ?>
        <label>Boş Koltuk Seç:</label>
        <select name="koltuk" required>
            <option value="">Seçiniz</option>
            <?php foreach ($bosKoltuklar as $koltuk): ?>
                <option value="<?= htmlspecialchars($koltuk) ?>" <?= (isset($_POST['koltuk']) && $_POST['koltuk'] == $koltuk) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($koltuk) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Ödeme Tutarı (TL):</label>
        <input type="number" step="0.01" name="odeme_tutari" required><br><br>

        <label>Ödeme Türü:</label>
        <select name="odeme_turu" required>
            <option value="Nakit">Nakit</option>
            <option value="Kredi Kartı">Kredi Kartı</option>
            <option value="Banka Ödemesi">Banka Ödemesi</option>
        </select><br><br>

        <button type="submit" name="bilet_kaydet">Bileti Kaydet</button>
    <?php endif; ?>
</form>

</body>
</html>

