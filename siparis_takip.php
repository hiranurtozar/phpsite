<?php
// siparis_takip.php
require_once 'header.php';

// Dil ayarı
$dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';

// Giriş kontrolü
if (!$is_logged_in) {
    $_SESSION['message'] = 'Sipariş takibi için giriş yapmalısınız!';
    $_SESSION['message_type'] = 'error';
    header('Location: auth.php');
    exit();
}

// Sipariş numarası kontrolü
$siparis_no = isset($_GET['siparis_no']) ? $_GET['siparis_no'] : (isset($_SESSION['son_siparis_no']) ? $_SESSION['son_siparis_no'] : '');
$siparis_detay = null;

// Siparişleri JSON'dan oku
$siparisler_dosya = 'siparisler.json';
$siparisler = [];

if (file_exists($siparisler_dosya)) {
    $siparisler = json_decode(file_get_contents($siparisler_dosya), true);
    if (!$siparisler || !is_array($siparisler)) {
        $siparisler = [];
    }
}

// Test için örnek sipariş oluştur (eğer yoksa)
if (empty($siparisler) && $is_logged_in && !$is_admin) {
    $ornek_siparis_no = 'SIP-' . date('Ymd') . '-001';
    $ornek_takip_kodu = 'TRK' . strtoupper(substr(md5(uniqid()), 0, 10));
    
    $siparisler = [
        [
            'siparis_no' => $ornek_siparis_no,
            'user_id' => $_SESSION['user_id'],
            'ad_soyad' => $_SESSION['ad_soyad'] ?? 'Test Kullanıcı',
            'email' => $_SESSION['email'] ?? 'test@example.com',
            'tarih' => date('d.m.Y H:i:s'),
            'durum' => 'onay_bekliyor',
            'takip_kodu' => $ornek_takip_kodu,
            'teslimat_adresi' => $_SESSION['adres'] ?? 'Test Adresi',
            'urunler' => [
                [
                    'ad' => 'Kırmızı Gül Buketi',
                    'fiyat' => 149.99,
                    'adet' => 1,
                    'simge' => '🌹'
                ],
                [
                    'ad' => 'Orkide',
                    'fiyat' => 89.99,
                    'adet' => 2,
                    'simge' => '💮'
                ]
            ],
            'toplam_tutar' => 329.97,
            'kdv' => 59.39,
            'genel_toplam' => 389.36,
            'takip_gecmisi' => [
                [
                    'tarih' => date('d.m.Y H:i:s'),
                    'durum' => 'onay_bekliyor',
                    'aciklama' => $dil == 'tr' ? 'Siparişiniz alındı, onay bekliyor.' : 'Your order has been received, pending approval.',
                    'icon' => '📝'
                ]
            ],
            'son_guncelleme' => time()
        ]
    ];
    file_put_contents($siparisler_dosya, json_encode($siparisler, JSON_PRETTY_PRINT));
    
    $siparis_no = $ornek_siparis_no;
    $_SESSION['son_siparis_no'] = $siparis_no;
}

// Sipariş numarasına göre siparişi bul
if (!empty($siparis_no)) {
    foreach ($siparisler as $siparis) {
        if (isset($siparis['siparis_no']) && $siparis['siparis_no'] == $siparis_no) {
            if (isset($siparis['user_id']) && $siparis['user_id'] == $_SESSION['user_id']) {
                $siparis_detay = $siparis;
                break;
            }
        }
    }
}

// Sipariş bulunamadıysa örnek oluştur
if (!$siparis_detay || !is_array($siparis_detay)) {
    $ornek_siparis_no = 'SIP-' . date('Ymd') . '-001';
    $ornek_takip_kodu = 'TRK' . strtoupper(substr(md5(uniqid()), 0, 10));
    
    $siparis_detay = [
        'siparis_no' => $ornek_siparis_no,
        'user_id' => $_SESSION['user_id'],
        'ad_soyad' => $_SESSION['ad_soyad'] ?? 'Test Kullanıcı',
        'email' => $_SESSION['email'] ?? 'test@example.com',
        'tarih' => date('d.m.Y H:i:s'),
        'durum' => 'onay_bekliyor',
        'takip_kodu' => $ornek_takip_kodu,
        'teslimat_adresi' => $_SESSION['adres'] ?? 'Test Adresi',
        'urunler' => [
            [
                'ad' => 'Kırmızı Gül Buketi',
                'fiyat' => 149.99,
                'adet' => 1,
                'simge' => '🌹'
            ]
        ],
        'toplam_tutar' => 149.99,
        'kdv' => 26.99,
        'genel_toplam' => 176.98,
        'takip_gecmisi' => [
            [
                'tarih' => date('d.m.Y H:i:s'),
                'durum' => 'onay_bekliyor',
                'aciklama' => $dil == 'tr' ? 'Siparişiniz alındı, onay bekliyor.' : 'Your order has been received, pending approval.',
                'icon' => '📝'
            ]
        ],
        'son_guncelleme' => time()
    ];
    
    $_SESSION['message'] = $dil == 'tr' ? 'Test siparişi oluşturuldu.' : 'Test order created.';
    $_SESSION['message_type'] = 'info';
}

// ============================================================================
// DURUM GÜNCELLEME FONKSİYONLARI
// ============================================================================

function sonrakiDurumuBul($mevcut_durum) {
    $durum_sirasi = [
        'onay_bekliyor' => 'hazirlaniyor',
        'hazirlaniyor' => 'kargoya_verildi', 
        'kargoya_verildi' => 'teslim_edildi',
        'teslim_edildi' => 'teslim_edildi'
    ];
    return $durum_sirasi[$mevcut_durum] ?? $mevcut_durum;
}

function durumMetniGetir($durum, $dil = 'tr') {
    $metinler = [
        'tr' => [
            'onay_bekliyor' => 'Onay Bekliyor',
            'hazirlaniyor' => 'Hazırlanıyor',
            'kargoya_verildi' => 'Kargoya Verildi',
            'teslim_edildi' => 'Teslim Edildi'
        ],
        'en' => [
            'onay_bekliyor' => 'Pending Approval',
            'hazirlaniyor' => 'Preparing',
            'kargoya_verildi' => 'Shipped',
            'teslim_edildi' => 'Delivered'
        ]
    ];
    return $metinler[$dil][$durum] ?? $durum;
}

function durumIkonuGetir($durum) {
    $ikonlar = [
        'onay_bekliyor' => '📝',
        'hazirlaniyor' => '🚚',
        'kargoya_verildi' => '📦',
        'teslim_edildi' => '✅'
    ];
    return $ikonlar[$durum] ?? '📋';
}

function durumAciklamasiGetir($durum, $dil = 'tr') {
    $aciklamalar = [
        'tr' => [
            'onay_bekliyor' => 'Siparişiniz alındı, onay bekliyor.',
            'hazirlaniyor' => 'Siparişiniz hazırlanıyor.',
            'kargoya_verildi' => 'Siparişiniz kargoya verildi.',
            'teslim_edildi' => 'Siparişiniz teslim edildi.'
        ],
        'en' => [
            'onay_bekliyor' => 'Your order has been received, pending approval.',
            'hazirlaniyor' => 'Your order is being prepared.',
            'kargoya_verildi' => 'Your order has been shipped.',
            'teslim_edildi' => 'Your order has been delivered.'
        ]
    ];
    return $aciklamalar[$dil][$durum] ?? ($dil == 'tr' ? 'Durum güncellendi.' : 'Status updated.');
}

// ============================================================================
// AJAX İLE DURUM GÜNCELLEME ENDPOINT
// ============================================================================

if (isset($_GET['ajax']) && $_GET['ajax'] == 'durum_guncelle') {
    // AJAX isteği için header.php'yi yükleme
    ob_clean(); // Önceki çıktıyı temizle
    
    // Eğer sipariş detayı yoksa hata döndür
    if (!isset($siparis_detay['siparis_no'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'mesaj' => 'Sipariş bulunamadı']);
        exit();
    }
    
    // Sadece JSON döndür
    header('Content-Type: application/json');
    
    if ($siparis_detay['durum'] !== 'teslim_edildi') {
        $yeni_durum = sonrakiDurumuBul($siparis_detay['durum']);
        
        // Durumu güncelle
        $siparis_detay['durum'] = $yeni_durum;
        $siparis_detay['son_guncelleme'] = time();
        
        // Takip geçmişine ekle
        $takip_gecmisi = [
            'tarih' => date('d.m.Y H:i:s'),
            'durum' => $yeni_durum,
            'aciklama' => durumAciklamasiGetir($yeni_durum, $dil),
            'icon' => durumIkonuGetir($yeni_durum)
        ];
        
        if (!isset($siparis_detay['takip_gecmisi']) || !is_array($siparis_detay['takip_gecmisi'])) {
            $siparis_detay['takip_gecmisi'] = [];
        }
        array_unshift($siparis_detay['takip_gecmisi'], $takip_gecmisi);
        
        // JSON dosyasını güncelle
        $guncellendi = false;
        foreach ($siparisler as &$siparis) {
            if (isset($siparis['siparis_no']) && $siparis['siparis_no'] == $siparis_detay['siparis_no']) {
                $siparis = $siparis_detay;
                $guncellendi = true;
                break;
            }
        }
        
        if ($guncellendi) {
            file_put_contents($siparisler_dosya, json_encode($siparisler, JSON_PRETTY_PRINT));
            
            echo json_encode([
                'success' => true,
                'yeni_durum' => $yeni_durum,
                'durum_metni' => durumMetniGetir($yeni_durum, $dil),
                'mesaj' => ($dil == 'tr' ? 'Durum güncellendi: ' : 'Status updated: ') . durumMetniGetir($yeni_durum, $dil)
            ]);
            exit();
        }
    }
    
    echo json_encode([
        'success' => false, 
        'mesaj' => $dil == 'tr' ? 'Durum güncellenemedi' : 'Status could not be updated'
    ]);
    exit();
}

// ============================================================================
// MEVCUT DURUM BİLGİLERİ
// ============================================================================

// Varsayılan değerler atama
$siparis_detay['siparis_no'] = $siparis_detay['siparis_no'] ?? 'SIP-' . date('Ymd') . '-001';
$siparis_detay['tarih'] = $siparis_detay['tarih'] ?? date('d.m.Y H:i:s');
$siparis_detay['durum'] = $siparis_detay['durum'] ?? 'onay_bekliyor';
$siparis_detay['ad_soyad'] = $siparis_detay['ad_soyad'] ?? ($_SESSION['ad_soyad'] ?? 'Müşteri');
$siparis_detay['email'] = $siparis_detay['email'] ?? ($_SESSION['email'] ?? '');
$siparis_detay['teslimat_adresi'] = $siparis_detay['teslimat_adresi'] ?? '';
$siparis_detay['toplam_tutar'] = $siparis_detay['toplam_tutar'] ?? 0;
$siparis_detay['kdv'] = $siparis_detay['kdv'] ?? 0;
$siparis_detay['genel_toplam'] = $siparis_detay['genel_toplam'] ?? 0;
$siparis_detay['takip_kodu'] = $siparis_detay['takip_kodu'] ?? 'TRK' . strtoupper(substr(md5(uniqid()), 0, 10));
$siparis_detay['urunler'] = $siparis_detay['urunler'] ?? [];
$siparis_detay['son_guncelleme'] = $siparis_detay['son_guncelleme'] ?? time();

if (!isset($siparis_detay['takip_gecmisi']) || !is_array($siparis_detay['takip_gecmisi'])) {
    $siparis_detay['takip_gecmisi'] = [[
        'tarih' => date('d.m.Y H:i:s'),
        'durum' => 'onay_bekliyor',
        'aciklama' => $dil == 'tr' ? 'Siparişiniz alındı, onay bekliyor.' : 'Your order has been received, pending approval.',
        'icon' => '📝'
    ]];
}

// Durum renkleri
$durum_renkleri = [
    'onay_bekliyor' => '#ff9800',
    'hazirlaniyor' => '#2196f3',
    'kargoya_verildi' => '#9c27b0',
    'teslim_edildi' => '#4caf50',
    'iptal_edildi' => '#f44336'
];
?>

<style>
    /* SIPARIS TAKIP STİLLERİ */
    .siparis-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .siparis-header {
        text-align: center;
        margin-bottom: 40px;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(255, 107, 157, 0.3);
    }
    
    .siparis-no {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 10px;
        letter-spacing: 2px;
    }
    
    .siparis-tarih {
        font-size: 1.1rem;
        opacity: 0.9;
    }
    
    .takip-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-top: 30px;
    }
    
    @media (max-width: 768px) {
        .takip-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* SİPARİŞ DETAY */
    .siparis-detay {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
    }
    
    /* TAKİP SÜRECİ */
    .takip-sureci {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
    }
    
    .takip-timeline {
        margin-top: 20px;
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        margin-bottom: 30px;
        position: relative;
        padding-left: 40px;
    }
    
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ff6b9d;
        border: 4px solid white;
        box-shadow: 0 0 0 2px #ff6b9d;
    }
    
    .timeline-item::after {
        content: '';
        position: absolute;
        left: 3px;
        top: 20px;
        bottom: -30px;
        width: 2px;
        background: #ffeef2;
    }
    
    .timeline-item:last-child::after {
        display: none;
    }
    
    .timeline-icon {
        position: absolute;
        left: -35px;
        top: -5px;
        font-size: 1.5rem;
        background: white;
        padding: 5px;
        border-radius: 50%;
    }
    
    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid #ff6b9d;
    }
    
    .timeline-date {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 5px;
    }
    
    .timeline-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .timeline-desc {
        font-size: 0.9rem;
        color: #666;
    }
    
    /* DURUM GÖSTERGESİ */
    .durum-gostergesi {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        position: relative;
    }
    
    .durum-adim {
        text-align: center;
        position: relative;
        flex: 1;
    }
    
    .durum-adim:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 20px;
        right: -50%;
        width: 100%;
        height: 3px;
        background: #ffeef2;
        z-index: 1;
    }
    
    .durum-adim.active:not(:last-child)::after {
        background: #4CAF50;
    }
    
    .durum-adim.completed:not(:last-child)::after {
        background: #4CAF50;
    }
    
    .durum-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ffeef2;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.2rem;
        position: relative;
        z-index: 2;
    }
    
    .durum-adim.active .durum-circle {
        background: #4CAF50;
        color: white;
        transform: scale(1.1);
    }
    
    .durum-adim.completed .durum-circle {
        background: #4CAF50;
        color: white;
    }
    
    .durum-adim .durum-label {
        font-size: 0.9rem;
        color: #666;
    }
    
    .durum-adim.active .durum-label {
        color: #333;
        font-weight: 600;
    }
    
    .durum-adim.completed .durum-label {
        color: #4CAF50;
        font-weight: 600;
    }
    
    /* ÜRÜN LİSTESİ */
    .urun-listesi {
        margin-top: 20px;
    }
    
    .urun-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #ffeef2;
        transition: all 0.3s;
    }
    
    .urun-item:hover {
        background: #f8f9fa;
    }
    
    .urun-item:last-child {
        border-bottom: none;
    }
    
    .urun-bilgi {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .urun-simge {
        font-size: 1.8rem;
    }
    
    .urun-ad {
        font-weight: 500;
    }
    
    .urun-detay {
        font-size: 0.9rem;
        color: #666;
    }
    
    .urun-tutar {
        text-align: right;
    }
    
    .urun-fiyat {
        font-weight: 600;
        color: #333;
    }
    
    .urun-miktar {
        font-size: 0.9rem;
        color: #666;
    }
    
    /* DEKONT BİLGİLERİ */
    .dekont-bilgileri {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .info-row:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    
    .info-label {
        color: #666;
    }
    
    .info-value {
        font-weight: 600;
        color: #333;
    }
    
    /* BUTONLAR */
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    
    .action-btn {
        flex: 1;
        padding: 12px;
        text-align: center;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn-print {
        background: #2196f3;
        color: white;
    }
    
    .btn-print:hover {
        background: #1976d2;
        transform: translateY(-2px);
    }
    
    .btn-back {
        background: #ff6b9d;
        color: white;
    }
    
    .btn-back:hover {
        background: #ff4081;
        transform: translateY(-2px);
    }
    
    /* TAHSİLAT MAKBUZU */
    .makbuz {
        background: white;
        border: 2px dashed #4CAF50;
        border-radius: 15px;
        padding: 30px;
        margin-top: 30px;
        text-align: center;
    }
    
    .makbuz-baslik {
        color: #4CAF50;
        font-size: 1.5rem;
        margin-bottom: 20px;
    }
    
    .makbuz-no {
        font-size: 1.8rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 20px;
        letter-spacing: 3px;
    }
    
    .makbuz-tutar {
        font-size: 2.5rem;
        font-weight: 700;
        color: #ff6b9d;
        margin: 20px 0;
    }
    
    .qr-code {
        width: 150px;
        height: 150px;
        margin: 20px auto;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #333;
        border-radius: 10px;
        transition: all 0.3s;
    }
    
    /* KART BİLGİSİ */
    .kart-bilgisi {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .kart-icon {
        font-size: 2rem;
        color: #666;
    }
    
    /* STATUS BADGE */
    .status-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 600;
        margin-left: 15px;
        background: rgba(255, 255, 255, 0.2);
    }
    
    /* MANUEL GÜNCELLEME BUTONU */
    .update-btn {
        background: linear-gradient(135deg, #ff9800 0%, #ffb74d 100%);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin: 10px auto;
    }
    
    .update-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3);
    }
    
    .update-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    @media print {
        .navbar, .arama-cubugu, .breadcrumb, .action-buttons, .update-btn {
            display: none !important;
        }
        
        .siparis-container {
            margin: 0;
            padding: 0;
        }
        
        .siparis-header {
            background: #f8f9fa !important;
            color: #333 !important;
            box-shadow: none !important;
            margin-bottom: 20px !important;
        }
        
        .takip-grid {
            display: block !important;
        }
        
        .siparis-detay, .takip-sureci {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            margin-bottom: 20px !important;
            page-break-inside: avoid;
        }
        
        .makbuz {
            border: 2px solid #4CAF50 !important;
        }
        
        .qr-code {
            border: 1px solid #ddd;
        }
    }
    
    /* Animasyonlar */
    .timeline-item {
        opacity: 0;
        transform: translateX(-20px);
        transition: all 0.5s ease;
    }
    
    .durum-circle {
        opacity: 0;
        transform: scale(0.8);
        transition: all 0.3s ease;
    }
    
    .urun-item {
        transition: all 0.3s ease;
    }
</style>

<div class="container">
    <div class="siparis-container">
        <!-- SİPARİŞ BAŞLIK -->
        <div class="siparis-header">
            <div class="siparis-no"><?php echo $siparis_detay['siparis_no']; ?></div>
            <div class="siparis-tarih"><?php echo $siparis_detay['tarih']; ?></div>
            <div style="margin-top: 15px; font-size: 1.2rem;">
                <span class="status-badge" style="color: <?php echo $durum_renkleri[$siparis_detay['durum']] ?? '#666'; ?>;">
                    <?php echo durumIkonuGetir($siparis_detay['durum']); ?> 
                    <?php echo durumMetniGetir($siparis_detay['durum'], $dil); ?>
                </span>
            </div>
            
            <!-- MANUEL GÜNCELLEME BUTONU (Sadece test için) -->
            <?php if ($siparis_detay['durum'] !== 'teslim_edildi'): ?>
            <button id="manualUpdateBtn" class="update-btn" onclick="manuelDurumGuncelle()">
                <i class="fas fa-forward"></i>
                <?php echo $dil == 'tr' ? 'Durumu İlerlet' : 'Advance Status'; ?>
            </button>
            <?php endif; ?>
        </div>
        
        <div class="takip-grid">
            <!-- SİPARİŞ DETAY -->
            <div class="siparis-detay">
                <h2 style="color: #333; margin-bottom: 25px; border-bottom: 2px solid #ffeef2; padding-bottom: 10px;">
                    <i class="fas fa-shopping-bag"></i>
                    <?php echo $dil == 'tr' ? 'Sipariş Detayları' : 'Order Details'; ?>
                </h2>
                
                <!-- Ürün Listesi -->
                <div class="urun-listesi">
                    <?php foreach ($siparis_detay['urunler'] as $urun): ?>
                        <div class="urun-item">
                            <div class="urun-bilgi">
                                <div class="urun-simge"><?php echo $urun['simge'] ?? '🌸'; ?></div>
                                <div>
                                    <div class="urun-ad"><?php echo htmlspecialchars($urun['ad'] ?? 'Ürün'); ?></div>
                                    <div class="urun-detay">
                                        <?php echo $dil == 'tr' ? 'Birim:' : 'Unit:'; ?>
                                        <?php echo number_format($urun['fiyat'] ?? 0, 2); ?> TL
                                    </div>
                                </div>
                            </div>
                            <div class="urun-tutar">
                                <div class="urun-fiyat"><?php echo number_format(($urun['fiyat'] ?? 0) * ($urun['adet'] ?? 1), 2); ?> TL</div>
                                <div class="urun-miktar"><?php echo $urun['adet'] ?? 1; ?> <?php echo $dil == 'tr' ? 'adet' : 'pcs'; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Toplam Özet -->
                <div class="dekont-bilgileri">
                    <div class="info-row">
                        <span class="info-label"><?php echo $dil == 'tr' ? 'Ara Toplam:' : 'Subtotal:'; ?></span>
                        <span class="info-value"><?php echo number_format($siparis_detay['toplam_tutar'], 2); ?> TL</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><?php echo $dil == 'tr' ? 'KDV (%18):' : 'VAT (18%):'; ?></span>
                        <span class="info-value"><?php echo number_format($siparis_detay['kdv'], 2); ?> TL</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><?php echo $dil == 'tr' ? 'Kargo:' : 'Shipping:'; ?></span>
                        <span class="info-value" style="color: #4CAF50;">
                            <?php echo $dil == 'tr' ? 'Ücretsiz' : 'Free'; ?>
                        </span>
                    </div>
                    <div class="info-row" style="border-top: 2px solid #ff6b9d; padding-top: 15px; margin-top: 15px;">
                        <span class="info-label" style="font-size: 1.2rem;">
                            <?php echo $dil == 'tr' ? 'GENEL TOPLAM:' : 'TOTAL AMOUNT:'; ?>
                        </span>
                        <span class="info-value" style="font-size: 1.2rem; color: #ff6b9d;">
                            <?php echo number_format($siparis_detay['genel_toplam'], 2); ?> TL
                        </span>
                    </div>
                </div>
                
                <!-- Teslimat Bilgileri -->
                <h3 style="color: #333; margin: 30px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #ffeef2;">
                    <i class="fas fa-truck"></i>
                    <?php echo $dil == 'tr' ? 'Teslimat Bilgileri' : 'Delivery Information'; ?>
                </h3>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 10px;">
                    <p><strong><?php echo $dil == 'tr' ? 'Adres:' : 'Address:'; ?></strong> <?php echo htmlspecialchars($siparis_detay['teslimat_adresi']); ?></p>
                    <p><strong><?php echo $dil == 'tr' ? 'Müşteri:' : 'Customer:'; ?></strong> <?php echo htmlspecialchars($siparis_detay['ad_soyad']); ?></p>
                    <p><strong><?php echo $dil == 'tr' ? 'E-posta:' : 'Email:'; ?></strong> <?php echo htmlspecialchars($siparis_detay['email']); ?></p>
                </div>
                
                <!-- Ödeme Bilgisi -->
                <h3 style="color: #333; margin: 30px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #ffeef2;">
                    <i class="fas fa-credit-card"></i>
                    <?php echo $dil == 'tr' ? 'Ödeme Bilgisi' : 'Payment Information'; ?>
                </h3>
                <div class="kart-bilgisi">
                    <div class="kart-icon">💳</div>
                    <div>
                        <div><strong><?php echo $dil == 'tr' ? 'Kart Son 4 Hanesi:' : 'Card Last 4 Digits:'; ?></strong> <?php echo isset($siparis_detay['kart_son_dort']) ? '**** **** **** ' . $siparis_detay['kart_son_dort'] : ($dil == 'tr' ? 'Bilinmiyor' : 'Unknown'); ?></div>
                        <div><strong><?php echo $dil == 'tr' ? 'Ödeme Durumu:' : 'Payment Status:'; ?></strong> <span style="color: #4CAF50; font-weight: 600;"><?php echo $dil == 'tr' ? 'Tamamlandı' : 'Completed'; ?></span></div>
                    </div>
                </div>
            </div>
            
            <!-- TAKİP SÜRECİ -->
            <div class="takip-sureci">
                <h2 style="color: #333; margin-bottom: 25px; border-bottom: 2px solid #ffeef2; padding-bottom: 10px;">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo $dil == 'tr' ? 'Sipariş Takip' : 'Order Tracking'; ?>
                </h2>
                
                <!-- Takip Kodu -->
                <div style="text-align: center; margin-bottom: 30px; padding: 15px; background: linear-gradient(135deg, #2196f3 0%, #21CBF3 100%); color: white; border-radius: 10px;">
                    <div style="font-size: 0.9rem; opacity: 0.9;">
                        <?php echo $dil == 'tr' ? 'Takip Kodu' : 'Tracking Code'; ?>
                    </div>
                    <div style="font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; cursor: pointer;" class="takip-kodu" onclick="kopyalaTakipKodu()" title="<?php echo $dil == 'tr' ? 'Kopyalamak için tıklayın' : 'Click to copy'; ?>">
                        <?php echo $siparis_detay['takip_kodu']; ?>
                    </div>
                </div>
                
                <!-- Durum Göstergesi -->
                <div class="durum-gostergesi">
                    <?php 
                    $durumlar = ['onay_bekliyor', 'hazirlaniyor', 'kargoya_verildi', 'teslim_edildi'];
                    $mevcut_durum = $siparis_detay['durum'];
                    $mevcut_index = array_search($mevcut_durum, $durumlar);
                    if ($mevcut_index === false) $mevcut_index = 0;
                    
                    foreach ($durumlar as $index => $durum): 
                        $aktif = $index == $mevcut_index;
                        $tamamlandi = $index < $mevcut_index;
                    ?>
                        <div class="durum-adim <?php echo $aktif ? 'active' : ''; ?> <?php echo $tamamlandi ? 'completed' : ''; ?>">
                            <div class="durum-circle" style="<?php echo $tamamlandi ? 'background: #4CAF50; color: white;' : ''; ?>">
                                <?php echo durumIkonuGetir($durum); ?>
                            </div>
                            <div class="durum-label"><?php echo durumMetniGetir($durum, $dil); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Takip Geçmişi -->
                <h3 style="color: #333; margin: 40px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #ffeef2;">
                    <i class="fas fa-history"></i>
                    <?php echo $dil == 'tr' ? 'Takip Geçmişi' : 'Tracking History'; ?>
                </h3>
                
                <div class="takip-timeline">
                    <?php foreach ($siparis_detay['takip_gecmisi'] as $gecmis): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon"><?php echo $gecmis['icon'] ?? '📝'; ?></div>
                            <div class="timeline-content">
                                <div class="timeline-date"><?php echo $gecmis['tarih'] ?? date('d.m.Y H:i:s'); ?></div>
                                <div class="timeline-title"><?php echo htmlspecialchars($gecmis['aciklama'] ?? ($dil == 'tr' ? 'İşlem yapıldı' : 'Process completed')); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Tahsilat Makbuzu -->
                <div class="makbuz">
                    <div class="makbuz-baslik">📋 <?php echo $dil == 'tr' ? 'TAHHİLAT MAKBUZU' : 'PAYMENT RECEIPT'; ?></div>
                    <div class="makbuz-no"><?php echo $siparis_detay['siparis_no']; ?></div>
                    <div style="font-size: 1.1rem; color: #666; margin: 10px 0;">
                        <?php echo $siparis_detay['ad_soyad']; ?>
                    </div>
                    <div class="makbuz-tutar"><?php echo number_format($siparis_detay['genel_toplam'], 2); ?> TL</div>
                    <div style="color: #666; margin: 10px 0;">
                        <?php echo $siparis_detay['tarih']; ?>
                    </div>
                    <div class="qr-code" id="qrCode">
                        📱
                    </div>
                    <div style="font-size: 0.9rem; color: #999; margin-top: 15px;">
                        <?php echo $dil == 'tr' ? 'Bu makbuz siparişinizin resmi kaydıdır.' : 'This receipt is the official record of your order.'; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- BUTONLAR -->
        <div class="action-buttons">
            <a href="javascript:window.print()" class="action-btn btn-print">
                <i class="fas fa-print"></i> <?php echo $dil == 'tr' ? 'Yazdır' : 'Print'; ?>
            </a>
            <a href="siparislerim.php" class="action-btn btn-back">
                <i class="fas fa-list"></i> <?php echo $dil == 'tr' ? 'Tüm Siparişlerim' : 'All My Orders'; ?>
            </a>
            <a href="anasayfa.php" class="action-btn" style="background: #4CAF50; color: white;">
                <i class="fas fa-home"></i> <?php echo $dil == 'tr' ? 'Ana Sayfa' : 'Home'; ?>
            </a>
        </div>
    </div>
</div>

<script>
// ============================================================================
// DURUM GÜNCELLEME FONKSİYONLARI
// ============================================================================

function manuelDurumGuncelle() {
    const btn = document.getElementById('manualUpdateBtn');
    const dil = '<?php echo $dil; ?>';
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + 
                       (dil === 'tr' ? 'Güncelleniyor...' : 'Updating...');
    }
    
    // AJAX ile PHP'ye istek gönder
    fetch('siparis_takip.php?siparis_no=<?php echo urlencode($siparis_detay['siparis_no']); ?>&ajax=durum_guncelle')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Başarı mesajı göster
                const mesaj = dil === 'tr' ? '✅ Durum güncellendi: ' : '✅ Status updated: ';
                alert(mesaj + data.durum_metni);
                
                // 1 saniye bekle ve sayfayı yenile
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                const hataMesaji = dil === 'tr' ? '❌ Durum güncellenemedi: ' : '❌ Status could not be updated: ';
                alert(hataMesaji + (data.mesaj || ''));
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-forward"></i> ' + 
                                   (dil === 'tr' ? 'Durumu İlerlet' : 'Advance Status');
                }
            }
        })
        .catch(error => {
            console.error('Durum güncellenirken hata:', error);
            const hataMesaji = dil === 'tr' ? '❌ Bir hata oluştu: ' : '❌ An error occurred: ';
            alert(hataMesaji + error.message);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-forward"></i> ' + 
                               (dil === 'tr' ? 'Durumu İlerlet' : 'Advance Status');
            }
        });
}

// ============================================================================
// YARDIMCI FONKSİYONLAR
// ============================================================================

function kopyalaTakipKodu() {
    const takipKodu = '<?php echo $siparis_detay['takip_kodu']; ?>';
    const dil = '<?php echo $dil; ?>';
    
    navigator.clipboard.writeText(takipKodu).then(() => {
        alert((dil === 'tr' ? 'Takip kodu kopyalandı: ' : 'Tracking code copied: ') + takipKodu);
    }).catch(err => {
        console.error('Kopyalama hatası:', err);
        alert(dil === 'tr' ? 'Kopyalama başarısız!' : 'Copy failed!');
    });
}

// ============================================================================
// MEVCUT KODLAR
// ============================================================================

// QR kod animasyonu
const qrCode = document.getElementById('qrCode');
if (qrCode) {
    setInterval(() => {
        qrCode.style.transform = 'scale(1.05)';
        qrCode.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
        setTimeout(() => {
            qrCode.style.transform = 'scale(1)';
            qrCode.style.boxShadow = 'none';
        }, 300);
    }, 3000);
    
    qrCode.addEventListener('click', function() {
        const siparisNo = '<?php echo $siparis_detay['siparis_no']; ?>';
        const takipKodu = '<?php echo $siparis_detay['takip_kodu']; ?>';
        const dil = '<?php echo $dil; ?>';
        
        alert((dil === 'tr' ? 'Sipariş No: ' : 'Order No: ') + siparisNo + '\n' +
              (dil === 'tr' ? 'Takip Kodu: ' : 'Tracking Code: ') + takipKodu + '\n\n' +
              (dil === 'tr' ? 'Bu bilgileri kopyalayabilirsiniz.' : 'You can copy this information.'));
    });
    
    qrCode.style.cursor = 'pointer';
    qrCode.title = '<?php echo $dil == 'tr' ? 'Sipariş bilgilerini görüntülemek için tıklayın' : 'Click to view order information'; ?>';
}

// Durum göstergesi animasyonu
document.querySelectorAll('.durum-circle').forEach((circle, index) => {
    setTimeout(() => {
        circle.style.opacity = '1';
        circle.style.transform = 'scale(1)';
    }, index * 200);
});

// Timeline animasyonu
document.querySelectorAll('.timeline-item').forEach((item, index) => {
    setTimeout(() => {
        item.style.opacity = '1';
        item.style.transform = 'translateX(0)';
    }, index * 300);
});

// Yazdırma iyileştirmesi
window.addEventListener('beforeprint', () => {
    document.body.classList.add('printing');
});

window.addEventListener('afterprint', () => {
    document.body.classList.remove('printing');
});
</script>

<?php require_once 'footer.php'; ?>