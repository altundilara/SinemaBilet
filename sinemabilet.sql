CREATE DATABASE SinemaBiletSistemi;
USE SinemaBiletSistemi;
CREATE TABLE Musteriler (
    MusteriID INT AUTO_INCREMENT PRIMARY KEY,
    Adi VARCHAR(50) NOT NULL,
    Soyadi VARCHAR(50) NOT NULL,
    Telefon VARCHAR(15),
    Mail VARCHAR(100) UNIQUE NOT NULL,
    Sifre VARCHAR(255) NOT NULL
);
CREATE TABLE Filmler (
    FilmID INT AUTO_INCREMENT PRIMARY KEY,
    FilmAdi VARCHAR(100) NOT NULL,
    Tur VARCHAR(50),
    Sure INT, -- dakika olarak
    Aciklama TEXT
);
CREATE TABLE Salonlar (
    SalonID INT AUTO_INCREMENT PRIMARY KEY,
    SalonAdi VARCHAR(50) NOT NULL,
    Kapasite INT NOT NULL
);
CREATE TABLE Koltuklar (
    KoltukID INT AUTO_INCREMENT PRIMARY KEY,
    SalonID INT NOT NULL,
    KoltukNumarasi VARCHAR(10) NOT NULL,
    FOREIGN KEY (SalonID) REFERENCES Salonlar(SalonID),
    UNIQUE(SalonID, KoltukNumarasi)
);
CREATE TABLE Seanslar (
    SeansID INT AUTO_INCREMENT PRIMARY KEY,
    FilmID INT NOT NULL,
    SalonID INT NOT NULL,
    SeansTarihi DATE NOT NULL,
    SeansSaati TIME NOT NULL,
    FOREIGN KEY (FilmID) REFERENCES Filmler(FilmID),
    FOREIGN KEY (SalonID) REFERENCES Salonlar(SalonID)
);
CREATE TABLE Odemeler (
    OdemeID INT AUTO_INCREMENT PRIMARY KEY,
    OdemeTarihi DATETIME NOT NULL,
    OdemeTutari DECIMAL(10,2) NOT NULL,
    OdemeTuru ENUM('Nakit', 'Kredi Kartı', 'Banka Ödemesi') NOT NULL
);
CREATE TABLE Biletler (
    BiletID INT AUTO_INCREMENT PRIMARY KEY,
    MusteriID INT NOT NULL,
    SeansID INT NOT NULL,
    KoltukID INT NOT NULL,
    OdemeID INT NOT NULL,
    SatisTarihi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (MusteriID) REFERENCES Musteriler(MusteriID),
    FOREIGN KEY (SeansID) REFERENCES Seanslar(SeansID),
    FOREIGN KEY (KoltukID) REFERENCES Koltuklar(KoltukID),
    FOREIGN KEY (OdemeID) REFERENCES Odemeler(OdemeID),UNIQUE(SeansID, KoltukID) -- Aynı seans için aynı koltuk birden fazla satılamaz
);


DELIMITER $$
CREATE PROCEDURE KullaniciEkle (
    IN p_adi VARCHAR(50),
    IN p_soyadi VARCHAR(50),
    IN p_telefon VARCHAR(20),
    IN p_mail VARCHAR(100),
    IN p_sifre VARCHAR(100)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kullanıcı ekleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    IF EXISTS (SELECT 1 FROM musteriler WHERE mail = p_mail) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Bu mail zaten kayıtlı!';
    ELSE
        INSERT INTO musteriler (adi, soyadi, telefon, mail, sifre)
        VALUES (p_adi, p_soyadi, p_telefon, p_mail, p_sifre);
    END IF;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE KullaniciGuncelle(
    IN p_musteri_id INT,
    IN p_adi VARCHAR(50),
    IN p_soyadi VARCHAR(50),
    IN p_telefon VARCHAR(20),
    IN p_mail VARCHAR(100),
    IN p_sifre VARCHAR(100))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kullanıcı güncelleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    UPDATE musteriler
    SET adi = p_adi,
        soyadi = p_soyadi,
        telefon = p_telefon,
        mail = p_mail,
        sifre = p_sifre
    WHERE musteri_id = p_musteri_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE KullaniciSil (
    IN p_musteri_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Kullanıcı silme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    DELETE FROM musteriler
    WHERE musteri_id = p_musteri_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE FilmEkle (
    IN p_film_adi VARCHAR(100),
    IN p_tur VARCHAR(50),
    IN p_sure INT,
    IN p_aciklama TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Film ekleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    INSERT INTO filmler (film_adi, tur, sure, aciklama)
    VALUES (p_film_adi, p_tur, p_sure, p_aciklama);
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE FilmGuncelle (
    IN p_film_id INT,
    IN p_film_adi VARCHAR(100),
    IN p_tur VARCHAR(50),
    IN p_sure INT,
    IN p_aciklama TEXT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Film güncelleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    UPDATE filmler
    SET film_adi = p_film_adi,
        tur = p_tur,
        sure = p_sure,
        aciklama = p_aciklama
    WHERE film_id = p_film_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE FilmSil (
    IN p_film_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Film silme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    DELETE FROM filmler
    WHERE film_id = p_film_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE SalonEkle (
    IN p_salon_adi VARCHAR(100),
    IN p_kapasite INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Salon ekleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    INSERT INTO salonlar (salon_adi, kapasite)
    VALUES (p_salon_adi, p_kapasite);
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE SalonGuncelle (
    IN p_salon_id INT,
    IN p_salon_adi VARCHAR(100),
    IN p_kapasite INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Salon güncelleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    UPDATE salonlar
    SET salon_adi = p_salon_adi,
        kapasite = p_kapasite
    WHERE salon_id = p_salon_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE SalonSil (
    IN p_salon_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Salon silme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    DELETE FROM salonlar
    WHERE salon_id = p_salon_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE KoltukEkle (
    IN p_salon_id INT,
    IN p_koltuk_numarasi INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Koltuk ekleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    INSERT INTO koltuklar (salon_id, koltuk_numarasi)
    VALUES (p_salon_id, p_koltuk_numarasi);
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE KoltukGuncelle (
    IN p_koltuk_id INT,
    IN p_salon_id INT,
    IN p_koltuk_numarasi INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Koltuk güncelleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    UPDATE koltuklar
    SET salon_id = p_salon_id,
        koltuk_numarasi = p_koltuk_numarasi
    WHERE koltuk_id = p_koltuk_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE KoltukSil (
    IN p_koltuk_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Koltuk silme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    DELETE FROM koltuklar
    WHERE koltuk_id = p_koltuk_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE SeansEkle (
    IN p_film_id INT,
    IN p_salon_id INT,
    IN p_seans_tarihi DATE,
    IN p_seans_saati TIME
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Seans ekleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    INSERT INTO seanslar (film_id, salon_id, seans_tarihi, seans_saati)
    VALUES (p_film_id, p_salon_id, p_seans_tarihi, p_seans_saati);
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE SeansGuncelle (
    IN p_seans_id INT,
    IN p_film_id INT,
    IN p_salon_id INT,
    IN p_seans_tarihi DATE,
    IN p_seans_saati TIME
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Seans güncelleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    UPDATE seanslar
    SET film_id = p_film_id,
        salon_id = p_salon_id,
        seans_tarihi = p_seans_tarihi,
        seans_saati = p_seans_saati
    WHERE seans_id = p_seans_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE SeansSil (
    IN p_seans_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Seans silme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    DELETE FROM seanslar
    WHERE seans_id = p_seans_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE OdemeEkle (
    IN p_odeme_tarihi DATE,
    IN p_odeme_tutari DECIMAL(10,2),
    IN p_odeme_turu VARCHAR(50),
    OUT p_odeme_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ödeme ekleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    INSERT INTO odemeler (odeme_tarihi, odeme_tutari, odeme_turu)
    VALUES (p_odeme_tarihi, p_odeme_tutari, p_odeme_turu);
    SET p_odeme_id = LAST_INSERT_ID();
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE OdemeGuncelle (
    IN p_odeme_id INT,
    IN p_odeme_tarihi DATE,
    IN p_odeme_tutari DECIMAL(10,2),
    IN p_odeme_turu VARCHAR(50)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ödeme güncelleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    UPDATE odemeler
    SET odeme_tarihi = p_odeme_tarihi,
        odeme_tutari = p_odeme_tutari,
        odeme_turu = p_odeme_turu
    WHERE odeme_id = p_odeme_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE OdemeSil (
    IN p_odeme_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ödeme silme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    DELETE FROM odemeler
    WHERE odeme_id = p_odeme_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE BiletEkle (
    IN p_musteri_id INT,
    IN p_seans_id INT,
    IN p_koltuk_id INT,
    IN p_odeme_id INT,
    IN p_satis_tarihi DATE
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Bilet ekleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    INSERT INTO biletler (musteri_id, seans_id, koltuk_id, odeme_id, satis_tarihi)
    VALUES (p_musteri_id, p_seans_id, p_koltuk_id, p_odeme_id, p_satis_tarihi);
    COMMIT;
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE BiletGuncelle (
    IN p_bilet_id INT,
    IN p_musteri_id INT,
    IN p_seans_id INT,
    IN p_koltuk_id INT,
    IN p_odeme_id INT,
    IN p_satis_tarihi DATE)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Bilet güncelleme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    UPDATE biletler
    SET musteri_id = p_musteri_id,
        seans_id = p_seans_id,
        koltuk_id = p_koltuk_id,
        odeme_id = p_odeme_id,
        satis_tarihi = p_satis_tarihi
    WHERE bilet_id = p_bilet_id;
    COMMIT;
END $$
DELIMITER ;


DELIMITER $$
CREATE PROCEDURE BiletSil (
    IN p_bilet_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Bilet silme işlemi başarısız oldu!';
    END;
    START TRANSACTION;
    DELETE FROM biletler
    WHERE bilet_id = p_bilet_id;
    COMMIT;
END $$
DELIMITER ;

DELIMITER //
CREATE TRIGGER before_koltuk_insert
BEFORE INSERT ON Koltuklar
FOR EACH ROW
BEGIN
    DECLARE mevcutKoltukSayisi INT;
    DECLARE salonKapasite INT;
    SELECT COUNT(*) INTO mevcutKoltukSayisi FROM Koltuklar WHERE SalonID = NEW.SalonID;
    SELECT Kapasite INTO salonKapasite FROM Salonlar WHERE SalonID = NEW.SalonID;
    IF mevcutKoltukSayisi >= salonKapasite THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Salon kapasitesi doldu, yeni koltuk eklenemez!';
    END IF;
END;
//
DELIMITER ;

DELIMITER //
CREATE TRIGGER before_seans_insert
BEFORE INSERT ON Seanslar
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1 FROM Seanslar 
        WHERE SalonID = NEW.SalonID 
          AND SeansTarihi = NEW.SeansTarihi 
          AND SeansSaati = NEW.SeansSaati
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Aynı salonda ve saatte başka bir seans mevcut!';
    END IF;
END;
//
DELIMITER ;

DELIMITER //
CREATE FUNCTION ToplamSatilanBilet(seansID INT) RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE toplam INT;
    SELECT COUNT(*) INTO toplam
    FROM Biletler
    WHERE SeansID = seansID;
    RETURN toplam;
END;
//
DELIMITER ;
