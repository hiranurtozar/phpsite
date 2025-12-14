<?php
// odeme.php - Output buffering ile
ob_start(); // Buffer'ı başlat
require_once 'header.php';

// Giriş kontrolü
if (!$is_logged_in) {
    $_SESSION['message'] = 'Ödeme yapmak için giriş yapmalısınız!';
    $_SESSION['message_type'] = 'error';
    header('Location: auth.php');
    ob_end_flush(); // Buffer'ı temizle
    exit();
}

// Sepet kontrolü
if (empty($_SESSION['sepet'])) {
    $_SESSION['message'] = 'Sepetiniz boş!';
    $_SESSION['message_type'] = 'error';
    header('Location: sepet.php');
    ob_end_flush();
    exit();
}

// Toplam hesapla
$toplam_tutar = 0;
$toplam_adet = 0;

foreach ($_SESSION['sepet'] as $urun) {
    $adet = isset($urun['adet']) ? intval($urun['adet']) : 1;
    $fiyat = isset($urun['fiyat']) ? floatval($urun['fiyat']) : 0;
    $toplam_tutar += $fiyat * $adet;
    $toplam_adet += $adet;
}

$kdv = $toplam_tutar * 0.18;
$genel_toplam = $toplam_tutar * 1.18;

// Ödeme işlemi
if (isset($_POST['odeme_yap'])) {
    // Ödeme başarılı mesajı
    $_SESSION['message'] = 'Ödemeniz başarıyla alındı! Siparişiniz hazırlanıyor.';
    $_SESSION['message_type'] = 'success';
    
    // Sepeti temizle
    $_SESSION['sepet'] = [];
    
    // Anasayfaya yönlendir
    header('Location: anasayfa.php');
    ob_end_flush();
    exit();
}
?>

<style>
    /* ÖDEME SAYFASI STİLLERİ */
    .odeme-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .odeme-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .odeme-header h1 {
        color: #ff6b9d;
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    
    .odeme-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }
    
    @media (max-width: 768px) {
        .odeme-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .odeme-form {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
    }
    
    .odeme-summary {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
        position: sticky;
        top: 100px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    
    .form-input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ffeef2;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.3s;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #ff6b9d;
        box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
    }
    
    .card-icons {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    
    .card-icon {
        width: 50px;
        height: 30px;
        border: 1px solid #ddd;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #ffeef2;
    }
    
    .summary-total {
        font-size: 1.3rem;
        font-weight: 700;
        color: #ff6b9d;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #ffeef2;
    }
    
    .odeme-btn {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.2rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 20px;
    }
    
    .odeme-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
    }
    
    .secure-payment {
        text-align: center;
        margin-top: 20px;
        color: #666;
    }
    
    .secure-payment i {
        color: #4CAF50;
        margin-right: 5px;
    }
    
    .cart-items {
        margin-top: 20px;
    }
    
    .cart-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #ffeef2;
    }
</style>

<div class="container">
    <div class="odeme-container">
        <div class="odeme-header">
            <h1>💳 Güvenli Ödeme</h1>
            <p>Ödeme bilgilerinizi güvenle tamamlayın</p>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; ?>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            </div>
        <?php endif; ?>
        
        <div class="odeme-grid">
            <!-- ÖDEME FORMU -->
            <div class="odeme-form">
                <h2 style="color: #333; margin-bottom: 25px; border-bottom: 2px solid #ffeef2; padding-bottom: 10px;">
                    <i class="fas fa-credit-card"></i> Kart Bilgileri
                </h2>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Kart Üzerindeki İsim</label>
                        <input type="text" name="kart_adi" class="form-input" placeholder="Ad Soyad" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kart Numarası</label>
                        <input type="text" name="kart_no" class="form-input" placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>
                    
                    <div class="card-icons">
                        <div class="card-icon">💳</div>
                        <div class="card-icon">🔒</div>
                        <div class="card-icon">🛡️</div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">Son Kullanma Tarihi</label>
                            <input type="text" name="son_kullanma" class="form-input" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">CVV</label>
                            <input type="text" name="cvv" class="form-input" placeholder="123" maxlength="3" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Teslimat Adresi</label>
                        <textarea name="teslimat_adresi" class="form-input" rows="3" required><?php echo htmlspecialchars($_SESSION['adres'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="odeme_yap" class="odeme-btn">
                        <i class="fas fa-lock"></i> ÖDEMEYİ TAMAMLA
                    </button>
                </form>
                
                <div class="secure-payment">
                    <p><i class="fas fa-shield-alt"></i> 256-bit SSL Güvenli Ödeme</p>
                </div>
            </div>
            
            <!-- ÖDEME ÖZETİ -->
            <div class="odeme-summary">
                <h2 style="color: #333; margin-bottom: 25px; border-bottom: 2px solid #ffeef2; padding-bottom: 10px;">
                    <i class="fas fa-receipt"></i> Sipariş Özeti
                </h2>
                
                <div class="cart-items">
                    <?php foreach ($_SESSION['sepet'] as $urun): 
                        $adet = isset($urun['adet']) ? intval($urun['adet']) : 1;
                        $fiyat = isset($urun['fiyat']) ? floatval($urun['fiyat']) : 0;
                        $toplam = $adet * $fiyat;
                        $urun_ad = isset($urun['ad']) ? htmlspecialchars($urun['ad']) : 'Ürün';
                    ?>
                        <div class="cart-item">
                            <span><?php echo $urun_ad; ?> x<?php echo $adet; ?></span>
                            <span><?php echo number_format($toplam, 2); ?> TL</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-item">
                    <span>Ara Toplam:</span>
                    <span><?php echo number_format($toplam_tutar, 2); ?> TL</span>
                </div>
                
                <div class="summary-item">
                    <span>Kargo:</span>
                    <span style="color: #4CAF50; font-weight: 600;">ÜCRETSİZ</span>
                </div>
                
                <div class="summary-item">
                    <span>KDV (%18):</span>
                    <span><?php echo number_format($kdv, 2); ?> TL</span>
                </div>
                
                <div class="summary-total summary-item">
                    <span>GENEL TOPLAM:</span>
                    <span><?php echo number_format($genel_toplam, 2); ?> TL</span>
                </div>
                
                <div style="margin-top: 30px; padding: 15px; background: #f9f9f9; border-radius: 10px;">
                    <p style="color: #666; margin-bottom: 10px;">
                        <i class="fas fa-box"></i> 
                        <strong>Teslimat:</strong> 2-3 iş günü içinde
                    </p>
                    <p style="color: #666;">
                        <i class="fas fa-gift"></i> 
                        <strong>Hediye Paketi:</strong> Ücretsiz hediye paketleme
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Kart numarası formatı
    const kartNoInput = document.querySelector('input[name="kart_no"]');
    if (kartNoInput) {
        kartNoInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formatted = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = formatted.trim().substr(0, 19);
        });
    }
    
    // Son kullanma tarihi formatı
    const sonKullanmaInput = document.querySelector('input[name="son_kullanma"]');
    if (sonKullanmaInput) {
        sonKullanmaInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value.substring(0, 5);
        });
    }
    
    // CVV formatı
    const cvvInput = document.querySelector('input[name="cvv"]');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').substr(0, 3);
        });
    }
});
</script>

<?php 
ob_end_flush(); // Buffer'ı temizle
require_once 'footer.php'; 
?>