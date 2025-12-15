<?php
ob_start(); // Output buffering'i BAŞLANGIÇTA başlat

// SESSION BAŞLATMA (Eğer başlatılmadıysa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dil ve tema değişkenlerini ayarla
$dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';
$tema = isset($_COOKIE['tema']) ? $_COOKIE['tema'] : 'light';

// Giriş kontrol fonksiyonları
function isNormalUser() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function isLoggedIn() {
    return isNormalUser() || isAdmin();
}

// Giriş durumu değişkenleri
$is_logged_in = isLoggedIn();
$is_admin = isAdmin();
$user_id = $_SESSION['user_id'] ?? null;
$admin_email = $_SESSION['admin_email'] ?? null;

// Favoriler için session kontrolü (giriş yapmadan da çalışsın)
if(!isset($_SESSION['favoriler'])) {
    $_SESSION['favoriler'] = [];
}

// Sepet için session kontrolü
if(!isset($_SESSION['sepet'])) {
    $_SESSION['sepet'] = [];
}

// CSRF token fonksiyonu
if(!function_exists('csrfTokenOlustur')) {
    function csrfTokenOlustur() {
        if(!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// CSRF token'ı oluştur (eğer yoksa)
if(!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = csrfTokenOlustur();
}

// $text_selected değişkeninin tanımlı olduğundan emin ol
if(!isset($text_selected)) {
    $text_selected = [
        'giris' => $dil == 'tr' ? 'Giriş Yap' : 'Login',
        'uye_ol' => $dil == 'tr' ? 'Üye Ol' : 'Register',
        'email' => $dil == 'tr' ? 'E-posta' : 'Email',
        'sifre' => $dil == 'tr' ? 'Şifre' : 'Password',
        'ad_soyad' => $dil == 'tr' ? 'Ad Soyad' : 'Full Name',
        'sifre_tekrar' => $dil == 'tr' ? 'Şifre Tekrar' : 'Confirm Password',
        'tel' => $dil == 'tr' ? 'Telefon' : 'Phone',
        'adres' => $dil == 'tr' ? 'Adres' : 'Address',
        'hosgeldin' => $dil == 'tr' ? 'Anasayfa' : 'Home',
        'urunler' => $dil == 'tr' ? 'Ürünler' : 'Products',
        'sepet' => $dil == 'tr' ? 'Sepet' : 'Cart',
        'favoriler' => $dil == 'tr' ? 'Favoriler' : 'Favorites',
        'siparisler' => $dil == 'tr' ? 'Siparişler' : 'Orders',
        'kuponlarim' => $dil == 'tr' ? 'Kuponlarım' : 'My Coupons',
        'kupon' => $dil == 'tr' ? 'Kupon' : 'Coupon',
        'profilim' => $dil == 'tr' ? 'Profilim' : 'My Profile',
        'iletisim' => $dil == 'tr' ? 'İletişim' : 'Contact',
        'cikis' => $dil == 'tr' ? 'Çıkış' : 'Logout',
        'tum_urunler' => $dil == 'tr' ? 'Tüm Ürünler' : 'All Products',
        'ara_placeholder' => $dil == 'tr' ? 'Ürün ara...' : 'Search products...',
        'urun_ara' => $dil == 'tr' ? 'Ara' : 'Search'
    ];
}

// Yorumlar için JSON dosyası kontrolü
$yorumlar_dosya = 'yorumlar.json';
if(!file_exists($yorumlar_dosya)) {
    file_put_contents($yorumlar_dosya, json_encode([]));
}

// Sayfa değişkenini kontrol et (breadcrumb için)
$sayfa = basename($_SERVER['PHP_SELF'], '.php');
$sayfa_isimleri = [
    'anasayfa' => $text_selected['hosgeldin'],
    'urunler' => $text_selected['urunler'],
    'sepet' => $text_selected['sepet'],
    'favoriler' => $text_selected['favoriler'],
    'profil' => $text_selected['profilim'],
    'iletisim' => $text_selected['iletisim'],
    'auth' => $text_selected['giris'],
    'odeme' => 'Ödeme',
    'siparisler' => $text_selected['siparisler'],
    'kuponlar' => $text_selected['kuponlarim'],
    'chat' => 'Sohbet',
    'cicek' => 'Çiçek Detay'
];
$sayfa_adi = $sayfa_isimleri[$sayfa] ?? ucfirst($sayfa);
?><!DOCTYPE html>
<html data-theme="<?php echo htmlspecialchars($tema); ?>" lang="<?php echo htmlspecialchars($dil); ?>">
<head>
    <title>ÇiçekBahçesi - <?php echo $dil == 'tr' ? 'En Güzel Çiçekler' : 'Most Beautiful Flowers'; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $dil == 'tr' ? 'ÇiçekBahçesi - En güzel çiçekler ve aranjmanlar, taze ve uygun fiyatlarla' : 'FlowerGarden - Beautiful flowers and arrangements, fresh and affordable'; ?>">
    <meta name="keywords" content="çiçek, buket, gül, orkide, lale, sukulent, hediye, çiçekçi">
    <meta name="author" content="ÇiçekBahçesi">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- PEMBE TASARIM STİLLERİ -->
    <style>
        /* TEMEL STİLLER */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        /* NAVBAR - PEMBE TASARIM */
        .navbar {
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(255, 107, 157, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-icon {
            font-size: 32px;
            animation: bounce 2s infinite;
        }
        
        .logo-text {
            font-family: 'Dancing Script', cursive;
            font-size: 28px;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        /* NAV LİNKLER */
        .nav-links {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        
        .nav-link.cikis {
            background: rgba(255,255,255,0.2);
        }
        
        .nav-link.cikis:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* KULLANICI BİLGİSİ */
        .kullanici-bilgi {
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 8px;
            color: white;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-right: 10px;
        }
        
        .kullanici-bilgi.admin {
            background: rgba(211, 47, 47, 0.2);
        }
        
        .user-points {
            background: white;
            color: #ff6b9d;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .admin-badge {
            background: #d32f2f;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        /* SAYAÇLAR */
        .sepet-sayaci, .favori-sayaci {
            background: white;
            color: #ff6b9d;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: bold;
            animation: bounce 0.5s;
        }
        
        /* BUTONLAR */
        .auth-button {
            background: white;
            color: #ff6b9d;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .auth-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,255,255,0.2);
        }
        
        .admin-button {
            background: #d32f2f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .admin-button:hover {
            background: #b71c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
        }
        
        /* DİL ve TEMA SEÇİCİLER */
        .dil-secici, .tema-degistirici {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .dil-secici option {
            color: #333;
        }
        
        /* ARAMA ÇUBUĞU */
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .arama-cubugu {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
            margin-bottom: 20px;
        }
        
        .arama-wrapper {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .kategori-select {
            padding: 12px;
            border: 2px solid #ffeef2;
            border-radius: 10px;
            font-size: 1rem;
            background: white;
            cursor: pointer;
            min-width: 200px;
        }
        
        .arama-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #ffeef2;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .arama-input:focus {
            outline: none;
            border-color: #ff6b9d;
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }
        
        .arama-buton {
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .arama-buton:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 157, 0.3);
        }
        
        /* BREADCRUMB */
        .breadcrumb {
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(255, 107, 157, 0.1);
        }
        
        .breadcrumb a {
            color: #ff6b9d;
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb .separator {
            margin: 0 10px;
            color: #999;
        }
        
        /* MESAJ KUTUSU */
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            animation: slideIn 0.3s ease-out;
            border-left: 4px solid;
        }
        
        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left-color: #4CAF50;
        }
        
        .message.error {
            background: #ffebee;
            color: #c62828;
            border-left-color: #f44336;
        }
        
        .message.info {
            background: #e3f2fd;
            color: #1565c0;
            border-left-color: #2196f3;
        }
        
        /* ANİMASYONLAR */
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .arama-wrapper {
                flex-direction: column;
            }
            
            .kategori-select {
                width: 100%;
            }
        }
    </style>
    
    <script>
    // GİRİŞ GEREKTİREN SAYFALAR İÇİN UYARI (SADECE PROFİL İÇİN)
    function showLoginRequired(pageType) {
        if(pageType === 'profil') {
            alert('Profilinizi görüntülemek için giriş yapmalısınız!');
            window.location.href = 'auth.php';
        }
        // Sepet için artık uyarı göstermiyoruz - giriş yapmadan da sepet görüntülenebilir
    }
    
    // DİL DEĞİŞTİR
    function dilDegistir(dil) {
        document.cookie = "dil=" + dil + "; path=/; max-age=31536000";
        location.reload();
    }
    
    // TEMA DEĞİŞTİR
    function temaDegistir() {
        const html = document.documentElement;
        const tema = html.getAttribute('data-theme');
        const yeniTema = tema === 'light' ? 'dark' : 'light';
        html.setAttribute('data-theme', yeniTema);
        document.cookie = "tema=" + yeniTema + "; path=/; max-age=31536000";
    }
    
    // SAYFA YÜKLENDİĞİNDE
    document.addEventListener('DOMContentLoaded', function() {
        // Tema butonu için ikonu ayarla
        const temaBtn = document.querySelector('.tema-degistirici');
        if(temaBtn) {
            const tema = document.documentElement.getAttribute('data-theme');
            temaBtn.innerHTML = tema === 'light' ? '🌙' : '☀️';
        }
    });
    </script>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <span class="logo-icon">🌸</span>
                <span class="logo-text">ÇiçekBahçesi</span>
            </div>
            
            <?php if($is_logged_in): ?>
                <!-- Giriş yapmış kullanıcı için menü -->
                <?php if($is_admin): ?>
                    <!-- Admin giriş yapmışsa -->
                    <div class="kullanici-bilgi admin">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin Paneli</span>
                        <span class="admin-badge">ADMIN</span>
                    </div>
                <?php else: ?>
                    <!-- Normal kullanıcı giriş yapmışsa -->
                    <div class="kullanici-bilgi">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['ad_soyad'] ?? ''); ?></span>
                        <span class="user-points"><?php echo $_SESSION['puan'] ?? 0; ?> puan</span>
                    </div>
                <?php endif; ?>
                
                <div class="nav-links">
                    <a href="anasayfa.php" class="nav-link">
                        <i class="fas fa-home"></i> <?php echo $text_selected['hosgeldin']; ?>
                    </a>
                    <a href="urunler.php" class="nav-link">
                        <i class="fas fa-store"></i> <?php echo $text_selected['urunler']; ?>
                    </a>
                    <a href="sepet.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> <?php echo $text_selected['sepet']; ?>
                        <?php if(isset($_SESSION['sepet']) && count($_SESSION['sepet']) > 0): ?>
                            <span class="sepet-sayaci"><?php echo count($_SESSION['sepet']); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="favoriler.php" class="nav-link">
                        <i class="fas fa-heart"></i> <?php echo $text_selected['favoriler']; ?>
                        <?php if(isset($_SESSION['favoriler']) && count($_SESSION['favoriler']) > 0): ?>
                            <span class="favori-sayaci"><?php echo count($_SESSION['favoriler']); ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <!-- SİPARİŞLERİM LİNKİ -->
                    <?php if(!$is_admin): ?>
                    <a href="siparislerim.php" class="nav-link">
                        <i class="fas fa-box"></i> <?php echo $text_selected['siparisler']; ?>
                    </a>
                    <?php endif; ?>
                    
                    <!-- PROFİL LİNKİ -->
                    <a href="profil.php" class="nav-link">
                        <i class="fas fa-user"></i> <?php echo $text_selected['profilim']; ?>
                    </a>
                    
                    <a href="auth.php?action=cikis" class="nav-link cikis">
                        <i class="fas fa-sign-out-alt"></i> <?php echo $text_selected['cikis']; ?>
                    </a>
                    
                    <select class="dil-secici" onchange="dilDegistir(this.value)">
                        <option value="tr" <?php echo $dil == 'tr' ? 'selected' : ''; ?>>🇹🇷 TR</option>
                        <option value="en" <?php echo $dil == 'en' ? 'selected' : ''; ?>>🇺🇸 EN</option>
                    </select>
                    
                    <button class="tema-degistirici" onclick="temaDegistir()">
                        <?php echo $tema === 'light' ? '🌙' : '☀️'; ?>
                    </button>
                </div>
                
            <?php else: ?>
                <!-- Giriş yapmamış kullanıcı için menü -->
                <div class="nav-links">
                    <a href="anasayfa.php" class="nav-link">
                        <i class="fas fa-home"></i> <?php echo $text_selected['hosgeldin']; ?>
                    </a>
                    <a href="urunler.php" class="nav-link">
                        <i class="fas fa-store"></i> <?php echo $text_selected['urunler']; ?>
                    </a>
                    <!-- SEPET LİNKİ - GİRİŞ YAPMADAN DA GÖRÜNTÜLENEBİLİR -->
                    <a href="sepet.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> <?php echo $text_selected['sepet']; ?>
                        <?php if(isset($_SESSION['sepet']) && count($_SESSION['sepet']) > 0): ?>
                            <span class="sepet-sayaci"><?php echo count($_SESSION['sepet']); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="favoriler.php" class="nav-link">
                        <i class="fas fa-heart"></i> <?php echo $text_selected['favoriler']; ?>
                        <?php if(isset($_SESSION['favoriler']) && count($_SESSION['favoriler']) > 0): ?>
                            <span class="favori-sayaci"><?php echo count($_SESSION['favoriler']); ?></span>
                        <?php endif; ?>
                    </a>
                    <!-- PROFİL LİNKİ - GİRİŞ YAPMA SAYFASINA YÖNLENDİRİR -->
                    <a href="auth.php" class="nav-link">
                        <i class="fas fa-user"></i> <?php echo $text_selected['profilim']; ?>
                    </a>
                    
                    <!-- GİRİŞ YAP BUTONU -->
                    <a href="auth.php" class="auth-button">
                        <i class="fas fa-user"></i> <?php echo $text_selected['giris']; ?>
                    </a>
                    
                    <!-- ADMIN GİRİŞ BUTONU -->
                    <a href="auth.php?type=admin" class="admin-button">
                        <i class="fas fa-user-shield"></i> Admin
                    </a>
                    
                    <!-- DİL SEÇİCİ -->
                    <select class="dil-secici" onchange="dilDegistir(this.value)">
                        <option value="tr" <?php echo $dil == 'tr' ? 'selected' : ''; ?>>🇹🇷 TR</option>
                        <option value="en" <?php echo $dil == 'en' ? 'selected' : ''; ?>>🇺🇸 EN</option>
                    </select>
                    
                    <!-- TEMA DEĞİŞTİRİCİ -->
                    <button class="tema-degistirici" onclick="temaDegistir()">
                        <?php echo $tema === 'light' ? '🌙' : '☀️'; ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- ARAMA ÇUBUĞU -->
    <div class="container">
        <div class="arama-cubugu">
            <form method="get" action="urunler.php">
                <div class="arama-wrapper">
                    <select name="kategori" class="kategori-select">
                        <option value=""><?php echo $text_selected['tum_urunler']; ?></option>
                        <option value="gul">🌹 <?php echo $dil == 'tr' ? 'Güller' : 'Roses'; ?></option>
                        <option value="orkide">💮 <?php echo $dil == 'tr' ? 'Orkideler' : 'Orchids'; ?></option>
                        <option value="lale">🌷 <?php echo $dil == 'tr' ? 'Laleler' : 'Tulips'; ?></option>
                        <option value="buket">💐 <?php echo $dil == 'tr' ? 'Buketler' : 'Bouquets'; ?></option>
                        <option value="sukulent">🌵 <?php echo $dil == 'tr' ? 'Sukulentler' : 'Succulents'; ?></option>
                    </select>
                    
                    <input type="text" name="arama" class="arama-input" placeholder="<?php echo $text_selected['ara_placeholder']; ?>">
                    
                    <button type="submit" class="arama-buton">
                        <i class="fas fa-search"></i> <?php echo $text_selected['urun_ara']; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- BREADCRUMB -->
        <div class="breadcrumb">
            <a href="anasayfa.php">🏠 <?php echo $text_selected['hosgeldin']; ?></a>
            <?php if($sayfa != "anasayfa"): ?>
                <span class="separator">›</span>
                <span><?php echo $sayfa_adi; ?></span>
            <?php endif; ?>
        </div>
        
        <!-- İÇERİK BAŞLANGICI -->
        <div class="main-content">