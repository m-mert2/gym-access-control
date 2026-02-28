USE [OGUGYMDB]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER   PROCEDURE [dbo].[sp_YillikVeriTemizligi]
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRANSACTION
    BEGIN TRY
        DELETE FROM giris_log WHERE kisisel_id IN (SELECT kisisel_id FROM kisisel_bilgiler WHERE kayit_tarihi < DATEADD(YEAR, -1, GETDATE()));
        DELETE FROM kartlar WHERE kisisel_id IN (SELECT kisisel_id FROM kisisel_bilgiler WHERE kayit_tarihi < DATEADD(YEAR, -1, GETDATE()));
        DELETE FROM uyelikler WHERE kisisel_id IN (SELECT kisisel_id FROM kisisel_bilgiler WHERE kayit_tarihi < DATEADD(YEAR, -1, GETDATE()));
        DELETE FROM kisisel_bilgiler 
        WHERE kayit_tarihi < DATEADD(YEAR, -1, GETDATE());

        COMMIT TRANSACTION;
        PRINT '1 yılı dolan eski veriler başarıyla temizlendi.';
    END TRY
    BEGIN CATCH
        ROLLBACK TRANSACTION;
        PRINT 'Hata oluştu: ' + ERROR_MESSAGE();
    END CATCH
END