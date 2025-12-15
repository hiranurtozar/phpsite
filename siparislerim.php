<?php
// siparislerim.php
require_once 'header.php';

// Dil ayarı
$dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';

// Giriş kontrolü
if (!$is_logged_in) {
    $_SESSION['message'] = 'Siparişlerinizi görüntülemek için giriş yapmalısınız!';
    $_SESSION['message_type'] = 'error';
    header('Location: auth.php');
    exit();
}

// Siparişleri JSON'dan oku
$siparisler_dosya = 'siparisler.json';
$tum_siparisler = [];

if (file_exists($siparisler_dosya)) {
    $tum_siparisler = json_decode(file_get_contents($siparisler_dosya), true);
    if (!$tum_siparisler || !is_array($tum_siparisler)) {
        $tum_siparisler = [];
    }
}

// Sadece bu kullanıcının siparişlerini filtrele
$kullanici_siparisler = [];
foreach ($tum_siparisler as $siparis) {
    if (isset($siparis['user_id']) && $siparis['user_id'] == $_SESSION['user_id']) {
        $kullanici_siparisler[] = $siparis;
    }
}

// Siparişleri tarihe göre ters sırala (en yeni en üstte)
usort($kullanici_siparisler, function($a, $b) {
    $tarihA = isset($a['tarih']) ? strtotime($a['tarih']) : 0;
    $tarihB = isset($b['tarih']) ? strtotime($b['tarih']) : 0;
    return $tarihB - $tarihA;
});

// Durum metinleri
$durum_metinleri = [
    'tr' => [
        'onay_bekliyor' => 'Onay Bekliyor',
        'hazirlaniyor' => 'Hazırlanıyor',
        'kargoya_verildi' => 'Kargoya Verildi',
        'teslim_edildi' => 'Teslim Edildi',
        'iptal_edildi' => 'İptal Edildi'
    ],
    'en' => [
        'onay_bekliyor' => 'Pending Approval',
        'hazirlaniyor' => 'Preparing',
        'kargoya_verildi' => 'Shipped',
        'teslim_edildi' => 'Delivered',
        'iptal_edildi' => 'Cancelled'
    ]
];

// Durum renkleri
$durum_renkleri = [
    'onay_bekliyor' => '#ff9800',
    'hazirlaniyor' => '#2196f3',
    'kargoya_verildi' => '#9c27b0',
    'teslim_edildi' => '#4caf50',
    'iptal_edildi' => '#f44336'
];

// Durum ikonları
$durum_ikonlari = [
    'onay_bekliyor' => '📝',
    'hazirlaniyor' => '🚚',
    'kargoya_verildi' => '📦',
    'teslim_edildi' => '✅',
    'iptal_edildi' => '❌'
];
?>

<style>
    /* SIPARISLERIM SAYFASI STİLLERİ */
    .siparislerim-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 30px 20px;
        min-height: 70vh;
    }
    
    .siparisler-header {
        margin-bottom: 40px;
        position: relative;
    }
    
    .siparisler-header h1 {
        color: #333;
        font-size: 2.2rem;
        margin-bottom: 8px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .siparisler-header h1 i {
        color: #ff6b9d;
    }
    
    .siparisler-header p {
        color: #666;
        font-size: 1rem;
        margin-bottom: 15px;
    }
    
    .siparis-count {
        background: #333;
        color: white;
        padding: 6px 18px;
        border-radius: 25px;
        font-weight: 600;
        display: inline-block;
        font-size: 0.9rem;
        border: 2px solid #444;
    }
    
    /* SİPARİŞ LİSTESİ */
    .siparis-listesi {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    
    .siparis-item {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1px solid #eee;
        position: relative;
        overflow: hidden;
    }
    
    .siparis-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        border-color: #ff6b9d;
    }
    
    .siparis-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #ff6b9d;
    }
    
    .siparis-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 18px;
        border-bottom: 1px solid #eee;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .siparis-no {
        font-size: 1.3rem;
        font-weight: 700;
        color: #222;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .siparis-no::before {
        content: '#';
        color: #ff6b9d;
        font-weight: 800;
    }
    
    .siparis-tarih {
        color: #777;
        font-size: 0.9rem;
        background: #f8f8f8;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-block;
    }
    
    .siparis-durum {
        padding: 8px 18px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    /* ÜRÜN ÖZETİ */
    .urun-ozeti {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 25px;
    }
    
    .urun-item-ozet {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f9f9f9;
        padding: 12px 18px;
        border-radius: 10px;
        min-width: 200px;
        border: 1px solid #eee;
        transition: all 0.2s;
    }
    
    .urun-item-ozet:hover {
        background: #fff;
        border-color: #ff6b9d;
        transform: translateY(-2px);
    }
    
    .urun-simge-ozet {
        font-size: 1.7rem;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    
    .urun-bilgi-ozet {
        flex: 1;
    }
    
    .urun-ad-ozet {
        font-weight: 600;
        font-size: 0.95rem;
        color: #333;
        margin-bottom: 3px;
    }
    
    .urun-detay-ozet {
        font-size: 0.85rem;
        color: #666;
    }
    
    /* SİPARİŞ BİLGİLERİ */
    .siparis-bilgiler {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
        background: #fafafa;
        padding: 20px;
        border-radius: 10px;
        border: 1px solid #eee;
    }
    
    .bilgi-item {
        background: white;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #eee;
        transition: all 0.2s;
    }
    
    .bilgi-item:hover {
        border-color: #ff6b9d;
        box-shadow: 0 3px 10px rgba(255,107,157,0.1);
    }
    
    .bilgi-label {
        font-size: 0.85rem;
        color: #777;
        margin-bottom: 8px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .bilgi-label i {
        color: #ff6b9d;
        font-size: 0.9rem;
    }
    
    .bilgi-value {
        font-weight: 600;
        color: #222;
        font-size: 1.1rem;
    }
    
    .tutar-deger {
        color: #ff6b9d;
        font-size: 1.3rem;
        font-weight: 700;
    }
    
    /* BUTONLAR */
    .siparis-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    
    .siparis-btn {
        padding: 10px 22px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
        border: 2px solid transparent;
    }
    
    .btn-detay {
        background: #333;
        color: white;
    }
    
    .btn-detay:hover {
        background: #444;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    
    .btn-yazdir {
        background: white;
        color: #333;
        border-color: #ddd;
    }
    
    .btn-yazdir:hover {
        background: #f8f8f8;
        border-color: #ff6b9d;
        color: #ff6b9d;
        transform: translateY(-2px);
    }
    
    /* BOŞ SİPARİŞ */
    .bos-siparis {
        text-align: center;
        padding: 60px 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-top: 20px;
        border: 1px solid #eee;
    }
    
    .bos-siparis .icon {
        font-size: 80px;
        color: #f0f0f0;
        margin-bottom: 25px;
        display: inline-block;
    }
    
    .bos-siparis h3 {
        color: #333;
        margin-bottom: 15px;
        font-size: 1.8rem;
    }
    
    .bos-siparis p {
        color: #777;
        margin-bottom: 30px;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
    }
    
    .btn-shopping {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 32px;
        background: #333;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
        border: 2px solid #333;
    }
    
    .btn-shopping:hover {
        background: #444;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    
    .btn-shopping i {
        font-size: 1.1rem;
    }
    
    /* RESPONSIVE */
    @media (max-width: 768px) {
        .siparislerim-container {
            padding: 20px 15px;
        }
        
        .siparis-header-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        
        .siparis-bilgiler {
            grid-template-columns: 1fr;
        }
        
        .urun-ozeti {
            flex-direction: column;
        }
        
        .urun-item-ozet {
            width: 100%;
        }
        
        .siparis-actions {
            flex-direction: column;
        }
        
        .siparis-btn {
            width: 100%;
            justify-content: center;
        }
        
        .siparisler-header h1 {
            font-size: 1.8rem;
        }
    }
</style>

<div class="siparislerim-container">
    <div class="siparisler-header">
        <h1><i class="fas fa-box-open"></i> <?php echo $dil == 'tr' ? 'Siparişlerim' : 'My Orders'; ?></h1>
        <p><?php echo $dil == 'tr' ? 'Tüm sipariş geçmişiniz burada listelenmektedir.' : 'All your order history is listed here.'; ?></p>
        <div class="siparis-count">
            <i class="fas fa-list"></i> <?php echo count($kullanici_siparisler); ?> <?php echo $dil == 'tr' ? 'sipariş' : 'orders'; ?>
        </div>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?php echo $_SESSION['message_type']; ?>">
            <?php echo $_SESSION['message']; ?>
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($kullanici_siparisler)): ?>
        <div class="bos-siparis">
            <div class="icon">📦</div>
            <h3><?php echo $dil == 'tr' ? 'Henüz siparişiniz yok' : 'No orders yet'; ?></h3>
            <p>
                <?php echo $dil == 'tr' 
                    ? 'Alışveriş yapmaya başlayarak ilk siparişinizi verebilirsiniz. Hemen ürünleri inceleyin!'
                    : 'You can place your first order by starting to shop. Browse products now!';
                ?>
            </p>
            <a href="urunler.php" class="btn-shopping">
                <i class="fas fa-shopping-bag"></i> 
                <?php echo $dil == 'tr' ? 'Alışverişe Başla' : 'Start Shopping'; ?>
            </a>
        </div>
    <?php else: ?>
        <div class="siparis-listesi">
            <?php foreach ($kullanici_siparisler as $siparis): 
                $durum = $siparis['durum'] ?? 'onay_bekliyor';
                $durum_renk = $durum_renkleri[$durum] ?? '#666';
                $durum_ikon = $durum_ikonlari[$durum] ?? '📋';
                $durum_metin = $durum_metinleri[$dil][$durum] ?? 'Sipariş';
                
                // Toplam ürün sayısı
                $toplam_urun_sayisi = 0;
                $urunler = $siparis['urunler'] ?? [];
                foreach ($urunler as $urun) {
                    $toplam_urun_sayisi += $urun['adet'] ?? 1;
                }
            ?>
                <div class="siparis-item">
                    <!-- SİPARİŞ BAŞLIK -->
                    <div class="siparis-header-row">
                        <div>
                            <div class="siparis-no"><?php echo $siparis['siparis_no'] ?? 'Bilinmiyor'; ?></div>
                            <div class="siparis-tarih"><i class="far fa-calendar"></i> <?php echo $siparis['tarih'] ?? ''; ?></div>
                        </div>
                        <div class="siparis-durum" style="background: <?php echo $durum_renk; ?>20; color: <?php echo $durum_renk; ?>; border: 1px solid <?php echo $durum_renk; ?>30;">
                            <?php echo $durum_ikon; ?> <?php echo $durum_metin; ?>
                        </div>
                    </div>
                    
                    <!-- ÜRÜN ÖZETİ -->
                    <?php if (!empty($urunler)): ?>
                        <div class="urun-ozeti">
                            <?php foreach (array_slice($urunler, 0, 3) as $urun): ?>
                                <div class="urun-item-ozet">
                                    <div class="urun-simge-ozet"><?php echo $urun['simge'] ?? '🌸'; ?></div>
                                    <div class="urun-bilgi-ozet">
                                        <div class="urun-ad-ozet"><?php echo htmlspecialchars($urun['ad'] ?? 'Ürün'); ?></div>
                                        <div class="urun-detay-ozet">
                                            <?php echo $urun['adet'] ?? 1; ?> × <?php echo number_format($urun['fiyat'] ?? 0, 2); ?> TL
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($urunler) > 3): ?>
                                <div class="urun-item-ozet" style="background: #fff5f8; border-color: #ff6b9d30;">
                                    <div class="urun-simge-ozet" style="color: #ff6b9d;">+</div>
                                    <div class="urun-bilgi-ozet">
                                        <div class="urun-ad-ozet" style="color: #ff6b9d;">
                                            +<?php echo count($urunler) - 3; ?> <?php echo $dil == 'tr' ? 'daha ürün' : 'more products'; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- SİPARİŞ BİLGİLERİ -->
                    <div class="siparis-bilgiler">
                        <div class="bilgi-item">
                            <div class="bilgi-label"><i class="fas fa-cube"></i> <?php echo $dil == 'tr' ? 'Toplam Ürün' : 'Total Items'; ?></div>
                            <div class="bilgi-value"><?php echo $toplam_urun_sayisi; ?> <?php echo $dil == 'tr' ? 'adet' : 'items'; ?></div>
                        </div>
                        
                        <div class="bilgi-item">
                            <div class="bilgi-label"><i class="fas fa-barcode"></i> <?php echo $dil == 'tr' ? 'Takip Kodu' : 'Tracking Code'; ?></div>
                            <div class="bilgi-value"><?php echo $siparis['takip_kodu'] ?? 'Bilinmiyor'; ?></div>
                        </div>
                        
                        <div class="bilgi-item">
                            <div class="bilgi-label"><i class="fas fa-map-marker-alt"></i> <?php echo $dil == 'tr' ? 'Teslimat Adresi' : 'Delivery Address'; ?></div>
                            <div class="bilgi-value"><?php echo substr($siparis['teslimat_adresi'] ?? '', 0, 30); ?>...</div>
                        </div>
                        
                        <div class="bilgi-item">
                            <div class="bilgi-label"><i class="fas fa-money-bill-wave"></i> <?php echo $dil == 'tr' ? 'Toplam Tutar' : 'Total Amount'; ?></div>
                            <div class="bilgi-value tutar-deger"><?php echo number_format($siparis['genel_toplam'] ?? 0, 2); ?> TL</div>
                        </div>
                    </div>
                    
                    <!-- BUTONLAR -->
                    <div class="siparis-actions">
                        <a href="siparis_takip.php?siparis_no=<?php echo $siparis['siparis_no'] ?? ''; ?>" class="siparis-btn btn-detay">
                            <i class="fas fa-external-link-alt"></i> <?php echo $dil == 'tr' ? 'Sipariş Detayları' : 'Order Details'; ?>
                        </a>
                        <a href="javascript:void(0)" onclick="window.print()" class="siparis-btn btn-yazdir">
                            <i class="fas fa-print"></i> <?php echo $dil == 'tr' ? 'Yazdır' : 'Print'; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sipariş öğelerine tıklama
    document.querySelectorAll('.siparis-item').forEach(item => {
        // Tıklanabilir alan
        item.style.cursor = 'pointer';
        
        // Tıklama olayı
        item.addEventListener('click', function(e) {
            // Eğer butonlardan birine tıklanmamışsa
            if (!e.target.closest('.siparis-btn')) {
                const detayLink = this.querySelector('.btn-detay');
                if (detayLink) {
                    detayLink.click();
                }
            }
        });
    });
    
    // Hover efektleri
    document.querySelectorAll('.siparis-durum').forEach(durum => {
        durum.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        durum.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Buton hover efektleri
    document.querySelectorAll('.siparis-btn').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>