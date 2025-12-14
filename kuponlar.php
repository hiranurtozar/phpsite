<?php
require_once 'cicek.php';
require_once 'header.php';

/* -------------------------
   Güvenli varsayılanlar
-------------------------- */
$text = $text ?? [];
$dil  = $dil ?? 'tr';

/* Metinler yoksa hata vermesin */
$text['kuponlarim'] = $text['kuponlarim'] ?? ($dil == 'tr' ? 'Kuponlarım' : 'My Coupons');
$text['giris']      = $text['giris']      ?? ($dil == 'tr' ? 'Giriş Yap' : 'Login');
$text['son_kullanma'] = $text['son_kullanma'] ?? ($dil == 'tr' ? 'Son Kullanma' : 'Expiry Date');
$text['kullan']     = $text['kullan']     ?? ($dil == 'tr' ? 'Kullan' : 'Use');
$text['kullanildi'] = $text['kullanildi'] ?? ($dil == 'tr' ? 'Kullanıldı' : 'Used');

/* Session kontrolü */
$giris = isset($_SESSION['user_id']) || isset($_SESSION['admin_logged_in']);
$kuponlar = $_SESSION['kullanici_kuponlari'] ?? [];
?>

<?php if(!$giris): ?>
    <div class="hosgeldin" style="text-align:center;">
        <h2>🎫 <?= $text['kuponlarim']; ?></h2>

        <div style="background: rgba(255, 95, 162, 0.1); padding: 20px; border-radius: var(--radius); margin: 20px 0;">
            🔒 <?= ($dil == 'tr' ? 'Kuponlarınızı görmek için giriş yapmalısınız' : 'You must login to view your coupons'); ?>
        </div>

        <button onclick="acModal()" class="odeme-btn"
                style="width:auto; display:inline-block; padding:12px 30px;">
            <?= $text['giris']; ?>
        </button>
    </div>

<?php else: ?>
    <div class="kuponlar-container">
        <h1 style="margin-bottom: 30px;">🎫 <?= $text['kuponlarim']; ?></h1>

        <?php if(empty($kuponlar)): ?>
            <div class="hosgeldin" style="text-align:center;">
                <h3>🎁 <?= ($dil == 'tr' ? 'Henüz kuponunuz yok' : 'You have no coupons yet'); ?></h3>

                <p style="margin:20px 0;">
                    <?= ($dil == 'tr'
                        ? 'İlk alışverişinizde otomatik olarak kupon kazanacaksınız!'
                        : 'You will automatically earn coupons on your first purchase!'); ?>
                </p>

                <a href="?sayfa=anasayfa" class="odeme-btn"
                   style="width:auto; display:inline-block; padding:12px 30px;">
                    <?= ($dil == 'tr' ? 'Alışverişe Başla' : 'Start Shopping'); ?>
                </a>
            </div>

        <?php else: ?>
            <div class="kupon-grid">
                <?php foreach($kuponlar as $kupon): 
                    $tarih_farki = strtotime($kupon['son_kullanma']) - time();
                    $kalan_gun = ceil($tarih_farki / 86400);
                ?>
                    <div class="kupon-kart">
                        <div class="kupon-header">
                            <div class="kupon-kodu"><?= htmlspecialchars($kupon['kod']); ?></div>
                            <div class="kupon-indirim">%<?= (int)$kupon['indirim']; ?></div>
                        </div>

                        <p class="kupon-aciklama"><?= htmlspecialchars($kupon['aciklama']); ?></p>

                        <div class="kupon-detay">
                            <div>
                                <div style="font-weight:600; margin-bottom:5px;">
                                    <?= $text['son_kullanma']; ?>
                                </div>
                                <div style="color: <?= ($kalan_gun < 7 ? 'var(--error-color)' : 'var(--success-color)'); ?>;">
                                    <?= date('d.m.Y', strtotime($kupon['son_kullanma'])); ?>
                                    (<?= $dil == 'tr' ? $kalan_gun.' gün kaldı' : $kalan_gun.' days left'; ?>)
                                </div>
                            </div>

                            <div>
                                <div style="font-weight:600; margin-bottom:5px;">
                                    <?= ($dil == 'tr' ? 'Durum' : 'Status'); ?>
                                </div>
                                <div class="kupon-durum <?= $kupon['durum'] == 'aktif' ? 'durum-aktif' : 'durum-kullanildi'; ?>">
                                    <?= $kupon['durum'] == 'aktif' ? $text['kullan'] : $text['kullanildi']; ?>
                                </div>
                            </div>
                        </div>

                        <?php if($kupon['durum'] == 'aktif'): ?>
                            <button class="kupon-kullan-btn"
                                    onclick="window.location.href='?kupon_kullan=<?= urlencode($kupon['kod']); ?>'">
                                <?= $text['kullan']; ?> 🎯
                            </button>
                        <?php else: ?>
                            <button class="kupon-kullan-btn" disabled>
                                <?= $text['kullanildi']; ?> ✅
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
