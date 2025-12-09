<?php
// auth.php - DÜZELTİLMİŞ VERSİYON
session_start();

// JSON dosyalarını kontrol et
$users_file = 'users.json';
$siparisler_file = 'siparisler.json';

if(!file_exists($users_file)) {
    file_put_contents($users_file, json_encode([]));
}

if(!file_exists($siparisler_file)) {
    file_put_contents($siparisler_file, json_encode([]));
}

// Dil ayarı
$dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';

// Hangi formun gösterileceğini belirle
$active_form = 'giris'; // Varsayılan: giriş formu

if(isset($_GET['form'])) {
    $form = $_GET['form'];
    if(in_array($form, ['giris', 'kayit', 'forgot'])) {
        $active_form = $form;
    }
}

// CSRF token oluştur
if(!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kullanıcı zaten giriş yapmışsa anasayfaya yönlendir
if(isset($_SESSION['user_id']) && !isset($_GET['action'])) {
    header('Location: anasayfa.php');
    exit;
}

// Mesaj fonksiyonu
function setMessage($type, $text) {
    $_SESSION['auth_message'] = ['type' => $type, 'text' => $text];
}

// CSRF token kontrolü
function validateCsrfToken() {
    global $dil;
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        setMessage('error', $dil == 'tr' ? 'Güvenlik hatası! Lütfen tekrar deneyin.' : 'Security error! Please try again.');
        return false;
    }
    return true;
}

// Kullanıcı doğrulama
function validateUserData($data, $is_register = false) {
    global $dil, $users_file;
    
    $errors = [];
    
    if($is_register) {
        // Kayıt için gerekli alanlar
        $required = ['ad_soyad', 'email', 'sifre', 'sifre_tekrar', 'telefon'];
        
        foreach($required as $field) {
            if(empty(trim($data[$field] ?? ''))) {
                $field_names = [
                    'tr' => ['ad_soyad' => 'Ad Soyad', 'email' => 'E-posta', 'sifre' => 'Şifre', 'sifre_tekrar' => 'Şifre Tekrar', 'telefon' => 'Telefon'],
                    'en' => ['ad_soyad' => 'Full Name', 'email' => 'Email', 'sifre' => 'Password', 'sifre_tekrar' => 'Confirm Password', 'telefon' => 'Phone']
                ];
                $errors[] = ($dil == 'tr' ? 'Bu alan gereklidir: ' : 'This field is required: ') . $field_names[$dil][$field];
            }
        }
        
        // Şifre kontrolü
        if($data['sifre'] !== $data['sifre_tekrar']) {
            $errors[] = $dil == 'tr' ? 'Şifreler eşleşmiyor!' : 'Passwords do not match!';
        }
        
        if(strlen($data['sifre']) < 6) {
            $errors[] = $dil == 'tr' ? 'Şifre en az 6 karakter olmalı!' : 'Password must be at least 6 characters!';
        }
        
        // Email formatı
        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = $dil == 'tr' ? 'Geçerli bir email adresi girin!' : 'Please enter a valid email address!';
        }
        
        // Telefon formatı
        $phone = preg_replace('/[^0-9]/', '', $data['telefon']);
        if(strlen($phone) < 10) {
            $errors[] = $dil == 'tr' ? 'Geçerli bir telefon numarası girin!' : 'Please enter a valid phone number!';
        }
        
        // Email kontrolü
        $users = json_decode(file_get_contents($users_file), true);
        $email = strtolower(trim($data['email']));
        
        foreach($users as $user) {
            if(strtolower($user['email']) === $email) {
                $errors[] = $dil == 'tr' ? 'Bu email adresi zaten kayıtlı!' : 'This email is already registered!';
                break;
            }
        }
    } else {
        // Giriş için gerekli alanlar
        if(empty(trim($data['email'] ?? ''))) {
            $errors[] = $dil == 'tr' ? 'E-posta adresi gereklidir!' : 'Email address is required!';
        }
        
        if(empty(trim($data['sifre'] ?? ''))) {
            $errors[] = $dil == 'tr' ? 'Şifre gereklidir!' : 'Password is required!';
        }
    }
    
    return $errors;
}

// Kullanıcı kaydı
if(isset($_POST['action']) && $_POST['action'] == 'kayit') {
    if(!validateCsrfToken()) {
        header('Location: auth.php?form=kayit');
        exit;
    }
    
    $errors = validateUserData($_POST, true);
    
    if(!empty($errors)) {
        setMessage('error', implode('<br>', $errors));
        header('Location: auth.php?form=kayit');
        exit;
    }
    
    $users = json_decode(file_get_contents($users_file), true);
    
    // Yeni kullanıcı oluştur
    $new_user = [
        'id' => uniqid('user_', true),
        'ad_soyad' => htmlspecialchars(trim($_POST['ad_soyad'])),
        'email' => strtolower(trim($_POST['email'])),
        'sifre' => password_hash($_POST['sifre'], PASSWORD_DEFAULT),
        'telefon' => htmlspecialchars(trim($_POST['telefon'])),
        'adres' => htmlspecialchars(trim($_POST['adres'] ?? '')),
        'cinsiyet' => htmlspecialchars(trim($_POST['cinsiyet'] ?? '')),
        'dogum_tarihi' => htmlspecialchars(trim($_POST['dogum_tarihi'] ?? '')),
        'bulten' => isset($_POST['bulten']) ? true : false,
        'kayit_tarihi' => date('Y-m-d H:i:s'),
        'son_giris' => date('Y-m-d H:i:s'),
        'aktif' => true,
        'avatar' => 'default.png',
        'puan' => 100
    ];
    
    $users[] = $new_user;
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // Oturumu başlat
    $_SESSION['user_id'] = $new_user['id'];
    $_SESSION['ad_soyad'] = $new_user['ad_soyad'];
    $_SESSION['email'] = $new_user['email'];
    $_SESSION['telefon'] = $new_user['telefon'];
    $_SESSION['adres'] = $new_user['adres'];
    $_SESSION['puan'] = $new_user['puan'];
    
    setMessage('success', $dil == 'tr' ? 'Başarıyla kayıt oldunuz! 100 hoş geldin puanı kazandınız.' : 'Registration successful! You earned 100 welcome points.');
    header('Location: anasayfa.php');
    exit;
}

// Kullanıcı girişi
if(isset($_POST['action']) && $_POST['action'] == 'giris') {
    if(!validateCsrfToken()) {
        header('Location: auth.php');
        exit;
    }
    
    $errors = validateUserData($_POST, false);
    
    if(!empty($errors)) {
        setMessage('error', implode('<br>', $errors));
        header('Location: auth.php');
        exit;
    }
    
    $users = json_decode(file_get_contents($users_file), true);
    $email = strtolower(trim($_POST['email']));
    $sifre = $_POST['sifre'];
    $user_found = false;
    
    foreach($users as $user) {
        if(strtolower($user['email']) === $email) {
            $user_found = true;
            
            if(password_verify($sifre, $user['sifre'])) {
                if(!($user['aktif'] ?? true)) {
                    setMessage('error', $dil == 'tr' ? 'Hesabınız askıya alınmış!' : 'Your account is suspended!');
                    header('Location: auth.php');
                    exit;
                }
                
                // Oturumu başlat
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['ad_soyad'] = $user['ad_soyad'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['telefon'] = $user['telefon'] ?? '';
                $_SESSION['adres'] = $user['adres'] ?? '';
                $_SESSION['puan'] = $user['puan'] ?? 0;
                
                // Son giriş tarihini güncelle
                $user['son_giris'] = date('Y-m-d H:i:s');
                $updated_users = array_map(function($u) use ($user) {
                    return $u['id'] === $user['id'] ? $user : $u;
                }, $users);
                file_put_contents($users_file, json_encode($updated_users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                setMessage('success', $dil == 'tr' ? 'Başarıyla giriş yaptınız!' : 'Login successful!');
                header('Location: anasayfa.php');
                exit;
            }
        }
    }
    
    if($user_found) {
        setMessage('error', $dil == 'tr' ? 'Şifre hatalı!' : 'Invalid password!');
    } else {
        setMessage('error', $dil == 'tr' ? 'Bu email adresi ile kayıtlı kullanıcı bulunamadı!' : 'No user found with this email address!');
    }
    
    header('Location: auth.php');
    exit;
}

// Şifremi unuttum
if(isset($_POST['action']) && $_POST['action'] == 'sifremi_unuttum') {
    if(!validateCsrfToken()) {
        header('Location: auth.php?form=forgot');
        exit;
    }
    
    $email = strtolower(trim($_POST['email'] ?? ''));
    
    if(empty($email)) {
        setMessage('error', $dil == 'tr' ? 'Email adresinizi girin!' : 'Please enter your email address!');
        header('Location: auth.php?form=forgot');
        exit;
    }
    
    $users = json_decode(file_get_contents($users_file), true);
    $user_found = false;
    
    foreach($users as &$user) {
        if(strtolower($user['email']) === $email) {
            $user_found = true;
            
            // Geçici şifre oluştur
            $temp_password = substr(md5(uniqid()), 0, 8);
            $user['sifre'] = password_hash($temp_password, PASSWORD_DEFAULT);
            
            setMessage('info', $dil == 'tr' 
                ? "Geçici şifreniz: <strong>$temp_password</strong><br>Lütfen giriş yaptıktan sonra şifrenizi değiştirin." 
                : "Your temporary password: <strong>$temp_password</strong><br>Please change your password after login.");
            break;
        }
    }
    
    if($user_found) {
        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } else {
        setMessage('error', $dil == 'tr' ? 'Bu email adresi ile kayıtlı kullanıcı bulunamadı!' : 'No user found with this email address!');
    }
    
    header('Location: auth.php?form=forgot');
    exit;
}

// Çıkış yap
if(isset($_GET['action']) && $_GET['action'] == 'cikis') {
    session_destroy();
    setMessage('success', $dil == 'tr' ? 'Başarıyla çıkış yaptınız!' : 'Logout successful!');
    header('Location: anasayfa.php');
    exit;
}

// Mesajları al
$message = '';
$message_type = '';
if(isset($_SESSION['auth_message'])) {
    $message = $_SESSION['auth_message']['text'];
    $message_type = $_SESSION['auth_message']['type'];
    unset($_SESSION['auth_message']);
}
?>

<!DOCTYPE html>
<html lang="<?php echo $dil; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ÇiçekBahçesi - <?php echo $active_form == 'giris' ? ($dil == 'tr' ? 'Giriş Yap' : 'Login') : ($dil == 'tr' ? 'Üye Ol' : 'Register'); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Auth.php Özel Stilleri */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f9f5 0%, #e8f5e9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header - header.php ile aynı */
        header {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        
        .logo a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .logo span {
            color: #ffeb3b;
            margin-left: 5px;
        }
        
        .logo-icon {
            font-size: 32px;
            margin-right: 10px;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            align-items: center;
            gap: 20px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            padding: 8px 12px;
            border-radius: 5px;
        }
        
        nav ul li a:hover {
            color: #ffeb3b;
            background-color: rgba(255,255,255,0.1);
        }
        
        /* Auth Container */
        .auth-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .auth-box {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }
        
        .auth-header {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .auth-header::before {
            content: '🌸🌹🌷💐🌺🌻';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            font-size: 40px;
            opacity: 0.1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .auth-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        /* Tab'lar - Sadece 2 tab */
        .auth-tabs {
            display: flex;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }
        
        .auth-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            border-radius: 8px;
            border: none;
            background: none;
            cursor: pointer;
        }
        
        .auth-tab:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .auth-tab.active {
            background: white;
            color: #2e7d32;
        }
        
        /* Form İçeriği */
        .auth-content {
            padding: 40px;
        }
        
        /* Mesajlar */
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
            animation: slideIn 0.5s ease-out;
        }
        
        .message.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .message.info {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }
        
        /* Form Stilleri */
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
            animation: fadeIn 0.5s ease-out;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
            font-size: 0.95rem;
        }
        
        .required::after {
            content: ' *';
            color: #e53935;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 18px;
            padding: 5px;
        }
        
        .form-hint {
            display: block;
            margin-top: 5px;
            font-size: 0.85rem;
            color: #666;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        /* Butonlar */
        .submit-btn {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(46, 125, 50, 0.3);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .form-links {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 1rem;
        }
        
        .form-links a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 600;
        }
        
        .form-links a:hover {
            text-decoration: underline;
        }
        
        /* Footer */
        footer {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            padding: 40px 0 20px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 30px;
        }
        
        .footer-section h3 {
            margin-bottom: 20px;
            color: #ffeb3b;
            font-size: 20px;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 10px;
        }
        
        .footer-section ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-section ul li a:hover {
            color: #ffeb3b;
        }
        
        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 14px;
            opacity: 0.8;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .auth-content {
                padding: 30px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
        
        @media (max-width: 480px) {
            .auth-header {
                padding: 20px;
            }
            
            .auth-header h1 {
                font-size: 1.8rem;
            }
            
            .auth-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-content">
            <div class="logo">
                <a href="anasayfa.php">
                    <span class="logo-icon">🌸</span>
                    ÇiçekBahçesi<span>.</span>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="anasayfa.php"><?php echo $dil == 'tr' ? 'Ana Sayfa' : 'Home'; ?></a></li>
                    <li><a href="urunler.php"><?php echo $dil == 'tr' ? 'Ürünler' : 'Products'; ?></a></li>
                    <li><a href="iletisim.php"><?php echo $dil == 'tr' ? 'İletişim' : 'Contact'; ?></a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="profil.php"><?php echo $dil == 'tr' ? 'Profilim' : 'My Profile'; ?></a></li>
                        <li><a href="auth.php?action=cikis"><?php echo $dil == 'tr' ? 'Çıkış Yap' : 'Logout'; ?></a></li>
                    <?php else: ?>
                        <li><a href="auth.php" style="background: #ffeb3b; color: #2e7d32; font-weight: bold; padding: 10px 20px;">
                            <?php echo $dil == 'tr' ? 'Giriş Yap / Üye Ol' : 'Login / Register'; ?>
                        </a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Auth Container -->
    <div class="container">
        <div class="auth-container">
            <div class="auth-box">
                <div class="auth-header">
                    <h1 id="form-title">
                        <?php if($active_form == 'giris'): ?>
                            <?php echo $dil == 'tr' ? 'Giriş Yap' : 'Login'; ?>
                        <?php elseif($active_form == 'kayit'): ?>
                            <?php echo $dil == 'tr' ? 'Üye Ol' : 'Register'; ?>
                        <?php else: ?>
                            <?php echo $dil == 'tr' ? 'Şifremi Unuttum' : 'Forgot Password'; ?>
                        <?php endif; ?>
                    </h1>
                    
                    <div class="auth-tabs">
                        <button class="auth-tab <?php echo $active_form == 'giris' ? 'active' : ''; ?>" data-tab="giris">
                            <?php echo $dil == 'tr' ? 'Giriş Yap' : 'Login'; ?>
                        </button>
                        <button class="auth-tab <?php echo $active_form == 'kayit' ? 'active' : ''; ?>" data-tab="kayit">
                            <?php echo $dil == 'tr' ? 'Üye Ol' : 'Register'; ?>
                        </button>
                    </div>
                </div>
                
                <div class="auth-content">
                    <!-- Mesajlar -->
                    <?php if($message): ?>
                        <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <!-- Giriş Formu -->
                    <form method="POST" action="auth.php" class="auth-form <?php echo $active_form == 'giris' ? 'active' : ''; ?>" id="girisForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="giris">
                        
                        <div class="form-group">
                            <label for="login_email" class="required"><?php echo $dil == 'tr' ? 'E-posta' : 'Email'; ?></label>
                            <input type="email" id="login_email" name="email" 
                                   placeholder="<?php echo $dil == 'tr' ? 'ornek@email.com' : 'example@email.com'; ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="login_sifre" class="required"><?php echo $dil == 'tr' ? 'Şifre' : 'Password'; ?></label>
                            <div class="password-wrapper">
                                <input type="password" id="login_sifre" name="sifre" 
                                       placeholder="<?php echo $dil == 'tr' ? 'Şifrenizi girin' : 'Enter your password'; ?>" 
                                       required>
                                <button type="button" class="toggle-password" onclick="togglePassword('login_sifre')">👁️</button>
                            </div>
                            <div class="form-links" style="text-align: right; margin-top: 5px;">
                                <a href="auth.php?form=forgot"><?php echo $dil == 'tr' ? 'Şifremi unuttum?' : 'Forgot password?'; ?></a>
                            </div>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <?php echo $dil == 'tr' ? 'Giriş Yap' : 'Login'; ?>
                        </button>
                        
                        <div class="form-links">
                            <?php echo $dil == 'tr' ? 'Hesabınız yok mu?' : 'Don\'t have an account?'; ?>
                            <a href="#" onclick="switchTab('kayit')"><?php echo $dil == 'tr' ? 'Üye Olun' : 'Register'; ?></a>
                        </div>
                    </form>
                    
                    <!-- Üye Ol Formu -->
                    <form method="POST" action="auth.php" class="auth-form <?php echo $active_form == 'kayit' ? 'active' : ''; ?>" id="kayitForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="kayit">
                        
                        <div class="form-group">
                            <label for="ad_soyad" class="required"><?php echo $dil == 'tr' ? 'Ad Soyad' : 'Full Name'; ?></label>
                            <input type="text" id="ad_soyad" name="ad_soyad" 
                                   placeholder="<?php echo $dil == 'tr' ? 'Adınız ve soyadınız' : 'Your name and surname'; ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="required"><?php echo $dil == 'tr' ? 'E-posta' : 'Email'; ?></label>
                            <input type="email" id="email" name="email" 
                                   placeholder="<?php echo $dil == 'tr' ? 'ornek@email.com' : 'example@email.com'; ?>" 
                                   required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sifre" class="required"><?php echo $dil == 'tr' ? 'Şifre' : 'Password'; ?></label>
                                <div class="password-wrapper">
                                    <input type="password" id="sifre" name="sifre" 
                                           placeholder="<?php echo $dil == 'tr' ? 'En az 6 karakter' : 'At least 6 characters'; ?>" 
                                           minlength="6" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('sifre')">👁️</button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="sifre_tekrar" class="required"><?php echo $dil == 'tr' ? 'Şifre Tekrar' : 'Confirm Password'; ?></label>
                                <div class="password-wrapper">
                                    <input type="password" id="sifre_tekrar" name="sifre_tekrar" 
                                           placeholder="<?php echo $dil == 'tr' ? 'Şifrenizi tekrar girin' : 'Re-enter your password'; ?>" 
                                           minlength="6" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('sifre_tekrar')">👁️</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefon" class="required"><?php echo $dil == 'tr' ? 'Telefon' : 'Phone'; ?></label>
                            <input type="tel" id="telefon" name="telefon" 
                                   placeholder="<?php echo $dil == 'tr' ? '5xx xxx xx xx' : '5xx xxx xx xx'; ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="kvkk" required style="width: auto;">
                                <span style="font-size: 0.9rem; color: #666;">
                                    <?php echo $dil == 'tr' 
                                        ? 'KVKK\'yı kabul ediyorum.' 
                                        : 'I accept the privacy policy.'; ?>
                                </span>
                            </label>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <?php echo $dil == 'tr' ? 'Üye Ol' : 'Register'; ?>
                        </button>
                        
                        <div class="form-links">
                            <?php echo $dil == 'tr' ? 'Zaten hesabınız var mı?' : 'Already have an account?'; ?>
                            <a href="#" onclick="switchTab('giris')"><?php echo $dil == 'tr' ? 'Giriş Yapın' : 'Login'; ?></a>
                        </div>
                    </form>
                    
                    <!-- Şifremi Unuttum Formu -->
                    <form method="POST" action="auth.php" class="auth-form <?php echo $active_form == 'forgot' ? 'active' : ''; ?>" id="forgotForm" style="display: <?php echo $active_form == 'forgot' ? 'block' : 'none'; ?>;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="sifremi_unuttum">
                        
                        <div class="form-group">
                            <label for="forgot_email" class="required"><?php echo $dil == 'tr' ? 'E-posta' : 'Email'; ?></label>
                            <input type="email" id="forgot_email" name="email" 
                                   placeholder="<?php echo $dil == 'tr' ? 'ornek@email.com' : 'example@email.com'; ?>" 
                                   required>
                            <span class="form-hint">
                                <?php echo $dil == 'tr' 
                                    ? 'E-posta adresinize şifre sıfırlama bağlantısı göndereceğiz.' 
                                    : 'We will send a password reset link to your email address.'; ?>
                            </span>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <?php echo $dil == 'tr' ? 'Şifre Sıfırla' : 'Reset Password'; ?>
                        </button>
                        
                        <div class="form-links">
                            <a href="#" onclick="switchTab('giris')"><?php echo $dil == 'tr' ? 'Giriş Sayfasına Dön' : 'Back to Login'; ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>ÇiçekBahçesi</h3>
                    <p><?php echo $dil == 'tr' ? 'En güzel çiçekler, en taze aranjmanlar için doğru adres.' : 'The right address for the most beautiful flowers and freshest arrangements.'; ?></p>
                </div>
                <div class="footer-section">
                    <h3><?php echo $dil == 'tr' ? 'Hızlı Bağlantılar' : 'Quick Links'; ?></h3>
                    <ul>
                        <li><a href="anasayfa.php"><?php echo $dil == 'tr' ? 'Ana Sayfa' : 'Home'; ?></a></li>
                        <li><a href="urunler.php"><?php echo $dil == 'tr' ? 'Ürünlerimiz' : 'Our Products'; ?></a></li>
                        <li><a href="iletisim.php"><?php echo $dil == 'tr' ? 'İletişim' : 'Contact'; ?></a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3><?php echo $dil == 'tr' ? 'Müşteri Hizmetleri' : 'Customer Service'; ?></h3>
                    <ul>
                        <li><a href="sikca-sorulan-sorular.php"><?php echo $dil == 'tr' ? 'SSS' : 'FAQ'; ?></a></li>
                        <li><a href="teslimat-bilgileri.php"><?php echo $dil == 'tr' ? 'Teslimat Bilgileri' : 'Delivery Info'; ?></a></li>
                        <li><a href="iptal-iade.php"><?php echo $dil == 'tr' ? 'İptal & İade' : 'Cancel & Return'; ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> ÇiçekBahçesi. <?php echo $dil == 'tr' ? 'Tüm hakları saklıdır.' : 'All rights reserved.'; ?>
            </div>
        </div>
    </footer>

    <script>
    // Sayfa yüklendiğinde aktif formu göster
    document.addEventListener('DOMContentLoaded', function() {
        const activeTab = "<?php echo $active_form; ?>";
        
        // Eğer "forgot" tab'ı aktifse, özel işlem yap
        if(activeTab === 'forgot') {
            document.querySelectorAll('.auth-form').forEach(form => {
                form.classList.remove('active');
                form.style.display = 'none';
            });
            document.getElementById('forgotForm').style.display = 'block';
            document.getElementById('forgotForm').classList.add('active');
            
            // Tab başlığını güncelle
            document.getElementById('form-title').textContent = "<?php echo $dil == 'tr' ? 'Şifremi Unuttum' : 'Forgot Password'; ?>";
            
            // Tab butonlarını gizle
            document.querySelector('.auth-tabs').style.display = 'none';
        } else {
            // Normal tab işlemleri
            document.querySelectorAll('.auth-tab').forEach(tab => {
                tab.classList.remove('active');
                if(tab.getAttribute('data-tab') === activeTab) {
                    tab.classList.add('active');
                }
            });
            
            document.querySelectorAll('.auth-form').forEach(form => {
                form.classList.remove('active');
                form.style.display = 'none';
            });
            
            const activeForm = document.getElementById(activeTab + 'Form');
            if(activeForm) {
                activeForm.classList.add('active');
                activeForm.style.display = 'block';
            }
        }
        
        // Telefon formatı
        const telefonInput = document.getElementById('telefon');
        if(telefonInput) {
            telefonInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    value = value.substring(0, 10);
                    let formatted = value.substring(0, 3);
                    if (value.length > 3) formatted += ' ' + value.substring(3, 6);
                    if (value.length > 6) formatted += ' ' + value.substring(6, 8);
                    if (value.length > 8) formatted += ' ' + value.substring(8, 10);
                    e.target.value = formatted;
                }
            });
        }
    });
    
    // Tab değiştirme fonksiyonu
    function switchTab(tabName) {
        // Tüm tab'ları deaktif et
        document.querySelectorAll('.auth-tab').forEach(tab => {
            tab.classList.remove('active');
            if(tab.getAttribute('data-tab') === tabName) {
                tab.classList.add('active');
            }
        });
        
        // Tüm formları gizle
        document.querySelectorAll('.auth-form').forEach(form => {
            form.classList.remove('active');
            form.style.display = 'none';
        });
        
        // Aktif formu göster
        const activeForm = document.getElementById(tabName + 'Form');
        if(activeForm) {
            activeForm.classList.add('active');
            activeForm.style.display = 'block';
        }
        
        // Başlığı güncelle
        const titleMap = {
            'giris': '<?php echo $dil == "tr" ? "Giriş Yap" : "Login"; ?>',
            'kayit': '<?php echo $dil == "tr" ? "Üye Ol" : "Register"; ?>'
        };
        document.getElementById('form-title').textContent = titleMap[tabName];
        
        // Tab container'ı göster (forgot'tan geri dönüldüğünde)
        document.querySelector('.auth-tabs').style.display = 'flex';
    }
    
    // Tab butonlarına tıklama event'i
    document.querySelectorAll('.auth-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
    
    // Şifre göster/gizle
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        if(field) {
            const type = field.type === 'password' ? 'text' : 'password';
            field.type = type;
        }
    }
    
    // Şifremi unuttum linki için
    const forgotLink = document.querySelector('a[href="auth.php?form=forgot"]');
    if(forgotLink) {
        forgotLink.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'auth.php?form=forgot';
        });
    }
    </script>
</body>
</html>