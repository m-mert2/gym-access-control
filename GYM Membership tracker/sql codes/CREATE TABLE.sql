USE OGUGYMDB;
GO

CREATE TABLE dbo.kisisel_bilgiler (
    kisisel_id     INT IDENTITY(1,1) PRIMARY KEY,
    isim           NVARCHAR(50)  NOT NULL,
    soyisim        NVARCHAR(50)  NOT NULL,
    cinsiyet       CHAR(1),
    tc_no          CHAR(11) UNIQUE,
    email          NVARCHAR(100),
    telefon        NVARCHAR(20),
    adres          NVARCHAR(250),
    bolum          NVARCHAR(100),
    ogrenci_no     NVARCHAR(20),
    kayit_tarihi   DATE DEFAULT GETDATE(),
    uye_grubu      VARCHAR(50) NOT NULL
);
GO

CREATE TABLE dbo.uyelik_turleri (
    tur_id                  INT IDENTITY(1,1) PRIMARY KEY,
    tur_adi                 NVARCHAR(50) NOT NULL,
    varsayilan_giris_hakki  INT DEFAULT 0,
    ucret                   DECIMAL(10,2)
);
GO

CREATE TABLE dbo.uyelikler (
    uyelik_id        INT IDENTITY(1,1) PRIMARY KEY,
    kisisel_id       INT,
    tur_id           INT,
    baslangic_tarihi DATE DEFAULT GETDATE(),
    bitis_tarihi     DATE,
    kalan_giris      INT,
    durum            BIT DEFAULT 1,

    CONSTRAINT fk_uyelik_kisi
        FOREIGN KEY (kisisel_id) REFERENCES dbo.kisisel_bilgiler(kisisel_id),

    CONSTRAINT fk_uyelik_tur
        FOREIGN KEY (tur_id) REFERENCES dbo.uyelik_turleri(tur_id)
);
GO

CREATE TABLE dbo.kartlar (
    kart_id        INT IDENTITY(1,1) PRIMARY KEY,
    kisisel_id     INT,
    uyelik_id      INT,
    kart_uid       NVARCHAR(50) UNIQUE,
    aktif          BIT DEFAULT 1,
    son_kullanim   DATETIME,

    CONSTRAINT fk_kart_kisi
        FOREIGN KEY (kisisel_id) REFERENCES dbo.kisisel_bilgiler(kisisel_id),

    CONSTRAINT fk_kart_uyelik
        FOREIGN KEY (uyelik_id) REFERENCES dbo.uyelikler(uyelik_id)
);
GO

CREATE TABLE dbo.turnikeler (
    turnike_id   INT IDENTITY(1,1) PRIMARY KEY,
    konum        NVARCHAR(100),
    durum        BIT DEFAULT 1,
    baglanti_ip  VARCHAR(20)
);
GO

CREATE TABLE dbo.giris_log (
    log_id       INT IDENTITY(1,1) PRIMARY KEY,
    tarih_saat   DATETIME DEFAULT GETDATE(),
    sonuc        NVARCHAR(50),
    kisisel_id   INT,
    uyelik_id    INT,
    kart_id      INT,
    turnike_id   INT,

    CONSTRAINT fk_log_kisi
        FOREIGN KEY (kisisel_id) REFERENCES dbo.kisisel_bilgiler(kisisel_id),

    CONSTRAINT fk_log_uyelik
        FOREIGN KEY (uyelik_id) REFERENCES dbo.uyelikler(uyelik_id),

    CONSTRAINT fk_log_kart
        FOREIGN KEY (kart_id) REFERENCES dbo.kartlar(kart_id),

    CONSTRAINT fk_log_turnike
        FOREIGN KEY (turnike_id) REFERENCES dbo.turnikeler(turnike_id)
);
GO

CREATE TABLE dbo.kullanicilar (
    user_id        INT IDENTITY(1,1) PRIMARY KEY,
    username       NVARCHAR(50) UNIQUE,
    password_hash  NVARCHAR(200),
    rol            NVARCHAR(20),
    aktif          BIT DEFAULT 1
);
GO

CREATE TABLE dbo.yetkiler (
    yetki_id   INT IDENTITY(1,1) PRIMARY KEY,
    yetki_adi  NVARCHAR(50)
);
GO

CREATE TABLE dbo.kullanici_yetkileri (
    user_id   INT,
    yetki_id  INT,

    PRIMARY KEY (user_id, yetki_id),

    CONSTRAINT fk_ky_user
        FOREIGN KEY (user_id) REFERENCES dbo.kullanicilar(user_id),

    CONSTRAINT fk_ky_yetki
        FOREIGN KEY (yetki_id) REFERENCES dbo.yetkiler(yetki_id)
);
GO

CREATE TABLE dbo.hata_tipleri (
    hata_tip_id   INT IDENTITY(1,1) PRIMARY KEY,
    tip_adi       NVARCHAR(50),
    tip_aciklama  NVARCHAR(200)
);
GO

CREATE TABLE dbo.sistem_hatalari (
    hata_id        INT IDENTITY(1,1) PRIMARY KEY,
    hata_tarih     DATETIME DEFAULT GETDATE(),
    hata_tip_id    INT,
    prosedur_adi   NVARCHAR(100),
    error_number   INT,
    error_state    INT,
    error_line     INT,
    hata_mesaji    NVARCHAR(MAX),
    kart_uid       NVARCHAR(50),

    CONSTRAINT fk_hata_tip
        FOREIGN KEY (hata_tip_id) REFERENCES dbo.hata_tipleri(hata_tip_id)
);
GO
