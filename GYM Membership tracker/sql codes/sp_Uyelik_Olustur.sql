USE [OGUGYMDB]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER   PROCEDURE [dbo].[sp_Uyelik_Olustur]
    @p_kisisel_id INT,
    @p_tur_id INT,
    @p_ay_suresi INT = 1 -- Varsayılan 1 ay
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @v_varsayilan_hak INT;
    DECLARE @v_bitis_tarihi DATE;
    DECLARE @v_yeni_uyelik_id INT;
    SELECT @v_varsayilan_hak = varsayilan_giris_hakki 
    FROM uyelik_turleri 
    WHERE tur_id = @p_tur_id;
    IF @v_varsayilan_hak IS NULL
    BEGIN
        RAISERROR('Geçersiz üyelik türü seçildi!', 16, 1);
        RETURN;
    END
    SET @v_bitis_tarihi = DATEADD(MONTH, @p_ay_suresi, CAST(GETDATE() AS DATE));
    INSERT INTO uyelikler (
        kisisel_id, 
        tur_id, 
        baslangic_tarihi, 
        bitis_tarihi, 
        kalan_giris, 
        durum
    )
    VALUES (
        @p_kisisel_id,
        @p_tur_id,
        CAST(GETDATE() AS DATE),
        @v_bitis_tarihi,
        @v_varsayilan_hak,
        1 -- Aktif
    );

    SET @v_yeni_uyelik_id = SCOPE_IDENTITY();
    IF EXISTS (SELECT 1 FROM kartlar WHERE kisisel_id = @p_kisisel_id)
    BEGIN
        UPDATE kartlar 
        SET uyelik_id = @v_yeni_uyelik_id 
        WHERE kisisel_id = @p_kisisel_id;
    END
    SELECT @v_yeni_uyelik_id AS YeniUyelikID, 'Üyelik Başarıyla Oluşturuldu ve Kart Bağlantısı Güncellendi' AS Mesaj;
END