<?php
require_once 'cicek.php';
require_once 'header.php';

if(!isset($_SESSION['giris'])) {
    echo '<div class="hosgeldin" style="text-align:center;">';
    echo '<h2>🎫 ' . $text['kuponlarim'] . '</h2>';
    echo '<div style="background: rgba(255, 95, 162, 0.1); padding: 20px; border-radius: var(--radius); margin: 20px 0;">';
    echo '🔒 ' . ($dil == 'tr' ? 'Kuponlarınızı görmek için giriş yapmalısınız' : 'You must login to view your coupons');
    echo '</div>';
    echo '<button onclick="acModal()" class="odeme-btn" style="width: auto; display: inline-block; padding: 12px 30px;">' . $text['giris'] . '</button>';
    echo '</div>';
} else {
    echo '<div class="kuponlar-container">';
    echo '<h1 style="margin-bottom: 30px;">🎫 ' . $text['kuponlarim'] . '</h1>';
    
    if(empty($_SESSION['kullanici_kuponlari'])) {
        echo '<div class="hosgeldin" style="text-align: center;">';
        echo '<h3>🎁 ' . ($dil == 'tr' ? 'Henüz kuponunuz yok' : 'You have no coupons yet') . '</h3>';
        echo '<p style="margin: 20px 0;">' . ($dil == 'tr' ? 'İlk alışverişinizde otomatik olarak kupon kazanacaksınız!' : 'You will automatically earn coupons on your first purchase!') . '</p>';
        echo '<a href="?sayfa=anasayfa" class="odeme-btn" style="width: auto; display: inline-block; padding: 12px 30px;">' . ($dil == 'tr' ? 'Alışverişe Başla' : 'Start Shopping') . '</a>';
        echo '</div>';
    } else {
        echo '<div class="kupon-grid">';
        
        foreach($_SESSION['kullanici_kuponlari'] as $kupon) {
            $tarih_farki = strtotime($kupon['son_kullanma']) - time();
            $kalan_gun = ceil($tarih_farki / (60 * 60 * 24));
            
            echo '<div class="kupon-kart">';
            echo '<div class="kupon-header">';
            echo '<div class="kupon-kodu">' . $kupon['kod'] . '</div>';
            echo '<div class="kupon-indirim">%' . $kupon['indirim'] . '</div>';
            echo '</div>';
            
            echo '<p class="kupon-aciklama">' . $kupon['aciklama'] . '</p>';
            
            echo '<div class="kupon-detay">';
            echo '<div>';
            echo '<div style="font-weight: 600; margin-bottom: 5px;">' . $text['son_kullanma'] . '</div>';
            echo '<div style="color: ' . ($kalan_gun < 7 ? 'var(--error-color)' : 'var(--success-color)') . ';">';
            echo date('d.m.Y', strtotime($kupon['son_kullanma']));
            echo ' (' . ($dil == 'tr' ? $kalan_gun . ' gün kaldı' : $kalan_gun . ' days left') . ')';
            echo '</div>';
            echo '</div>';
            
            echo '<div>';
            echo '<div style="font-weight: 600; margin-bottom: 5px;">' . ($dil == 'tr' ? 'Durum' : 'Status') . '</div>';
            echo '<div class="kupon-durum ' . ($kupon['durum'] == 'aktif' ? 'durum-aktif' : 'durum-kullanildi') . '">';
            echo $kupon['durum'] == 'aktif' ? $text['kullan'] : $text['kullanildi'];
            echo '</div>';
            echo '</div>';
            echo '</div>';
            
            if($kupon['durum'] == 'aktif') {
                echo '<button onclick="window.location.href=\'?kupon_kullan=' . $kupon['kod'] . '\'" class="kupon-kullan-btn">' . $text['kullan'] . ' 🎯</button>';
            } else {
                echo '<button class="kupon-kullan-btn" disabled>' . $text['kullanildi'] . ' ✅</button>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    echo '</div>';
}
require_once 'footer.php';

?>