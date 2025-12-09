<?php
// HATALARI GÖSTER
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// SESSION BAŞLAT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// TEMEL DEĞİŞKENLERİ BAŞLAT
if (!isset($_SESSION['sepet'])) $_SESSION['sepet'] = [];
if (!isset($_SESSION['favoriler'])) $_SESSION['favoriler'] = [];
if (!isset($_SESSION['kullanici_id'])) $_SESSION['kullanici_id'] = null;
if (!isset($_SESSION['ad_soyad'])) $_SESSION['ad_soyad'] = '';

// DİL AYARI
$dil = $_COOKIE['dil'] ?? 'tr';
if (isset($_GET['dil']) && in_array($_GET['dil'], ['tr', 'en'])) {
    $dil = $_GET['dil'];
    setcookie('dil', $dil, time() + (365 * 24 * 60 * 60), '/');
}

// TEMA AYARI
$tema = $_COOKIE['tema'] ?? 'light';
if (isset($_GET['tema']) && in_array($_GET['tema'], ['light', 'dark'])) {
    $tema = $_GET['tema'];
    setcookie('tema', $tema, time() + (365 * 24 * 60 * 60), '/');
}

// SAYFA ve KATEGORİ
$sayfa = $_GET['sayfa'] ?? 'anasayfa';
$kategori = $_GET['kategori'] ?? '';

// ÇIKIŞ İŞLEMİ
if (isset($_GET['cikis'])) {
    session_destroy();
    header('Location: anasayfa.php');
    exit;
}

// METİN DİZİSİ - TÜM ANAHTARLAR TAM
$text = [
    'tr' => [
        'hosgeldin' => 'Hoş Geldiniz',
        'urunler' => 'Ürünler',
        'sepet' => 'Sepet',
        'favoriler' => 'Favoriler',
        'siparisler' => 'Siparişler',
        'kuponlarim' => 'Kuponlarım',
        'kupon' => 'Kupon',
        'profilim' => 'Profilim',
        'iletisim' => 'İletişim',
        'giris' => 'Giriş Yap',
        'uye_ol' => 'Üye Ol',
        'cikis' => 'Çıkış',
        'devam_eden' => 'Devam Eden Siparişler',
        'son_siparisler' => 'Son Siparişler',
        'tumunu_gor' => 'Tümünü Gör',
        'tum_urunler' => 'Tüm Ürünler',
        'urun_ara' => 'Ürün Ara',
        'ara_placeholder' => 'Çiçek adı veya kategori ara...',
        'email' => 'E-posta',
        'sifre' => 'Şifre',
        'ad_soyad' => 'Ad Soyad',
        'tel' => 'Telefon'
    ],
    'en' => [
        'hosgeldin' => 'Welcome',
        'urunler' => 'Products',
        'sepet' => 'Cart',
        'favoriler' => 'Favorites',
        'siparisler' => 'Orders',
        'kuponlarim' => 'My Coupons',
        'kupon' => 'Coupon',
        'profilim' => 'My Profile',
        'iletisim' => 'Contact',
        'giris' => 'Login',
        'uye_ol' => 'Sign Up',
        'cikis' => 'Logout',
        'devam_eden' => 'Ongoing Orders',
        'son_siparisler' => 'Recent Orders',
        'tumunu_gor' => 'View All',
        'tum_urunler' => 'All Products',
        'urun_ara' => 'Search Products',
        'ara_placeholder' => 'Search flower or category...',
        'email' => 'Email',
        'sifre' => 'Password',
        'ad_soyad' => 'Full Name',
        'tel' => 'Phone'
    ]
];

// METİNLERİ SEÇ
$text_selected = isset($text[$dil]) ? $text[$dil] : $text['tr'];

// FONKSİYONLAR
function kullaniciGirisKontrol() {
    return isset($_SESSION['kullanici_id']) && $_SESSION['kullanici_id'] !== null;
}

function csrfTokenOlustur() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfTokenKontrol($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function jsonOku($dosya) {
    if (file_exists($dosya)) {
        $icerik = @file_get_contents($dosya);
        if ($icerik === false) return [];
        $data = @json_decode($icerik, true);
        return $data ?? [];
    }
    return [];
}

function jsonYaz($dosya, $veri) {
    $json = json_encode($veri, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return @file_put_contents($dosya, $json);
}

function urunleriGetir($kategori = null) {
    $urunler = jsonOku('urunler.json');
    
    if (empty($urunler) || !is_array($urunler)) {
        return [];
    }
    
    if ($kategori && $kategori !== '') {
        return array_filter($urunler, function($urun) use ($kategori) {
            return isset($urun['kategori']) && $urun['kategori'] == $kategori;
        });
    }
    
    return $urunler;
}

function urunAra($kelime, $kategori = null) {
    $urunler = urunleriGetir();
    
    if (empty($urunler)) {
        return [];
    }
    
    $sonuclar = [];
    $kelime = strtolower(trim($kelime));
    
    foreach ($urunler as $urun) {
        // Kategori filtresi
        if ($kategori && $kategori !== '' && isset($urun['kategori']) && $urun['kategori'] != $kategori) {
            continue;
        }
        
        // Arama
        $bulundu = false;
        
        if (!empty($kelime)) {
            // Ürün adında ara
            if (isset($urun['ad']) && stripos(strtolower($urun['ad']), $kelime) !== false) {
                $bulundu = true;
            }
            // Açıklamada ara
            elseif (isset($urun['aciklama']) && stripos(strtolower($urun['aciklama']), $kelime) !== false) {
                $bulundu = true;
            }
            // Kategoride ara
            elseif (isset($urun['kategori']) && stripos(strtolower($urun['kategori']), $kelime) !== false) {
                $bulundu = true;
            }
        } else {
            // Kelime yoksa, sadece kategoriye göre filtrele
            $bulundu = true;
        }
        
        if ($bulundu) {
            $sonuclar[] = $urun;
        }
    }
    
    return $sonuclar;
}

function kullaniciSiparisleriGetir($kullaniciId) {
    $tumSiparisler = jsonOku('siparisler.json');
    
    if (empty($tumSiparisler) || !is_array($tumSiparisler)) {
        return [];
    }
    
    return array_filter($tumSiparisler, function($siparis) use ($kullaniciId) {
        return isset($siparis['kullanici_id']) && $siparis['kullanici_id'] == $kullaniciId;
    });
}

function yorumlariGetir() {
    return jsonOku('yorumlar.json');
}

function toastMesaji($tip, $mesaj) {
    $_SESSION['toast'] = ['tip' => $tip, 'mesaj' => $mesaj];
}

function hataMesaji($mesaj) {
    $_SESSION['hata'] = $mesaj;
}

// TOAST ve HATA MESAJLARI
$toast = null;
$hata = null;

if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

if (isset($_SESSION['hata'])) {
    $hata = $_SESSION['hata'];
    unset($_SESSION['hata']);
}
?>