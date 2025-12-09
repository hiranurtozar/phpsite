<?php
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
    
    // Gerekli alanlar
    $required = $is_register 
        ? ['ad_soyad', 'email', 'sifre', 'sifre_tekrar', 'telefon']
        : ['email', 'sifre'];
    
    $errors = [];
    
    foreach($required as $field) {
        if(empty(trim($data[$field] ?? ''))) {
            $field_names = [
                'tr' => ['ad_soyad' => 'Ad Soyad', 'email' => 'E-posta', 'sifre' => 'Şifre', 'sifre_tekrar' => 'Şifre Tekrar', 'telefon' => 'Telefon'],
                'en' => ['ad_soyad' => 'Full Name', 'email' => 'Email', 'sifre' => 'Password', 'sifre_tekrar' => 'Confirm Password', 'telefon' => 'Phone']
            ];
            $errors[] = ($dil == 'tr' ? 'Bu alan gereklidir: ' : 'This field is required: ') . $field_names[$dil][$field];
        }
    }
    
    if($is_register) {
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
        
        // Telefon formatı (basit kontrol)
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
    }
    
    return $errors;
}

// Kullanıcı kaydı
if(isset($_POST['action']) && $_POST['action'] == 'kayit') {
    if(!validateCsrfToken()) {
        echo '<script>window.location.href = "anasayfa.php";</script>';
        exit;
    }
    
    $errors = validateUserData($_POST, true);
    
    if(!empty($errors)) {
        setMessage('error', implode('<br>', $errors));
        echo '<script>window.location.href = "anasayfa.php";</script>';
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
        'kayit_tarihi' => date('Y-m-d H:i:s'),
        'son_giris' => date('Y-m-d H:i:s'),
        'aktif' => true,
        'avatar' => 'default.png',
        'puan' => 100 // Hoş geldin puanı
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
    echo '<script>window.location.href = "anasayfa.php";</script>';
    exit;
}

// Kullanıcı girişi
if(isset($_POST['action']) && $_POST['action'] == 'giris') {
    if(!validateCsrfToken()) {
        echo '<script>window.location.href = "anasayfa.php";</script>';
        exit;
    }
    
    $errors = validateUserData($_POST, false);
    
    if(!empty($errors)) {
        setMessage('error', implode('<br>', $errors));
        echo '<script>window.location.href = "anasayfa.php";</script>';
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
                    echo '<script>window.location.href = "anasayfa.php";</script>';
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
                echo '<script>window.location.href = "anasayfa.php";</script>';
                exit;
            }
        }
    }
    
    if($user_found) {
        setMessage('error', $dil == 'tr' ? 'Şifre hatalı!' : 'Invalid password!');
    } else {
        setMessage('error', $dil == 'tr' ? 'Bu email adresi ile kayıtlı kullanıcı bulunamadı!' : 'No user found with this email address!');
    }
    
    echo '<script>window.location.href = "anasayfa.php";</script>';
    exit;
}

// Şifremi unuttum
if(isset($_POST['action']) && $_POST['action'] == 'sifremi_unuttum') {
    // Basit şifre sıfırlama
    $email = strtolower(trim($_POST['email'] ?? ''));
    
    if(empty($email)) {
        setMessage('error', $dil == 'tr' ? 'Email adresinizi girin!' : 'Please enter your email address!');
        echo '<script>window.location.href = "anasayfa.php";</script>';
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
            
            // TODO: Burada email gönderme işlemi yapılacak
            // Şimdilik mesaj olarak gösterelim
            
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
    
    echo '<script>window.location.href = "anasayfa.php";</script>';
    exit;
}

// Çıkış yap
if(isset($_GET['action']) && $_GET['action'] == 'cikis') {
    session_destroy();
    setMessage('success', $dil == 'tr' ? 'Başarıyla çıkış yaptınız!' : 'Logout successful!');
    echo '<script>window.location.href = "anasayfa.php";</script>';
    exit;
}

// Kullanıcı profil güncelleme
if(isset($_POST['action']) && $_POST['action'] == 'profil_guncelle') {
    if(!kullaniciGirisKontrol() || !validateCsrfToken()) {
        echo '<script>window.location.href = "anasayfa.php";</script>';
        exit;
    }
    
    $users = json_decode(file_get_contents($users_file), true);
    $updated = false;
    
    foreach($users as &$user) {
        if($user['id'] === $_SESSION['user_id']) {
            $user['ad_soyad'] = htmlspecialchars(trim($_POST['ad_soyad'] ?? $user['ad_soyad']));
            $user['telefon'] = htmlspecialchars(trim($_POST['telefon'] ?? $user['telefon']));
            $user['adres'] = htmlspecialchars(trim($_POST['adres'] ?? $user['adres']));
            
            // Şifre değişikliği
            if(!empty($_POST['yeni_sifre']) && !empty($_POST['mevcut_sifre'])) {
                if(password_verify($_POST['mevcut_sifre'], $user['sifre'])) {
                    if($_POST['yeni_sifre'] === $_POST['yeni_sifre_tekrar']) {
                        $user['sifre'] = password_hash($_POST['yeni_sifre'], PASSWORD_DEFAULT);
                    } else {
                        setMessage('error', $dil == 'tr' ? 'Yeni şifreler eşleşmiyor!' : 'New passwords do not match!');
                        echo '<script>window.location.href = "profil.php";</script>';
                        exit;
                    }
                } else {
                    setMessage('error', $dil == 'tr' ? 'Mevcut şifre hatalı!' : 'Current password is incorrect!');
                    echo '<script>window.location.href = "profil.php";</script>';
                    exit;
                }
            }
            
            // Session'ı güncelle
            $_SESSION['ad_soyad'] = $user['ad_soyad'];
            $_SESSION['telefon'] = $user['telefon'];
            $_SESSION['adres'] = $user['adres'];
            
            $updated = true;
            break;
        }
    }
    
    if($updated) {
        file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        setMessage('success', $dil == 'tr' ? 'Profil bilgileriniz güncellendi!' : 'Your profile has been updated!');
    }
    
    echo '<script>window.location.href = "profil.php";</script>';
    exit;
}

// Eğer hiçbir işlem yoksa anasayfaya yönlendir
echo '<script>window.location.href = "anasayfa.php";</script>';
exit;
?>