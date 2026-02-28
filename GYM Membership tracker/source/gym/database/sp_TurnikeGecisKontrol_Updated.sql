
CREATE OR ALTER PROCEDURE sp_TurnikeGecisKontrol
    @kart_uid NVARCHAR(50),
    @turnike_id INT = 1
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @sonuc_kodu INT = 0;
    DECLARE @mesaj NVARCHAR(200);
    DECLARE @hata_tipi NVARCHAR(20) = 'HATA';
    DECLARE @kart_id INT;
    DECLARE @kisisel_id INT;
    DECLARE @uyelik_id INT;
    DECLARE @kalan_giris INT;
    DECLARE @son_giris_tarihi DATETIME;
    DECLARE @dakika_farki INT;
    DECLARE @tur_adi NVARCHAR(50);
    DECLARE @uyelik_durum BIT;
    DECLARE @bitis_tarihi DATE;
    
    BEGIN TRY
        SELECT @kart_id = kart_id, @kisisel_id = kisisel_id, @uyelik_id = uyelik_id
        FROM kartlar
        WHERE kart_uid = @kart_uid AND aktif = 1;
        
        IF @kart_id IS NULL
        BEGIN
            SET @sonuc_kodu = 0;
            SET @mesaj = 'Kart bulunamadı veya pasif';
            SET @hata_tipi = 'HATA';
            GOTO DonusYap;
        END
        SELECT @kalan_giris = kalan_giris, @uyelik_durum = durum, @bitis_tarihi = bitis_tarihi, @tur_adi = ut.tur_adi
        FROM uyelikler u
        INNER JOIN uyelik_turleri ut ON u.tur_id = ut.tur_id
        WHERE u.uyelik_id = @uyelik_id;
        IF @uyelik_durum = 0
        BEGIN
            SET @sonuc_kodu = 0;
            SET @mesaj = 'Üyelik pasif durumda';
            SET @hata_tipi = 'UYARI';
            GOTO DonusYap;
        END
        
        IF CAST(GETDATE() AS DATE) > @bitis_tarihi
        BEGIN
            SET @sonuc_kodu = 0;
            SET @mesaj = 'Üyelik süresi bitmiş';
            SET @hata_tipi = 'UYARI';
            GOTO DonusYap;
        END
        
        IF @kalan_giris = 0
        BEGIN
            SET @sonuc_kodu = 0;
            SET @mesaj = 'Giriş hakkı bitti';
            SET @hata_tipi = 'UYARI';
            GOTO DonusYap;
        END
        SELECT TOP 1 @son_giris_tarihi = tarih_saat
        FROM giris_log
        WHERE kart_id = @kart_id
        ORDER BY tarih_saat DESC;
        IF @son_giris_tarihi IS NOT NULL
        BEGIN
            SET @dakika_farki = DATEDIFF(MINUTE, @son_giris_tarihi, GETDATE());
            IF @dakika_farki < 1
            BEGIN
                SET @sonuc_kodu = 0;
                SET @mesaj = 'Art arda giriş yapılamaz. Lütfen 1 dakika bekleyiniz.';
                SET @hata_tipi = 'UYARI';
                GOTO DonusYap;
            END
            IF @dakika_farki >= 90
            BEGIN
                IF @kalan_giris > 0
                BEGIN
                    UPDATE uyelikler
                    SET kalan_giris = kalan_giris - 1
                    WHERE uyelik_id = @uyelik_id;
                    
                    SET @kalan_giris = @kalan_giris - 1;
                END
                INSERT INTO giris_log (tarih_saat, sonuc, kisisel_id, uyelik_id, kart_id, turnike_id)
                VALUES (GETDATE(), 'BASARILI', @kisisel_id, @uyelik_id, @kart_id, @turnike_id);
                
                SET @sonuc_kodu = 1;
                SET @mesaj = 'Yeni seans başlatıldı, 1 hak düşüldü.';
                SET @hata_tipi = 'BASARILI';
                GOTO DonusYap;
            END
            IF @dakika_farki >= 1 AND @dakika_farki < 90
            BEGIN
                INSERT INTO giris_log (tarih_saat, sonuc, kisisel_id, uyelik_id, kart_id, turnike_id)
                VALUES (GETDATE(), 'BASARILI', @kisisel_id, @uyelik_id, @kart_id, @turnike_id);
                
                SET @sonuc_kodu = 1;
                SET @mesaj = 'Seansa devam ettiniz.';
                SET @hata_tipi = 'BASARILI';
                GOTO DonusYap;
            END
        END
        ELSE
        BEGIN
            UPDATE uyelikler
            SET kalan_giris = kalan_giris - 1
            WHERE uyelik_id = @uyelik_id;
            
            SET @kalan_giris = @kalan_giris - 1;
            INSERT INTO giris_log (tarih_saat, sonuc, kisisel_id, uyelik_id, kart_id, turnike_id)
            VALUES (GETDATE(), 'BASARILI', @kisisel_id, @uyelik_id, @kart_id, @turnike_id);
            
            SET @sonuc_kodu = 1;
            SET @mesaj = 'Yeni seans başlatıldı, 1 hak düşüldü.';
            SET @hata_tipi = 'BASARILI';
            GOTO DonusYap;
        END
        
    END TRY
    BEGIN CATCH
        SET @sonuc_kodu = 0;
        SET @mesaj = ERROR_MESSAGE();
        SET @hata_tipi = 'HATA';
        INSERT INTO sistem_hatalari (hata_tarih, hata_tip_id, prosedur_adi, error_number, error_state, error_line, hata_mesaji, kart_uid)
        VALUES (GETDATE(), 1, 'sp_TurnikeGecisKontrol', ERROR_NUMBER(), ERROR_STATE(), ERROR_LINE(), ERROR_MESSAGE(), @kart_uid);
    END CATCH
    
    DonusYap:
    SELECT @sonuc_kodu AS SonucKodu, @mesaj AS Mesaj, @hata_tipi AS HataTipi;
    IF @sonuc_kodu = 1
    BEGIN
        SELECT TOP 1
            kb.isim,
            kb.soyisim,
            @kalan_giris AS kalan_giris,
            @tur_adi AS tur_adi
        FROM kisisel_bilgiler kb
        WHERE kb.kisisel_id = @kisisel_id;
    END
END
