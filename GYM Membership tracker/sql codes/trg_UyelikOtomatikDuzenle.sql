USE [OGUGYMDB]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER   TRIGGER [dbo].[trg_UyelikOtomatikDuzenle]
ON [dbo].[uyelikler]
AFTER INSERT
AS
BEGIN
    DECLARE @yeni_id INT, @kisi_id INT;
    
    SELECT @yeni_id = uyelik_id, @kisi_id = kisisel_id FROM inserted;
    UPDATE uyelikler 
    SET durum = 0 
    WHERE kisisel_id = @kisi_id AND uyelik_id <> @yeni_id;
    
    PRINT 'Eski üyelikler otomatik olarak arşivlendi.';
END