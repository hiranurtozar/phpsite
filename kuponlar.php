<?php
require_once 'cicek.php';
require_once 'header.php';

/* -------------------------
   Kupon sistemi için ek metinler
-------------------------- */
$text_selected['son_kullanma'] = $text_selected['son_kullanma'] ?? ($dil == 'tr' ? 'Son Kullanma' : 'Expiry Date');
$text_selected['kullan'] = $text_selected['kullan'] ?? ($dil == 'tr' ? 'Kullan' : 'Use');
$text_selected['kullanildi'] = $text_selected['kullanildi'] ?? ($dil == 'tr' ? 'Kullanıldı' : 'Used');
$text_selected['min_sepet'] = $text_selected['min_sepet'] ?? ($dil == 'tr' ? 'Min. Sepet' : 'Min. Cart');
$text_selected['tek_kullanim'] = $text_selected['tek_kullanim'] ?? ($dil == 'tr' ? 'Tek Kullanımlık' : 'Single Use');
$text_selected['sepet_yetersiz'] = $text_selected['sepet_yetersiz'] ?? ($dil == 'tr' ? 'Sepet Yetersiz' : 'Cart Insufficient');
$text_selected['ekle'] = $text_selected['ekle'] ?? ($dil == 'tr' ? 'ekleyin' : 'add');

// Kuponları session'dan al
$kuponlar = $_SESSION['kullanici_kuponlari'] ?? [];

// Sepet toplamını hesapla
$sepetToplam = 0;
if(isset($_SESSION['sepet']) && is_array($_SESSION['sepet'])) {
    foreach($_SESSION['sepet'] as $urun) {
        $fiyat = floatval($urun['fiyat'] ?? 0);
        $adet = intval($urun['adet'] ?? 1);
        $sepetToplam += $fiyat * $adet;
    }
}
$_SESSION['sepet_toplam'] = $sepetToplam;

/* -------------------------
   ÖRNEK KUPONLARI EKLE (Demo için)
-------------------------- */
if(empty($kuponlar) && $is_logged_in && !$is_admin) {
    $ornekKuponlar = [
        [
            'kod' => '1000E100',
            'indirim' => 100,
            'min_sepet' => 1000,
            'aciklama' => ($dil == 'tr' ? '1000 TL ve üzeri alışverişlerde 100 TL indirim' : '100 TL discount on 1000 TL+ purchases'),
            'son_kullanma' => date('Y-m-d', strtotime('+30 days')),
            'durum' => 'aktif',
            'kullanildi' => false,
            'tek_kullanim' => true,
            'kullanma_tarihi' => null
        ],
        [
            'kod' => '500E50',
            'indirim' => 50,
            'min_sepet' => 500,
            'aciklama' => ($dil == 'tr' ? '500 TL ve üzeri alışverişlerde 50 TL indirim' : '50 TL discount on 500 TL+ purchases'),
            'son_kullanma' => date('Y-m-d', strtotime('+15 days')),
            'durum' => 'aktif',
            'kullanildi' => false,
            'tek_kullanim' => true,
            'kullanma_tarihi' => null
        ],
        [
            'kod' => '2000E200',
            'indirim' => 200,
            'min_sepet' => 2000,
            'aciklama' => ($dil == 'tr' ? '2000 TL ve üzeri alışverişlerde 200 TL indirim' : '200 TL discount on 2000 TL+ purchases'),
            'son_kullanma' => date('Y-m-d', strtotime('+60 days')),
            'durum' => 'aktif',
            'kullanildi' => false,
            'tek_kullanim' => true,
            'kullanma_tarihi' => null
        ],
        [
            'kod' => 'ILKALISVERIS',
            'indirim' => 20,
            'min_sepet' => 200,
            'aciklama' => ($dil == 'tr' ? 'İlk alışverişe özel 20 TL indirim' : '20 TL discount for first purchase'),
            'son_kullanma' => date('Y-m-d', strtotime('+90 days')),
            'durum' => 'aktif',
            'kullanildi' => false,
            'tek_kullanim' => true,
            'kullanma_tarihi' => null
        ]
    ];
    
    $_SESSION['kullanici_kuponlari'] = $ornekKuponlar;
    $kuponlar = $ornekKuponlar;
}

/* -------------------------
   KUPON KULLANMA İŞLEMİ
-------------------------- */
if(isset($_GET['kupon_kullan']) && $is_logged_in && !$is_admin) {
    $kuponKodu = urldecode($_GET['kupon_kullan']);
    $kullanildi = false;
    
    foreach($kuponlar as &$kupon) {
        if($kupon['kod'] == $kuponKodu) {
            // Kupon durum kontrolü
            if($kupon['durum'] != 'aktif' || $kupon['kullanildi']) {
                $_SESSION['hata'] = ($dil == 'tr' 
                    ? 'Bu kupon zaten kullanılmış veya aktif değil!' 
                    : 'This coupon has already been used or is not active!');
                break;
            }
            
            // Sepet minimum tutar kontrolü
            if($sepetToplam < $kupon['min_sepet']) {
                $requiredAmount = $kupon['min_sepet'] - $sepetToplam;
                $_SESSION['hata'] = ($dil == 'tr'
                    ? "Bu kuponu kullanmak için sepete " . number_format($requiredAmount, 2) . " TL daha eklemelisiniz!"
                    : "You need to add " . number_format($requiredAmount, 2) . " TL more to your cart to use this coupon!");
                break;
            }
            
            // Kuponu uygula
            $kupon['durum'] = 'kullanildi';
            $kupon['kullanildi'] = true;
            $kupon['kullanma_tarihi'] = date('Y-m-d H:i:s');
            
            // Sepete indirimi uygula
            $_SESSION['uygulanan_kupon'] = [
                'kod' => $kuponKodu,
                'indirim' => $kupon['indirim'],
                'min_sepet' => $kupon['min_sepet'],
                'aciklama' => $kupon['aciklama']
            ];
            
            $_SESSION['mesaj'] = ($dil == 'tr'
                ? "Tebrikler! " . $kupon['indirim'] . " TL indirim kuponu başarıyla uygulandı."
                : "Congratulations! " . $kupon['indirim'] . " TL discount coupon applied successfully.");
            
            $kullanildi = true;
            break;
        }
    }
    
    if($kullanildi) {
        $_SESSION['kullanici_kuponlari'] = $kuponlar;
        header('Location: sepet.php');
        exit();
    } else {
        header('Location: kuponlar.php');
        exit();
    }
}

/* -------------------------
   HATA VEYA BAŞARI MESAJLARINI GÖSTER
-------------------------- */
if(isset($_SESSION['hata'])) {
    echo '<div class="message error">' . htmlspecialchars($_SESSION['hata']) . '</div>';
    unset($_SESSION['hata']);
}
if(isset($_SESSION['mesaj'])) {
    echo '<div class="message success">' . htmlspecialchars($_SESSION['mesaj']) . '</div>';
    unset($_SESSION['mesaj']);
}
?>

<!-- KUPONLAR SAYFA İÇERİĞİ -->
<div class="kuponlar-container">
    <h1 style="margin-bottom: 20px;">🎫 <?= $text_selected['kuponlarim']; ?></h1>
    
    <?php if(!$is_logged_in): ?>
        <!-- GİRİŞ YAPMAMIŞ KULLANICI -->
        <div class="hosgeldin" style="text-align:center; padding: 40px 20px;">
            <div style="font-size: 5rem; margin-bottom: 20px;">🔒</div>
            <h2 style="color: var(--error-color); margin-bottom: 20px;">
                <?= $text_selected['kuponlarim']; ?>
            </h2>
            
            <div style="background: rgba(255, 107, 157, 0.1); padding: 25px; border-radius: 15px; margin: 20px 0; max-width: 600px; margin-left: auto; margin-right: auto;">
                <h3 style="color: #ff6b9d; margin-bottom: 15px;">
                    <i class="fas fa-lock"></i> 
                    <?= ($dil == 'tr' ? 'Kuponlarınızı görmek için giriş yapmalısınız' : 'You must login to view your coupons'); ?>
                </h3>
                
                <p style="color: #666; margin-bottom: 25px; line-height: 1.6;">
                    <?= ($dil == 'tr'
                        ? 'Kuponlarınızı görmek, kullanmak ve indirimlerden yararlanmak için lütfen giriş yapın veya hesap oluşturun.'
                        : 'Please login or create an account to view, use your coupons and take advantage of discounts.'); ?>
                </p>
                
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="auth.php" class="auth-button" style="padding: 12px 30px;">
                        <i class="fas fa-sign-in-alt"></i> <?= $text_selected['giris']; ?>
                    </a>
                    <a href="auth.php?type=register" class="auth-button" style="background: #4CAF50; padding: 12px 30px;">
                        <i class="fas fa-user-plus"></i> <?= $text_selected['uye_ol']; ?>
                    </a>
                </div>
            </div>
        </div>
        
    <?php elseif($is_admin): ?>
        <!-- ADMIN KULLANICI -->
        <div class="hosgeldin" style="text-align:center; padding: 40px 20px;">
            <div style="font-size: 5rem; margin-bottom: 20px;">👑</div>
            <h2 style="color: #d32f2f; margin-bottom: 20px;">Admin Paneli</h2>
            
            <div style="background: rgba(211, 47, 47, 0.1); padding: 25px; border-radius: 15px; margin: 20px 0; max-width: 600px; margin-left: auto; margin-right: auto;">
                <h3 style="color: #d32f2f; margin-bottom: 15px;">
                    <i class="fas fa-user-shield"></i> 
                    <?= ($dil == 'tr' ? 'Yönetici Modu' : 'Administrator Mode'); ?>
                </h3>
                
                <p style="color: #666; margin-bottom: 25px; line-height: 1.6;">
                    <?= ($dil == 'tr'
                        ? 'Admin kullanıcıları kupon sistemi kullanmamaktadır. Kuponları yönetmek için admin paneline gidin.'
                        : 'Admin users do not use the coupon system. Go to admin panel to manage coupons.'); ?>
                </p>
                
                <a href="admin_panel.php" class="admin-button" style="padding: 12px 30px;">
                    <i class="fas fa-cogs"></i> Admin Paneline Git
                </a>
            </div>
        </div>
        
    <?php else: ?>
        <!-- GİRİŞ YAPMIŞ NORMAL KULLANICI -->
        
        <!-- SEPET TOPLAMI BİLGİSİ -->
        <div class="sepet-bilgi" style="background: white; padding: 20px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1); border-left: 4px solid #ff6b9d;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="color: #ff6b9d; margin-bottom: 10px;">
                        <i class="fas fa-shopping-cart"></i> 
                        <?= ($dil == 'tr' ? 'Mevcut Sepet Durumunuz' : 'Current Cart Status'); ?>
                    </h3>
                    <p style="color: #666; margin-bottom: 5px;">
                        <?= ($dil == 'tr' ? 'Kuponları kullanabilmek için sepetiniz minimum tutara ulaşmalıdır.' : 'Your cart must reach the minimum amount to use coupons.'); ?>
                    </p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.2rem; color: #666; margin-bottom: 5px;">
                        <?= ($dil == 'tr' ? 'Sepet Toplamı:' : 'Cart Total:'); ?>
                    </div>
                    <div style="font-size: 2rem; color: #ff6b9d; font-weight: bold;">
                        <?= number_format($sepetToplam, 2); ?> TL
                    </div>
                </div>
            </div>
            
            <?php if($sepetToplam == 0): ?>
                <div style="margin-top: 15px; padding: 15px; background: #ffeef2; border-radius: 10px; color: #ff6b9d;">
                    <i class="fas fa-info-circle"></i>
                    <?= ($dil == 'tr' 
                        ? 'Sepetiniz boş. Kupon kullanmak için önce sepetinize ürün ekleyin.'
                        : 'Your cart is empty. Add products to your cart first to use coupons.'); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if(empty($kuponlar)): ?>
            <!-- KUPONU YOK -->
            <div class="hosgeldin" style="text-align:center; padding: 40px 20px;">
                <div style="font-size: 5rem; margin-bottom: 20px;">🎁</div>
                <h2 style="color: #ff6b9d; margin-bottom: 20px;">
                    <?= ($dil == 'tr' ? 'Henüz kuponunuz yok' : 'You have no coupons yet'); ?>
                </h2>
                
                <div style="background: rgba(255, 107, 157, 0.05); padding: 25px; border-radius: 15px; margin: 20px 0; max-width: 600px; margin-left: auto; margin-right: auto;">
                    <h3 style="color: #ff6b9d; margin-bottom: 15px;">
                        <i class="fas fa-gift"></i> 
                        <?= ($dil == 'tr' ? 'Kupon Kazanma Yolları' : 'Ways to Earn Coupons'); ?>
                    </h3>
                    
                    <ul style="text-align: left; color: #666; line-height: 1.8; margin-bottom: 25px; list-style-type: none; padding: 0;">
                        <li style="margin-bottom: 10px; padding-left: 30px; position: relative;">
                            <i class="fas fa-shopping-bag" style="color: #ff6b9d; position: absolute; left: 0;"></i>
                            <?= ($dil == 'tr' 
                                ? 'İlk alışverişinizde otomatik olarak indirim kuponu kazanacaksınız!'
                                : 'You will automatically earn a discount coupon on your first purchase!'); ?>
                        </li>
                        <li style="margin-bottom: 10px; padding-left: 30px; position: relative;">
                            <i class="fas fa-birthday-cake" style="color: #ff6b9d; position: absolute; left: 0;"></i>
                            <?= ($dil == 'tr' 
                                ? 'Doğum gününüzde özel hediye kuponu alacaksınız'
                                : 'You will receive a special gift coupon on your birthday'); ?>
                        </li>
                        <li style="margin-bottom: 10px; padding-left: 30px; position: relative;">
                            <i class="fas fa-star" style="color: #ff6b9d; position: absolute; left: 0;"></i>
                            <?= ($dil == 'tr' 
                                ? 'Belirli alışveriş tutarlarını aşarak bonus kuponlar kazanın'
                                : 'Earn bonus coupons by exceeding certain purchase amounts'); ?>
                        </li>
                    </ul>
                    
                    <a href="urunler.php" class="auth-button" style="padding: 12px 30px;">
                        <i class="fas fa-store"></i> 
                        <?= ($dil == 'tr' ? 'Alışverişe Başla' : 'Start Shopping'); ?>
                    </a>
                </div>
            </div>
            
        <?php else: ?>
            <!-- KUPONLAR LİSTESİ -->
            <div class="kupon-grid">
                <?php foreach($kuponlar as $kupon): 
                    $tarih_farki = strtotime($kupon['son_kullanma']) - time();
                    $kalan_gun = ceil($tarih_farki / 86400);
                    $kalan_gun = max(0, $kalan_gun);
                    
                    $yetersiz_sepet = $sepetToplam < $kupon['min_sepet'];
                    $kullanilabilir = $kupon['durum'] == 'aktif' && !$yetersiz_sepet;
                    
                    // Renk kodu belirle
                    if($kupon['durum'] == 'kullanildi') {
                        $kupon_rengi = '#95a5a6';
                        $border_color = '#bdc3c7';
                    } elseif($yetersiz_sepet) {
                        $kupon_rengi = '#f39c12';
                        $border_color = '#f1c40f';
                    } elseif($kalan_gun < 3) {
                        $kupon_rengi = '#e74c3c';
                        $border_color = '#c0392b';
                    } elseif($kalan_gun < 7) {
                        $kupon_rengi = '#e67e22';
                        $border_color = '#d35400';
                    } else {
                        $kupon_rengi = '#ff6b9d';
                        $border_color = '#ff8fab';
                    }
                ?>
                    <div class="kupon-kart" style="
                        background: linear-gradient(135deg, white 0%, #fff5f7 100%);
                        border: 2px solid <?= $border_color; ?>;
                        border-radius: 15px;
                        padding: 25px;
                        position: relative;
                        overflow: hidden;
                        transition: all 0.3s ease;
                        opacity: <?= $kupon['durum'] == 'kullanildi' ? '0.8' : '1'; ?>;
                        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
                    ">
                        <?php if($kupon['durum'] == 'kullanildi'): ?>
                            <div style="position: absolute; top: 20px; right: 20px; background: #95a5a6; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">
                                <?= $text_selected['kullanildi']; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Kupon Başlığı -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px dashed <?= $border_color; ?>;">
                            <div>
                                <div style="font-family: monospace; font-size: 1.4rem; font-weight: bold; color: <?= $kupon_rengi; ?>; background: rgba(<?= hexdec(substr($kupon_rengi,1,2)); ?>, <?= hexdec(substr($kupon_rengi,3,2)); ?>, <?= hexdec(substr($kupon_rengi,5,2)); ?>, 0.1); padding: 8px 15px; border-radius: 8px; display: inline-block;">
                                    <?= htmlspecialchars($kupon['kod']); ?>
                                </div>
                            </div>
                            <div style="font-size: 2.5rem; font-weight: bold; color: <?= $kupon_rengi; ?>; text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">
                                <?= $kupon['indirim']; ?> TL
                            </div>
                        </div>
                        
                        <!-- Kupon Açıklaması -->
                        <p style="color: #666; margin-bottom: 25px; line-height: 1.6; font-size: 1.1rem;">
                            <?= htmlspecialchars($kupon['aciklama']); ?>
                        </p>
                        
                        <!-- Kupon Detayları -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                            <!-- Son Kullanma -->
                            <div>
                                <div style="font-weight: 600; margin-bottom: 8px; color: #555;">
                                    <?= $text_selected['son_kullanma']; ?>
                                </div>
                                <div style="color: <?= ($kalan_gun < 7 ? '#e74c3c' : '#27ae60'); ?>; font-weight: 500;">
                                    <?= date('d.m.Y', strtotime($kupon['son_kullanma'])); ?>
                                    <br>
                                    <small>
                                        (<?= $dil == 'tr' ? $kalan_gun.' gün kaldı' : $kalan_gun.' days left'; ?>)
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Minimum Sepet -->
                            <div>
                                <div style="font-weight: 600; margin-bottom: 8px; color: #555;">
                                    <?= $text_selected['min_sepet']; ?>
                                </div>
                                <div style="color: <?= ($yetersiz_sepet ? '#e74c3c' : '#27ae60'); ?>; font-weight: 500;">
                                    <?= number_format($kupon['min_sepet'], 2); ?> TL
                                </div>
                            </div>
                        </div>
                        
                        <!-- Durum ve Kullanım -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                            <!-- Durum -->
                            <div>
                                <div style="font-weight: 600; margin-bottom: 8px; color: #555;">
                                    <?= ($dil == 'tr' ? 'Durum' : 'Status'); ?>
                                </div>
                                <div style="display: inline-block; padding: 8px 15px; border-radius: 20px; font-weight: 600; background: <?= $kupon['durum'] == 'aktif' ? 'rgba(46, 204, 113, 0.2)' : 'rgba(149, 165, 166, 0.2)'; ?>; color: <?= $kupon['durum'] == 'aktif' ? '#27ae60' : '#7f8c8d'; ?>;">
                                    <?= $kupon['durum'] == 'aktif' ? $text_selected['kullan'] : $text_selected['kullanildi']; ?>
                                </div>
                            </div>
                            
                            <!-- Tek Kullanım -->
                            <div>
                                <div style="font-weight: 600; margin-bottom: 8px; color: #555;">
                                    <?= $text_selected['tek_kullanim']; ?>
                                </div>
                                <div style="color: #666; font-weight: 500;">
                                    <?= $kupon['tek_kullanim'] ? ($dil == 'tr' ? 'Evet' : 'Yes') : ($dil == 'tr' ? 'Hayır' : 'No'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kullan Butonu -->
                        <?php if($kupon['durum'] == 'aktif'): ?>
                            <?php if($yetersiz_sepet): ?>
                                <button disabled style="
                                    width: 100%;
                                    padding: 15px;
                                    background: #f39c12;
                                    color: white;
                                    border: none;
                                    border-radius: 10px;
                                    font-weight: 600;
                                    cursor: not-allowed;
                                    font-size: 1.1rem;
                                ">
                                    ⚠️ <?= $text_selected['sepet_yetersiz']; ?>
                                </button>
                                <div style="text-align: center; margin-top: 10px; color: #e74c3c; font-size: 0.9rem;">
                                    <i class="fas fa-info-circle"></i>
                                    <?php 
                                    $required = $kupon['min_sepet'] - $sepetToplam;
                                    echo ($dil == 'tr' 
                                        ? "Sepetinize " . number_format($required, 2) . " TL daha " . $text_selected['ekle'] 
                                        : "Add " . number_format($required, 2) . " TL more " . $text_selected['ekle']);
                                    ?>
                                </div>
                            <?php else: ?>
                                <button onclick="if(confirm('<?= ($dil == 'tr' ? 'Bu kuponu kullanmak istediğinize emin misiniz? Kupon tek kullanımlıktır!' : 'Are you sure you want to use this coupon? It is single use only!'); ?>')) { window.location.href='kuponlar.php?kupon_kullan=<?= urlencode($kupon['kod']); ?>' }" style="
                                    width: 100%;
                                    padding: 15px;
                                    background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
                                    color: white;
                                    border: none;
                                    border-radius: 10px;
                                    font-weight: 600;
                                    cursor: pointer;
                                    font-size: 1.1rem;
                                    transition: all 0.3s ease;
                                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(255, 107, 157, 0.3)';" 
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <?= $text_selected['kullan']; ?> 🎯
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <button disabled style="
                                width: 100%;
                                padding: 15px;
                                background: #95a5a6;
                                color: white;
                                border: none;
                                border-radius: 10px;
                                font-weight: 600;
                                cursor: not-allowed;
                                font-size: 1.1rem;
                            ">
                                <?= $text_selected['kullanildi']; ?> ✅
                            </button>
                            <?php if(isset($kupon['kullanma_tarihi'])): ?>
                                <div style="text-align: center; margin-top: 10px; color: #95a5a6; font-size: 0.9rem;">
                                    <i class="fas fa-clock"></i>
                                    <?= ($dil == 'tr' ? 'Kullanıldı: ' : 'Used: ') . date('d.m.Y H:i', strtotime($kupon['kullanma_tarihi'])); ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- KUPON BİLGİLENDİRME -->
            <div style="margin-top: 40px; padding: 25px; background: linear-gradient(135deg, #fff5f7 0%, white 100%); border-radius: 15px; border-left: 4px solid #ff6b9d;">
                <h3 style="color: #ff6b9d; margin-bottom: 15px;">
                    <i class="fas fa-question-circle"></i> 
                    <?= ($dil == 'tr' ? 'Kupon Kullanımı Hakkında' : 'About Coupon Usage'); ?>
                </h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
                        <div style="font-size: 2rem; color: #ff6b9d; margin-bottom: 10px;">🎯</div>
                        <h4 style="color: #555; margin-bottom: 10px;"><?= ($dil == 'tr' ? 'Minimum Sepet' : 'Minimum Cart'); ?></h4>
                        <p style="color: #666; font-size: 0.9rem; line-height: 1.5;">
                            <?= ($dil == 'tr' 
                                ? 'Kuponları kullanabilmek için sepetiniz belirtilen minimum tutara ulaşmalıdır.'
                                : 'Your cart must reach the specified minimum amount to use coupons.'); ?>
                        </p>
                    </div>
                    
                    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
                        <div style="font-size: 2rem; color: #ff6b9d; margin-bottom: 10px;">⚠️</div>
                        <h4 style="color: #555; margin-bottom: 10px;"><?= $text_selected['tek_kullanim']; ?></h4>
                        <p style="color: #666; font-size: 0.9rem; line-height: 1.5;">
                            <?= ($dil == 'tr' 
                                ? 'Tüm kuponlar tek kullanımlıktır. Bir kez kullanıldıktan sonra tekrar kullanılamaz.'
                                : 'All coupons are single use. They cannot be used again once used.'); ?>
                        </p>
                    </div>
                    
                    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.05);">
                        <div style="font-size: 2rem; color: #ff6b9d; margin-bottom: 10px;">⏰</div>
                        <h4 style="color: #555; margin-bottom: 10px;"><?= $text_selected['son_kullanma']; ?></h4>
                        <p style="color: #666; font-size: 0.9rem; line-height: 1.5;">
                            <?= ($dil == 'tr' 
                                ? 'Kuponların son kullanma tarihleri vardır. Süresi dolan kuponlar kullanılamaz.'
                                : 'Coupons have expiry dates. Expired coupons cannot be used.'); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.kuponlar-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.kupon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.kupon-kart:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(255, 107, 157, 0.15) !important;
}

/* Responsive Tasarım */
@media (max-width: 768px) {
    .kupon-grid {
        grid-template-columns: 1fr;
    }
    
    .kupon-kart {
        padding: 20px !important;
    }
    
    .kuponlar-container {
        padding: 15px;
    }
}
</style>

<?php require_once 'footer.php'; ?>