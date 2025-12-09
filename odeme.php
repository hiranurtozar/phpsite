<?php
require_once 'cicek.php';
require_once 'header.php';

// 1. KULLANICI GİRİŞ KONTROLÜ EKLE
if(!kullaniciGirisKontrol()) {
    $_SESSION['auth_message'] = [
        'type' => 'warning',
        'text' => $dil == 'tr' ? 'Sipariş verebilmek için giriş yapmalısınız!' : 'You must login to place an order!'
    ];
    echo '<script>window.location.href = "anasayfa.php?sayfa=anasayfa";</script>';
    exit;
}

// 2. SEPET BOŞSA KONTROL
if(empty($_SESSION['sepet'])) {
    $_SESSION['mesaj'] = [
        'tip' => 'warning',
        'metin' => $dil == 'tr' ? 'Sepetiniz boş!' : 'Your cart is empty!'
    ];
    echo '<script>window.location.href = "sepet.php?sayfa=sepet";</script>';
    exit;
}

// Sepet toplamını hesapla
function sepetToplam() {
    if(empty($_SESSION['sepet'])) return 0;
    
    $toplam = 0;
    foreach($_SESSION['sepet'] as $item) {
        $toplam += $item['fiyat'] * $item['miktar'];
    }
    return $toplam;
}

// Ödeme işlemi
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF token kontrolü
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['mesaj'] = [
            'tip' => 'error',
            'metin' => $dil == 'tr' ? 'Güvenlik hatası! Lütfen tekrar deneyin.' : 'Security error! Please try again.'
        ];
        echo '<script>window.location.href = "odeme.php?sayfa=odeme";</script>';
        exit;
    }
    
    // Ödeme bilgilerini al
    $odeme_tipi = $_POST['odeme_tipi'] ?? 'kredi_karti';
    $kart_numarasi = $_POST['kart_numarasi'] ?? '';
    $kart_sahibi = $_POST['kart_sahibi'] ?? '';
    
    // Sipariş numarası oluştur
    $siparis_no = 'SIP' . date('YmdHis') . rand(100, 999);
    
    // Sipariş verilerini hazırla
    $siparis = [
        'siparis_no' => $siparis_no,
        'user_id' => $_SESSION['user_id'] ?? null,
        'ad_soyad' => $_SESSION['ad_soyad'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'telefon' => $_SESSION['telefon'] ?? '',
        'adres' => $_SESSION['adres'] ?? '',
        'urunler' => $_SESSION['sepet'],
        'toplam' => sepetToplam(),
        'odeme_tipi' => $odeme_tipi,
        'durum' => $odeme_tipi == 'kapida_odeme' ? 'beklemede' : 'odendi',
        'tarih' => date('Y-m-d H:i:s'),
        'teslim_tarihi' => date('Y-m-d', strtotime('+3 days'))
    ];
    
    // Siparişleri JSON'a kaydet
    $siparisler_dosya = 'siparisler.json';
    if(!file_exists($siparisler_dosya)) {
        file_put_contents($siparisler_dosya, json_encode([]));
    }
    
    $siparisler = json_decode(file_get_contents($siparisler_dosya), true);
    $siparisler[] = $siparis;
    file_put_contents($siparisler_dosya, json_encode($siparisler, JSON_PRETTY_PRINT));
    
    // Sepeti temizle
    $_SESSION['sepet'] = [];
    
    // Başarı mesajı
    $_SESSION['mesaj'] = [
        'tip' => 'success',
        'metin' => $dil == 'tr' 
            ? "Siparişiniz alındı! Sipariş numaranız: {$siparis_no}" 
            : "Your order has been received! Your order number: {$siparis_no}"
    ];
    
    // JavaScript ile yönlendirme
    echo '<script>window.location.href = "siparisler.php";</script>';
    exit;
}

// Dil çevirileri
$odeme_text = [
    'tr' => [
        'odeme' => 'Ödeme',
        'siparis_detay' => 'Sipariş Detayları',
        'urun' => 'Ürün',
        'fiyat' => 'Fiyat',
        'adet' => 'Adet',
        'toplam' => 'Toplam',
        'ara_toplam' => 'Ara Toplam',
        'kargo' => 'Kargo',
        'ucretsiz' => 'Ücretsiz',
        'genel_toplam' => 'Genel Toplam',
        'odeme_yontemi' => 'Ödeme Yöntemi',
        'kredi_karti' => 'Kredi Kartı',
        'kredi_karti_aciklama' => 'Kredi kartınızla güvenli ödeme yapın',
        'kart_numarasi' => 'Kart Numarası',
        'son_kullanma' => 'Son Kullanma Tarihi',
        'cvv' => 'CVV',
        'kart_sahibi' => 'Kart Sahibi',
        'taksit' => 'Taksit Seçeneği',
        'taksit_secin' => 'Taksit Seçin',
        'peşin' => 'Peşin',
        'taksit_2' => '2 Taksit',
        'taksit_3' => '3 Taksit',
        'taksit_6' => '6 Taksit',
        'taksit_9' => '9 Taksit',
        'taksit_12' => '12 Taksit',
        'kapida_odeme' => 'Kapıda Ödeme',
        'kapida_aciklama' => 'Teslimatta nakit veya kart ile ödeme yapın',
        'banka_havalesi' => 'Banka Havalesi/EFT',
        'banka_aciklama' => 'Banka havalesi veya EFT ile ödeme yapın',
        'banka_adi' => 'Banka Adı:',
        'hesap_sahibi' => 'Hesap Sahibi:',
        'iban' => 'IBAN:',
        'aciklama_not' => '💡 Lütfen açıklama kısmına sipariş numaranızı yazmayı unutmayın!',
        'tahmini_teslim' => 'Tahmini Teslim Tarihi',
        'odemeye_gec' => 'Ödemeye Geç',
        'siparis_ver' => 'Siparişi Ver'
    ],
    'en' => [
        'odeme' => 'Payment',
        'siparis_detay' => 'Order Details',
        'urun' => 'Product',
        'fiyat' => 'Price',
        'adet' => 'Quantity',
        'toplam' => 'Total',
        'ara_toplam' => 'Subtotal',
        'kargo' => 'Shipping',
        'ucretsiz' => 'Free',
        'genel_toplam' => 'Total Amount',
        'odeme_yontemi' => 'Payment Method',
        'kredi_karti' => 'Credit Card',
        'kredi_karti_aciklama' => 'Pay securely with your credit card',
        'kart_numarasi' => 'Card Number',
        'son_kullanma' => 'Expiry Date',
        'cvv' => 'CVV',
        'kart_sahibi' => 'Card Holder',
        'taksit' => 'Installment Option',
        'taksit_secin' => 'Select Installment',
        'peşin' => 'Single Payment',
        'taksit_2' => '2 Installments',
        'taksit_3' => '3 Installments',
        'taksit_6' => '6 Installments',
        'taksit_9' => '9 Installments',
        'taksit_12' => '12 Installments',
        'kapida_odeme' => 'Cash on Delivery',
        'kapida_aciklama' => 'Pay with cash or card on delivery',
        'banka_havalesi' => 'Bank Transfer/EFT',
        'banka_aciklama' => 'Pay via bank transfer or EFT',
        'banka_adi' => 'Bank Name:',
        'hesap_sahibi' => 'Account Holder:',
        'iban' => 'IBAN:',
        'aciklama_not' => '💡 Please remember to write your order number in the description!',
        'tahmini_teslim' => 'Estimated Delivery Date',
        'odemeye_gec' => 'Proceed to Payment',
        'siparis_ver' => 'Place Order'
    ]
];
$t = $odeme_text[$dil];
?>

<style>
/* ... mevcut CSS kodları aynı ... */
</style>

<div class="payment-page">
    <div class="page-title">
        <h1>💳 <?php echo $t['odeme']; ?></h1>
        <p><?php echo $dil == 'tr' ? 'Son adımda siparişinizi tamamlayın' : 'Complete your order in the final step'; ?></p>
    </div>

    <form method="post" action="" id="paymentForm">
        <input type="hidden" name="csrf_token" value="<?php echo csrfTokenOlustur(); ?>">
        
        <div class="payment-container">
            <!-- Sipariş Özeti -->
            <div class="order-summary">
                <h2>📋 <?php echo $t['siparis_detay']; ?></h2>
                
                <div class="order-items">
                    <?php 
                    $ara_toplam = 0;
                    foreach($_SESSION['sepet'] as $item): 
                        $item_total = $item['fiyat'] * $item['miktar'];
                        $ara_toplam += $item_total;
                    ?>
                    <div class="order-item">
                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($item['ad'] ?? 'Ürün'); ?></h4>
                            <div class="item-quantity"><?php echo $t['adet']; ?>: <?php echo $item['miktar']; ?></div>
                        </div>
                        <div class="item-price"><?php echo number_format($item_total, 2); ?> ₺</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span><?php echo $t['ara_toplam']; ?></span>
                        <span><?php echo number_format($ara_toplam, 2); ?> ₺</span>
                    </div>
                    
                    <div class="total-row">
                        <span><?php echo $t['kargo']; ?></span>
                        <span><?php echo $t['ucretsiz']; ?></span>
                    </div>
                    
                    <div class="total-row">
                        <span><?php echo $t['genel_toplam']; ?></span>
                        <span><?php echo number_format($ara_toplam, 2); ?> ₺</span>
                    </div>
                </div>
                
                <div class="delivery-info">
                    <h4>📅 <?php echo $t['tahmini_teslim']; ?></h4>
                    <p><?php echo date('d.m.Y', strtotime('+3 days')); ?></p>
                </div>
            </div>

            <!-- Ödeme Yöntemleri -->
            <div class="payment-methods">
                <h2>💳 <?php echo $t['odeme_yontemi']; ?></h2>
                
                <!-- Kredi Kartı -->
                <div class="payment-option">
                    <div class="payment-header" onclick="togglePaymentForm('kredi_karti')">
                        <div class="payment-icon">💳</div>
                        <div class="payment-title"><?php echo $t['kredi_karti']; ?></div>
                    </div>
                    <div class="payment-description"><?php echo $t['kredi_karti_aciklama']; ?></div>
                    
                    <div class="payment-form" id="kredi_kartiForm">
                        <div class="form-group">
                            <label class="form-label"><?php echo $t['kart_numarasi']; ?> *</label>
                            <input type="text" name="kart_numarasi" class="form-input" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" pattern="[0-9\s]{13,19}">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label"><?php echo $t['son_kullanma']; ?> *</label>
                                <input type="text" name="son_kullanma" class="form-input" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><?php echo $t['cvv']; ?> *</label>
                                <input type="text" name="cvv" class="form-input" placeholder="123" maxlength="3" pattern="[0-9]{3}">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo $t['kart_sahibi']; ?> *</label>
                            <input type="text" name="kart_sahibi" class="form-input" placeholder="AD SOYAD">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo $t['taksit']; ?></label>
                            <select name="taksit" class="form-input">
                                <option value="peşin"><?php echo $t['peşin']; ?></option>
                                <option value="2"><?php echo $t['taksit_2']; ?></option>
                                <option value="3"><?php echo $t['taksit_3']; ?></option>
                                <option value="6"><?php echo $t['taksit_6']; ?></option>
                                <option value="9"><?php echo $t['taksit_9']; ?></option>
                                <option value="12"><?php echo $t['taksit_12']; ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Kapıda Ödeme -->
                <div class="payment-option">
                    <div class="payment-header" onclick="togglePaymentForm('kapida_odeme')">
                        <div class="payment-icon">💰</div>
                        <div class="payment-title"><?php echo $t['kapida_odeme']; ?></div>
                    </div>
                    <div class="payment-description"><?php echo $t['kapida_aciklama']; ?></div>
                    
                    <div class="payment-form" id="kapida_odemeForm">
                        <p style="color: #666; font-size: 0.9rem; background: #f9f9f9; padding: 15px; border-radius: 8px;">
                            <?php echo $dil == 'tr' 
                                ? 'Siparişiniz teslim edilirken ödemenizi nakit veya kredi kartı ile yapabilirsiniz. Kapıda ödeme için ekstra bir ücret alınmamaktadır.' 
                                : 'You can pay with cash or credit card when your order is delivered. No extra fee for cash on delivery.'; ?>
                        </p>
                    </div>
                </div>
                
                <!-- Banka Havalesi -->
                <div class="payment-option">
                    <div class="payment-header" onclick="togglePaymentForm('banka_havalesi')">
                        <div class="payment-icon">🏦</div>
                        <div class="payment-title"><?php echo $t['banka_havalesi']; ?></div>
                    </div>
                    <div class="payment-description"><?php echo $t['banka_aciklama']; ?></div>
                    
                    <div class="payment-form" id="banka_havalesiForm">
                        <div class="bank-info">
                            <div class="bank-detail">
                                <span class="bank-label"><?php echo $t['banka_adi']; ?></span>
                                <span>Ziraat Bankası</span>
                            </div>
                            <div class="bank-detail">
                                <span class="bank-label"><?php echo $t['hesap_sahibi']; ?></span>
                                <span>ÇiçekBahçesi Ltd. Şti.</span>
                            </div>
                            <div class="bank-detail">
                                <span class="bank-label"><?php echo $t['iban']; ?></span>
                                <span>TR12 0006 2000 1234 0006 1234 56</span>
                            </div>
                        </div>
                        <p style="margin-top: 15px; color: #666; font-size: 0.9rem;"><?php echo $t['aciklama_not']; ?></p>
                    </div>
                </div>
                
                <!-- Ödeme Butonu -->
                <div class="submit-payment">
                    <button type="submit" class="btn-payment">
                        ✅ <?php echo $t['siparis_ver']; ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Ödeme formu seçimi
let activeForm = 'kredi_karti';

function togglePaymentForm(formType) {
    // Tüm formları gizle
    document.querySelectorAll('.payment-form').forEach(form => {
        form.classList.remove('active');
    });
    
    // Seçilen formu göster
    const selectedForm = document.getElementById(formType + 'Form');
    if(selectedForm) {
        selectedForm.classList.add('active');
        activeForm = formType;
        
        // Ödeme tipini güncelle
        const paymentTypeInput = document.createElement('input');
        paymentTypeInput.type = 'hidden';
        paymentTypeInput.name = 'odeme_tipi';
        paymentTypeInput.value = formType;
        
        // Eski input'u kaldır
        const oldInput = document.querySelector('input[name="odeme_tipi"]');
        if(oldInput) oldInput.remove();
        
        // Forma yeni input ekle
        document.getElementById('paymentForm').appendChild(paymentTypeInput);
    }
}

// Kart numarası formatlama
document.querySelector('input[name="kart_numarasi"]')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formatted = value.replace(/(\d{4})/g, '$1 ').trim();
    e.target.value = formatted.substring(0, 19);
});

// Son kullanma tarihi formatlama
document.querySelector('input[name="son_kullanma"]')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^0-9]/g, '');
    if(value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value.substring(0, 5);
});

// Sayfa yüklendiğinde ilk formu göster
document.addEventListener('DOMContentLoaded', function() {
    togglePaymentForm('kredi_karti');
});
</script>

<?php 
// Siparişler JSON dosyasını oluştur (yoksa)
$siparisler_dosya = 'siparisler.json';
if(!file_exists($siparisler_dosya)) {
    file_put_contents($siparisler_dosya, json_encode([]));
}
require_once 'footer.php'; 
?>