<?php
ob_start(); // Output buffering'i BAŞLANGIÇTA başlat

// SESSION BAŞLATMA (Eğer başlatılmadıysa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dil ve tema değişkenlerini ayarla
$dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';
$tema = isset($_COOKIE['tema']) ? $_COOKIE['tema'] : 'light';

// Chat mesaj sayısını kontrol et
$chat_file = 'chat.json';
$unread_messages = 0;
if(file_exists($chat_file)) {
    $messages = json_decode(file_get_contents($chat_file), true);
    if(is_array($messages)) {
        // Son 24 saat içindeki bot mesajlarını say
        $last_24_hours = strtotime('-24 hours');
        foreach($messages as $message) {
            if(isset($message['type']) && $message['type'] === 'bot') {
                $message_time = strtotime($message['date'] . ' ' . $message['time']);
                if($message_time > $last_24_hours) {
                    $unread_messages++;
                }
            }
        }
    }
}

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
        'urun_ara' => $dil == 'tr' ? 'Ara' : 'Search',
        'chat_title' => $dil == 'tr' ? 'Canlı Destek' : 'Live Support',
        'chat_placeholder' => $dil == 'tr' ? 'Mesajınızı yazın...' : 'Type your message...',
        'chat_send' => $dil == 'tr' ? 'Gönder' : 'Send',
        'chat_quick_replies' => $dil == 'tr' ? 'Hızlı Yanıtlar:' : 'Quick Replies:'
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

// Arama formu için mevcut değerleri al
$mevcut_arama = isset($_GET['arama']) ? $_GET['arama'] : '';
$mevcut_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
?>

<!DOCTYPE html>
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
        /* CSS DEĞİŞKENLERİ - TEMA SİSTEMİ */
        :root {
            /* Light theme variables */
            --primary-color: #ff6b9d;
            --secondary-color: #ff8fab;
            --accent-color: #4ecdc4;
            --text-color: #333;
            --bg-color: #fff5f7;
            --bg-secondary: #ffeef2;
            --card-bg: white;
            --border-color: #ffeef2;
            --shadow-color: rgba(255, 107, 157, 0.1);
            --navbar-gradient: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            --chat-gradient: linear-gradient(135deg, #4ecdc4 0%, #88d3ce 100%);
            --message-user-bg: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            --message-bot-bg: white;
            --input-bg: white;
            --input-border: #ffeef2;
            --button-text: white;
            --admin-color: #d32f2f;
            --success-bg: #e8f5e9;
            --error-bg: #ffebee;
            --info-bg: #e3f2fd;
            --breadcrumb-bg: white;
            --quick-reply-bg: #f8f9fa;
            --quick-reply-border: #e9ecef;
        }

        [data-theme="dark"] {
            /* Dark theme variables */
            --primary-color: #ff6b9d;
            --secondary-color: #ff8fab;
            --accent-color: #4ecdc4;
            --text-color: #ffffff;
            --bg-color: #1a1a2e;
            --bg-secondary: #16213e;
            --card-bg: #0f3460;
            --border-color: #1e3a8a;
            --shadow-color: rgba(0, 0, 0, 0.3);
            --navbar-gradient: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            --chat-gradient: linear-gradient(135deg, #4ecdc4 0%, #88d3ce 100%);
            --message-user-bg: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            --message-bot-bg: #2d3748;
            --input-bg: #2d3748;
            --input-border: #4a5568;
            --button-text: white;
            --admin-color: #f44336;
            --success-bg: #1b5e20;
            --error-bg: #c62828;
            --info-bg: #0d47a1;
            --breadcrumb-bg: #0f3460;
            --quick-reply-bg: #2d3748;
            --quick-reply-border: #4a5568;
        }
        
        /* TEMEL STİLLER */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-color);
            min-height: 100vh;
            color: var(--text-color);
            position: relative;
            overflow-x: hidden;
            transition: background-color 0.3s, color 0.3s;
        }
        
        /* NAVBAR - PEMBE TASARIM */
        .navbar {
            background: var(--navbar-gradient);
            padding: 15px 0;
            box-shadow: 0 4px 20px var(--shadow-color);
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
            color: var(--primary-color);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .admin-badge {
            background: var(--admin-color);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        /* SAYAÇLAR */
        .sepet-sayaci, .favori-sayaci {
            background: white;
            color: var(--primary-color);
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: bold;
            animation: bounce 0.5s;
        }
        
        /* BUTONLAR */
        .auth-button {
            background: white;
            color: var(--primary-color);
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
            background: var(--admin-color);
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
            background: white;
            color: #333;
        }
        
        [data-theme="dark"] .dil-secici option {
            background: #2d3748;
            color: white;
        }
        
        /* ARAMA ÇUBUĞU */
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .arama-cubugu {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px var(--shadow-color);
            margin-bottom: 20px;
            transition: background-color 0.3s, box-shadow 0.3s;
        }
        
        .arama-wrapper {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .kategori-select {
            padding: 12px;
            border: 2px solid var(--input-border);
            border-radius: 10px;
            font-size: 1rem;
            background: var(--input-bg);
            color: var(--text-color);
            cursor: pointer;
            min-width: 220px;
            transition: background-color 0.3s, border-color 0.3s;
        }
        
        .arama-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid var(--input-border);
            border-radius: 10px;
            font-size: 1rem;
            background: var(--input-bg);
            color: var(--text-color);
            transition: all 0.3s;
        }
        
        .arama-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }
        
        .arama-buton {
            background: var(--navbar-gradient);
            color: var(--button-text);
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
            background: var(--breadcrumb-bg);
            border-radius: 10px;
            box-shadow: 0 2px 10px var(--shadow-color);
            transition: background-color 0.3s;
        }
        
        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb .separator {
            margin: 0 10px;
            color: #999;
        }
        
        [data-theme="dark"] .breadcrumb .separator {
            color: #ccc;
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
            background: var(--success-bg);
            color: #2e7d32;
            border-left-color: #4CAF50;
        }
        
        .message.error {
            background: var(--error-bg);
            color: #c62828;
            border-left-color: #f44336;
        }
        
        .message.info {
            background: var(--info-bg);
            color: #1565c0;
            border-left-color: #2196f3;
        }
        
        [data-theme="dark"] .message.success {
            color: #a5d6a7;
        }
        
        [data-theme="dark"] .message.error {
            color: #ef9a9a;
        }
        
        [data-theme="dark"] .message.info {
            color: #90caf9;
        }
        
        /* CHAT WIDGET STİLLERİ */
        .chat-widget {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9998;
            font-family: 'Poppins', sans-serif;
        }
        
        .chat-button {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--chat-gradient);
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(78, 205, 196, 0.4);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: pulse 2s infinite;
        }
        
        .chat-button:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 40px rgba(78, 205, 196, 0.6);
        }
        
        .chat-button::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: inherit;
            animation: ripple 1.5s infinite;
            z-index: -1;
        }
        
        .chat-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            animation: bounce 1s infinite;
        }
        
        .chat-container {
            position: absolute;
            bottom: 90px;
            right: 0;
            width: 380px;
            max-height: 600px;
            background: var(--card-bg);
            border-radius: 25px;
            box-shadow: 0 20px 60px var(--shadow-color);
            overflow: hidden;
            display: none;
            opacity: 0;
            transform: translateY(20px) scale(0.95);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid var(--border-color);
        }
        
        .chat-container.active {
            display: flex;
            flex-direction: column;
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        
        .chat-header {
            background: var(--chat-gradient);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .chat-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .chat-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .chat-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            max-height: 400px;
            background: var(--bg-secondary);
            scroll-behavior: smooth;
        }
        
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }
        
        .chat-messages::-webkit-scrollbar-track {
            background: var(--bg-color);
            border-radius: 10px;
        }
        
        .chat-messages::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
            opacity: 0.5;
        }
        
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
        
        .chat-message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }
        
        .message-bubble {
            padding: 12px 16px;
            border-radius: 18px;
            max-width: 80%;
            word-wrap: break-word;
            position: relative;
            box-shadow: 0 2px 8px var(--shadow-color);
        }
        
        .message-user {
            background: var(--message-user-bg);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        
        .message-bot {
            background: var(--message-bot-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }
        
        .message-sender {
            font-size: 0.8rem;
            color: var(--text-color);
            opacity: 0.8;
            margin-bottom: 4px;
            margin-left: 5px;
            margin-right: 5px;
        }
        
        .message-time {
            font-size: 0.7rem;
            color: var(--text-color);
            opacity: 0.7;
            text-align: right;
            margin-top: 4px;
            margin-right: 5px;
        }
        
        .chat-input-area {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            background: var(--card-bg);
        }
        
        .chat-input-wrapper {
            display: flex;
            gap: 10px;
        }
        
        .chat-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid var(--input-border);
            border-radius: 25px;
            font-size: 0.95rem;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
            background: var(--input-bg);
            color: var(--text-color);
        }
        
        .chat-input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.1);
        }
        
        .chat-send-btn {
            background: var(--chat-gradient);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .chat-send-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(78, 205, 196, 0.3);
        }
        
        .chat-send-btn:active {
            transform: scale(0.95);
        }
        
        .quick-replies {
            padding: 15px 20px 0;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
        }
        
        .quick-replies-title {
            font-size: 0.9rem;
            color: var(--text-color);
            opacity: 0.8;
            margin-bottom: 8px;
            display: block;
            font-weight: 600;
        }
        
        .quick-replies-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .quick-reply-btn {
            background: var(--quick-reply-bg);
            border: 1px solid var(--quick-reply-border);
            color: var(--text-color);
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        
        .quick-reply-btn:hover {
            background: var(--navbar-gradient);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
        }
        
        /* MAIN CONTENT STYLES - Tüm sayfalarda uygulanacak */
        .main-content {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px var(--shadow-color);
            margin-bottom: 40px;
            transition: background-color 0.3s, box-shadow 0.3s;
        }
        
        /* TABLO STİLLERİ */
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            color: var(--text-color);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px var(--shadow-color);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background: var(--bg-secondary);
            font-weight: 600;
        }
        
        /* FORM ELEMENTLERİ */
        input, textarea, select {
            background: var(--input-bg);
            color: var(--text-color);
            border: 2px solid var(--input-border);
            border-radius: 8px;
            padding: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }
        
        /* CARD STİLLERİ */
        .card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px var(--shadow-color);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px var(--shadow-color);
        }
        
        /* BUTON STİLLERİ */
        .btn {
            background: var(--navbar-gradient);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 157, 0.3);
        }
        
        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-color);
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
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(78, 205, 196, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(78, 205, 196, 0); }
            100% { box-shadow: 0 0 0 0 rgba(78, 205, 196, 0); }
        }
        
        @keyframes ripple {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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
                min-width: auto;
            }
            
            .chat-container {
                width: calc(100vw - 40px);
                right: 20px;
                max-height: 70vh;
                bottom: 80px;
            }
            
            .chat-widget {
                bottom: 20px;
                right: 20px;
            }
            
            .chat-button {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
            
            .quick-replies-buttons {
                justify-content: center;
            }
            
            .quick-reply-btn {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 480px) {
            .chat-container {
                width: calc(100vw - 30px);
                right: 15px;
                bottom: 75px;
            }
            
            .chat-button {
                width: 55px;
                height: 55px;
                font-size: 22px;
            }
            
            .nav-links {
                gap: 10px;
            }
            
            .nav-link {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
            
            .auth-button, .admin-button {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
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
                        <option value="" <?php echo empty($mevcut_kategori) ? 'selected' : ''; ?>>
                            <?php echo $text_selected['tum_urunler']; ?>
                        </option>
                        <option value="gul" <?php echo $mevcut_kategori == 'gul' ? 'selected' : ''; ?>>
                            🌹 <?php echo $dil == 'tr' ? 'Güller' : 'Roses'; ?>
                        </option>
                        <option value="orkide" <?php echo $mevcut_kategori == 'orkide' ? 'selected' : ''; ?>>
                            💮 <?php echo $dil == 'tr' ? 'Orkideler' : 'Orchids'; ?>
                        </option>
                        <option value="lale" <?php echo $mevcut_kategori == 'lale' ? 'selected' : ''; ?>>
                            🌷 <?php echo $dil == 'tr' ? 'Laleler' : 'Tulips'; ?>
                        </option>
                        <option value="buket" <?php echo $mevcut_kategori == 'buket' ? 'selected' : ''; ?>>
                            💐 <?php echo $dil == 'tr' ? 'Buketler' : 'Bouquets'; ?>
                        </option>
                        <option value="sukulent" <?php echo $mevcut_kategori == 'sukulent' ? 'selected' : ''; ?>>
                            🌵 <?php echo $dil == 'tr' ? 'Sukulentler' : 'Succulents'; ?>
                        </option>
                        <option value="aranjman" <?php echo $mevcut_kategori == 'aranjman' ? 'selected' : ''; ?>>
                            🏵️ <?php echo $dil == 'tr' ? 'Aranjmanlar' : 'Arrangements'; ?>
                        </option>
                        <option value="hediye" <?php echo $mevcut_kategori == 'hediye' ? 'selected' : ''; ?>>
                            🎁 <?php echo $dil == 'tr' ? 'Hediye Setleri' : 'Gift Sets'; ?>
                        </option>
                        <option value="doga" <?php echo $mevcut_kategori == 'doga' ? 'selected' : ''; ?>>
                            🌼 <?php echo $dil == 'tr' ? 'Doğa Çiçekleri' : 'Natural Flowers'; ?>
                        </option>
                    </select>
                    
                    <input type="text" name="arama" class="arama-input" 
                           placeholder="<?php echo $text_selected['ara_placeholder']; ?>"
                           value="<?php echo htmlspecialchars($mevcut_arama); ?>">
                    
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

<script>
// JAVASCRIPT FONKSİYONLARI

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
    
    // Buton ikonunu güncelle
    const temaBtn = document.querySelector('.tema-degistirici');
    if(temaBtn) {
        temaBtn.innerHTML = yeniTema === 'light' ? '🌙' : '☀️';
    }
}

// CHAT WIDGET FONKSİYONLARI
let chatOpen = false;

function toggleChat() {
    const chatContainer = document.getElementById('chatContainer');
    const chatButton = document.getElementById('chatButton');
    const chatNotification = document.getElementById('chatNotification');
    
    chatOpen = !chatOpen;
    
    if(chatOpen) {
        chatContainer.classList.add('active');
        chatButton.innerHTML = '<i class="fas fa-times"></i>';
        
        // Bildirimleri temizle
        if(chatNotification) {
            chatNotification.style.display = 'none';
        }
        
        // Mesajları yükle
        loadChatMessages();
        
        // Oto focus
        setTimeout(() => {
            document.getElementById('chatInput')?.focus();
        }, 300);
    } else {
        chatContainer.classList.remove('active');
        chatButton.innerHTML = '<i class="fas fa-comment-dots"></i>';
    }
}

function loadChatMessages() {
    const messagesContainer = document.getElementById('chatMessages');
    if(!messagesContainer) return;
    
    fetch('chat.php?action=get')
        .then(response => response.json())
        .then(messages => {
            messagesContainer.innerHTML = '';
            
            if(messages.length === 0) {
                messagesContainer.innerHTML = `
                    <div style="text-align: center; color: #666; padding: 40px 20px;">
                        <i class="fas fa-comments" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                        <p><?php echo $dil == 'tr' ? 'Hoş geldiniz! Nasıl yardımcı olabilirim?' : 'Welcome! How can I help you?'; ?></p>
                    </div>
                `;
                return;
            }
            
            messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'chat-message';
                
                const isBot = msg.user_id === 'bot' || (msg.type && msg.type === 'bot');
                const time = msg.time ? msg.time.split(':').slice(0, 2).join(':') : '';
                const senderName = isBot ? '🤖 ' + (msg.user_name || 'Destek Botu') : '👤 ' + (msg.user_name || 'Misafir');
                
                messageDiv.innerHTML = `
                    <div class="message-sender" style="${isBot ? 'text-align: left' : 'text-align: right'}">
                        ${senderName}
                    </div>
                    <div class="message-bubble ${isBot ? 'message-bot' : 'message-user'}">
                        ${msg.message}
                        <div class="message-time">${time}</div>
                    </div>
                `;
                
                messagesContainer.appendChild(messageDiv);
            });
            
            // En son mesaja scroll
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        })
        .catch(error => {
            console.error('Mesajlar yüklenirken hata:', error);
            messagesContainer.innerHTML = `
                <div style="text-align: center; color: #ff4757; padding: 20px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p><?php echo $dil == 'tr' ? 'Mesajlar yüklenirken bir hata oluştu.' : 'An error occurred while loading messages.'; ?></p>
                </div>
            `;
        });
}

function sendChatMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if(!message) return;
    
    // Mesajı göster
    const messagesContainer = document.getElementById('chatMessages');
    if(messagesContainer.innerHTML.includes('Hoş geldiniz') || messagesContainer.innerHTML.includes('Welcome')) {
        messagesContainer.innerHTML = '';
    }
    
    const userMessageDiv = document.createElement('div');
    userMessageDiv.className = 'chat-message';
    userMessageDiv.innerHTML = `
        <div class="message-sender" style="text-align: right">
            👤 <?php echo $is_logged_in ? htmlspecialchars($_SESSION['ad_soyad'] ?? 'Misafir') : 'Misafir'; ?>
        </div>
        <div class="message-bubble message-user">
            ${message}
            <div class="message-time">${new Date().toLocaleTimeString('tr-TR', {hour: '2-digit', minute:'2-digit'})}</div>
        </div>
    `;
    
    messagesContainer.appendChild(userMessageDiv);
    input.value = '';
    
    // Input'a tekrar focus
    input.focus();
    
    // Mesajları en sona scroll yap
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    // AJAX ile mesajı gönder
    fetch('chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=send&message=' + encodeURIComponent(message)
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            // 1 saniye sonra bot mesajını yükle
            setTimeout(loadChatMessages, 1000);
        } else if(data.status === 'error') {
            const errorMessage = document.createElement('div');
            errorMessage.className = 'chat-message';
            errorMessage.innerHTML = `
                <div class="message-sender" style="text-align: left">
                    🤖 <?php echo $dil == 'tr' ? 'Destek Botu' : 'Support Bot'; ?>
                </div>
                <div class="message-bubble message-bot">
                    <i class="fas fa-exclamation-triangle"></i> 
                    ${data.message || '<?php echo $dil == 'tr' ? 'Mesaj göndermek için giriş yapmalısınız.' : 'You must login to send messages.'; ?>'}
                </div>
            `;
            messagesContainer.appendChild(errorMessage);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    })
    .catch(error => {
        console.error('Mesaj gönderilirken hata:', error);
        const errorMessage = document.createElement('div');
        errorMessage.className = 'chat-message';
        errorMessage.innerHTML = `
            <div class="message-sender" style="text-align: left">
                🤖 <?php echo $dil == 'tr' ? 'Destek Botu' : 'Support Bot'; ?>
            </div>
            <div class="message-bubble message-bot">
                <i class="fas fa-exclamation-triangle"></i> 
                <?php echo $dil == 'tr' ? 'Mesaj gönderilirken bir hata oluştu.' : 'An error occurred while sending message.'; ?>
            </div>
        `;
        messagesContainer.appendChild(errorMessage);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    });
}

function sendQuickReply(type) {
    let message = '';
    const quickTexts = {
        'iade': '<?php echo $dil == 'tr' ? "İade işlemleri nasıl yapılır?" : "How do I make a return?" ?>',
        'siparis': '<?php echo $dil == 'tr' ? "Siparişimi nasıl takip edebilirim?" : "How can I track my order?" ?>',
        'takip': '<?php echo $dil == 'tr' ? "Sipariş takibi yapmak istiyorum" : "I want to track my order" ?>',
        'urun': '<?php echo $dil == 'tr' ? "Ürünler hakkında bilgi almak istiyorum" : "I want information about products" ?>',
        'teslimat': '<?php echo $dil == 'tr' ? "Teslimat süreleri nedir?" : "What are the delivery times?" ?>',
        'iletisim': '<?php echo $dil == 'tr' ? "İletişim bilgileriniz nelerdir?" : "What are your contact details?" ?>'
    };
    
    message = quickTexts[type] || '<?php echo $dil == 'tr' ? "Yardım almak istiyorum" : "I need help" ?>';
    
    // Mesajı input'a yaz
    document.getElementById('chatInput').value = message;
    
    // Hemen gönder
    setTimeout(() => {
        sendChatMessage();
    }, 100);
}

function checkEnterKey(event) {
    if(event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendChatMessage();
    }
}

// SAYFA YÜKLENDİĞİNDE
document.addEventListener('DOMContentLoaded', function() {
    // Tema butonu için ikonu ayarla
    const temaBtn = document.querySelector('.tema-degistirici');
    if(temaBtn) {
        const tema = document.documentElement.getAttribute('data-theme');
        temaBtn.innerHTML = tema === 'light' ? '🌙' : '☀️';
    }
    
    // Chat bildirimini ayarla
    const unreadCount = <?php echo $unread_messages; ?>;
    const chatNotification = document.getElementById('chatNotification');
    if(chatNotification && unreadCount > 0) {
        chatNotification.textContent = unreadCount > 9 ? '9+' : unreadCount;
        chatNotification.style.display = 'flex';
    }
    
    // Her 30 saniyede bir yeni mesaj kontrolü
    setInterval(() => {
        if(!chatOpen && chatNotification) {
            fetch('chat.php?action=get')
                .then(response => response.json())
                .then(messages => {
                    // Son 5 dakikadaki bot mesajlarını say
                    const last_5_min = new Date(Date.now() - 5 * 60 * 1000);
                    let newUnread = 0;
                    
                    messages.forEach(msg => {
                        if(msg.user_id === 'bot' || (msg.type && msg.type === 'bot')) {
                            const messageTime = new Date(msg.date + ' ' + msg.time);
                            if(messageTime > last_5_min) {
                                newUnread++;
                            }
                        }
                    });
                    
                    if(newUnread > 0 && chatNotification) {
                        chatNotification.textContent = newUnread > 9 ? '9+' : newUnread;
                        chatNotification.style.display = 'flex';
                        chatNotification.style.animation = 'bounce 1s infinite';
                    }
                });
        }
    }, 30000);
    
    console.log('Header.php yüklendi - Chat widget aktif');
});

// Chat kutusunun dışına tıklayınca kapat
document.addEventListener('click', function(event) {
    const chatContainer = document.getElementById('chatContainer');
    const chatButton = document.getElementById('chatButton');
    
    if(chatOpen && chatContainer && !chatContainer.contains(event.target) && !chatButton.contains(event.target)) {
        toggleChat();
    }
});
</script>

<!-- CHAT WIDGET -->
<div class="chat-widget">
    <button id="chatButton" class="chat-button" onclick="toggleChat()">
        <i class="fas fa-comment-dots"></i>
        <span id="chatNotification" class="chat-notification" style="display: none;"></span>
    </button>
    
    <div id="chatContainer" class="chat-container">
        <div class="chat-header">
            <div class="chat-title">
                <i class="fas fa-headset"></i>
                <?php echo $text_selected['chat_title']; ?>
            </div>
            <button class="chat-close" onclick="toggleChat()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="quick-replies">
            <span class="quick-replies-title"><?php echo $text_selected['chat_quick_replies']; ?></span>
            <div class="quick-replies-buttons">
                <button class="quick-reply-btn" onclick="sendQuickReply('iade')">
                    🔄 <?php echo $dil == 'tr' ? 'İade' : 'Return'; ?>
                </button>
                <button class="quick-reply-btn" onclick="sendQuickReply('siparis')">
                    📦 <?php echo $dil == 'tr' ? 'Sipariş' : 'Order'; ?>
                </button>
                <button class="quick-reply-btn" onclick="sendQuickReply('takip')">
                    🚚 <?php echo $dil == 'tr' ? 'Takip' : 'Tracking'; ?>
                </button>
                <button class="quick-reply-btn" onclick="sendQuickReply('teslimat')">
                    ⏰ <?php echo $dil == 'tr' ? 'Teslimat' : 'Delivery'; ?>
                </button>
                <button class="quick-reply-btn" onclick="sendQuickReply('iletisim')">
                    📞 <?php echo $dil == 'tr' ? 'İletişim' : 'Contact'; ?>
                </button>
            </div>
        </div>
        
        <div id="chatMessages" class="chat-messages">
            <!-- Mesajlar buraya yüklenecek -->
        </div>
        
        <div class="chat-input-area">
            <div class="chat-input-wrapper">
                <input type="text" 
                       id="chatInput" 
                       class="chat-input" 
                       placeholder="<?php echo $text_selected['chat_placeholder']; ?>"
                       onkeypress="checkEnterKey(event)"
                       <?php echo !$is_logged_in ? 'disabled' : ''; ?>>
                <button class="chat-send-btn" onclick="sendChatMessage()" <?php echo !$is_logged_in ? 'disabled' : ''; ?>>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>