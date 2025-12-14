<?php
require_once 'cicek.php';
require_once 'header.php';

// Oturum başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin giriş kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Giriş yapılmamışsa auth.php'ye yönlendir
    $_SESSION['auth_message'] = [
        'type' => 'error', 
        'text' => 'Bu sayfayı görüntülemek için admin girişi yapmalısınız!'
    ];
    header('Location: auth.php');
    exit;
}

// Dil ayarı
$dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';

// Mevcut sayfa bilgisi (dosya adından al)
$sayfa = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);

// Admin email
$admin_email = $_SESSION['admin_email'] ?? 'Admin';
?>

if(!isset($_SESSION['giris'])) {
    echo '<div class="hosgeldin" style="text-align:center;">';
    echo '<h2>📋 ' . $text['siparislerim'] . '</h2>';
    echo '<div style="background: rgba(255, 95, 162, 0.1); padding: 20px; border-radius: var(--radius); margin: 20px 0;">';
    echo '🔒 ' . ($dil == 'tr' ? 'Siparişlerinizi görmek için giriş yapmalısınız' : 'You must login to view your orders');
    echo '</div>';
    echo '<button onclick="acModal()" class="odeme-btn" style="width: auto; display: inline-block; padding: 12px 30px;">' . $text['giris'] . '</button>';
    echo '</div>';
} else {
    echo '<div class="siparis-takip-container">';
    echo '<h1 style="margin-bottom: 30px;">📋 ' . $text['siparislerim'] . '</h1>';
    
    if(empty($_SESSION['siparisler'])) {
        echo '<div class="hosgeldin" style="text-align: center;">';
        echo '<h3>📭 ' . ($dil == 'tr' ? 'Henüz siparişiniz yok' : 'You have no orders yet') . '</h3>';
        echo '<p style="margin: 20px 0;">' . ($dil == 'tr' ? 'İlk siparişinizi vermek için hemen alışverişe başlayın!' : 'Start shopping now to place your first order!') . '</p>';
        echo '<a href="?sayfa=anasayfa" class="odeme-btn" style="width: auto; display: inline-block; padding: 12px 30px;">' . ($dil == 'tr' ? 'Alışverişe Başla' : 'Start Shopping') . '</a>';
        echo '</div>';
    } else {
        echo '<div class="siparis-grid">';
        
        foreach(array_reverse($_SESSION['siparisler']) as $siparis) {
            $durum_sinif = 'durum-siparis-alindi';
            if(strpos($siparis['durum'], 'Hazırlanıyor') !== false) {
                $durum_sinif = 'durum-hazirlaniyor';
            } elseif(strpos($siparis['durum'], 'Kargoda') !== false) {
                $durum_sinif = 'durum-kargoda';
            } elseif(strpos($siparis['durum'], 'Teslim Edildi') !== false) {
                $durum_sinif = 'durum-teslim-edildi';
            }
            
            echo '<div class="siparis-kart">';
            echo '<div class="siparis-header">';
            echo '<div class="siparis-no">' . $siparis['siparis_no'] . '</div>';
            echo '<div class="siparis-durum ' . $durum_sinif . '">' . $siparis['durum'] . '</div>';
            echo '</div>';
            
            echo '<div class="siparis-detay">';
            echo '<div class="siparis-detay-item">';
            echo '<span>' . $text['siparis_tarihi'] . ':</span>';
            echo '<span>' . $siparis['tarih'] . '</span>';
            echo '</div>';
            
            echo '<div class="siparis-detay-item">';
            echo '<span>' . $text['takip_kodu'] . ':</span>';
            echo '<span style="font-weight: 700; color: var(--accent-color);">' . $siparis['takip_kodu'] . '</span>';
            echo '</div>';
            
            echo '<div class="siparis-detay-item">';
            echo '<span>' . $text['tahmini_teslim'] . ':</span>';
            echo '<span>' . $siparis['tahmini_teslim'] . '</span>';
            echo '</div>';
            
            echo '<div class="siparis-detay-item">';
            echo '<span>' . $text['odeme_secenekleri'] . ':</span>';
            echo '<span>' . $siparis['odeme_tipi'] . '</span>';
            echo '</div>';
            
            echo '<div class="siparis-detay-item">';
            echo '<span>' . $text['teslimat_adresi'] . ':</span>';
            echo '<span style="text-align: right;">' . $siparis['teslimat_adresi'] . '</span>';
            echo '</div>';
            echo '</div>';
            
            echo '<div class="siparis-urun-listesi">';
            echo '<h4 style="margin-bottom: 15px; color: var(--text-primary);">' . $text['urun_detaylari'] . ':</h4>';
            foreach($siparis['urunler'] as $urun) {
                echo '<div class="siparis-urun-item">';
                echo '<div>' . $urun['isim'] . ' × ' . $urun['adet'] . '</div>';
                echo '<div>' . number_format($urun['fiyat'] * $urun['adet'], 2) . ' TL</div>';
                echo '</div>';
            }
            echo '</div>';
            
            echo '<div class="siparis-detay-item toplam" style="margin-top: 20px; padding-top: 15px; border-top: 2px solid var(--accent-color);">';
            echo '<span style="font-size: 18px; font-weight: 700;">' . $text['genel_toplam'] . ':</span>';
            echo '<span style="font-size: 20px; font-weight: 800; color: var(--accent-color);">' . number_format($siparis['toplam'], 2) . ' TL</span>';
            echo '</div>';
            
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    echo '</div>';
}
require_once 'footer.php';

?>