<?php
require_once 'cicek.php';
require_once 'header.php';

// Kullanıcı giriş kontrolü
if(!kullaniciGirisKontrol()) {
    $_SESSION['auth_message'] = [
        'type' => 'warning',
        'text' => $dil == 'tr' ? 'Profilinizi görmek için giriş yapmalısınız!' : 'You must login to view your profile!'
    ];
    echo '<script>window.location.href = "anasayfa.php";</script>';
    exit;
}

// Kullanıcı bilgilerini al
$users = json_decode(file_get_contents('users.json'), true);
$current_user = null;

foreach($users as $user) {
    if($user['id'] === $_SESSION['user_id']) {
        $current_user = $user;
        break;
    }
}

// Siparişleri al
$siparisler = json_decode(file_get_contents('siparisler.json'), true);
$user_siparisler = array_filter($siparisler, function($siparis) {
    return $siparis['kullanici_id'] === $_SESSION['user_id'];
});
$user_siparisler = array_reverse(array_values($user_siparisler)); // En yeniden eskiye
?>

<style>
/* Profil Sayfası Stilleri */
.profile-page {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-header h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.profile-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
}

@media (max-width: 992px) {
    .profile-container {
        grid-template-columns: 1fr;
    }
}

/* Profil Sidebar */
.profile-sidebar {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
}

.user-avatar {
    text-align: center;
    margin-bottom: 25px;
}

.avatar-circle {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
    border-radius: 50%;
    margin: 0 auto 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: white;
    border: 5px solid white;
    box-shadow: 0 10px 20px rgba(255,107,157,0.2);
}

.user-name {
    font-size: 1.3rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.user-email {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 25px;
}

.user-stats {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    color: #666;
}

.stat-value {
    font-weight: bold;
    color: #ff6b9d;
}

.profile-menu {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.menu-item {
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    color: #666;
    font-weight: 500;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.menu-item:hover {
    background: #fff5f7;
    color: #ff6b9d;
}

.menu-item.active {
    background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
    color: white;
}

.menu-item i {
    width: 20px;
}

/* Profil Content */
.profile-content {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
}

.section-title {
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #ff8fab;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-form {
    max-width: 600px;
}

.form-group {
    margin-bottom: 25px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
}

.form-input {
    width: 100%;
    padding: 14px 20px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s;
    background: #fafafa;
}

.form-input:focus {
    outline: none;
    border-color: #ff6b9d;
    background: white;
    box-shadow: 0 0 0 4px rgba(255,107,157,0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.submit-btn {
    background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
    color: white;
    border: none;
    padding: 16px 40px;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(255,107,157,0.2);
}

/* Siparişler Tablosu */
.orders-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.orders-table th {
    background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
    color: white;
    padding: 15px;
    text-align: left;
    font-weight: 600;
}

.orders-table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.order-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-beklemede {
    background: #fff3cd;
    color: #856404;
}

.status-hazirlaniyor {
    background: #cce5ff;
    color: #004085;
}

.status-kargoda {
    background: #d1ecf1;
    color: #0c5460;
}

.status-teslim-edildi {
    background: #d4edda;
    color: #155724;
}

.status-iptal {
    background: #f8d7da;
    color: #721c24;
}

.empty-orders {
    text-align: center;
    padding: 40px;
    color: #666;
}

.empty-orders i {
    font-size: 3rem;
    margin-bottom: 20px;
    opacity: 0.5;
}
</style>

<div class="profile-page">
    <div class="page-header">
        <h1>👤 <?php echo $text_selected['profilim']; ?></h1>
        <p><?php echo $dil == 'tr' ? 'Hesap bilgilerinizi ve siparişlerinizi görüntüleyin' : 'View your account information and orders'; ?></p>
    </div>

    <div class="profile-container">
        <!-- Sidebar -->
        <div class="profile-sidebar">
            <div class="user-avatar">
                <div class="avatar-circle">
                    <?php echo substr($_SESSION['ad_soyad'], 0, 1); ?>
                </div>
                <h3 class="user-name"><?php echo htmlspecialchars($_SESSION['ad_soyad']); ?></h3>
                <p class="user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
            </div>

            <div class="user-stats">
                <div class="stat-item">
                    <span class="stat-label"><?php echo $dil == 'tr' ? 'Sipariş Sayısı' : 'Orders'; ?></span>
                    <span class="stat-value"><?php echo count($user_siparisler); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo $dil == 'tr' ? 'Puan' : 'Points'; ?></span>
                    <span class="stat-value"><?php echo $_SESSION['puan'] ?? 0; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label"><?php echo $dil == 'tr' ? 'Üyelik Tarihi' : 'Member Since'; ?></span>
                    <span class="stat-value"><?php echo date('d.m.Y', strtotime($current_user['kayit_tarihi'] ?? 'now')); ?></span>
                </div>
            </div>

            <div class="profile-menu">
                <a href="#bilgilerim" class="menu-item active">
                    <i class="fas fa-user-circle"></i>
                    <?php echo $dil == 'tr' ? 'Bilgilerim' : 'My Information'; ?>
                </a>
                <a href="#siparislerim" class="menu-item">
                    <i class="fas fa-shopping-bag"></i>
                    <?php echo $text_selected['siparisler']; ?>
                </a>
                <a href="#adreslerim" class="menu-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo $dil == 'tr' ? 'Adreslerim' : 'My Addresses'; ?>
                </a>
                <a href="#favorilerim" class="menu-item">
                    <i class="fas fa-heart"></i>
                    <?php echo $text_selected['favoriler']; ?>
                </a>
                <a href="#guvenlik" class="menu-item">
                    <i class="fas fa-shield-alt"></i>
                    <?php echo $dil == 'tr' ? 'Güvenlik' : 'Security'; ?>
                </a>
                <a href="auth.php?action=cikis" class="menu-item" style="color: #ff4757;">
                    <i class="fas fa-sign-out-alt"></i>
                    <?php echo $text_selected['cikis']; ?>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="profile-content">
            <!-- Bilgilerim -->
            <div id="bilgilerim">
                <h2 class="section-title"><i class="fas fa-user-circle"></i> <?php echo $dil == 'tr' ? 'Kişisel Bilgilerim' : 'Personal Information'; ?></h2>
                
                <form method="post" action="auth.php" class="profile-form">
                    <input type="hidden" name="action" value="profil_guncelle">
                    <input type="hidden" name="csrf_token" value="<?php echo csrfTokenOlustur(); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo $text_selected['ad_soyad']; ?></label>
                            <input type="text" name="ad_soyad" class="form-input" 
                                   value="<?php echo htmlspecialchars($_SESSION['ad_soyad'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><?php echo $text_selected['email']; ?></label>
                            <input type="email" class="form-input" 
                                   value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" disabled>
                            <small style="color: #666; font-size: 0.85rem;"><?php echo $dil == 'tr' ? 'Email adresi değiştirilemez' : 'Email address cannot be changed'; ?></small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo $text_selected['tel']; ?></label>
                            <input type="tel" name="telefon" class="form-input" 
                                   value="<?php echo htmlspecialchars($_SESSION['telefon'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php echo $dil == 'tr' ? 'Adres' : 'Address'; ?></label>
                        <textarea name="adres" class="form-input" rows="4"><?php echo htmlspecialchars($_SESSION['adres'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> <?php echo $dil == 'tr' ? 'Bilgilerimi Güncelle' : 'Update Information'; ?>
                    </button>
                </form>
            </div>

            <!-- Siparişlerim -->
            <div id="siparislerim" style="display: none;">
                <h2 class="section-title"><i class="fas fa-shopping-bag"></i> <?php echo $text_selected['siparisler']; ?></h2>
                
                <?php if(empty($user_siparisler)): ?>
                    <div class="empty-orders">
                        <i class="fas fa-box-open"></i>
                        <h3><?php echo $dil == 'tr' ? 'Henüz siparişiniz yok' : 'No orders yet'; ?></h3>
                        <p><?php echo $dil == 'tr' 
                            ? 'İlk siparişinizi vermek için ürünlerimizi inceleyin!' 
                            : 'Browse our products to place your first order!'; ?></p>
                        <a href="urunler.php?sayfa=urunler" class="submit-btn" style="display: inline-flex; margin-top: 20px;">
                            <?php echo $dil == 'tr' ? 'Alışverişe Başla' : 'Start Shopping'; ?>
                        </a>
                    </div>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th><?php echo $dil == 'tr' ? 'Sipariş No' : 'Order No'; ?></th>
                                <th><?php echo $dil == 'tr' ? 'Tarih' : 'Date'; ?></th>
                                <th><?php echo $dil == 'tr' ? 'Ürünler' : 'Products'; ?></th>
                                <th><?php echo $dil == 'tr' ? 'Toplam' : 'Total'; ?></th>
                                <th><?php echo $dil == 'tr' ? 'Durum' : 'Status'; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($user_siparisler as $siparis): ?>
                            <tr>
                                <td><?php echo $siparis['siparis_no'] ?? 'N/A'; ?></td>
                                <td><?php echo date('d.m.Y', strtotime($siparis['tarih'] ?? 'now')); ?></td>
                                <td>
                                    <?php 
                                    $urun_sayisi = count($siparis['urunler'] ?? []);
                                    echo $dil == 'tr' ? "{$urun_sayisi} ürün" : "{$urun_sayisi} items";
                                    ?>
                                </td>
                                <td><?php echo number_format($siparis['toplam'] ?? 0, 2); ?> ₺</td>
                                <td>
                                    <span class="order-status status-<?php echo $siparis['durum'] ?? 'beklemede'; ?>">
                                        <?php 
                                        $durumlar = [
                                            'tr' => [
                                                'beklemede' => 'Beklemede',
                                                'hazirlaniyor' => 'Hazırlanıyor',
                                                'kargoda' => 'Kargoda',
                                                'teslim_edildi' => 'Teslim Edildi',
                                                'iptal' => 'İptal Edildi'
                                            ],
                                            'en' => [
                                                'beklemede' => 'Pending',
                                                'hazirlaniyor' => 'Preparing',
                                                'kargoda' => 'Shipped',
                                                'teslim_edildi' => 'Delivered',
                                                'iptal' => 'Cancelled'
                                            ]
                                        ];
                                        echo $durumlar[$dil][$siparis['durum'] ?? 'beklemede'] ?? 'Beklemede';
                                        ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Profil menü geçişleri
document.querySelectorAll('.profile-menu a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Tüm menü öğelerinden active class'ını kaldır
        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Tıklanana active class'ını ekle
        this.classList.add('active');
        
        // Tüm içerikleri gizle
        document.querySelectorAll('.profile-content > div').forEach(content => {
            content.style.display = 'none';
        });
        
        // İlgili içeriği göster
        const targetId = this.getAttribute('href').substring(1);
        document.getElementById(targetId).style.display = 'block';
    });
});

// Telefon formatı
document.querySelector('input[name="telefon"]')?.addEventListener('input', function(e) {
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
</script>

<?php require_once 'footer.php'; ?>