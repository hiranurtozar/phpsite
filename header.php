<?php
// HATALARI GÖSTER (development için)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// SESSION BAŞLAT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Eğer cicek.php'den gelmiyorsa, cicek.php'yi çağır
if(!isset($dil) || !isset($tema)) {
    if(file_exists('cicek.php')) {
        require_once 'cicek.php';
    } else {
        $dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';
        $tema = isset($_COOKIE['tema']) ? $_COOKIE['tema'] : 'light';
    }
}

// Dil ve tema değişkenlerini ayarla
$dil = isset($dil) ? $dil : (isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr');
$tema = isset($tema) ? $tema : (isset($_COOKIE['tema']) ? $_COOKIE['tema'] : 'light');

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

// Kullanıcı giriş kontrol fonksiyonu
if(!function_exists('kullaniciGirisKontrol')) {
    function kullaniciGirisKontrol() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
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

// Favoriler için session kontrolü
if(!isset($_SESSION['favoriler'])) {
    $_SESSION['favoriler'] = [];
}

// Yorumlar için JSON dosyası kontrolü
$yorumlar_dosya = 'yorumlar.json';
if(!file_exists($yorumlar_dosya)) {
    file_put_contents($yorumlar_dosya, json_encode([]));
}
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
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Animasyon CSS -->
    <style>
        /* Animasyonlar */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 5px rgba(255,107,157,0.5); }
            50% { box-shadow: 0 0 20px rgba(255,107,157,0.8); }
        }
        
        @keyframes flowerFloat {
            0% { transform: translateY(0px) rotate(0deg); }
            25% { transform: translateY(-20px) rotate(5deg); }
            50% { transform: translateY(-10px) rotate(-5deg); }
            75% { transform: translateY(-15px) rotate(3deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        
        @keyframes petalFall {
            0% { transform: translateY(-100px) rotate(0deg); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
        }
        
        /* Çiçek animasyonları için */
        .flower-bg {
            position: fixed;
            z-index: -1;
            pointer-events: none;
        }
        
        .flower-1 {
            top: 10%;
            left: 5%;
            font-size: 40px;
            animation: flowerFloat 8s infinite ease-in-out;
            opacity: 0.3;
        }
        
        .flower-2 {
            top: 20%;
            right: 10%;
            font-size: 50px;
            animation: flowerFloat 10s infinite ease-in-out 1s;
            opacity: 0.4;
        }
        
        .flower-3 {
            bottom: 30%;
            left: 15%;
            font-size: 35px;
            animation: flowerFloat 12s infinite ease-in-out 2s;
            opacity: 0.3;
        }
        
        .flower-4 {
            bottom: 20%;
            right: 5%;
            font-size: 45px;
            animation: flowerFloat 9s infinite ease-in-out 3s;
            opacity: 0.4;
        }
        
        .petal {
            position: absolute;
            font-size: 20px;
            opacity: 0.3;
            animation: petalFall linear infinite;
        }
        
        /* Canlı Sohbet */
        .chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .chat-toggle {
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            color: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 24px;
            box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);
            animation: bounce 2s infinite;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .chat-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(255, 107, 157, 0.4);
            animation: none;
        }
        
        .chat-box {
            position: absolute;
            bottom: 70px;
            right: 0;
            width: 350px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            display: none;
            animation: slideInRight 0.3s ease-out;
            border: 1px solid #ffeef2;
            overflow: hidden;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-header h3 {
            margin: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chat-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        
        .chat-close:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .chat-messages {
            height: 300px;
            overflow-y: auto;
            padding: 15px;
            background: #fff9fb;
        }
        
        .message {
            background: white;
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-left: 3px solid #ff6b9d;
        }
        
        .message strong {
            color: #333;
            font-size: 0.9rem;
        }
        
        .message small {
            color: #999;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        
        .chat-input {
            padding: 15px;
            border-top: 1px solid #ffeef2;
            display: flex;
            gap: 10px;
            background: white;
            border-radius: 0 0 15px 15px;
        }
        
        .chat-input input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #ffeef2;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #fff9fb;
        }
        
        .chat-input input:focus {
            outline: none;
            border-color: #ff6b9d;
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }
        
        .chat-input button {
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .chat-input button:hover {
            transform: scale(1.05);
        }
        
        /* Animasyonlu butonlar */
        .btn-animated {
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .btn-animated:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(255, 107, 157, 0.15);
        }
        
        .btn-animated:active {
            transform: translateY(-1px);
        }
        
        /* Ürün kartları */
        .product-card {
            transition: all 0.3s ease;
            animation: fadeIn 0.5s ease-out;
        }
        
        .product-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 30px rgba(255, 107, 157, 0.15);
        }
        
        /* Arama Çubuğu Animasyonları */
        .search-glow {
            animation: searchGlow 2s infinite alternate;
        }
        
        @keyframes searchGlow {
            0% { box-shadow: 0 5px 15px rgba(255, 107, 157, 0.2); }
            100% { box-shadow: 0 8px 25px rgba(255, 107, 157, 0.3); }
        }
        
        .search-expand {
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .search-expand:focus-within {
            transform: scale(1.02);
        }
        
        /* Toast Mesajları */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            animation: slideInRight 0.3s ease-out;
        }
        
        .toast {
            background: white;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 5px solid;
            animation: fadeIn 0.3s;
            font-size: 1rem;
            color: #333;
            min-width: 300px;
            max-width: 400px;
            word-wrap: break-word;
            z-index: 10000;
        }
        
        .toast span {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        
        .toast-success {
            border-left-color: #28a745;
            background: linear-gradient(135deg, #f8fff9 0%, #f1fff4 100%);
        }
        
        .toast-error {
            border-left-color: #dc3545;
            background: linear-gradient(135deg, #fff8f9 0%, #fff1f3 100%);
        }
        
        .toast-warning {
            border-left-color: #ffc107;
            background: linear-gradient(135deg, #fffdf8 0%, #fffbf1 100%);
        }
        
        .toast-info {
            border-left-color: #17a2b8;
            background: linear-gradient(135deg, #f8fdff 0%, #f1fbff 100%);
        }
        
        /* Navbar Animasyonları */
        .animated-nav {
            animation: slideIn 0.5s ease-out;
        }
        
        .animated-fadein {
            animation: fadeIn 0.5s ease-out;
        }
        
        .animated-bounce {
            animation: bounce 0.5s;
        }
        
        /* Progress Bar */
        .progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #ff6b9d, #ff8fab);
            z-index: 9998;
            transition: width 0.3s;
        }
        
        /* Modal Stilleri - YENİ TASARIM */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            width: 95%;
            max-width: 450px;
            max-height: 85vh;
            overflow-y: auto;
            position: relative;
            animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,107,157,0.1);
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            color: #666;
            cursor: pointer;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 10;
        }
        
        .modal-close:hover {
            background: #f5f5f5;
            color: #ff6b9d;
            transform: rotate(90deg);
        }
        
        /* Modal Tabs - Sadece 2 tab */
        .modal-tabs {
            display: flex;
            background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%);
            border-radius: 20px 20px 0 0;
            padding: 15px 15px 0;
            border-bottom: 2px solid #ffeef2;
        }
        
        .modal-tab {
            flex: 1;
            background: none;
            border: none;
            padding: 12px 10px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            white-space: nowrap;
        }
        
        .modal-tab:hover {
            color: #ff6b9d;
        }
        
        .modal-tab.active {
            color: #ff6b9d;
            border-bottom-color: #ff6b9d;
            background: rgba(255,107,157,0.05);
        }
        
        .modal-form {
            padding: 20px;
            display: none;
            animation: fadeIn 0.5s;
        }
        
        .modal-form.active {
            display: block;
        }
        
        /* Giriş Formu */
        #girisForm {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1rem;
        }
        
        .input-with-icon .form-input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #fafafa;
            box-sizing: border-box;
        }
        
        .input-with-icon .form-input:focus {
            outline: none;
            border-color: #ff6b9d;
            background: white;
            box-shadow: 0 0 0 3px rgba(255,107,157,0.1);
        }
        
        .show-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            color: #666;
        }
        
        .checkbox-label input {
            width: 16px;
            height: 16px;
            accent-color: #ff6b9d;
        }
        
        .forgot-password {
            color: #ff6b9d;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .form-button {
            width: 100%;
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }
        
        .form-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255,107,157,0.2);
        }
        
        .social-login {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .social-login p {
            color: #666;
            margin-bottom: 12px;
            font-size: 0.85rem;
        }
        
        .social-buttons {
            display: flex;
            gap: 10px;
        }
        
        .social-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.9rem;
        }
        
        .social-btn.google:hover {
            background: #f1f1f1;
            border-color: #ddd;
        }
        
        .social-btn.facebook {
            background: #1877f2;
            color: white;
            border-color: #1877f2;
        }
        
        .social-btn.facebook:hover {
            background: #166fe5;
        }
        
        /* Kayıt Formu - YENİ TASARIM */
        #kayitForm {
            padding: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        @media (min-width: 768px) {
            .form-row {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .input-with-icon textarea.form-input {
            padding: 12px;
            min-height: 70px;
            resize: vertical;
        }
        
        .register-benefits {
            margin-top: 20px;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            border-left: 4px solid #ff6b9d;
        }
        
        .register-benefits h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .register-benefits h4 i {
            color: #ff6b9d;
        }
        
        .register-benefits ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .register-benefits li {
            color: #666;
            margin-bottom: 6px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 6px;
            line-height: 1.4;
        }
        
        .register-benefits li i {
            color: #28a745;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        
        .privacy-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 15px 0;
            font-size: 0.8rem;
            color: #666;
            line-height: 1.4;
        }
        
        .privacy-checkbox input {
            margin-top: 2px;
            width: 16px;
            height: 16px;
            accent-color: #ff6b9d;
            flex-shrink: 0;
        }
        
        .privacy-checkbox a {
            color: #ff6b9d;
            text-decoration: none;
        }
        
        .privacy-checkbox a:hover {
            text-decoration: underline;
        }
        
        /* User points styling */
        .user-points {
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            margin-left: 5px;
            font-weight: 600;
        }
        
        /* Şifremi unuttum formu */
        .forgot-password-icon {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .forgot-password-icon i {
            font-size: 2.5rem;
            color: #ff6b9d;
            background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        
        #sifremi_unuttumForm h3 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        #sifremi_unuttumForm p {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
            font-size: 0.9rem;
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 15px;
        }
        
        .back-to-login a {
            color: #666;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-to-login a:hover {
            color: #ff6b9d;
        }
        
        /* Favori sayacı */
        .favori-sayaci {
            background: #ff4757;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 5px;
            font-weight: bold;
            animation: bounce 0.5s;
        }
        
        /* Ana sayfa çiçek animasyonları için */
        .hero-flowers {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
            pointer-events: none;
            overflow: hidden;
        }
        
        .hero-flower {
            position: absolute;
            font-size: 50px;
            opacity: 0.5;
            animation: flowerFloat 6s infinite ease-in-out;
        }
        
        .hero-flower-1 { top: 20%; left: 10%; animation-delay: 0s; }
        .hero-flower-2 { top: 30%; right: 15%; animation-delay: 1s; }
        .hero-flower-3 { bottom: 25%; left: 15%; animation-delay: 2s; }
        .hero-flower-4 { bottom: 35%; right: 10%; animation-delay: 3s; }
        .hero-flower-5 { top: 40%; left: 20%; animation-delay: 4s; }
        .hero-flower-6 { top: 50%; right: 20%; animation-delay: 5s; }
        
        /* Auth Button Styling */
        .auth-button {
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
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
            font-size: 0.9rem;
        }
        
        .auth-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(255, 107, 157, 0.15);
            color: white;
            text-decoration: none;
        }
        
        /* Sepet sayacı */
        .sepet-sayaci {
            background: #ff6b9d;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 5px;
            font-weight: bold;
            animation: bounce 0.5s;
        }
    </style>
    
    <!-- JavaScript Fonksiyonları -->
    <script>
    // MODAL
    function acModal() {
        document.getElementById('loginModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function kapatModal() {
        document.getElementById('loginModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // MODAL TAB değiştirme fonksiyonunu düzelt
    function acModalTab(tab) {
        // Tüm tab'ları pasif yap
        document.querySelectorAll('.modal-tab').forEach(t => {
            t.classList.remove('active');
        });
        
        // Tüm formları gizle
        document.querySelectorAll('.modal-form').forEach(f => {
            f.classList.remove('active');
            f.style.display = 'none';
        });
        
        if(tab === 'giris') {
            // Giriş tab'ını aktif yap
            const girisTab = document.querySelector('.modal-tab[data-tab="giris"]');
            if(girisTab) girisTab.classList.add('active');
            // Giriş formunu göster
            const girisForm = document.getElementById('girisForm');
            if(girisForm) {
                girisForm.classList.add('active');
                girisForm.style.display = 'block';
            }
        } else if(tab === 'kayit') {
            // Kayıt tab'ını aktif yap
            const kayitTab = document.querySelector('.modal-tab[data-tab="kayit"]');
            if(kayitTab) kayitTab.classList.add('active');
            // Kayıt formunu göster
            const kayitForm = document.getElementById('kayitForm');
            if(kayitForm) {
                kayitForm.classList.add('active');
                kayitForm.style.display = 'block';
            }
        }
    }
    
    // DİL DEĞİŞTİR
    function dilDegistir(dil) {
        document.cookie = "dil=" + dil + "; path=/; max-age=31536000";
        document.cookie = "tema=" + document.documentElement.getAttribute('data-theme') + "; path=/; max-age=31536000";
        window.location.href = window.location.pathname + "?dil=" + dil;
    }
    
    // TEMA DEĞİŞTİR
    function temaDegistir() {
        const html = document.documentElement;
        const tema = html.getAttribute('data-theme');
        const yeniTema = tema === 'light' ? 'dark' : 'light';
        html.setAttribute('data-theme', yeniTema);
        document.cookie = "tema=" + yeniTema + "; path=/; max-age=31536000";
        
        const temaBtn = document.querySelector('.tema-degistirici');
        if(temaBtn) {
            temaBtn.innerHTML = yeniTema === 'light' ? '🌙' : '☀️';
        }
    }
    
    // ARAMA DOĞRULAMA
    function validateSearch() {
        const aramaInput = document.getElementById('aramaInput');
        const kategoriSelect = document.getElementById('kategoriSelect');
        const aramaUyari = document.getElementById('aramaUyari');
        
        if(aramaInput.value.trim() === '' && kategoriSelect.value === '') {
            aramaUyari.style.display = 'block';
            aramaUyari.textContent = '<?php echo $dil == "tr" ? "Lütfen bir kelime girin veya kategori seçin" : "Please enter a word or select a category"; ?>';
            return false;
        }
        
        if(aramaInput.value.trim() !== '' && aramaInput.value.length < 2) {
            aramaUyari.style.display = 'block';
            aramaUyari.textContent = '<?php echo $dil == "tr" ? "En az 2 karakter giriniz" : "Enter at least 2 characters"; ?>';
            return false;
        }
        
        aramaUyari.style.display = 'none';
        return true;
    }
    
    // CANLI SOHBET - HERKES KULLANABİLİR
    let chatOpen = false;
    function toggleChat() {
        const chatBox = document.getElementById('chatBox');
        chatOpen = !chatOpen;
        chatBox.style.display = chatOpen ? 'block' : 'none';
        
        if(chatOpen) {
            loadChatMessages();
        }
    }
    
    function loadChatMessages() {
        const container = document.getElementById('chatMessages');
        if(!container) return;
        
        // Örnek mesajlar
        const messages = [
            {
                user_name: '<?php echo $dil == "tr" ? "Destek Ekibi" : "Support Team"; ?>',
                message: '<?php echo $dil == "tr" ? "Size nasıl yardımcı olabilirim?" : "How can I help you?"; ?>',
                time: '<?php echo date("H:i"); ?>'
            }
        ];
        
        container.innerHTML = '';
        
        messages.forEach(msg => {
            const div = document.createElement('div');
            div.className = 'message';
            div.innerHTML = `<strong>${msg.user_name}:</strong> ${msg.message} <small>${msg.time}</small>`;
            container.appendChild(div);
        });
        
        container.scrollTop = container.scrollHeight;
    }
    
    function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        
        if(message === '') return;
        
        const container = document.getElementById('chatMessages');
        const userName = '<?php echo kullaniciGirisKontrol() ? ($_SESSION["ad_soyad"] ?? ($dil == "tr" ? "Siz" : "You")) : ($dil == "tr" ? "Misafir" : "Guest"); ?>';
        const div = document.createElement('div');
        div.className = 'message';
        div.innerHTML = `<strong>${userName}:</strong> ${message} <small>${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</small>`;
        container.appendChild(div);
        
        input.value = '';
        container.scrollTop = container.scrollHeight;
        
        // Otomatik cevap (simülasyon)
        setTimeout(() => {
            const autoReply = document.createElement('div');
            autoReply.className = 'message';
            autoReply.innerHTML = `<strong><?php echo $dil == "tr" ? "Destek Ekibi" : "Support Team"; ?>:</strong> <?php echo $dil == "tr" ? "Mesajınızı aldık. En kısa sürede dönüş yapacağız." : "We received your message. We'll get back to you soon."; ?> <small>${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</small>`;
            container.appendChild(autoReply);
            container.scrollTop = container.scrollHeight;
        }, 1000);
    }
    
    // FAVORİ EKLE - HERKES KULLANABİLİR
    function addToFavorites(productId) {
        const btn = event.currentTarget;
        btn.classList.toggle('active');
        
        // Session'daki favorileri güncelle
        fetch('favoriler.php?action=toggle&id=' + productId)
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    const isAdded = btn.classList.contains('active');
                    showNotification(
                        isAdded 
                            ? '<?php echo $dil == "tr" ? "Favorilere eklendi!" : "Added to favorites!"; ?>' 
                            : '<?php echo $dil == "tr" ? "Favorilerden çıkarıldı!" : "Removed from favorites!"; ?>',
                        'success'
                    );
                    
                    // Favori sayacını güncelle
                    updateFavoriteCount();
                }
            })
            .catch(error => {
                console.error('Favori ekleme hatası:', error);
            });
    }
    
    // Favori sayacını güncelle
    function updateFavoriteCount() {
        const counter = document.querySelector('.favori-sayaci');
        const favoriLink = document.querySelector('a[href="favoriler.php?sayfa=favoriler"]');
        
        fetch('favoriler.php?action=count')
            .then(response => response.json())
            .then(data => {
                if(data.count > 0) {
                    if(!counter && favoriLink) {
                        const newCounter = document.createElement('span');
                        newCounter.className = 'favori-sayaci animated-bounce';
                        newCounter.textContent = data.count;
                        favoriLink.appendChild(newCounter);
                    } else if(counter) {
                        counter.textContent = data.count;
                        counter.classList.add('animated-bounce');
                        setTimeout(() => {
                            counter.classList.remove('animated-bounce');
                        }, 1000);
                    }
                } else if(counter) {
                    counter.remove();
                }
            });
    }
    
    // SEPET EKLE
    function addToCart(productId) {
        if(!<?php echo kullaniciGirisKontrol() ? 'true' : 'false'; ?>) {
            showNotification('<?php echo $dil == "tr" ? "Sepete ürün eklemek için giriş yapmalısınız!" : "You must login to add items to cart!"; ?>', 'warning');
            acModal();
            acModalTab('giris');
            return;
        }
        
        fetch('sepet.php?action=add&id=' + productId)
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showNotification('<?php echo $dil == "tr" ? "Ürün sepete eklendi!" : "Product added to cart!"; ?>', 'success');
                    updateCartCount();
                } else {
                    showNotification(data.message || '<?php echo $dil == "tr" ? "Bir hata oluştu!" : "An error occurred!"; ?>', 'error');
                }
            })
            .catch(error => {
                showNotification('<?php echo $dil == "tr" ? "Bir hata oluştu!" : "An error occurred!"; ?>', 'error');
                console.error('Sepete ekleme hatası:', error);
            });
    }
    
    // Sepet sayacını güncelle
    function updateCartCount() {
        const counter = document.querySelector('.sepet-sayaci');
        if(counter) {
            let count = parseInt(counter.textContent || 0);
            counter.textContent = count + 1;
            counter.classList.add('animated-bounce');
            setTimeout(() => {
                counter.classList.remove('animated-bounce');
            }, 1000);
        } else {
            // Sepet sayacı yoksa oluştur
            const sepetIkonu = document.querySelector('.sepet-ikonu');
            if(sepetIkonu) {
                const newCounter = document.createElement('span');
                newCounter.className = 'sepet-sayaci animated-bounce';
                newCounter.textContent = '1';
                sepetIkonu.appendChild(newCounter);
            }
        }
    }
    
    // SAYFA YÜKLENDİĞİNDE
    document.addEventListener('DOMContentLoaded', function() {
        // Modal dışına tıklayınca kapat
        const modal = document.getElementById('loginModal');
        if(modal) {
            modal.addEventListener('click', function(e) {
                if(e.target === this) {
                    kapatModal();
                }
            });
        }
        
        // Canlı sohbet input enter tuşu
        const chatInput = document.getElementById('chatInput');
        if(chatInput) {
            chatInput.addEventListener('keypress', function(e) {
                if(e.key === 'Enter') {
                    sendMessage();
                }
            });
        }
        
        // Arama çubuğu animasyonu
        const searchInput = document.getElementById('aramaInput');
        if(searchInput) {
            searchInput.addEventListener('focus', function() {
                this.parentElement.classList.add('search-expand');
            });
            
            searchInput.addEventListener('blur', function() {
                this.parentElement.classList.remove('search-expand');
            });
        }
        
        // Progress bar
        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        document.body.appendChild(progressBar);
        
        window.addEventListener('scroll', function() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            progressBar.style.width = scrolled + "%";
        });
        
        // Şifre göster/gizle için event listener'ları ekle
        document.querySelectorAll('.show-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.closest('.input-with-icon').querySelector('input');
                const icon = this.querySelector('i');
                
                if(input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                } else {
                    input.type = 'password';
                    icon.className = 'fas fa-eye';
                }
            });
        });
        
        // Telefon formatı
        const phoneInputs = document.querySelectorAll('input[name="telefon"]');
        phoneInputs.forEach(phoneInput => {
            if(phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if(value.length > 0) {
                        if(value.length <= 3) {
                            value = value;
                        } else if(value.length <= 6) {
                            value = value.substring(0, 3) + ' ' + value.substring(3);
                        } else if(value.length <= 8) {
                            value = value.substring(0, 3) + ' ' + value.substring(3, 6) + ' ' + value.substring(6);
                        } else {
                            value = value.substring(0, 3) + ' ' + value.substring(3, 6) + ' ' + value.substring(6, 8) + ' ' + value.substring(8, 10);
                        }
                    }
                    e.target.value = value;
                });
            }
        });
        
        // Toast mesajlarını otomatik kaldır
        setTimeout(() => {
            const toast = document.querySelector('.toast-container');
            if(toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if(toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        }, 5000);
        
        // Sosyal giriş butonları
        document.querySelectorAll('.social-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                showNotification('<?php echo $dil == "tr" ? "Bu özellik yakında eklenecek!" : "This feature will be added soon!"; ?>', 'info');
            });
        });
        
        // Form validasyonu
        document.querySelectorAll('.modal-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('.form-button');
                if(submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    
                    // Butonu devre dışı bırak
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo $dil == "tr" ? "İşleniyor..." : "Processing..."; ?>';
                    
                    // 3 saniye sonra eski haline getir (eğer sayfa yönlendirmezse)
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }, 3000);
                }
            });
        });
        
        // Çiçek animasyonları oluştur
        createFlowerAnimations();
        
        // Favori sayısını yükle
        updateFavoriteCount();
        
        // Modal tab'ları için event listener'lar ekle
        document.querySelectorAll('.modal-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                acModalTab(tabName);
            });
        });
    });
    
    // Çiçek animasyonları oluştur
    function createFlowerAnimations() {
        const flowerIcons = ['🌸', '🌹', '🌺', '🌻', '🌼', '💐', '🌷', '🥀', '💮'];
        const container = document.querySelector('.hero-flowers') || document.body;
        
        // Ana sayfada daha fazla çiçek
        if(window.location.pathname.includes('anasayfa.php') || window.location.pathname === '/' || window.location.pathname.includes('index')) {
            for(let i = 0; i < 8; i++) {
                const flower = document.createElement('div');
                flower.className = `hero-flower hero-flower-${i+1}`;
                flower.innerHTML = flowerIcons[Math.floor(Math.random() * flowerIcons.length)];
                flower.style.opacity = (0.4 + Math.random() * 0.3).toFixed(2);
                flower.style.fontSize = (40 + Math.random() * 30) + 'px';
                container.appendChild(flower);
            }
        }
        
        // Diğer sayfalarda daha az çiçek
        else {
            const bgContainer = document.createElement('div');
            bgContainer.className = 'flower-bg';
            
            for(let i = 0; i < 4; i++) {
                const flower = document.createElement('div');
                flower.className = `flower-${i+1}`;
                flower.innerHTML = flowerIcons[Math.floor(Math.random() * flowerIcons.length)];
                bgContainer.appendChild(flower);
            }
            
            // Yaprak animasyonları
            for(let i = 0; i < 5; i++) {
                const petal = document.createElement('div');
                petal.className = 'petal';
                petal.innerHTML = '🌸';
                petal.style.left = Math.random() * 100 + '%';
                petal.style.animationDuration = (10 + Math.random() * 10) + 's';
                petal.style.animationDelay = Math.random() * 5 + 's';
                petal.style.fontSize = (15 + Math.random() * 15) + 'px';
                bgContainer.appendChild(petal);
            }
            
            document.body.appendChild(bgContainer);
        }
    }
    
    // Şifre göster/gizle
    function togglePassword(btn) {
        const input = btn.closest('.input-with-icon').querySelector('input');
        const icon = btn.querySelector('i');
        
        if(input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
    
    // Şifremi unuttum modalını aç
    function openForgotPassword() {
        // Önce giriş modalını aç
        acModal();
        // Sonra şifremi unuttum formunu oluştur ve göster
        setTimeout(() => {
            const modalContent = document.querySelector('.modal-content');
            
            // Mevcut formları temizle
            document.querySelectorAll('.modal-form').forEach(form => form.style.display = 'none');
            document.querySelectorAll('.modal-tab').forEach(tab => tab.classList.remove('active'));
            
            // Şifremi unuttum formu yoksa oluştur
            let forgotForm = document.getElementById('sifremi_unuttumForm');
            if(!forgotForm) {
                forgotForm = document.createElement('form');
                forgotForm.id = 'sifremi_unuttumForm';
                forgotForm.className = 'modal-form active';
                forgotForm.innerHTML = `
                    <div class="forgot-password-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    
                    <h3><?php echo $dil == 'tr' ? 'Şifremi Unuttum' : 'Forgot Password'; ?></h3>
                    <p><?php echo $dil == 'tr' 
                        ? 'Email adresinizi girin, size şifre sıfırlama bağlantısı gönderelim.' 
                        : 'Enter your email address and we\'ll send you a password reset link.'; ?>
                    </p>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $text_selected['email']; ?></label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-input" placeholder="ornek@email.com" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="form-button btn-animated">
                        <i class="fas fa-paper-plane"></i> <?php echo $dil == 'tr' ? 'Şifre Sıfırlama Bağlantısı Gönder' : 'Send Reset Link'; ?>
                    </button>
                    
                    <p class="back-to-login">
                        <a href="#" onclick="acModalTab('giris')">
                            <i class="fas fa-arrow-left"></i> 
                            <?php echo $dil == 'tr' ? 'Giriş sayfasına dön' : 'Back to login'; ?>
                        </a>
                    </p>
                `;
                
                modalContent.appendChild(forgotForm);
            } else {
                forgotForm.classList.add('active');
                forgotForm.style.display = 'block';
            }
        }, 100);
    }
    
    // Bildirim göster
    function showNotification(message, type = 'info') {
        // Önceki toast'ları temizle
        const existingToast = document.querySelector('.toast-container');
        if(existingToast) {
            existingToast.remove();
        }
        
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        let icon = 'ℹ️';
        if(type === 'success') icon = '✅';
        if(type === 'error') icon = '❌';
        if(type === 'warning') icon = '⚠️';
        
        toast.innerHTML = `<span>${icon} ${message}</span>`;
        toastContainer.appendChild(toast);
        document.body.appendChild(toastContainer);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if(toastContainer.parentNode) {
                    toastContainer.parentNode.removeChild(toastContainer);
                }
            }, 300);
        }, 5000);
    }
    </script>
</head>
<body>
    <!-- ÇİÇEK ANİMASYONLARI -->
    <div class="hero-flowers"></div>
    
    <!-- PROGRESS BAR -->
    <div class="progress-bar"></div>

    <!-- TOAST MESAJLARI -->
    <?php if(isset($_SESSION['mesaj'])): ?>
    <div class="toast-container">
        <div class="toast toast-<?php echo $_SESSION['mesaj']['tip']; ?>">
            <?php 
            $icon = '✅';
            if($_SESSION['mesaj']['tip'] == 'error') $icon = '❌';
            if($_SESSION['mesaj']['tip'] == 'warning') $icon = '⚠️';
            if($_SESSION['mesaj']['tip'] == 'info') $icon = 'ℹ️';
            ?>
            <span><?php echo $icon . ' ' . $_SESSION['mesaj']['metin']; ?></span>
        </div>
    </div>
    <?php unset($_SESSION['mesaj']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['auth_message'])): ?>
    <div class="toast-container">
        <div class="toast toast-<?php echo $_SESSION['auth_message']['type']; ?>">
            <?php 
            $icon = '✅';
            if($_SESSION['auth_message']['type'] == 'error') $icon = '❌';
            if($_SESSION['auth_message']['type'] == 'warning') $icon = '⚠️';
            if($_SESSION['auth_message']['type'] == 'info') $icon = 'ℹ️';
            ?>
            <span><?php echo $icon . ' ' . $_SESSION['auth_message']['text']; ?></span>
        </div>
    </div>
    <?php unset($_SESSION['auth_message']); ?>
    <?php endif; ?>

    <!-- NAVBAR -->
    <nav class="navbar animated-nav">
        <div class="nav-container">
            <div class="logo">
                <span class="logo-icon">🌸</span>
                <span class="logo-text"><?php echo $dil == 'tr' ? 'ÇiçekBahçesi' : 'FlowerGarden'; ?></span>
            </div>
            
            <?php if(kullaniciGirisKontrol()): ?>
                <!-- Giriş yapmış kullanıcı için menü -->
                <div class="kullanici-bilgi animated-fadein">
                    <i class="fas fa-user-circle"></i> 👋 <?php echo $dil == 'tr' ? 'Hoş geldin,' : 'Welcome,'; ?> 
                    <strong><?php echo htmlspecialchars($_SESSION['ad_soyad'] ?? ''); ?></strong>
                    <span class="user-points">(<?php echo $_SESSION['puan'] ?? 0; ?> puan)</span>
                </div>
                
                <div class="nav-links">
                    <a href="anasayfa.php?sayfa=anasayfa" class="nav-link btn-animated">🏠 <?php echo $text_selected['hosgeldin']; ?></a>
                    <a href="urunler.php?sayfa=urunler&kategori=tumu" class="nav-link btn-animated">🌸 <?php echo $text_selected['urunler']; ?></a>
                    <a href="sepet.php?sayfa=sepet" class="nav-link btn-animated sepet-ikonu">
                        🛒 <?php echo $text_selected['sepet']; ?> 
                        <?php if(isset($_SESSION['sepet']) && count($_SESSION['sepet']) > 0): ?>
                            <span class="sepet-sayaci animated-bounce"><?php echo count($_SESSION['sepet']); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="favoriler.php?sayfa=favoriler" class="nav-link btn-animated">
                        ❤️ <?php echo $text_selected['favoriler']; ?>
                        <?php if(isset($_SESSION['favoriler']) && count($_SESSION['favoriler']) > 0): ?>
                            <span class="favori-sayaci animated-bounce"><?php echo count($_SESSION['favoriler']); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="siparisler.php?sayfa=siparisler" class="nav-link btn-animated">📋 <?php echo $text_selected['siparisler']; ?></a>
                    <a href="kuponlar.php?sayfa=kuponlarim" class="nav-link btn-animated">🎫 <?php echo $text_selected['kuponlarim']; ?></a>
                    <a href="profil.php?sayfa=profil" class="nav-link btn-animated">👤 <?php echo $text_selected['profilim']; ?></a>
                    <a href="iletisim.php?sayfa=iletisim" class="nav-link btn-animated">📞 <?php echo $text_selected['iletisim']; ?></a>
                    <a href="auth.php?action=cikis" class="nav-link cikis btn-animated">🚪 <?php echo $text_selected['cikis']; ?></a>
                    
                    <select class="dil-secici btn-animated" onchange="dilDegistir(this.value)">
                        <option value="tr" <?php echo $dil == 'tr' ? 'selected' : ''; ?>>🇹🇷 Türkçe</option>
                        <option value="en" <?php echo $dil == 'en' ? 'selected' : ''; ?>>🇺🇸 English</option>
                    </select>
                    
                    <button class="tema-degistirici btn-animated" onclick="temaDegistir()">
                        <?php echo $tema === 'light' ? '🌙' : '☀️'; ?>
                    </button>
                </div>
                
            <?php else: ?>
                <!-- Giriş yapmamış kullanıcı için menü -->
                <div class="nav-links">
                    <a href="anasayfa.php?sayfa=anasayfa" class="nav-link btn-animated">🏠 <?php echo $text_selected['hosgeldin']; ?></a>
                    <a href="urunler.php?sayfa=urunler&kategori=tumu" class="nav-link btn-animated">🌸 <?php echo $text_selected['urunler']; ?></a>
                    <a href="#" onclick="acModal(); acModalTab('giris'); return false;" class="nav-link btn-animated sepet-ikonu">
                        🛒 <?php echo $text_selected['sepet']; ?>
                    </a>
                    <a href="favoriler.php?sayfa=favoriler" class="nav-link btn-animated">
                        ❤️ <?php echo $text_selected['favoriler']; ?>
                        <?php if(isset($_SESSION['favoriler']) && count($_SESSION['favoriler']) > 0): ?>
                            <span class="favori-sayaci animated-bounce"><?php echo count($_SESSION['favoriler']); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="#" onclick="acModal(); acModalTab('giris'); return false;" class="nav-link btn-animated">📋 <?php echo $text_selected['siparisler']; ?></a>
                    <a href="#" onclick="acModal(); acModalTab('giris'); return false;" class="nav-link btn-animated">🎫 <?php echo $text_selected['kupon']; ?></a>
                    <a href="#" onclick="acModal(); acModalTab('giris'); return false;" class="nav-link btn-animated">👤 <?php echo $text_selected['profilim']; ?></a>
                    <a href="iletisim.php?sayfa=iletisim" class="nav-link btn-animated">📞 <?php echo $text_selected['iletisim']; ?></a>
                    
                    <!-- TEK BUTON: Hem giriş hem üye ol için -->
                    <button class="auth-button btn-animated" onclick="acModal(); acModalTab('giris');">
                        <i class="fas fa-user"></i>
                        <span><?php echo $dil == 'tr' ? 'Giriş Yap / Üye Ol' : 'Login / Register'; ?></span>
                    </button>
                    
                    <select class="dil-secici btn-animated" onchange="dilDegistir(this.value)">
                        <option value="tr" <?php echo $dil == 'tr' ? 'selected' : ''; ?>>🇹🇷 Türkçe</option>
                        <option value="en" <?php echo $dil == 'en' ? 'selected' : ''; ?>>🇺🇸 English</option>
                    </select>
                    
                    <button class="tema-degistirici btn-animated" onclick="temaDegistir()">
                        <?php echo $tema === 'light' ? '🌙' : '☀️'; ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- CANLI SOHBET WIDGET - HERKES KULLANABİLİR -->
    <div class="chat-widget">
        <button class="chat-toggle" onclick="toggleChat()">
            <i class="fas fa-comment-dots"></i>
        </button>
        <div class="chat-box" id="chatBox">
            <div class="chat-header">
                <h3><i class="fas fa-headset"></i> <?php echo $dil == 'tr' ? 'Canlı Destek' : 'Live Support'; ?></h3>
                <button class="chat-close" onclick="toggleChat()">&times;</button>
            </div>
            <div class="chat-messages" id="chatMessages">
                <!-- Mesajlar buraya gelecek -->
            </div>
            <div class="chat-input">
                <input type="text" id="chatInput" placeholder="<?php echo $dil == 'tr' ? 'Mesajınızı yazın...' : 'Type your message...'; ?>" autocomplete="off">
                <button onclick="sendMessage()" class="btn-animated"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <!-- GELİŞMİŞ GİRİŞ MODALI -->
    <div id="loginModal" class="modal" style="display: none;">
        <div class="modal-content">
            <button class="modal-close" onclick="kapatModal()">×</button>
            
            <!-- Modal Tabs - Sadece 2 tab -->
            <div class="modal-tabs">
                <button class="modal-tab active" data-tab="giris" onclick="acModalTab('giris')">
                    <i class="fas fa-sign-in-alt"></i> <?php echo $text_selected['giris']; ?>
                </button>
                <button class="modal-tab" data-tab="kayit" onclick="acModalTab('kayit')">
                    <i class="fas fa-user-plus"></i> <?php echo $text_selected['uye_ol']; ?>
                </button>
            </div>
            
            <!-- Giriş Formu -->
            <form method="post" action="auth.php" class="modal-form active" id="girisForm">
                <input type="hidden" name="action" value="giris">
                <input type="hidden" name="csrf_token" value="<?php echo csrfTokenOlustur(); ?>">
                
                <div class="form-group">
                    <label class="form-label"><?php echo $text_selected['email']; ?></label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-input" placeholder="ornek@email.com" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo $text_selected['sifre']; ?></label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="sifre" class="form-input" placeholder="••••••••" required>
                        <button type="button" class="show-password" onclick="togglePassword(this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="beni_hatirla">
                        <span><?php echo $dil == 'tr' ? 'Beni Hatırla' : 'Remember Me'; ?></span>
                    </label>
                    <a href="#" onclick="openForgotPassword()" class="forgot-password">
                        <?php echo $dil == 'tr' ? 'Şifremi unuttum?' : 'Forgot password?'; ?>
                    </a>
                </div>
                
                <button type="submit" class="form-button btn-animated">
                    <i class="fas fa-sign-in-alt"></i> <?php echo $text_selected['giris']; ?>
                </button>
                
                <div class="social-login">
                    <p><?php echo $dil == 'tr' ? 'Veya sosyal medya ile giriş yap' : 'Or login with social media'; ?></p>
                    <div class="social-buttons">
                        <button type="button" class="social-btn google">
                            <i class="fab fa-google"></i> Google
                        </button>
                        <button type="button" class="social-btn facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Kayıt Formu -->
            <form method="post" action="auth.php" class="modal-form" id="kayitForm" style="display: none;">
                <input type="hidden" name="action" value="kayit">
                <input type="hidden" name="csrf_token" value="<?php echo csrfTokenOlustur(); ?>">
                
                <div class="form-group">
                    <label class="form-label"><?php echo $text_selected['ad_soyad']; ?> *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="ad_soyad" class="form-input" placeholder="<?php echo $dil == 'tr' ? 'Adınız Soyadınız' : 'Your Full Name'; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo $text_selected['email']; ?> *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-input" placeholder="ornek@email.com" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><?php echo $text_selected['sifre']; ?> *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="sifre" class="form-input" placeholder="••••••••" required minlength="6">
                            <button type="button" class="show-password" onclick="togglePassword(this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $dil == 'tr' ? 'Şifre Tekrar' : 'Confirm Password'; ?> *</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="sifre_tekrar" class="form-input" placeholder="••••••••" required minlength="6">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo $text_selected['tel']; ?> *</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="telefon" class="form-input" placeholder="5xx xxx xx xx" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo $dil == 'tr' ? 'Adres' : 'Address'; ?></label>
                    <div class="input-with-icon">
                        <i class="fas fa-map-marker-alt"></i>
                        <textarea name="adres" class="form-input" rows="2" placeholder="<?php echo $dil == 'tr' ? 'Teslimat adresiniz (opsiyonel)' : 'Your delivery address (optional)'; ?>"></textarea>
                    </div>
                </div>
                
                <div class="privacy-checkbox">
                    <input type="checkbox" name="kvkk" id="kvkk" required>
                    <label for="kvkk">
                        <?php echo $dil == 'tr' 
                            ? '<a href="#" onclick="event.preventDefault(); showNotification(\'KVKK metni yakında eklenecek\', \'info\')">KVKK</a> ve <a href="#" onclick="event.preventDefault(); showNotification(\'Gizlilik politikası yakında eklenecek\', \'info\')">Gizlilik Politikası</a>\'nı okudum ve kabul ediyorum.' 
                            : 'I have read and accept the <a href="#" onclick="event.preventDefault(); showNotification(\'Privacy policy will be added soon\', \'info\')">Privacy Policy</a> and <a href="#" onclick="event.preventDefault(); showNotification(\'Terms of service will be added soon\', \'info\')">Terms of Service</a>.'; ?>
                    </label>
                </div>
                
                <button type="submit" class="form-button btn-animated">
                    <i class="fas fa-user-plus"></i> <?php echo $text_selected['uye_ol']; ?>
                </button>
                
                <div class="register-benefits">
                    <h4><i class="fas fa-gift"></i> <?php echo $dil == 'tr' ? 'Üye Olmanın Avantajları:' : 'Benefits of Registering:'; ?></h4>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> <?php echo $dil == 'tr' ? '100 Hoş Geldin Puanı - İlk alışverişinizde kullanın' : '100 Welcome Points - Use on your first purchase'; ?></li>
                        <li><i class="fas fa-check-circle"></i> <?php echo $dil == 'tr' ? 'Hızlı Ödeme - Kayıtlı bilgilerinizle hızlıca ödeyin' : 'Fast Checkout - Pay quickly with your saved info'; ?></li>
                        <li><i class="fas fa-check-circle"></i> <?php echo $dil == 'tr' ? 'Sipariş Takibi - Tüm siparişlerinizi takip edin' : 'Order Tracking - Track all your orders'; ?></li>
                        <li><i class="fas fa-check-circle"></i> <?php echo $dil == 'tr' ? 'Özel İndirimler - Üyelere özel kampanyalardan yararlanın' : 'Special Discounts - Enjoy members-only campaigns'; ?></li>
                        <li><i class="fas fa-check-circle"></i> <?php echo $dil == 'tr' ? 'Doğum Günü Hediyesi - Doğum gününüzde özel sürprizler' : 'Birthday Gift - Special surprises on your birthday'; ?></li>
                    </ul>
                </div>
            </form>
        </div>
    </div>

    <!-- ARAMA ÇUBUĞU -->
    <div class="container">
        <div class="arama-cubugu animated-fadein">
            <form method="get" action="urunler.php" onsubmit="return validateSearch()">
                <input type="hidden" name="sayfa" value="urunler">
                <div class="arama-wrapper" style="display: flex; gap: 15px; align-items: center;">
                    <div class="kategori-select-wrapper" style="position: relative; flex: 0 0 200px;">
                        <select name="kategori" id="kategoriSelect" class="kategori-select" style="width: 100%; padding: 15px 40px 15px 20px; border: 2px solid #ffeef2; border-radius: 12px; font-size: 1rem; background: white; cursor: pointer; appearance: none; transition: all 0.3s; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"%23ff6b9d\" viewBox=\"0 0 16 16\"><path d=\"M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z\"/></svg>'); background-repeat: no-repeat; background-position: right 15px center; background-size: 16px;">
                            <option value=""><?php echo $text_selected['tum_urunler']; ?></option>
                            <option value="gul" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'gul') ? 'selected' : ''; ?>>🌹 <?php echo $dil == 'tr' ? 'Güller' : 'Roses'; ?></option>
                            <option value="orkide" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'orkide') ? 'selected' : ''; ?>>💮 <?php echo $dil == 'tr' ? 'Orkideler' : 'Orchids'; ?></option>
                            <option value="lale" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'lale') ? 'selected' : ''; ?>>🌷 <?php echo $dil == 'tr' ? 'Laleler' : 'Tulips'; ?></option>
                            <option value="buket" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'buket') ? 'selected' : ''; ?>>💐 <?php echo $dil == 'tr' ? 'Buketler' : 'Bouquets'; ?></option>
                            <option value="sukulent" <?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'sukulent') ? 'selected' : ''; ?>>🌵 <?php echo $dil == 'tr' ? 'Sukulentler' : 'Succulents'; ?></option>
                        </select>
                    </div>
                    
                    <div class="arama-input-wrapper" style="flex: 1; position: relative;">
                        <input type="text" name="arama" class="arama-input search-glow" 
                               placeholder="<?php echo $text_selected['ara_placeholder']; ?>" 
                               value="<?php echo isset($_GET['arama']) ? htmlspecialchars($_GET['arama']) : ''; ?>"
                               id="aramaInput"
                               style="width: 100%; padding: 15px 50px 15px 20px; border: 2px solid #ffeef2; border-radius: 12px; font-size: 1rem; transition: all 0.3s; background: linear-gradient(135deg, #fff9fb 0%, white 100%);">
                        <button type="submit" class="arama-buton btn-animated" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%); color: white; border: none; width: 40px; height: 40px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">🔍</button>
                    </div>
                </div>
                <div id="aramaUyari" class="arama-uyari" style="display: none; color: #ff6b9d; margin-top: 10px; text-align: center; font-size: 0.9rem;"></div>
            </form>
        </div>

        <!-- Breadcrumb -->
        <div class="breadcrumb" style="margin: 20px 0; padding: 15px 0; border-bottom: 1px solid #ffeef2;">
            <a href="anasayfa.php?sayfa=anasayfa" style="color: #ff6b9d; text-decoration: none; font-weight: 500;">🏠 <?php echo $text_selected['hosgeldin']; ?></a>
            <?php if(isset($sayfa) && $sayfa != "anasayfa"): ?>
                <span class="separator" style="margin: 0 10px; color: #999;">›</span>
                <span style="color: #666;">
                    <?php 
                    $sayfa_isimleri = [
                        "tr" => [
                            "urunler" => "Ürünler",
                            "sepet" => "Sepet",
                            "odeme" => "Ödeme",
                            "siparisler" => "Siparişler",
                            "iletisim" => "İletişim",
                            "favoriler" => "Favoriler",
                            "kuponlarim" => "Kuponlarım",
                            "arama" => "Arama",
                            "profil" => "Profilim",
                            "yorumlar" => "Yorumlar"
                        ],
                        "en" => [
                            "urunler" => "Products",
                            "sepet" => "Cart",
                            "odeme" => "Payment",
                            "siparisler" => "Orders",
                            "iletisim" => "Contact",
                            "favoriler" => "Favorites",
                            "kuponlarim" => "My Coupons",
                            "arama" => "Search",
                            "profil" => "My Profile",
                            "yorumlar" => "Comments"
                        ]
                    ];
                    echo isset($sayfa_isimleri[$dil][$sayfa]) ? $sayfa_isimleri[$dil][$sayfa] : ucfirst($sayfa);
                    ?>
                </span>
                <?php if(isset($kategori) && $kategori): ?>
                    <span class="separator" style="margin: 0 10px; color: #999;">›</span>
                    <span style="color: #ff6b9d; font-weight: 500;"><?php echo ucfirst($kategori); ?></span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- İÇERİK BAŞLANGICI -->
        <div class="main-content">