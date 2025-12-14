<?php
// profil.php - HATA DÜZELTMELİ
require_once 'header.php';

// Giriş kontrolü (header.php'de yapıldı ama yine de kontrol edelim)
if (!$is_logged_in) {
    // Header.php'de yönlendirme yapılıyor, buraya gelmemeli
    exit;
}

// Kullanıcı bilgilerini al
$user_id = $_SESSION['user_id'];
$users = json_decode(file_get_contents('users.json'), true);
$current_user = null;

foreach ($users as $user) {
    if ($user['id'] == $user_id) {
        $current_user = $user;
        break;
    }
}

// Profil güncelleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'guncelle') {
        $ad_soyad = trim($_POST['ad_soyad'] ?? '');
        $telefon = trim($_POST['telefon'] ?? '');
        $adres = trim($_POST['adres'] ?? '');
        $cinsiyet = $_POST['cinsiyet'] ?? '';
        $dogum_tarihi = $_POST['dogum_tarihi'] ?? '';
        $bulten = isset($_POST['bulten']) ? true : false;
        
        if (empty($ad_soyad)) {
            $error = "Ad Soyad gereklidir!";
        } else {
            // Kullanıcıyı güncelle
            foreach ($users as &$user) {
                if ($user['id'] == $user_id) {
                    $user['ad_soyad'] = $ad_soyad;
                    $user['telefon'] = $telefon;
                    $user['adres'] = $adres;
                    $user['cinsiyet'] = $cinsiyet;
                    $user['dogum_tarihi'] = $dogum_tarihi;
                    $user['bulten'] = $bulten;
                    
                    // Session'u da güncelle
                    $_SESSION['ad_soyad'] = $ad_soyad;
                    break;
                }
            }
            
            // JSON dosyasına kaydet
            file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $success = "Profil bilgileriniz başarıyla güncellendi!";
            $current_user = null; // Yeniden yükle
        }
    }
    
    // Şifre değiştirme
    if ($_POST['action'] == 'sifre_degistir') {
        $eski_sifre = $_POST['eski_sifre'] ?? '';
        $yeni_sifre = $_POST['yeni_sifre'] ?? '';
        $yeni_sifre_tekrar = $_POST['yeni_sifre_tekrar'] ?? '';
        
        if (empty($eski_sifre) || empty($yeni_sifre) || empty($yeni_sifre_tekrar)) {
            $sifre_error = "Tüm alanları doldurun!";
        } elseif ($yeni_sifre !== $yeni_sifre_tekrar) {
            $sifre_error = "Yeni şifreler uyuşmuyor!";
        } elseif (strlen($yeni_sifre) < 6) {
            $sifre_error = "Yeni şifre en az 6 karakter olmalıdır!";
        } else {
            // Eski şifreyi kontrol et
            $user_found = false;
            foreach ($users as &$user) {
                if ($user['id'] == $user_id) {
                    $user_found = true;
                    
                    // Şifre kontrolü
                    if (password_verify($eski_sifre, $user['sifre']) || $eski_sifre === $user['sifre']) {
                        // Yeni şifreyi hashle
                        $user['sifre'] = password_hash($yeni_sifre, PASSWORD_DEFAULT);
                        file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        $sifre_success = "Şifreniz başarıyla değiştirildi!";
                    } else {
                        $sifre_error = "Eski şifre hatalı!";
                    }
                    break;
                }
            }
            
            if (!$user_found) {
                $sifre_error = "Kullanıcı bulunamadı!";
            }
        }
    }
}

// Kullanıcı bilgilerini yeniden yükle
if ($current_user === null) {
    $users = json_decode(file_get_contents('users.json'), true);
    foreach ($users as $user) {
        if ($user['id'] == $user_id) {
            $current_user = $user;
            break;
        }
    }
}
?>

<div class="container">
    <h1 style="color: #ff6b9d; margin-bottom: 20px;"><i class="fas fa-user-circle"></i> Profilim</h1>
    
    <?php if (isset($error)): ?>
        <div class="message error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="message success">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">
        <!-- SOL TARAF: PROFİL BİLGİLERİ -->
        <div>
            <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);">
                <h2 style="color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ffeef2;">
                    <i class="fas fa-user-edit"></i> Profil Bilgileri
                </h2>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="guncelle">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">Ad Soyad</label>
                        <input type="text" name="ad_soyad" value="<?php echo htmlspecialchars($current_user['ad_soyad'] ?? ''); ?>" 
                               style="width: 100%; padding: 10px; border: 2px solid #ffeef2; border-radius: 8px;" required>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">E-posta</label>
                        <input type="email" value="<?php echo htmlspecialchars($current_user['email'] ?? ''); ?>" 
                               style="width: 100%; padding: 10px; border: 2px solid #ffeef2; border-radius: 8px; background: #f5f5f5;" disabled>
                        <small style="color: #888;">E-posta adresi değiştirilemez</small>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">Telefon</label>
                        <input type="tel" name="telefon" value="<?php echo htmlspecialchars($current_user['telefon'] ?? ''); ?>" 
                               style="width: 100%; padding: 10px; border: 2px solid #ffeef2; border-radius: 8px;">
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">Adres</label>
                        <textarea name="adres" rows="3" style="width: 100%; padding: 10px; border: 2px solid #ffeef2; border-radius: 8px; resize: vertical;"><?php echo htmlspecialchars($current_user['adres'] ?? ''); ?></textarea>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">Cinsiyet</label>
                        <select name="cinsiyet" style="width: 100%; padding: 10px; border: 2px solid #ffeef2; border-radius: 8px;">
                            <option value="">Seçiniz</option>
                            <option value="erkek" <?php echo ($current_user['cinsiyet'] ?? '') == 'erkek' ? 'selected' : ''; ?>>Erkek</option>
                            <option value="kadin" <?php echo ($current_user['cinsiyet'] ?? '') == 'kadin' ? 'selected' : ''; ?>>Kadın</option>
                            <option value="belirtmek_istemiyorum" <?php echo ($current_user['cinsiyet'] ?? '') == 'belirtmek_istemiyorum' ? 'selected' : ''; ?>>Belirtmek İstemiyorum</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">Doğum Tarihi</label>
                        <input type="date" name="dogum_tarihi" value="<?php echo htmlspecialchars($current_user['dogum_tarihi'] ?? ''); ?>" 
                               style="width: 100%; padding: 10px; border: 2px solid #ffeef2; border-radius: 8px;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="bulten" <?php echo ($current_user['bulten'] ?? false) ? 'checked' : ''; ?>>
                            <span>Kampanya ve indirimler hakkında e-posta almak istiyorum</span>
                        </label>
                    </div>
                    
                    <button type="submit" style="
                        width: 100%;
                        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
                        color: white;
                        border: none;
                        padding: 12px;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s;
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(255, 107, 157, 0.3)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <i class="fas fa-save"></i> Bilgileri Güncelle
                    </button>
                </form>
            </div>
        </div>
        
        <!-- SAĞ TARAF: ŞİFRE DEĞİŞTİRME VE DİĞER BİLGİLER -->
        <div>
            <!-- ŞİFRE DEĞİŞTİRME -->
            <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1); margin-bottom: 20px;">
                <h2 style="color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ffeef2;">
                    <i class="fas fa-key"></i> Şifre Değiştir
                </h2>
                
                <?php if (isset($sifre_error)): ?>
                    <div class="message error" style="margin-bottom: 15px;">
                        <?php echo $sifre_error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($sifre_success)): ?>
                    <div class="message success" style="margin-bottom: 15px;">
                        <?php echo $sifre_success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="sifre_degistir">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">Eski Şifre</label>
                        <input type="password" name="eski_sifre" style="width: 100%; padding: 10px; border: 2px solid #ffeef2; border-radius: 8px;" required>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">Yeni Şifre</label>
                        <input type="password" name="yeni_sifre" style="width: 100%; padding: 10px; border: 2px solid #ffeef2; border-radius: 8px;" required>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">Yeni Şifre (Tekrar)</label>
                        <input type="password" name="yeni_sifre_tekrar" style="width: 100%; padding: 10px; border: 2px solid #ffeef2; border-radius: 8px;" required>
                    </div>
                    
                    <button type="submit" style="
                        width: 100%;
                        background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
                        color: white;
                        border: none;
                        padding: 12px;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s;
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(76, 175, 80, 0.3)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <i class="fas fa-sync-alt"></i> Şifreyi Değiştir
                    </button>
                </form>
            </div>
            
            <!-- HESAP BİLGİLERİ -->
            <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);">
                <h2 style="color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ffeef2;">
                    <i class="fas fa-info-circle"></i> Hesap Bilgileri
                </h2>
                
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-weight: 600; color: #555;">Üyelik Durumu:</span>
                        <span style="background: #4CAF50; color: white; padding: 3px 10px; border-radius: 12px; font-size: 0.9rem;">
                            Aktif
                        </span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-weight: 600; color: #555;">Üyelik Tarihi:</span>
                        <span><?php echo date('d.m.Y', strtotime($current_user['kayit_tarihi'] ?? '2024-01-01')); ?></span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-weight: 600; color: #555;">Puanınız:</span>
                        <span style="color: #ff6b9d; font-weight: 700;"><?php echo $current_user['puan'] ?? 0; ?> puan</span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-weight: 600; color: #555;">Favori Ürünler:</span>
                        <span><?php echo count($_SESSION['favoriler'] ?? []); ?> ürün</span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: 600; color: #555;">Sepetiniz:</span>
                        <span><?php echo count($_SESSION['sepet'] ?? []); ?> ürün</span>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #ffeef2;">
                    <a href="siparisler.php" style="
                        display: block;
                        text-align: center;
                        background: #2196f3;
                        color: white;
                        padding: 10px;
                        border-radius: 8px;
                        text-decoration: none;
                        font-weight: 600;
                        margin-bottom: 10px;
                        transition: all 0.3s;
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 10px rgba(33, 150, 243, 0.3)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <i class="fas fa-receipt"></i> Siparişlerim
                    </a>
                    
                    <a href="kuponlar.php" style="
                        display: block;
                        text-align: center;
                        background: #ff9800;
                        color: white;
                        padding: 10px;
                        border-radius: 8px;
                        text-decoration: none;
                        font-weight: 600;
                        margin-bottom: 10px;
                        transition: all 0.3s;
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 10px rgba(255, 152, 0, 0.3)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <i class="fas fa-tags"></i> Kuponlarım
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>