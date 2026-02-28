USE [OGUGYMDB]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER   PROCEDURE [dbo].[sp_UyeDashboardRaporu]
    @p_kisisel_id INT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        SELECT 
            kb.isim + ' ' + kb.soyisim AS AdSoyad,
            ut.tur_adi AS PaketAdi,
            u.baslangic_tarihi,
            u.bitis_tarihi,
            u.kalan_giris,
            DATEDIFF(DAY, GETDATE(), u.bitis_tarihi) AS KalanGun,
            (SELECT COUNT(*) FROM giris_log WHERE kisisel_id = @p_kisisel_id AND sonuc = 'BASARILI') AS ToplamGirisSayisi,
            CASE 
                WHEN u.bitis_tarihi < GETDATE() THEN 'Süresi Dolmuş'
                WHEN u.kalan_giris <= 0 THEN 'Hakkı Bitmiş'
                ELSE 'Aktif'
            END AS UyelikDurumOzeti
        FROM kisisel_bilgiler kb
        INNER JOIN uyelikler u ON kb.kisisel_id = u.kisisel_id
        INNER JOIN uyelik_turleri ut ON u.tur_id = ut.tur_id
        WHERE kb.kisisel_id = @p_kisisel_id AND u.durum = 1;
    END TRY
    BEGIN CATCH
        PRINT 'Hata oluştu: ' + ERROR_MESSAGE();
    END CATCH
END

EXEC sp_UyeDashboardRaporu @p_kisisel_id = 1