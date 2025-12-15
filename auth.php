<?php
// auth.php - DÜZENLENMİŞ VERSİYON (SEPET KORUMA ÖZELLİKLİ)

// 1. SESSION BAŞLATMA
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. MISAFİR SEPETİNİ KORUMAK İÇİN (girişten önce)
// Eğer kullanıcı giriş yapacaksa ve misafir sepeti yoksa, mevcut sepeti misafir sepetine kaydet
if (!isset($_SESSION['user_id']) && !isset($_SESSION['misafir_sepeti']) && isset($_SESSION['sepet']) && !empty($_SESSION['sepet'])) {
    $_SESSION['misafir_sepeti'] = $_SESSION['sepet'];
}

// 3. Dil ve tema değişkenlerini ayarla
$dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';
$tema = isset($_COOKIE['tema']) ? $_COOKIE['tema'] : 'light';

// 4. Admin sabit bilgileri
define('ADMIN_EMAIL', 'tozarhiranur@gmail.com');
define('ADMIN_PASSWORD', '123456');

// 5. JSON dosyasını kontrol et
$users_file = 'users.json';
if (!file_exists($users_file)) {
    file_put_contents($users_file, json_encode([]));
}

// 6. Giriş kontrol fonksiyonu
function isLoggedIn() {
    return isset($_SESSION['user_id']) || (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true);
}

// 7. Eğer giriş yapmışsa anasayfaya yönlendir
if (isLoggedIn() && !isset($_GET['action'])) {
    header('Location: anasayfa.php');
    exit;
}

// 8. Hangi giriş türü
$login_type = isset($_GET['type']) && $_GET['type'] == 'admin' ? 'admin' : 'normal';
$is_register = isset($_GET['form']) && $_GET['form'] == 'kayit';

// 9. Dil metinleri
$text_selected = [
    'giris' => $dil == 'tr' ? 'Giriş Yap' : 'Login',
    'uye_ol' => $dil == 'tr' ? 'Üye Ol' : 'Register',
    'email' => $dil == 'tr' ? 'E-posta' : 'Email',
    'sifre' => $dil == 'tr' ? 'Şifre' : 'Password',
    'ad_soyad' => $dil == 'tr' ? 'Ad Soyad' : 'Full Name',
    'tel' => $dil == 'tr' ? 'Telefon' : 'Phone',
    'adres' => $dil == 'tr' ? 'Adres' : 'Address'
];

// 10. Mesaj fonksiyonu
function setMessage($type, $text) {
    $_SESSION['auth_message'] = ['type' => $type, 'text' => $text];
}

// 11. KULLANICI KONTROL FONKSİYONLARI
function getUserByEmail($email) {
    global $users_file;
    if (!file_exists($users_file)) {
        return null;
    }
    
    $users = json_decode(file_get_contents($users_file), true);
    if (!$users || !is_array($users)) {
        return null;
    }
    
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            return $user;
        }
    }
    return null;
}

function checkUserExists($email) {
    return getUserByEmail($email) !== null;
}

// 12. ŞİFRE DOĞRULAMA FONKSİYONU
function verifyPassword($input_password, $stored_password) {
    // Eğer stored_password hash'li ise
    if (password_verify($input_password, $stored_password)) {
        return true;
    }
    
    // Eğer stored_password plain text ise
    if ($input_password === $stored_password) {
        return true;
    }
    
    return false;
}

// 13. SEPET BİRLEŞTİRME FONKSİYONU
function mergeShoppingCart($user_sepet, $guest_sepet) {
    if (empty($guest_sepet)) {
        return $user_sepet;
    }
    
    if (empty($user_sepet)) {
        return $guest_sepet;
    }
    
    $merged_cart = $user_sepet;
    
    foreach ($guest_sepet as $guest_item) {
        $item_found = false;
        
        foreach ($merged_cart as &$user_item) {
            if (isset($user_item['id']) && isset($guest_item['id']) && $user_item['id'] == $guest_item['id']) {
                // Aynı ürün varsa adetleri topla
                $user_adet = isset($user_item['adet']) ? intval($user_item['adet']) : 1;
                $guest_adet = isset($guest_item['adet']) ? intval($guest_item['adet']) : 1;
                $user_item['adet'] = $user_adet + $guest_adet;
                $item_found = true;
                break;
            }
        }
        
        if (!$item_found) {
            $merged_cart[] = $guest_item;
        }
    }
    
    return $merged_cart;
}

// 14. NORMAL GİRİŞ - SEPET BİRLEŞTİRMELİ
if (isset($_POST['action']) && $_POST['action'] == 'giris_normal') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $sifre = $_POST['sifre'] ?? '';
    
    if (empty($email) || empty($sifre)) {
        setMessage('error', $dil == 'tr' ? 'Email ve şifre gereklidir!' : 'Email and password are required!');
        header('Location: auth.php');
        exit;
    }
    
    // Kullanıcıyı bul
    $user = getUserByEmail($email);
    
    if (!$user) {
        setMessage('error', $dil == 'tr' ? 'Bu email ile kayıtlı kullanıcı bulunamadı!' : 'No user found with this email!');
        header('Location: auth.php');
        exit;
    }
    
    // Şifre kontrolü
    if (!verifyPassword($sifre, $user['sifre'])) {
        setMessage('error', $dil == 'tr' ? 'Hatalı şifre!' : 'Wrong password!');
        header('Location: auth.php');
        exit;
    }
    
    // MISAFİR SEPETİNİ AL
    $misafir_sepeti = isset($_SESSION['misafir_sepeti']) ? $_SESSION['misafir_sepeti'] : [];
    
    // KULLANICI SE PETİNİ AL (eğer daha önceden varsa)
    $kullanici_sepeti = isset($_SESSION['sepet']) ? $_SESSION['sepet'] : [];
    
    // SEPETLERİ BİRLEŞTİR
    $birlesik_sepet = mergeShoppingCart($kullanici_sepeti, $misafir_sepeti);
    
    // Giriş başarılı
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['ad_soyad'] = $user['ad_soyad'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['puan'] = $user['puan'] ?? 0;
    
    // BİRLEŞTİRİLMİŞ SEPETİ KAYDET
    $_SESSION['sepet'] = $birlesik_sepet;
    
    // MISAFİR SEPETİNİ TEMİZLE (artık gerek yok)
    unset($_SESSION['misafir_sepeti']);
    
    // Mesajı ayarla (sepet birleşme bilgisiyle)
    if (!empty($misafir_sepeti)) {
        $toplam_urun = 0;
        foreach ($misafir_sepeti as $urun) {
            $adet = isset($urun['adet']) ? intval($urun['adet']) : 1;
            $toplam_urun += $adet;
        }
        
        setMessage('success', 
            $dil == 'tr' 
            ? "Başarıyla giriş yaptınız! Misafir sepetinizdeki $toplam_urun ürün hesabınıza aktarıldı." 
            : "Login successful! $toplam_urun items from your guest cart have been transferred to your account."
        );
    } else {
        setMessage('success', 
            $dil == 'tr' 
            ? 'Başarıyla giriş yaptınız!' 
            : 'Login successful!'
        );
    }
    
    header('Location: anasayfa.php');
    exit;
}

// 15. ADMIN GİRİŞ
if (isset($_POST['action']) && $_POST['action'] == 'giris_admin') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        setMessage('error', $dil == 'tr' ? 'Email ve şifre gereklidir!' : 'Email and password are required!');
        header('Location: auth.php?type=admin');
        exit;
    }
    
    // Admin kontrolü
    if ($email === ADMIN_EMAIL && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        
        setMessage('success', $dil == 'tr' ? 'Admin olarak başarıyla giriş yapıldı!' : 'Logged in as admin successfully!');
        header('Location: anasayfa.php');
        exit;
    } else {
        setMessage('error', $dil == 'tr' ? 'Hatalı admin bilgileri!' : 'Wrong admin credentials!');
        header('Location: auth.php?type=admin');
        exit;
    }
}

// 16. KAYIT İŞLEMİ (ÜYE OL) - SEPET BİRLEŞTİRMELİ
if (isset($_POST['action']) && $_POST['action'] == 'kayit') {
    $ad_soyad = trim($_POST['ad_soyad'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $sifre = $_POST['sifre'] ?? '';
    $telefon = trim($_POST['telefon'] ?? '');
    
    if (empty($ad_soyad) || empty($email) || empty($sifre) || empty($telefon)) {
        setMessage('error', $dil == 'tr' ? 'Tüm alanları doldurun!' : 'Please fill all fields!');
        header('Location: auth.php?form=kayit');
        exit;
    }
    
    // Email format kontrolü
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setMessage('error', $dil == 'tr' ? 'Geçerli bir email adresi girin!' : 'Please enter a valid email address!');
        header('Location: auth.php?form=kayit');
        exit;
    }
    
    // Şifre uzunluk kontrolü
    if (strlen($sifre) < 6) {
        setMessage('error', $dil == 'tr' ? 'Şifre en az 6 karakter olmalıdır!' : 'Password must be at least 6 characters!');
        header('Location: auth.php?form=kayit');
        exit;
    }
    
    // Email kontrolü
    if (checkUserExists($email)) {
        setMessage('error', $dil == 'tr' ? 'Bu email zaten kayıtlı! Lütfen giriş yapın.' : 'This email is already registered! Please login.');
        header('Location: auth.php');
        exit;
    }
    
    // MISAFİR SEPETİNİ AL
    $misafir_sepeti = isset($_SESSION['misafir_sepeti']) ? $_SESSION['misafir_sepeti'] : [];
    
    $users = [];
    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true);
        if (!$users || !is_array($users)) {
            $users = [];
        }
    }
    
    // Yeni kullanıcı
    $new_user = [
        'id' => 'user_' . uniqid(),
        'ad_soyad' => $ad_soyad,
        'email' => $email,
        'sifre' => password_hash($sifre, PASSWORD_DEFAULT),
        'telefon' => $telefon,
        'adres' => $_POST['adres'] ?? '',
        'kayit_tarihi' => date('Y-m-d H:i:s'),
        'puan' => 100
    ];
    
    $users[] = $new_user;
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    $_SESSION['user_id'] = $new_user['id'];
    $_SESSION['ad_soyad'] = $new_user['ad_soyad'];
    $_SESSION['email'] = $new_user['email'];
    $_SESSION['puan'] = 100;
    
    // MISAFİR SEPETİNİ KULLANICI SE PETİNE AKTAR
    $_SESSION['sepet'] = $misafir_sepeti;
    
    // MISAFİR SEPETİNİ TEMİZLE
    unset($_SESSION['misafir_sepeti']);
    
    // Mesajı ayarla (sepet aktarma bilgisiyle)
    if (!empty($misafir_sepeti)) {
        $toplam_urun = 0;
        foreach ($misafir_sepeti as $urun) {
            $adet = isset($urun['adet']) ? intval($urun['adet']) : 1;
            $toplam_urun += $adet;
        }
        
        setMessage('success', 
            $dil == 'tr' 
            ? "Başarıyla kayıt oldunuz! 100 hoş geldin puanı kazandınız. Misafir sepetinizdeki $toplam_urun ürün hesabınıza aktarıldı." 
            : "Registration successful! You earned 100 welcome points. $toplam_urun items from your guest cart have been transferred to your account."
        );
    } else {
        setMessage('success', 
            $dil == 'tr' 
            ? 'Başarıyla kayıt oldunuz! 100 hoş geldin puanı kazandınız.' 
            : 'Registration successful! You earned 100 welcome points.'
        );
    }
    
    header('Location: anasayfa.php');
    exit;
}

// 17. ÇIKIŞ - SEPETİ MISAFİR SEPETİNE KAYDET
if (isset($_GET['action']) && $_GET['action'] == 'cikis') {
    // Çıkış yapmadan önce sepeti misafir sepetine kaydet
    if (isset($_SESSION['sepet']) && !empty($_SESSION['sepet'])) {
        $_SESSION['misafir_sepeti'] = $_SESSION['sepet'];
    }
    
    // Tüm session verilerini temizle
    session_destroy();
    
    // Yeni session başlat ve mesajı kaydet
    session_start();
    setMessage('success', $dil == 'tr' ? 'Başarıyla çıkış yaptınız! Sepetiniz korundu.' : 'Logged out successfully! Your cart has been saved.');
    
    header('Location: anasayfa.php');
    exit;
}

// 18. Mesajları al
$message = '';
$message_type = '';
if (isset($_SESSION['auth_message'])) {
    $message = $_SESSION['auth_message']['text'];
    $message_type = $_SESSION['auth_message']['type'];
    unset($_SESSION['auth_message']);
}
?>

<!DOCTYPE html>
<html lang="<?php echo $dil; ?>" data-theme="<?php echo $tema; ?>">
<head>
    <title>ÇiçekBahçesi - <?php echo $login_type == 'admin' ? 'Admin Giriş' : ($is_register ? 'Üye Ol' : 'Giriş Yap'); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }
        
        /* ANA SAYFA DÖNÜŞ BUTONU */
        .home-return {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        .home-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            transition: all 0.3s;
        }
        
        .home-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }
        
        /* AUTH CONTAINER */
        .auth-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(255, 107, 157, 0.15);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }
        
        /* HEADER */
        .auth-header {
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .auth-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1%, transparent 20%);
            animation: rotate 20s linear infinite;
        }
        
        .auth-logo {
            font-size: 48px;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
            animation: bounce 2s infinite;
        }
        
        .auth-title {
            font-family: 'Dancing Script', cursive;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }
        
        .auth-subtitle {
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        /* CONTENT */
        .auth-content {
            padding: 40px 30px;
        }
        
        /* MESSAGE */
        .message {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            animation: slideIn 0.3s ease-out;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4CAF50;
        }
        
        .message.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }
        
        /* TABS */
        .auth-tabs {
            display: flex;
            background: #f5f5f5;
            padding: 6px;
            border-radius: 12px;
            margin-bottom: 30px;
            position: relative;
        }
        
        .tab-slider {
            position: absolute;
            background: white;
            height: calc(100% - 12px);
            border-radius: 8px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            width: calc(50% - 6px);
            box-shadow: 0 4px 12px rgba(255, 107, 157, 0.2);
        }
        
        .auth-tab {
            flex: 1;
            background: none;
            border: none;
            color: #666;
            padding: 12px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
            position: relative;
            z-index: 1;
            font-size: 15px;
        }
        
        .auth-tab.active {
            color: #ff6b9d;
        }
        
        /* FORMS */
        .form-wrapper {
            position: relative;
            overflow: hidden;
        }
        
        .form-page {
            width: 100%;
            opacity: 1;
            transform: translateX(0);
            transition: all 0.4s ease;
        }
        
        .form-page.hidden {
            opacity: 0;
            transform: translateX(100%);
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
        }
        
        /* FORM ELEMENTS */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 14px;
            border: 2px solid #f0f0f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #ff6b9d;
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }
        
        /* BUTTONS */
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 157, 0.3);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        .admin-btn {
            background: linear-gradient(135deg, #d32f2f 0%, #f44336 100%);
        }
        
        .admin-btn:hover {
            box-shadow: 0 8px 20px rgba(211, 47, 47, 0.3);
        }
        
        /* LINKS */
        .form-links {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .form-link {
            color: #ff6b9d;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-block;
            margin: 5px 0;
            cursor: pointer;
        }
        
        .form-link:hover {
            text-decoration: underline;
            color: #ff4081;
        }
        
        .admin-link {
            color: #d32f2f;
            font-weight: 600;
        }
        
        /* ADMIN NOTE */
        .admin-note {
            background: #fff3e0;
            border: 1px solid #ffb74d;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            margin-top: 25px;
            color: #e65100;
            font-size: 13px;
        }
        
        .admin-note i {
            margin-right: 8px;
        }
        
        /* SEPET KORUMA BİLDİRİMİ */
        .cart-notice {
            background: #e3f2fd;
            border: 1px solid #64b5f6;
            border-radius: 8px;
            padding: 12px 15px;
            text-align: center;
            margin-bottom: 20px;
            color: #1565c0;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            animation: slideIn 0.3s ease-out;
        }
        
        .cart-notice.hidden {
            display: none;
        }
        
        /* FOOTER */
        .auth-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 12px;
        }
        
        /* ANIMATIONS */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* RESPONSIVE */
        @media (max-width: 480px) {
            .home-return {
                top: 10px;
                left: 10px;
            }
            
            .auth-container {
                margin: 20px;
            }
            
            .auth-header {
                padding: 30px 20px;
            }
            
            .auth-title {
                font-size: 28px;
            }
            
            .auth-content {
                padding: 30px 20px;
            }
            
            .auth-logo {
                font-size: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- ANA SAYFA DÖNÜŞ BUTONU -->
    <div class="home-return">
        <a href="anasayfa.php" class="home-btn">
            <i class="fas fa-home"></i>
            Anasayfaya Dön
        </a>
    </div>
    
    <!-- AUTH CONTAINER -->
    <div class="auth-container">
        <!-- HEADER -->
        <div class="auth-header">
            <div class="auth-logo">🌸</div>
            <h1 class="auth-title">
                <?php if($login_type == 'admin'): ?>
                    Admin Giriş
                <?php elseif($is_register): ?>
                    Üye Ol
                <?php else: ?>
                    Giriş Yap
                <?php endif; ?>
            </h1>
            <p class="auth-subtitle">
                <?php if($login_type == 'admin'): ?>
                    Yetkili personel girişi
                <?php elseif($is_register): ?>
                    ÇiçekBahçesi'ne hoş geldiniz
                <?php else: ?>
                    Hesabınıza giriş yapın
                <?php endif; ?>
            </p>
        </div>
        
        <!-- CONTENT -->
        <div class="auth-content">
            <?php if($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- SEPET KORUMA BİLDİRİMİ -->
            <?php if(isset($_SESSION['misafir_sepeti']) && !empty($_SESSION['misafir_sepeti']) && $login_type != 'admin'): ?>
                <?php 
                $toplam_urun = 0;
                foreach ($_SESSION['misafir_sepeti'] as $urun) {
                    $adet = isset($urun['adet']) ? intval($urun['adet']) : 1;
                    $toplam_urun += $adet;
                }
                ?>
                <div class="cart-notice" id="cartNotice">
                    <i class="fas fa-shopping-cart"></i>
                    <span>
                        <?php echo $dil == 'tr' 
                            ? "Sepetinizde $toplam_urun ürün var. Giriş yapınca sepetiniz hesabınıza aktarılacak."
                            : "You have $toplam_urun items in your cart. They will be transferred to your account when you login.";
                        ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if($login_type == 'admin'): ?>
                <!-- ADMIN LOGIN FORM -->
                <form method="POST" action="auth.php?type=admin" id="adminForm">
                    <input type="hidden" name="action" value="giris_admin">
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i> E-posta
                        </label>
                        <input type="email" name="email" class="form-input" placeholder="tozarhiranur@gmail.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock"></i> Şifre
                        </label>
                        <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                    </div>
                    
                    <button type="submit" class="submit-btn admin-btn">
                        <i class="fas fa-user-shield"></i>
                        Admin Giriş Yap
                    </button>
                    
                    <div class="form-links">
                        <a href="auth.php" class="form-link">
                            <i class="fas fa-arrow-left"></i> 
                            <?php echo $dil == 'tr' ? 'Kullanıcı Girişine Dön' : 'Back to User Login'; ?>
                        </a>
                    </div>
                </form>
                
                <div class="admin-note">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $dil == 'tr' ? 'Sadece yetkili admin personel girebilir' : 'Only authorized admin personnel can access'; ?>
                </div>
                
            <?php else: ?>
                <!-- USER LOGIN/REGISTER -->
                <?php if(!$is_register): ?>
                    <!-- TABS -->
                    <div class="auth-tabs" id="authTabs">
                        <div class="tab-slider" id="tabSlider"></div>
                        <button type="button" class="auth-tab active" data-tab="login" id="loginTab">
                            <?php echo $text_selected['giris']; ?>
                        </button>
                        <button type="button" class="auth-tab" data-tab="register" id="registerTab">
                            <?php echo $text_selected['uye_ol']; ?>
                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- FORM WRAPPER -->
                <div class="form-wrapper">
                    <!-- LOGIN FORM -->
                    <div class="form-page <?php echo $is_register ? 'hidden' : ''; ?>" id="loginPage">
                        <form method="POST" action="auth.php" id="loginForm">
                            <input type="hidden" name="action" value="giris_normal">
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i> <?php echo $text_selected['email']; ?>
                                </label>
                                <input type="email" name="email" class="form-input" 
                                       placeholder="<?php echo $dil == 'tr' ? 'ornek@email.com' : 'example@email.com'; ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> <?php echo $text_selected['sifre']; ?>
                                </label>
                                <input type="password" name="sifre" class="form-input" 
                                       placeholder="••••••••" 
                                       required>
                            </div>
                            
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-sign-in-alt"></i>
                                <?php echo $text_selected['giris']; ?>
                            </button>
                        </form>
                        
                        <?php if(!$is_register): ?>
                            <div class="form-links">
                                <a href="auth.php?type=admin" class="form-link admin-link">
                                    <i class="fas fa-user-shield"></i> 
                                    <?php echo $dil == 'tr' ? 'Admin Girişi' : 'Admin Login'; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- REGISTER FORM -->
                    <div class="form-page <?php echo !$is_register ? 'hidden' : ''; ?>" id="registerPage">
                        <form method="POST" action="auth.php?form=kayit" id="registerForm">
                            <input type="hidden" name="action" value="kayit">
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> <?php echo $text_selected['ad_soyad']; ?>
                                </label>
                                <input type="text" name="ad_soyad" class="form-input" 
                                       placeholder="<?php echo $dil == 'tr' ? 'Adınız Soyadınız' : 'Your Full Name'; ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i> <?php echo $text_selected['email']; ?>
                                </label>
                                <input type="email" name="email" class="form-input" 
                                       placeholder="<?php echo $dil == 'tr' ? 'ornek@email.com' : 'example@email.com'; ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> <?php echo $text_selected['sifre']; ?>
                                </label>
                                <input type="password" name="sifre" class="form-input" 
                                       placeholder="<?php echo $dil == 'tr' ? 'En az 6 karakter' : 'At least 6 characters'; ?>" 
                                       required minlength="6">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-phone"></i> <?php echo $text_selected['tel']; ?>
                                </label>
                                <input type="tel" name="telefon" class="form-input" 
                                       placeholder="<?php echo $dil == 'tr' ? '5xx xxx xx xx' : 'Phone number'; ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo $text_selected['adres']; ?>
                                </label>
                                <input type="text" name="adres" class="form-input" 
                                       placeholder="<?php echo $dil == 'tr' ? 'Teslimat adresiniz' : 'Delivery address'; ?>">
                            </div>
                            
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-user-plus"></i>
                                <?php echo $text_selected['uye_ol']; ?>
                            </button>
                        </form>
                        
                        <?php if(!$is_register): ?>
                            <div class="form-links">
                                <span><?php echo $dil == 'tr' ? 'Zaten hesabınız var mı?' : 'Already have an account?'; ?></span>
                                <a href="javascript:void(0)" class="form-link" id="goToLoginLink">
                                    <?php echo $dil == 'tr' ? 'Giriş yapın' : 'Login here'; ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="form-links">
                                <a href="auth.php" class="form-link">
                                    <i class="fas fa-arrow-left"></i> 
                                    <?php echo $dil == 'tr' ? 'Giriş sayfasına dön' : 'Back to login page'; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- FOOTER -->
            <div class="auth-footer">
                <i class="fas fa-lock"></i> 
                <?php echo $dil == 'tr' ? 'Güvenli bağlantı' : 'Secure connection'; ?>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginTab = document.getElementById('loginTab');
            const registerTab = document.getElementById('registerTab');
            const tabSlider = document.getElementById('tabSlider');
            const loginPage = document.getElementById('loginPage');
            const registerPage = document.getElementById('registerPage');
            const goToLoginLink = document.getElementById('goToLoginLink');
            const cartNotice = document.getElementById('cartNotice');
            
            // Tab switching for user login/register
            if(loginTab && registerTab) {
                loginTab.addEventListener('click', function() {
                    switchToLogin();
                });
                
                registerTab.addEventListener('click', function() {
                    switchToRegister();
                });
            }
            
            // Go to login link
            if(goToLoginLink) {
                goToLoginLink.addEventListener('click', function() {
                    switchToLogin();
                });
            }
            
            function switchToLogin() {
                if(tabSlider) {
                    tabSlider.style.transform = 'translateX(0)';
                }
                if(loginTab && registerTab) {
                    loginTab.classList.add('active');
                    registerTab.classList.remove('active');
                }
                if(loginPage && registerPage) {
                    loginPage.classList.remove('hidden');
                    registerPage.classList.add('hidden');
                }
            }
            
            function switchToRegister() {
                if(tabSlider) {
                    tabSlider.style.transform = 'translateX(100%)';
                }
                if(loginTab && registerTab) {
                    loginTab.classList.remove('active');
                    registerTab.classList.add('active');
                }
                if(loginPage && registerPage) {
                    loginPage.classList.add('hidden');
                    registerPage.classList.remove('hidden');
                }
            }
            
            // Form validation
            const registerForm = document.getElementById('registerForm');
            if(registerForm) {
                const passwordInput = registerForm.querySelector('input[name="sifre"]');
                if(passwordInput) {
                    passwordInput.addEventListener('blur', function() {
                        if(this.value.length < 6 && this.value.length > 0) {
                            this.style.borderColor = '#f44336';
                            this.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
                        } else {
                            this.style.borderColor = '#f0f0f0';
                            this.style.boxShadow = 'none';
                        }
                    });
                }
                
                const emailInput = registerForm.querySelector('input[name="email"]');
                if(emailInput) {
                    emailInput.addEventListener('blur', function() {
                        const email = this.value;
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if(email && !emailRegex.test(email)) {
                            this.style.borderColor = '#f44336';
                            this.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
                        } else {
                            this.style.borderColor = '#f0f0f0';
                            this.style.boxShadow = 'none';
                        }
                    });
                }
            }
            
            // Auto-focus first input
            const firstInput = document.querySelector('.form-input');
            if(firstInput) {
                firstInput.focus();
            }
            
            // Sepet bildirimini 10 saniye sonra gizle
            if(cartNotice) {
                setTimeout(function() {
                    cartNotice.classList.add('hidden');
                }, 10000); // 10 saniye
            }
        });
    </script>
</body>
</html>