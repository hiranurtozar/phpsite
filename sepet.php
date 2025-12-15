<?php
// sepet.php - DÜZENLENMİŞ VE HATASIZ VERSİYON

// 1. ÖNCE SESSION BAŞLAT
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. DİL AYARI
$dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';

// 3. HEADER'I ÇAĞIR
require_once 'header.php';

// 4. SEPET SESSION'INI KONTROL ET VE BAŞLAT
if (!isset($_SESSION['sepet'])) {
    $_SESSION['sepet'] = [];
}

// 5. MESAJ DEĞİŞKENLERİNİ BAŞLAT
$message = '';
$message_type = '';

// 6. SEPET İŞLEMLERİ
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // 6.1 TEMİZLE İŞLEMİ
    if ($action == 'temizle') {
        $_SESSION['sepet'] = [];
        unset($_SESSION['uygulanan_kupon']);
        unset($_SESSION['kullanilan_puan']);
        $_SESSION['message'] = ($dil == 'tr') ? 'Sepetiniz temizlendi!' : 'Your cart has been cleared!';
        $_SESSION['message_type'] = 'success';
        header('Location: sepet.php');
        exit();
    }
    
    // 6.2 ÖDEME İŞLEMİ
    if ($action == 'odeme') {
        if (!isset($is_logged_in) || !$is_logged_in) {
            $_SESSION['message'] = ($dil == 'tr') ? 'Ödeme yapmak için giriş yapmalısınız!' : 'You must login to make a payment!';
            $_SESSION['message_type'] = 'info';
            header('Location: auth.php');
            exit();
        } else {
            header('Location: odeme.php');
            exit();
        }
    }
    
    // 6.3 KUPON KALDIRMA
    if ($action == 'kupon_kaldir') {
        unset($_SESSION['uygulanan_kupon']);
        $_SESSION['message'] = ($dil == 'tr') ? 'Kupon kaldırıldı!' : 'Coupon removed!';
        $_SESSION['message_type'] = 'success';
        header('Location: sepet.php');
        exit();
    }
    
    // 6.4 PUAN KALDIRMA
    if ($action == 'puan_kaldir') {
        unset($_SESSION['kullanilan_puan']);
        $_SESSION['message'] = ($dil == 'tr') ? 'Puan kullanımı kaldırıldı!' : 'Point usage removed!';
        $_SESSION['message_type'] = 'success';
        header('Location: sepet.php');
        exit();
    }
    
    // 6.5 ÜRÜN İŞLEMLERİ
    if (isset($_GET['urun_id'])) {
        $urun_id = intval($_GET['urun_id']);
        
        if ($action == 'ekle') {
            // Ürün bilgilerini al
            $urun_ad = isset($_GET['urun_ad']) ? urldecode($_GET['urun_ad']) : "Ürün $urun_id";
            $urun_fiyat = isset($_GET['urun_fiyat']) ? floatval($_GET['urun_fiyat']) : rand(50, 300);
            $urun_simge = isset($_GET['urun_simge']) ? urldecode($_GET['urun_simge']) : '🌸';
            $urun_kategori = isset($_GET['urun_kategori']) ? $_GET['urun_kategori'] : 'tumu';
            
            // Ürünü sepette ara
            $urun_bulundu = false;
            foreach ($_SESSION['sepet'] as $key => &$sepet_urun) {
                if (isset($sepet_urun['id']) && $sepet_urun['id'] == $urun_id) {
                    if (!isset($sepet_urun['adet'])) {
                        $sepet_urun['adet'] = 1;
                    }
                    $sepet_urun['adet']++;
                    $urun_bulundu = true;
                    break;
                }
            }
            
            if (!$urun_bulundu) {
                // Yeni ürün ekle
                $urun_bilgisi = [
                    'id' => $urun_id,
                    'ad' => $urun_ad,
                    'fiyat' => $urun_fiyat,
                    'simge' => $urun_simge,
                    'kategori' => $urun_kategori,
                    'adet' => 1
                ];
                $_SESSION['sepet'][] = $urun_bilgisi;
            }
            
            $_SESSION['message'] = ($dil == 'tr') ? $urun_ad . ' sepete eklendi!' : $urun_ad . ' added to cart!';
            $_SESSION['message_type'] = 'success';
            
        } elseif ($action == 'azalt') {
            // Ürün adetini azalt
            foreach ($_SESSION['sepet'] as $key => &$sepet_urun) {
                if (isset($sepet_urun['id']) && $sepet_urun['id'] == $urun_id) {
                    if (!isset($sepet_urun['adet'])) {
                        $sepet_urun['adet'] = 1;
                    }
                    
                    if ($sepet_urun['adet'] > 1) {
                        $sepet_urun['adet']--;
                    } else {
                        unset($_SESSION['sepet'][$key]);
                        $_SESSION['sepet'] = array_values($_SESSION['sepet']);
                    }
                    break;
                }
            }
            
        } elseif ($action == 'arttir') {
            // Ürün adetini arttır
            foreach ($_SESSION['sepet'] as &$sepet_urun) {
                if (isset($sepet_urun['id']) && $sepet_urun['id'] == $urun_id) {
                    if (!isset($sepet_urun['adet'])) {
                        $sepet_urun['adet'] = 1;
                    }
                    $sepet_urun['adet']++;
                    break;
                }
            }
            
        } elseif ($action == 'sil') {
            // Ürünü sepetten sil
            foreach ($_SESSION['sepet'] as $key => $sepet_urun) {
                if (isset($sepet_urun['id']) && $sepet_urun['id'] == $urun_id) {
                    $urun_ad = isset($sepet_urun['ad']) ? $sepet_urun['ad'] : 'Ürün';
                    $_SESSION['message'] = ($dil == 'tr') ? $urun_ad . ' sepetten silindi!' : $urun_ad . ' removed from cart!';
                    $_SESSION['message_type'] = 'success';
                    
                    unset($_SESSION['sepet'][$key]);
                    $_SESSION['sepet'] = array_values($_SESSION['sepet']);
                    break;
                }
            }
        }
        
        header('Location: sepet.php');
        exit();
    }
}

// 7. POST İŞLEMLERİ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 7.1 KUPON UYGULAMA
    if (isset($_POST['kupon_uygula'])) {
        $kupon_kodu = trim($_POST['kupon_kodu']);
        
        // Örnek kuponlar
        $ornek_kuponlar = [
            'ILKALISVERIS' => ['indirim' => 20, 'min_sepet' => 200, 'aciklama' => 'İlk alışverişe özel 20 TL indirim'],
            '1000E100' => ['indirim' => 100, 'min_sepet' => 1000, 'aciklama' => '1000 TL ve üzeri alışverişlerde 100 TL indirim'],
            '500E50' => ['indirim' => 50, 'min_sepet' => 500, 'aciklama' => '500 TL ve üzeri alışverişlerde 50 TL indirim'],
            'FLOWER10' => ['indirim' => 10, 'min_sepet' => 100, 'aciklama' => '10 TL indirim kuponu']
        ];
        
        if (isset($ornek_kuponlar[$kupon_kodu])) {
            $kupon = $ornek_kuponlar[$kupon_kodu];
            
            // Sepet toplamını hesapla
            $toplam_tutar = 0;
            if (!empty($_SESSION['sepet'])) {
                foreach ($_SESSION['sepet'] as $urun) {
                    $adet = isset($urun['adet']) ? intval($urun['adet']) : 1;
                    $fiyat = isset($urun['fiyat']) ? floatval($urun['fiyat']) : 0;
                    $toplam_tutar += $fiyat * $adet;
                }
            }
            
            // Minimum sepet kontrolü
            if ($toplam_tutar >= $kupon['min_sepet']) {
                $_SESSION['uygulanan_kupon'] = [
                    'kod' => $kupon_kodu,
                    'indirim' => $kupon['indirim'],
                    'min_sepet' => $kupon['min_sepet'],
                    'aciklama' => $kupon['aciklama']
                ];
                $_SESSION['message'] = ($dil == 'tr') 
                    ? 'Kupon başarıyla uygulandı! ' . $kupon['indirim'] . ' TL indirim kazandınız.' 
                    : 'Coupon applied successfully! You got ' . $kupon['indirim'] . ' TL discount.';
                $_SESSION['message_type'] = 'success';
            } else {
                $eksik_tutar = $kupon['min_sepet'] - $toplam_tutar;
                $_SESSION['message'] = ($dil == 'tr') 
                    ? 'Bu kuponu kullanmak için sepet tutarınız en az ' . number_format($kupon['min_sepet'], 2) . ' TL olmalıdır. (Eksik: ' . number_format($eksik_tutar, 2) . ' TL)' 
                    : 'You need at least ' . number_format($kupon['min_sepet'], 2) . ' TL in your cart to use this coupon. (Missing: ' . number_format($eksik_tutar, 2) . ' TL)';
                $_SESSION['message_type'] = 'error';
            }
        } else {
            $_SESSION['message'] = ($dil == 'tr') ? 'Geçersiz kupon kodu!' : 'Invalid coupon code!';
            $_SESSION['message_type'] = 'error';
        }
        
        header('Location: sepet.php');
        exit();
    }
    
    // 7.2 PUAN KULLANMA
    if (isset($_POST['puan_kullan'])) {
        $kullanilacak_puan = intval($_POST['kullanilacak_puan']);
        $kullanici_puani = isset($_SESSION['kullanici_puani']) ? intval($_SESSION['kullanici_puani']) : 0;
        
        if ($kullanilacak_puan <= $kullanici_puani && $kullanilacak_puan >= 100) {
            $_SESSION['kullanilan_puan'] = $kullanilacak_puan;
            $_SESSION['message'] = ($dil == 'tr') 
                ? $kullanilacak_puan . ' puan başarıyla kullanıldı!' 
                : $kullanilacak_puan . ' points used successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = ($dil == 'tr') 
                ? 'Yetersiz puan veya minimum 100 puan kullanmalısınız!' 
                : 'Insufficient points or minimum 100 points required!';
            $_SESSION['message_type'] = 'error';
        }
        
        header('Location: sepet.php');
        exit();
    }
}

// 8. KULLANICI PUAN BİLGİSİ
$kullanici_puani = isset($_SESSION['kullanici_puani']) ? intval($_SESSION['kullanici_puani']) : 0;
$kullanilan_puan = isset($_SESSION['kullanilan_puan']) ? intval($_SESSION['kullanilan_puan']) : 0;
$uygulanan_kupon = isset($_SESSION['uygulanan_kupon']) ? $_SESSION['uygulanan_kupon'] : null;

// 9. MESAJLARI AL
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// 10. TOPLAM HESAPLA
$toplam_tutar = 0;
$toplam_adet = 0;

if (!empty($_SESSION['sepet'])) {
    foreach ($_SESSION['sepet'] as $urun) {
        $adet = isset($urun['adet']) ? intval($urun['adet']) : 1;
        $fiyat = isset($urun['fiyat']) ? floatval($urun['fiyat']) : 0;
        
        $toplam_tutar += $fiyat * $adet;
        $toplam_adet += $adet;
    }
}

// 11. KUPON İNDİRİMİ HESAPLA
$kupon_indirimi_tl = 0;
if ($uygulanan_kupon && $toplam_tutar > 0) {
    $kupon_min_sepet = isset($uygulanan_kupon['min_sepet']) ? floatval($uygulanan_kupon['min_sepet']) : 0;
    
    if ($toplam_tutar >= $kupon_min_sepet) {
        $kupon_indirimi_tl = floatval($uygulanan_kupon['indirim']);
        $kupon_indirimi_tl = min($kupon_indirimi_tl, $toplam_tutar);
    } else {
        unset($_SESSION['uygulanan_kupon']);
        $uygulanan_kupon = null;
        $kupon_indirimi_tl = 0;
    }
}

// 12. PUAN İNDİRİMİ HESAPLA (1000 puan = 100 TL)
$puan_indirimi_tl = $kullanilan_puan / 10;

// 13. İNDİRİMLİ TOPLAM
$indirimli_toplam_kdvsiz = $toplam_tutar - $kupon_indirimi_tl - $puan_indirimi_tl;
if ($indirimli_toplam_kdvsiz < 0) $indirimli_toplam_kdvsiz = 0;

// 14. KDV HESAPLA
$kdv_tutari = $indirimli_toplam_kdvsiz * 0.18;
$genel_toplam = $indirimli_toplam_kdvsiz + $kdv_tutari;

// 15. KATEGORİ İSİMLERİ
$kategori_isimleri = [
    'tr' => ['gul' => 'Gül', 'orkide' => 'Orkide', 'lale' => 'Lale', 'buket' => 'Buket', 'sukulent' => 'Sukulent', 'tumu' => 'Tümü'],
    'en' => ['gul' => 'Rose', 'orkide' => 'Orchid', 'lale' => 'Tulip', 'buket' => 'Bouquet', 'sukulent' => 'Succulent', 'tumu' => 'All']
];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($dil == 'tr') ? 'Sepetim' : 'My Cart'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* STİL TANIMLAMALARI */
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .cart-container { margin-top: 20px; }
        .cart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #ffeef2; }
        
        .cart-items { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1); margin-bottom: 30px; }
        .cart-item { display: flex; align-items: center; gap: 20px; padding: 20px 0; border-bottom: 1px solid #ffeef2; }
        .cart-item:last-child { border-bottom: none; }
        
        .item-image { width: 80px; height: 80px; background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 40px; color: #ff6b9d; }
        .item-info { flex: 1; }
        .item-name { font-size: 1.2rem; font-weight: 600; color: #333; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; }
        .item-category { font-size: 0.8rem; background: #ffeef2; color: #ff6b9d; padding: 3px 10px; border-radius: 12px; font-weight: 500; }
        .item-price { color: #ff6b9d; font-weight: 700; font-size: 1.1rem; }
        
        .item-quantity { display: flex; align-items: center; gap: 10px; }
        .quantity-btn { background: #f5f5f5; color: #333; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; font-weight: bold; font-size: 1.2rem; transition: all 0.3s; }
        .quantity-btn:hover { background: #ff6b9d; color: white; }
        .quantity-number { font-weight: 600; min-width: 30px; text-align: center; }
        
        .item-total { min-width: 100px; text-align: right; }
        .total-price { font-weight: 700; font-size: 1.2rem; color: #333; }
        
        .item-remove { margin-left: 20px; }
        .remove-btn { color: #f44336; text-decoration: none; padding: 8px; border-radius: 5px; transition: all 0.3s; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; }
        .remove-btn:hover { background: #ffebee; }
        
        .order-summary { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1); position: sticky; top: 100px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ffeef2; }
        .summary-row:last-child { border-bottom: none; }
        .summary-label { color: #666; }
        .summary-value { font-weight: 600; }
        .total-row { font-size: 1.2rem; font-weight: 700; color: #ff6b9d; }
        
        .checkout-btn { display: block; width: 100%; padding: 15px; background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%); color: white; border: none; border-radius: 10px; font-size: 1.1rem; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; margin-top: 20px; transition: all 0.3s; }
        .checkout-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 107, 157, 0.3); }
        
        .empty-cart { text-align: center; padding: 60px 20px; background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1); margin-top: 20px; }
        .empty-cart i { font-size: 80px; color: #ffeef2; margin-bottom: 20px; }
        .empty-cart h3 { color: #ff6b9d; margin-bottom: 10px; }
        .empty-cart p { color: #666; margin-bottom: 30px; }
        
        /* Kategori renkleri */
        .category-gul { background: #ffebee; color: #d32f2f; }
        .category-orkide { background: #f3e5f5; color: #7b1fa2; }
        .category-lale { background: #fff3e0; color: #f57c00; }
        .category-buket { background: #e8f5e9; color: #388e3c; }
        .category-sukulent { background: #e8eaf6; color: #303f9f; }
        .category-tumu { background: #e3f2fd; color: #1976d2; }
        
        /* Kupon ve Puan Bölümleri */
        .discount-bubbles { display: flex; flex-wrap: wrap; gap: 15px; margin: 20px 0; background: #f9f9f9; padding: 20px; border-radius: 10px; border: 2px dashed #ffeef2; }
        .discount-bubble { flex: 1; min-width: 250px; }
        .bubble-content { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 3px 15px rgba(0,0,0,0.08); border: 2px solid #ffeef2; transition: all 0.3s; }
        .bubble-content:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(255, 107, 157, 0.15); }
        .bubble-title { display: flex; align-items: center; gap: 10px; color: #333; margin-bottom: 15px; font-size: 1.1rem; font-weight: 600; }
        .bubble-title i { color: #ff6b9d; font-size: 1.2rem; }
        
        .coupon-form, .use-points-form { display: flex; gap: 10px; }
        .coupon-input, .points-input { flex: 1; padding: 12px 15px; border: 2px solid #ffeef2; border-radius: 8px; font-size: 14px; transition: all 0.3s; }
        .coupon-input:focus, .points-input:focus { border-color: #ff6b9d; outline: none; }
        
        .apply-btn { background: #ff6b9d; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .apply-btn:hover { background: #ff4d87; }
        
        .points-display { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; background: #f8f9fa; padding: 10px 15px; border-radius: 8px; }
        .points-count { font-weight: 700; color: #ff6b9d; font-size: 1.1rem; }
        .points-info { font-size: 12px; color: #666; margin-top: 5px; }
        
        .applied-discounts { margin-top: 15px; padding: 15px; background: #f0fff4; border-radius: 8px; border: 2px solid #c8e6c9; }
        .applied-discount-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #e8f5e9; }
        .applied-discount-item:last-child { border-bottom: none; }
        .discount-type { display: flex; align-items: center; gap: 10px; }
        .discount-amount { font-weight: 700; color: #4CAF50; }
        .remove-discount { color: #f44336; background: none; border: none; cursor: pointer; font-size: 1.1rem; padding: 5px; margin-left: 10px; transition: all 0.3s; }
        .remove-discount:hover { transform: scale(1.2); }
        
        .indirim-row { color: #4CAF50; font-weight: 600; }
        .indirim-row .summary-value { color: #4CAF50; }
        
        /* Mesaj stili */
        .message { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500; animation: slideIn 0.3s ease; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .message.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .cart-item { flex-wrap: wrap; gap: 10px; }
            .item-image { width: 60px; height: 60px; font-size: 30px; }
            .item-info { order: 2; }
            .item-quantity { order: 3; }
            .item-total { order: 4; text-align: left; }
            .item-remove { order: 5; }
            .cart-header { flex-direction: column; gap: 10px; text-align: center; }
            .cart-header h1 { margin-bottom: 10px; }
            .discount-bubbles { flex-direction: column; }
            .discount-bubble { min-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="cart-container">
            <div class="cart-header">
                <h1 style="color: #ff6b9d;">
                    <i class="fas fa-shopping-cart"></i> 
                    <?php echo isset($text_selected['sepet']) ? $text_selected['sepet'] : (($dil == 'tr') ? 'Sepetim' : 'My Cart'); ?>
                </h1>
                
                <?php if ($toplam_adet > 0): ?>
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <span style="background: #ff6b9d; color: white; padding: 8px 15px; border-radius: 20px; font-weight: 600;">
                            <?php echo $toplam_adet; ?> <?php echo ($dil == 'tr') ? 'ürün' : 'products'; ?>
                        </span>
                        <a href="sepet.php?action=temizle" style="background: #f44336; color: white; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s;"
                           onclick="return confirm('<?php echo ($dil == 'tr') ? "Sepetinizi temizlemek istediğinize emin misiniz?" : "Are you sure you want to clear your cart?"; ?>')">
                            <i class="fas fa-trash"></i> 
                            <?php echo ($dil == 'tr') ? 'Sepeti Temizle' : 'Clear Cart'; ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : ($message_type == 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                    <?php 
                    // ARRAY TO STRING HATASI DÜZELTMESİ
                    if (is_array($message)) {
                        echo implode('<br>', $message);
                    } else {
                        echo htmlspecialchars($message);
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($_SESSION['sepet'])): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3><?php echo ($dil == 'tr') ? 'Sepetiniz boş' : 'Your cart is empty'; ?></h3>
                    <p>
                        <?php echo ($dil == 'tr') 
                            ? 'Alışverişe başlamak için ürünleri sepete ekleyin.' 
                            : 'Add products to your cart to start shopping.'; 
                        ?>
                    </p>
                    <a href="urunler.php" class="checkout-btn" style="max-width: 300px; margin: 0 auto;">
                        <i class="fas fa-store"></i> 
                        <?php echo ($dil == 'tr') ? 'Alışverişe Başla' : 'Start Shopping'; ?>
                    </a>
                </div>
            <?php else: ?>
                <!-- KUPON VE PUAN BÖLÜMLERİ -->
                <div class="discount-bubbles">
                    <!-- Kupon Bölümü -->
                    <div class="discount-bubble">
                        <div class="bubble-content">
                            <div class="bubble-title">
                                <i class="fas fa-tag"></i>
                                <?php echo ($dil == 'tr') ? 'Kupon Kodu' : 'Coupon Code'; ?>
                            </div>
                            
                            <?php if ($uygulanan_kupon): ?>
                                <div class="applied-discounts">
                                    <div class="applied-discount-item">
                                        <div class="discount-type">
                                            <i class="fas fa-tag" style="color: #ff6b9d;"></i>
                                            <span>
                                                <?php echo htmlspecialchars($uygulanan_kupon['kod']); ?> 
                                                (<?php echo $uygulanan_kupon['indirim']; ?> TL <?php echo ($dil == 'tr') ? 'indirim' : 'discount'; ?>)
                                            </span>
                                        </div>
                                        <div style="display: flex; align-items: center;">
                                            <span class="discount-amount">-<?php echo number_format($kupon_indirimi_tl, 2); ?> TL</span>
                                            <a href="sepet.php?action=kupon_kaldir" class="remove-discount" title="<?php echo ($dil == 'tr') ? 'Kaldır' : 'Remove'; ?>">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <form method="POST" class="coupon-form">
                                    <input type="text" name="kupon_kodu" class="coupon-input" 
                                           placeholder="<?php echo ($dil == 'tr') ? 'Kupon kodunuzu girin' : 'Enter your coupon code'; ?>"
                                           required>
                                    <button type="submit" name="kupon_uygula" class="apply-btn">
                                        <?php echo ($dil == 'tr') ? 'Uygula' : 'Apply'; ?>
                                    </button>
                                </form>
                                <div class="points-info">
                                    <?php echo ($dil == 'tr') 
                                        ? 'Geçerli kuponlar: ILKALISVERIS (20 TL), 1000E100 (100 TL), 500E50 (50 TL), FLOWER10 (10 TL)' 
                                        : 'Valid coupons: ILKALISVERIS (20 TL), 1000E100 (100 TL), 500E50 (50 TL), FLOWER10 (10 TL)'; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Puan Bölümü -->
                    <div class="discount-bubble">
                        <div class="bubble-content">
                            <div class="bubble-title">
                                <i class="fas fa-star"></i>
                                <?php echo ($dil == 'tr') ? 'Puanlarım' : 'My Points'; ?>
                            </div>
                            
                            <div class="points-display">
                                <div>
                                    <div><?php echo ($dil == 'tr') ? 'Mevcut Puan' : 'Available Points'; ?></div>
                                    <div class="points-count"><?php echo number_format($kullanici_puani); ?> <?php echo ($dil == 'tr') ? 'puan' : 'points'; ?></div>
                                </div>
                                <div style="text-align: right;">
                                    <div><?php echo ($dil == 'tr') ? 'Değeri' : 'Value'; ?></div>
                                    <div class="points-count"><?php echo number_format($kullanici_puani / 10, 2); ?> TL</div>
                                </div>
                            </div>
                            
                            <?php if ($kullanici_puani >= 100): ?>
                                <?php if ($kullanilan_puan > 0): ?>
                                    <div class="applied-discounts">
                                        <div class="applied-discount-item">
                                            <div class="discount-type">
                                                <i class="fas fa-star" style="color: #FFD700;"></i>
                                                <span>
                                                    <?php echo $kullanilan_puan; ?> <?php echo ($dil == 'tr') ? 'puan kullanıldı' : 'points used'; ?>
                                                </span>
                                            </div>
                                            <div style="display: flex; align-items: center;">
                                                <span class="discount-amount">-<?php echo number_format($puan_indirimi_tl, 2); ?> TL</span>
                                                <a href="sepet.php?action=puan_kaldir" class="remove-discount" title="<?php echo ($dil == 'tr') ? 'Kaldır' : 'Remove'; ?>">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" class="use-points-form">
                                        <input type="number" name="kullanilacak_puan" class="points-input" 
                                               min="100" max="<?php echo $kullanici_puani; ?>" 
                                               step="100" value="100"
                                               placeholder="<?php echo ($dil == 'tr') ? 'Kullanılacak puan' : 'Points to use'; ?>"
                                               required>
                                        <button type="submit" name="puan_kullan" class="apply-btn">
                                            <?php echo ($dil == 'tr') ? 'Kullan' : 'Use'; ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <div class="points-info">
                                    <?php echo ($dil == 'tr') 
                                        ? '1000 puan = 100 TL indirim • En az 100 puan kullanabilirsiniz' 
                                        : '1000 points = 100 TL discount • Minimum 100 points required'; ?>
                                </div>
                            <?php else: ?>
                                <p style="color: #666; font-size: 14px; text-align: center; padding: 10px; background: #f5f5f5; border-radius: 8px;">
                                    <?php echo ($dil == 'tr') 
                                        ? 'Puan kullanmak için en az 100 puanınız olmalıdır.<br>Her 10 TL alışverişte 1 puan kazanırsınız.' 
                                        : 'You need at least 100 points to use them.<br>You earn 1 point for every 10 TL purchase.'; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                    <!-- SEPET ÜRÜNLERİ -->
                    <div class="cart-items">
                        <h2 style="color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ffeef2;">
                            <i class="fas fa-shopping-basket"></i> 
                            <?php echo ($dil == 'tr') ? 'Sepetim' : 'My Cart'; ?> 
                            (<?php echo $toplam_adet; ?> <?php echo ($dil == 'tr') ? 'ürün' : 'products'; ?>)
                        </h2>
                        
                        <?php foreach ($_SESSION['sepet'] as $urun): 
                            $urun_adet = isset($urun['adet']) ? intval($urun['adet']) : 1;
                            $urun_fiyat = isset($urun['fiyat']) ? floatval($urun['fiyat']) : 0;
                            $urun_toplam = $urun_adet * $urun_fiyat;
                            $urun_ad = isset($urun['ad']) ? htmlspecialchars($urun['ad']) : 'Ürün';
                            $urun_simge = isset($urun['simge']) ? $urun['simge'] : '🌸';
                            $urun_kategori = isset($urun['kategori']) ? $urun['kategori'] : 'tumu';
                            
                            $kategori_ad = isset($kategori_isimleri[$dil][$urun_kategori]) 
                                ? $kategori_isimleri[$dil][$urun_kategori] 
                                : $urun_kategori;
                        ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <?php echo htmlspecialchars($urun_simge); ?>
                                </div>
                                
                                <div class="item-info">
                                    <div class="item-name">
                                        <?php echo htmlspecialchars($urun_simge) . ' ' . $urun_ad; ?>
                                        <span class="item-category category-<?php echo htmlspecialchars($urun_kategori); ?>">
                                            <?php echo htmlspecialchars($kategori_ad); ?>
                                        </span>
                                    </div>
                                    <div class="item-price"><?php echo number_format($urun_fiyat, 2); ?> TL</div>
                                </div>
                                
                                <div class="item-quantity">
                                    <a href="sepet.php?action=azalt&urun_id=<?php echo $urun['id']; ?>" class="quantity-btn">-</a>
                                    <span class="quantity-number"><?php echo $urun_adet; ?></span>
                                    <a href="sepet.php?action=arttir&urun_id=<?php echo $urun['id']; ?>" class="quantity-btn">+</a>
                                </div>
                                
                                <div class="item-total">
                                    <div class="total-price"><?php echo number_format($urun_toplam, 2); ?> TL</div>
                                    <div style="font-size: 0.9rem; color: #666;">
                                        <?php echo $urun_adet; ?> x <?php echo number_format($urun_fiyat, 2); ?> TL
                                    </div>
                                </div>
                                
                                <div class="item-remove">
                                    <a href="sepet.php?action=sil&urun_id=<?php echo $urun['id']; ?>" class="remove-btn"
                                       onclick="return confirm('<?php echo ($dil == "tr") ? "Ürünü sepetten silmek istediğinize emin misiniz?" : "Are you sure you want to remove this item from cart?"; ?>')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- ÖDEME ÖZETİ -->
                    <div class="order-summary">
                        <h2 style="color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ffeef2;">
                            <i class="fas fa-receipt"></i> 
                            <?php echo ($dil == 'tr') ? 'Sipariş Özeti' : 'Order Summary'; ?>
                        </h2>
                        
                        <div class="summary-row">
                            <span class="summary-label"><?php echo ($dil == 'tr') ? 'Ara Toplam:' : 'Subtotal:'; ?></span>
                            <span class="summary-value"><?php echo number_format($toplam_tutar, 2); ?> TL</span>
                        </div>
                        
                        <?php if ($kupon_indirimi_tl > 0): ?>
                            <div class="summary-row indirim-row">
                                <span class="summary-label">
                                    <i class="fas fa-tag"></i> 
                                    <?php echo ($dil == 'tr') ? 'Kupon İndirimi:' : 'Coupon Discount:'; ?>
                                    <?php if ($uygulanan_kupon): ?>
                                        (<?php echo htmlspecialchars($uygulanan_kupon['kod']); ?>)
                                    <?php endif; ?>
                                </span>
                                <span class="summary-value">-<?php echo number_format($kupon_indirimi_tl, 2); ?> TL</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($puan_indirimi_tl > 0): ?>
                            <div class="summary-row indirim-row">
                                <span class="summary-label">
                                    <i class="fas fa-star"></i> 
                                    <?php echo ($dil == 'tr') ? 'Puan İndirimi:' : 'Points Discount:'; ?>
                                </span>
                                <span class="summary-value">-<?php echo number_format($puan_indirimi_tl, 2); ?> TL</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="summary-row">
                            <span class="summary-label"><?php echo ($dil == 'tr') ? 'Kargo:' : 'Shipping:'; ?></span>
                            <span class="summary-value" style="color: #4CAF50;">
                                <?php echo ($dil == 'tr') ? 'Ücretsiz' : 'Free'; ?>
                            </span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label"><?php echo ($dil == 'tr') ? 'KDV (%18):' : 'VAT (18%):'; ?></span>
                            <span class="summary-value"><?php echo number_format($kdv_tutari, 2); ?> TL</span>
                        </div>
                        
                        <div class="summary-row total-row">
                            <span><?php echo ($dil == 'tr') ? 'GENEL TOPLAM:' : 'GRAND TOTAL:'; ?></span>
                            <span><?php echo number_format($genel_toplam, 2); ?> TL</span>
                        </div>
                        
                        <?php if ($toplam_adet > 0): ?>
                            <a href="sepet.php?action=odeme" class="checkout-btn">
                                <i class="fas fa-lock"></i> 
                                <?php echo ($dil == 'tr') ? 'Güvenli Ödemeye Geç' : 'Proceed to Secure Checkout'; ?>
                            </a>
                        <?php endif; ?>
                        
                        <a href="urunler.php" style="display: block; text-align: center; background: white; color: #ff6b9d; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 600; border: 2px solid #ff6b9d; transition: all 0.3s; margin-top: 15px;"
                           onmouseover="this.style.background='#ff6b9d'; this.style.color='white'"
                           onmouseout="this.style.background='white'; this.style.color='#ff6b9d'">
                            <i class="fas fa-store"></i> 
                            <?php echo ($dil == 'tr') ? 'Alışverişe Devam Et' : 'Continue Shopping'; ?>
                        </a>
                        
                        <div style="padding-top: 20px; margin-top: 20px; border-top: 2px solid #ffeef2;">
                            <p style="color: #666; font-size: 14px; margin-bottom: 10px;">
                                <i class="fas fa-shield-alt" style="color: #4CAF50;"></i>
                                256-bit SSL <?php echo ($dil == 'tr') ? 'Güvenli Ödeme' : 'Secure Payment'; ?>
                            </p>
                            <p style="color: #666; font-size: 14px;">
                                <i class="fas fa-truck" style="color: #2196f3;"></i>
                                <?php echo ($dil == 'tr') ? '2-3 iş günü içinde teslimat' : 'Delivery within 2-3 business days'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Miktar değişikliklerini güncelle
        function updateQuantity(urunId, change) {
            // JavaScript ile miktar güncelleme
            window.location.href = 'sepet.php?action=' + change + '&urun_id=' + urunId;
        }
        
        // Temizleme onayı
        function confirmClear() {
            return confirm('<?php echo ($dil == "tr") ? "Sepetinizi temizlemek istediğinize emin misiniz?" : "Are you sure you want to clear your cart?"; ?>');
        }
        
        // Silme onayı
        function confirmRemove(productName) {
            return confirm('<?php echo ($dil == "tr") ? "Ürünü sepetten silmek istediğinize emin misiniz?" : "Are you sure you want to remove this item from cart?"; ?>' + (productName ? ': ' + productName : ''));
        }
    </script>
</body>
</html>