<?php
require_once 'header.php';

// Giriş kontrolü
if (!$is_logged_in) {
    echo '<script>showLoginRequired("sepet")</script>';
    exit();
}

// Sepet işlemleri
if (isset($_GET['action']) && isset($_GET['urun_id'])) {
    $urun_id = intval($_GET['urun_id']);
    $action = $_GET['action'];
    
    if ($action == 'ekle') {
        // Ürünü sepete ekle
        $urun_bulundu = false;
        foreach ($_SESSION['sepet'] as $key => &$sepet_urun) {
            if (isset($sepet_urun['id']) && $sepet_urun['id'] == $urun_id) {
                // adet anahtarını kontrol et, yoksa 1 yap
                if (!isset($sepet_urun['adet'])) {
                    $sepet_urun['adet'] = 1;
                }
                $sepet_urun['adet']++;
                $urun_bulundu = true;
                break;
            }
        }
        
        if (!$urun_bulundu) {
            // Örnek ürün bilgisi
            $urun_bilgisi = [
                'id' => $urun_id,
                'ad' => "Ürün $urun_id",
                'fiyat' => rand(50, 300),
                'adet' => 1
            ];
            $_SESSION['sepet'][] = $urun_bilgisi;
        }
        
        $_SESSION['message'] = 'Ürün sepete eklendi!';
        $_SESSION['message_type'] = 'success';
        
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'sepet.php'));
        exit();
        
    } elseif ($action == 'azalt') {
        // Ürün adetini azalt
        foreach ($_SESSION['sepet'] as $key => &$sepet_urun) {
            if (isset($sepet_urun['id']) && $sepet_urun['id'] == $urun_id) {
                // adet anahtarını kontrol et
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
                // adet anahtarını kontrol et
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
                unset($_SESSION['sepet'][$key]);
                $_SESSION['sepet'] = array_values($_SESSION['sepet']);
                break;
            }
        }
        $_SESSION['message'] = 'Ürün sepetten silindi!';
        $_SESSION['message_type'] = 'success';
    }
    
    header('Location: sepet.php');
    exit();
}

// Sepeti temizle
if (isset($_GET['action']) && $_GET['action'] == 'temizle') {
    $_SESSION['sepet'] = [];
    $_SESSION['message'] = 'Sepetiniz temizlendi!';
    $_SESSION['message_type'] = 'success';
    header('Location: sepet.php');
    exit();
}

// Toplam hesapla - GÜVENLİ VERSİYON
$toplam_tutar = 0;
$toplam_adet = 0;

if (!empty($_SESSION['sepet'])) {
    foreach ($_SESSION['sepet'] as $urun) {
        // Tüm gerekli anahtarların var olduğundan emin ol
        $adet = isset($urun['adet']) ? intval($urun['adet']) : 1;
        $fiyat = isset($urun['fiyat']) ? floatval($urun['fiyat']) : 0;
        
        $toplam_tutar += $fiyat * $adet;
        $toplam_adet += $adet;
    }
}

// Mesajları göster
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>

<style>
    /* SEPET SAYFASI STİLLERİ */
    .cart-container {
        margin-top: 20px;
    }
    
    .cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #ffeef2;
    }
    
    .cart-items {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
        margin-bottom: 30px;
    }
    
    .cart-item {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px 0;
        border-bottom: 1px solid #ffeef2;
    }
    
    .cart-item:last-child {
        border-bottom: none;
    }
    
    .item-image {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: #ff6b9d;
    }
    
    .item-info {
        flex: 1;
    }
    
    .item-name {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .item-price {
        color: #ff6b9d;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .item-quantity {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .quantity-btn {
        background: #f5f5f5;
        color: #333;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.2rem;
        transition: all 0.3s;
    }
    
    .quantity-btn:hover {
        background: #ff6b9d;
        color: white;
    }
    
    .quantity-number {
        font-weight: 600;
        min-width: 30px;
        text-align: center;
    }
    
    .item-total {
        min-width: 100px;
        text-align: right;
    }
    
    .total-price {
        font-weight: 700;
        font-size: 1.2rem;
        color: #333;
    }
    
    .item-remove {
        margin-left: 20px;
    }
    
    .remove-btn {
        color: #f44336;
        text-decoration: none;
        padding: 8px;
        border-radius: 5px;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
    }
    
    .remove-btn:hover {
        background: #ffebee;
    }
    
    .order-summary {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
        position: sticky;
        top: 100px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #ffeef2;
    }
    
    .summary-row:last-child {
        border-bottom: none;
    }
    
    .summary-label {
        color: #666;
    }
    
    .summary-value {
        font-weight: 600;
    }
    
    .total-row {
        font-size: 1.2rem;
        font-weight: 700;
        color: #ff6b9d;
    }
    
    .checkout-btn {
        display: block;
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        margin-top: 20px;
        transition: all 0.3s;
    }
    
    .checkout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 107, 157, 0.3);
    }
    
    .empty-cart {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
        margin-top: 20px;
    }
    
    .empty-cart i {
        font-size: 80px;
        color: #ffeef2;
        margin-bottom: 20px;
    }
    
    .empty-cart h3 {
        color: #ff6b9d;
        margin-bottom: 10px;
    }
    
    .empty-cart p {
        color: #666;
        margin-bottom: 30px;
    }
</style>

<div class="container">
    <div class="cart-container">
        <div class="cart-header">
            <h1 style="color: #ff6b9d;">
                <i class="fas fa-shopping-cart"></i> <?php echo $text_selected['sepet']; ?>
            </h1>
            
            <?php if ($toplam_adet > 0): ?>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <span style="background: #ff6b9d; color: white; padding: 8px 15px; border-radius: 20px; font-weight: 600;">
                        <?php echo $toplam_adet; ?> ürün
                    </span>
                    <a href="sepet.php?action=temizle" style="
                        background: #f44336;
                        color: white;
                        padding: 8px 15px;
                        border-radius: 8px;
                        text-decoration: none;
                        font-weight: 600;
                        transition: all 0.3s;
                    " onclick="return confirm('Sepetinizi temizlemek istediğinize emin misiniz?')"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 10px rgba(244, 67, 54, 0.3)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <i class="fas fa-trash"></i> Sepeti Temizle
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'info-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($_SESSION['sepet'])): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3><?php echo $dil == 'tr' ? 'Sepetiniz boş' : 'Your cart is empty'; ?></h3>
                <p>
                    <?php echo $dil == 'tr' 
                        ? 'Alışverişe başlamak için ürünleri sepete ekleyin.' 
                        : 'Add products to your cart to start shopping.'; 
                    ?>
                </p>
                <a href="urunler.php" class="checkout-btn" style="max-width: 300px; margin: 0 auto;">
                    <i class="fas fa-store"></i> 
                    <?php echo $dil == 'tr' ? 'Alışverişe Başla' : 'Start Shopping'; ?>
                </a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <!-- SEPET ÜRÜNLERİ -->
                <div class="cart-items">
                    <h2 style="color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ffeef2;">
                        <i class="fas fa-shopping-basket"></i> 
                        <?php echo $dil == 'tr' ? 'Sepetim' : 'My Cart'; ?> 
                        (<?php echo $toplam_adet; ?> <?php echo $dil == 'tr' ? 'ürün' : 'products'; ?>)
                    </h2>
                    
                    <?php foreach ($_SESSION['sepet'] as $urun): 
                        $urun_adet = isset($urun['adet']) ? intval($urun['adet']) : 1;
                        $urun_fiyat = isset($urun['fiyat']) ? floatval($urun['fiyat']) : 0;
                        $urun_toplam = $urun_adet * $urun_fiyat;
                        $urun_ad = isset($urun['ad']) ? htmlspecialchars($urun['ad']) : 'Ürün';
                    ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <i class="fas fa-flower"></i>
                            </div>
                            
                            <div class="item-info">
                                <div class="item-name"><?php echo $urun_ad; ?></div>
                                <div class="item-price"><?php echo number_format($urun_fiyat, 2); ?> TL</div>
                            </div>
                            
                            <div class="item-quantity">
                                <a href="sepet.php?action=azalt&urun_id=<?php echo $urun['id']; ?>" class="quantity-btn">
                                    -
                                </a>
                                <span class="quantity-number"><?php echo $urun_adet; ?></span>
                                <a href="sepet.php?action=arttir&urun_id=<?php echo $urun['id']; ?>" class="quantity-btn">
                                    +
                                </a>
                            </div>
                            
                            <div class="item-total">
                                <div class="total-price"><?php echo number_format($urun_toplam, 2); ?> TL</div>
                            </div>
                            
                            <div class="item-remove">
                                 <a href="sepet.php?action=sil&urun_id=<?php echo $urun['id']; ?>" class="remove-btn"
                                      onclick="return confirm('<?php echo $dil == "tr" ? "Ürünü sepetten silmek istediğinize emin misiniz?" : "Are you sure you want to remove this item from cart?"; ?>')">
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
                        <?php echo $dil == 'tr' ? 'Sipariş Özeti' : 'Order Summary'; ?>
                    </h2>
                    
                    <div class="summary-row">
                        <span class="summary-label"><?php echo $dil == 'tr' ? 'Ara Toplam:' : 'Subtotal:'; ?></span>
                        <span class="summary-value"><?php echo number_format($toplam_tutar, 2); ?> TL</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label"><?php echo $dil == 'tr' ? 'Kargo:' : 'Shipping:'; ?></span>
                        <span class="summary-value" style="color: #4CAF50;">
                            <?php echo $dil == 'tr' ? 'Ücretsiz' : 'Free'; ?>
                        </span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label"><?php echo $dil == 'tr' ? 'KDV (%18):' : 'VAT (18%):'; ?></span>
                        <span class="summary-value"><?php echo number_format($toplam_tutar * 0.18, 2); ?> TL</span>
                    </div>
                    
                    <div class="summary-row total-row">
                        <span><?php echo $dil == 'tr' ? 'Toplam:' : 'Total:'; ?></span>
                        <span><?php echo number_format($toplam_tutar * 1.18, 2); ?> TL</span>
                    </div>
                    
                    <?php if ($toplam_adet > 0): ?>
                        <a href="odeme.php" class="checkout-btn">
                            <i class="fas fa-lock"></i> 
                            <?php echo $dil == 'tr' ? 'Güvenli Ödemeye Geç' : 'Proceed to Secure Checkout'; ?>
                        </a>
                    <?php endif; ?>
                    
                    <a href="urunler.php" style="
                        display: block;
                        text-align: center;
                        background: white;
                        color: #ff6b9d;
                        padding: 12px;
                        border-radius: 8px;
                        text-decoration: none;
                        font-weight: 600;
                        border: 2px solid #ff6b9d;
                        transition: all 0.3s;
                        margin-top: 15px;
                    " onmouseover="this.style.background='#ff6b9d'; this.style.color='white'"
                       onmouseout="this.style.background='white'; this.style.color='#ff6b9d'">
                        <i class="fas fa-store"></i> 
                        <?php echo $dil == 'tr' ? 'Alışverişe Devam Et' : 'Continue Shopping'; ?>
                    </a>
                    
                    <div style="padding-top: 20px; margin-top: 20px; border-top: 2px solid #ffeef2;">
                        <p style="color: #666; font-size: 14px; margin-bottom: 10px;">
                            <i class="fas fa-shield-alt" style="color: #4CAF50;"></i>
                            256-bit SSL <?php echo $dil == 'tr' ? 'Güvenli Ödeme' : 'Secure Payment'; ?>
                        </p>
                        <p style="color: #666; font-size: 14px;">
                            <i class="fas fa-truck" style="color: #2196f3;"></i>
                            <?php echo $dil == 'tr' ? '2-3 iş günü içinde teslimat' : 'Delivery within 2-3 business days'; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>