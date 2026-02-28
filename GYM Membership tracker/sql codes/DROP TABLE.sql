USE OGUGYMDB;
GO

IF OBJECT_ID('dbo.kullanici_yetkileri', 'U') IS NOT NULL
    DROP TABLE dbo.kullanici_yetkileri;
GO

IF OBJECT_ID('dbo.giris_log', 'U') IS NOT NULL
    DROP TABLE dbo.giris_log;
GO

IF OBJECT_ID('dbo.sistem_hatalari', 'U') IS NOT NULL
    DROP TABLE dbo.sistem_hatalari;
GO

IF OBJECT_ID('dbo.kartlar', 'U') IS NOT NULL
    DROP TABLE dbo.kartlar;
GO

IF OBJECT_ID('dbo.uyelikler', 'U') IS NOT NULL
    DROP TABLE dbo.uyelikler;
GO

IF OBJECT_ID('dbo.turnikeler', 'U') IS NOT NULL
    DROP TABLE dbo.turnikeler;
GO

IF OBJECT_ID('dbo.uyelik_turleri', 'U') IS NOT NULL
    DROP TABLE dbo.uyelik_turleri;
GO

IF OBJECT_ID('dbo.hata_tipleri', 'U') IS NOT NULL
    DROP TABLE dbo.hata_tipleri;
GO

IF OBJECT_ID('dbo.yetkiler', 'U') IS NOT NULL
    DROP TABLE dbo.yetkiler;
GO

IF OBJECT_ID('dbo.kullanicilar', 'U') IS NOT NULL
    DROP TABLE dbo.kullanicilar;
GO

IF OBJECT_ID('dbo.kisisel_bilgiler', 'U') IS NOT NULL
    DROP TABLE dbo.kisisel_bilgiler;
GO
