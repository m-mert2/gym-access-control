USE [OGUGYMDB]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER   PROCEDURE [dbo].[sp_TurnikeGecisKontrol]
    @p_kart_uid NVARCHAR(50),
    @p_turnike_id INT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @v_kisisel_id INT, @v_uyelik_id INT, @v_kart_id INT;
    DECLARE @v_kalan_hak INT, @v_bitis_tarihi DATE;
    DECLARE @v_son_giris_saati DATETIME;
    DECLARE @seans_suresi_dakika INT = 60; -- 1 Saatlik Seans Penceresi
    DECLARE @minimum_bekleme_dakika INT = 1; -- İlk girişten sonra minimum 1 dakika bekleme
    SELECT 
        @v_kart_id = k.kart_id, 
        @v_kisisel_id = k.kisisel_id,
        @v_uyelik_id = k.uyelik_id
    FROM kartlar k
    WHERE k.kart_uid = @p_kart_uid AND k.aktif = 1;
    IF @v_kart_id IS NULL
    BEGIN
        SELECT 0 AS Durum, 'Geçersiz kart!' AS Mesaj, 0 AS KalanHak;
        RETURN;
    END
    IF @v_uyelik_id IS NULL
    BEGIN
        INSERT INTO giris_log (tarih_saat, sonuc, kisisel_id, uyelik_id, kart_id, turnike_id) 
        VALUES (GETDATE(), 'ÜYELİK YOK', @v_kisisel_id, NULL, @v_kart_id, @p_turnike_id);
        
        SELECT 0 AS Durum, 'Üyeliğiniz bulunmuyor! Lütfen vezneye başvurun.' AS Mesaj, 0 AS KalanHak;
        RETURN;
    END
    SELECT 
        @v_kalan_hak = u.kalan_giris,
        @v_bitis_tarihi = u.bitis_tarihi
    FROM uyelikler u
    WHERE u.uyelik_id = @v_uyelik_id AND u.durum = 1;
    IF @v_kalan_hak IS NULL
    BEGIN
        INSERT INTO giris_log (tarih_saat, sonuc, kisisel_id, uyelik_id, kart_id, turnike_id) 
        VALUES (GETDATE(), 'ÜYELİK PASİF', @v_kisisel_id, @v_uyelik_id, @v_kart_id, @p_turnike_id);
        
        SELECT 0 AS Durum, 'Üyeliğiniz pasif durumda!' AS Mesaj, 0 AS KalanHak;
        RETURN;
    END 
    IF @v_kalan_hak <> -1
    BEGIN
        SELECT TOP 1 @v_son_giris_saati = tarih_saat 
        FROM giris_log 
        WHERE kart_id = @v_kart_id AND sonuc IN ('BASARILI', 'SEANS_DEVAM')
        ORDER BY tarih_saat DESC;
        DECLARE @gecen_dakika INT = DATEDIFF(MINUTE, @v_son_giris_saati, GETDATE());
        IF @v_son_giris_saati IS NOT NULL AND @gecen_dakika < @minimum_bekleme_dakika
        BEGIN
            INSERT INTO giris_log (tarih_saat, sonuc, kisisel_id, uyelik_id, kart_id, turnike_id) 
            VALUES (GETDATE(), 'ÇOK_ERKEN', @v_kisisel_id, @v_uyelik_id, @v_kart_id, @p_turnike_id);

            SELECT 0 AS Durum, 'Lütfen 1 dakika bekleyiniz!' AS Mesaj, @v_kalan_hak AS KalanHak;
            RETURN;
        END
        IF @v_son_giris_saati IS NOT NULL AND @gecen_dakika >= @minimum_bekleme_dakika AND @gecen_dakika < @seans_suresi_dakika
        BEGIN
            INSERT INTO giris_log (tarih_saat, sonuc, kisisel_id, uyelik_id, kart_id, turnike_id) 
            VALUES (GETDATE(), 'SEANS_DEVAM', @v_kisisel_id, @v_uyelik_id, @v_kart_id, @p_turnike_id);

            SELECT 1 AS Durum, 'Seans devam ediyor, hak düşülmedi.' AS Mesaj, @v_kalan_hak AS KalanHak;
            RETURN;
        END
    END
    IF @v_bitis_tarihi < CAST(GETDATE() AS DATE)
    BEGIN
        INSERT INTO giris_log (tarih_saat, sonuc, kisisel_id, uyelik_id, kart_id, turnike_id) 
        VALUES (GETDATE(), 'SÜRESİ DOLMUŞ', @v_kisisel_id, @v_uyelik_id, @v_kart_id, @p_turnike_id);
        
        SELECT 0 AS Durum, 'Üyelik süreniz dolmuş!' AS Mesaj, 0 AS KalanHak;
        RETURN;
    END
    IF @v_kalan_hak <> -1 AND @v_kalan_hak <= 0
    BEGIN
        INSERT INTO giris_log (tarih_saat, sonuc, kisisel_id, uyelik_id, kart_id, turnike_id) 
        VALUES (GETDATE(), 'HAK BİTMİŞ', @v_kisisel_id, @v_uyelik_id, @v_kart_id, @p_turnike_id);
        
        SELECT 0 AS Durum, 'Giriş hakkınız kalmadı!' AS Mesaj, 0 AS KalanHak;
        RETURN;
    END
    BEGIN TRANSACTION
    BEGIN TRY
        IF @v_kalan_hak <> -1
        BEGIN
            UPDATE uyelikler SET kalan_giris = kalan_giris - 1 WHERE uyelik_id = @v_uyelik_id;
        END
        
        INSERT INTO giris_log (tarih_saat, sonuc, kisisel_id, uyelik_id, kart_id, turnike_id) 
        VALUES (GETDATE(), 'BASARILI', @v_kisisel_id, @v_uyelik_id, @v_kart_id, @p_turnike_id);
        
        COMMIT TRANSACTION;
        DECLARE @yeni_kalan INT = CASE WHEN @v_kalan_hak = -1 THEN -1 ELSE (@v_kalan_hak - 1) END;
        SELECT 1 AS Durum, 'Hoş geldiniz! Yeni seans başlatıldı.' AS Mesaj, @yeni_kalan AS KalanHak;
    END TRY
    BEGIN CATCH
        ROLLBACK TRANSACTION;
        
        SELECT 0 AS Durum, 'Sistem hatası!' AS Mesaj, @v_kalan_hak AS KalanHak;
    END CATCH
END