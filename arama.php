<?php
require_once 'cicek.php';
require_once 'header.php';

// Arama parametreleri
$aranan = isset($_GET['arama']) ? trim($_GET['arama']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

// Ürünleri getir ve filtrele
function urunleriGetir($kategori = 'tumu') {
    $urunler_dosya = 'urunler.json';
    if(!file_exists($urunler_dosya)) {
        return [];
    }
    
    $urunler = json_decode(file_get_contents($urunler_dosya), true);
    
    if($kategori != 'tumu' && !empty($kategori)) {
        $urunler = array_filter($urunler, function($urun) use ($kategori) {
            return ($urun['kategori'] ?? '') == $kategori;
        });
        $urunler = array_values($urunler);
    }
    
    return $urunler;
}

function urunAra($kelime, $kategori = '', $dil = 'tr') {
    $urunler = urunleriGetir($kategori);
    
    if(empty($kelime)) {
        return $urunler;
    }
    
    $kelime = strtolower(trim($kelime));
    $sonuclar = array_filter($urunler, function($urun) use ($kelime, $dil) {
        $ad = strtolower(($dil == 'tr' ? ($urun['tr_ad'] ?? $urun['ad'] ?? '') : ($urun['en_ad'] ?? $urun['ad'] ?? '')));
        $aciklama = strtolower(($dil == 'tr' ? ($urun['tr_aciklama'] ?? $urun['aciklama'] ?? '') : ($urun['en_aciklama'] ?? $urun['aciklama'] ?? '')));
        
        return strpos($ad, $kelime) !== false || 
               strpos($aciklama, $kelime) !== false;
    });
    
    return array_values($sonuclar);
}

$sonuclar = urunAra($aranan, $kategori);

echo '<div class="breadcrumb">';
echo '<a href="anasayfa.php?sayfa=anasayfa">🏠 ' . $text_selected['hosgeldin'] . '</a>';
echo '<span class="separator">›</span>';
echo '<span>' . ($dil == 'tr' ? 'Arama Sonuçları' : 'Search Results') . '</span>';
if(!empty($aranan)) {
    echo '<span class="separator">›</span>';
    echo '<span>"' . htmlspecialchars($aranan) . '"</span>';
}
echo '</div>';

echo '<div class="arama-sonuc-container" style="padding: 20px; max-width: 1400px; margin: 0 auto;">';
echo '<h1 style="margin-bottom: 30px;">' . ($dil == 'tr' ? 'Arama Sonuçları' : 'Search Results') . '</h1>';

if(!empty($sonuclar)) {
    echo '<p style="margin-bottom: 30px;"><strong>' . count($sonuclar) . '</strong> ' . ($dil == 'tr' ? 'ürün bulundu.' : 'products found.') . '</p>';
    
    echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; margin-top: 20px;">';
    foreach($sonuclar as $urun) {
        $urun_ad = $dil == 'tr' ? ($urun['tr_ad'] ?? $urun['ad'] ?? 'Ürün') : ($urun['en_ad'] ?? $urun['ad'] ?? 'Product');
        $urun_aciklama = $dil == 'tr' ? ($urun['tr_aciklama'] ?? $urun['aciklama'] ?? '') : ($urun['en_aciklama'] ?? $urun['aciklama'] ?? '');
        $kategori_adi = $urun['kategori'] ?? '';
        
        // Kategori ikonu
        switch($kategori_adi) {
            case 'gul': $kategori_ikon = '🌹'; break;
            case 'orkide': $kategori_ikon = '💮'; break;
            case 'lale': $kategori_ikon = '🌷'; break;
            case 'buket': $kategori_ikon = '💐'; break;
            case 'sukulent': $kategori_ikon = '🌵'; break;
            default: $kategori_ikon = '🌸';
        }
        
        echo '<div style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: transform 0.3s;">';
        echo '<div style="background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%); height: 180px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">';
        echo '<span style="font-size: 60px;">' . $kategori_ikon . '</span>';
        echo '</div>';
        echo '<h3 style="margin: 0 0 10px 0; color: #333; font-size: 1.2rem;">' . htmlspecialchars($urun_ad) . '</h3>';
        echo '<p style="color: #666; font-size: 14px; margin-bottom: 15px; height: 40px; overflow: hidden;">' . htmlspecialchars(substr($urun_aciklama, 0, 80)) . '...</p>';
        echo '<div style="display: flex; justify-content: space-between; align-items: center;">';
        echo '<span style="font-weight: bold; color: #ff6b9d; font-size: 1.3rem;">' . number_format($urun['fiyat'] ?? 0, 2) . ' ₺</span>';
        echo '<button onclick="addToCart(' . ($urun['id'] ?? 0) . ')" style="background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 5px;">';
        echo '🛒 ' . ($dil == 'tr' ? 'Sepete Ekle' : 'Add to Cart');
        echo '</button>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
} else {
    echo '<div style="text-align: center; padding: 60px 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">';
    echo '<p style="font-size: 1.2rem; color: #666; margin-bottom: 20px;">';
    echo $dil == 'tr' 
        ? 'Aradığınız kriterlere uygun ürün bulunamadı.' 
        : 'No products matching your search criteria were found.';
    echo '</p>';
    echo '<a href="urunler.php?sayfa=urunler&kategori=tumu" style="display: inline-block; background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%); color: white; padding: 12px 30px; border-radius: 25px; text-decoration: none; font-weight: bold; margin-top: 20px;">';
    echo $dil == 'tr' ? 'Tüm Ürünleri Gör' : 'View All Products';
    echo '</a>';
    echo '</div>';
}

echo '</div>';

require_once 'footer.php';
?>