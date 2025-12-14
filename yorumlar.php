<?php
// yorumlar.php - Yorum ekleme ve görüntüleme sayfası
ob_start(); // Output buffering başlat
require_once 'header.php';

// Dosya kontrolü
$yorumlar_dosya = 'yorumlar.json';

// Yorumları getir fonksiyonu
function getYorumlar($limit = 100) {
    global $yorumlar_dosya;
    
    if (!file_exists($yorumlar_dosya)) {
        file_put_contents($yorumlar_dosya, json_encode([]));
        return [];
    }
    
    $yorumlar = json_decode(file_get_contents($yorumlar_dosya), true);
    if (!is_array($yorumlar)) {
        return [];
    }
    
    // Tarihe göre sırala (yeni en üstte)
    usort($yorumlar, function($a, $b) {
        return strtotime($b['tarih']) - strtotime($a['tarih']);
    });
    
    return $limit > 0 ? array_slice($yorumlar, 0, $limit) : $yorumlar;
}

// Ortalama puanı hesapla
function getOrtalamaPuan() {
    $yorumlar = getYorumlar(0);
    if (empty($yorumlar)) return 0;
    
    $toplam_puan = 0;
    foreach ($yorumlar as $yorum) {
        $toplam_puan += $yorum['puan'];
    }
    
    return round($toplam_puan / count($yorumlar), 1);
}

// Yorum ekleme
if (isset($_POST['yorum_ekle']) && $is_logged_in) {
    $yorum_metni = trim($_POST['yorum']);
    $puan = intval($_POST['puan']);
    
    if (empty($yorum_metni) || $puan < 1 || $puan > 5) {
        $_SESSION['message'] = 'Lütfen geçerli bir yorum ve puan girin!';
        $_SESSION['message_type'] = 'error';
    } else {
        $yorum = [
            'id' => uniqid(),
            'kullanici_id' => $user_id,
            'kullanici_adi' => htmlspecialchars($_SESSION['ad_soyad']),
            'yorum' => htmlspecialchars($yorum_metni),
            'puan' => $puan,
            'tarih' => date('Y-m-d H:i:s'),
            'urun_id' => null
        ];
        
        // Mevcut yorumları oku
        $mevcut_yorumlar = getYorumlar(0);
        $mevcut_yorumlar[] = $yorum;
        
        // Dosyaya kaydet
        file_put_contents($yorumlar_dosya, json_encode($mevcut_yorumlar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $_SESSION['message'] = 'Yorumunuz başarıyla eklendi! Teşekkür ederiz.';
        $_SESSION['message_type'] = 'success';
    }
    
    header('Location: yorumlar.php');
    ob_end_flush();
    exit();
}

// Yorum silme
if (isset($_GET['yorum_sil']) && $is_logged_in) {
    $silinecek_id = $_GET['yorum_sil'];
    
    $yorumlar = getYorumlar(0);
    $yeni_yorumlar = [];
    $silindi = false;
    
    foreach ($yorumlar as $yorum) {
        if ($yorum['id'] == $silinecek_id) {
            // Admin tüm yorumları, kullanıcı sadece kendi yorumunu silebilir
            if ($is_admin || $yorum['kullanici_id'] == $user_id) {
                $silindi = true;
                continue; // Bu yorumu atla
            }
        }
        $yeni_yorumlar[] = $yorum;
    }
    
    if ($silindi) {
        file_put_contents($yorumlar_dosya, json_encode($yeni_yorumlar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $_SESSION['message'] = 'Yorum silindi!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Yorum silinemedi!';
        $_SESSION['message_type'] = 'error';
    }
    
    header('Location: yorumlar.php');
    ob_end_flush();
    exit();
}

// Tüm yorumları al
$tumYorumlar = getYorumlar(0);
$toplamYorum = count($tumYorumlar);
$ortalamaPuan = getOrtalamaPuan();

// Sayfalama
$sayfa = isset($_GET['sayfa']) ? max(1, intval($_GET['sayfa'])) : 1;
$sayfa_basina = 10;
$toplam_sayfa = ceil($toplamYorum / $sayfa_basina);
$baslangic = ($sayfa - 1) * $sayfa_basina;
$gosterilecek_yorumlar = array_slice($tumYorumlar, $baslangic, $sayfa_basina);
?>

<!DOCTYPE html>
<html lang="<?php echo $dil; ?>" data-theme="<?php echo $tema; ?>">
<head>
    <title>ÇiçekBahçesi - <?php echo $dil == 'tr' ? 'Müşteri Yorumları' : 'Customer Reviews'; ?></title>
    <style>
        /* YORUMLAR SAYFASI STİLLERİ */
        .yorumlar-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .yorumlar-header {
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 107, 157, 0.1);
        }
        
        .yorumlar-header h1 {
            color: #ff6b9d;
            font-size: 2.8rem;
            margin-bottom: 15px;
            font-family: 'Dancing Script', cursive;
        }
        
        .header-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .stat-box {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            text-align: center;
            min-width: 150px;
            box-shadow: 0 5px 15px rgba(255, 107, 157, 0.1);
            transition: all 0.3s;
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(255, 107, 157, 0.2);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ff6b9d;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        /* YORUM EKLEME FORMU */
        .yorum-ekle-form {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(255, 107, 157, 0.1);
            border: 1px solid rgba(255, 107, 157, 0.1);
        }
        
        .form-title {
            color: #333;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 5px;
            margin-bottom: 20px;
        }
        
        .star-rating input {
            display: none;
        }
        
        .star-rating label {
            font-size: 40px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #FFD700;
        }
        
        .yorum-textarea {
            width: 100%;
            padding: 20px;
            border: 2px solid #ffeef2;
            border-radius: 15px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            resize: vertical;
            min-height: 150px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .yorum-textarea:focus {
            outline: none;
            border-color: #ff6b9d;
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }
        
        .yorum-submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .yorum-submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 107, 157, 0.3);
        }
        
        /* YORUM LİSTESİ */
        .yorumlar-listesi {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .yorum-karti {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 107, 157, 0.1);
            border: 1px solid rgba(255, 107, 157, 0.1);
            transition: all 0.3s;
            position: relative;
        }
        
        .yorum-karti:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(255, 107, 157, 0.15);
        }
        
        .yorum-ust {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .yorumcu-bilgi {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .yorumcu-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(255, 107, 157, 0.3);
        }
        
        .yorumcu-detay h3 {
            color: #333;
            margin-bottom: 5px;
            font-size: 1.2rem;
        }
        
        .yorum-tarihi {
            color: #888;
            font-size: 0.9rem;
        }
        
        .yorum-puan {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .yildiz {
            color: #FFD700;
            font-size: 24px;
        }
        
        .yorum-metni {
            color: #555;
            line-height: 1.7;
            font-size: 1.1rem;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            border-left: 4px solid #ff6b9d;
            margin-bottom: 15px;
        }
        
        .yorum-aksiyon {
            text-align: right;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .sil-btn {
            color: #f44336;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sil-btn:hover {
            background: #ffebee;
        }
        
        /* SAYFALAMA */
        .sayfalama {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 50px;
            margin-bottom: 30px;
        }
        
        .sayfa-btn {
            padding: 12px 20px;
            background: white;
            color: #ff6b9d;
            border: 2px solid #ff6b9d;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .sayfa-btn:hover {
            background: #ff6b9d;
            color: white;
            transform: translateY(-2px);
        }
        
        .sayfa-btn.active {
            background: #ff6b9d;
            color: white;
        }
        
        /* BOŞ YORUM MESAJI */
        .bos-yorum {
            text-align: center;
            padding: 60px 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 107, 157, 0.1);
            margin: 30px 0;
        }
        
        .bos-yorum i {
            font-size: 80px;
            color: #ffeef2;
            margin-bottom: 20px;
        }
        
        .bos-yorum h3 {
            color: #ff6b9d;
            margin-bottom: 15px;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .yorumlar-header {
                padding: 30px 20px;
            }
            
            .yorumlar-header h1 {
                font-size: 2.2rem;
            }
            
            .header-stats {
                gap: 15px;
            }
            
            .stat-box {
                min-width: 120px;
                padding: 15px 20px;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .yorum-ust {
                flex-direction: column;
                gap: 15px;
            }
            
            .yorum-puan {
                align-self: flex-start;
            }
            
            .yorumcu-avatar {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .yorum-ekle-form {
                padding: 25px;
            }
            
            .star-rating label {
                font-size: 30px;
            }
        }
        
        @media (max-width: 480px) {
            .yorumlar-container {
                padding: 10px;
            }
            
            .stat-box {
                min-width: 100px;
                padding: 12px 15px;
            }
            
            .sayfalama {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Header'ı dahil et
    $header_included = true;
    ?>
    
    <div class="container">
        <div class="yorumlar-container">
            <!-- BAŞLIK VE İSTATİSTİKLER -->
            <div class="yorumlar-header">
                <h1>
                    <i class="fas fa-comments"></i>
                    <?php echo $dil == 'tr' ? 'Müşteri Yorumları' : 'Customer Reviews'; ?>
                </h1>
                <p style="color: #666; max-width: 600px; margin: 0 auto; font-size: 1.1rem;">
                    <?php echo $dil == 'tr' 
                        ? 'ÇiçekBahçesi deneyimlerinizi paylaşın. Görüşleriniz bizim için çok değerli!' 
                        : 'Share your FlowerGarden experiences. Your opinions are very valuable to us!'; 
                    ?>
                </p>
                
                <div class="header-stats">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo $toplamYorum; ?></div>
                        <div class="stat-label"><?php echo $dil == 'tr' ? 'Toplam Yorum' : 'Total Reviews'; ?></div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-number"><?php echo $ortalamaPuan; ?></div>
                        <div class="stat-label"><?php echo $dil == 'tr' ? 'Ortalama Puan' : 'Average Rating'; ?></div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-number">5.0</div>
                        <div class="stat-label"><?php echo $dil == 'tr' ? 'Memnuniyet' : 'Satisfaction'; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- MESAJ GÖSTER -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message <?php echo $_SESSION['message_type']; ?>" style="margin-bottom: 30px;">
                    <i class="fas fa-<?php echo $_SESSION['message_type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $_SESSION['message']; ?>
                    <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                </div>
            <?php endif; ?>
            
            <!-- YORUM EKLEME FORMU (GİRİŞ YAPMIŞ KULLANICILAR İÇİN) -->
            <?php if($is_logged_in): ?>
                <div class="yorum-ekle-form">
                    <h2 class="form-title">
                        <i class="fas fa-pen"></i>
                        <?php echo $dil == 'tr' ? 'Deneyiminizi Paylaşın' : 'Share Your Experience'; ?>
                    </h2>
                    
                    <form method="POST" action="yorumlar.php">
                        <!-- YILDIZ PUANLAMA -->
                        <div class="star-rating" id="starRating">
                            <input type="radio" id="star5" name="puan" value="5" required>
                            <label for="star5" title="5 yıldız">★</label>
                            
                            <input type="radio" id="star4" name="puan" value="4">
                            <label for="star4" title="4 yıldız">★</label>
                            
                            <input type="radio" id="star3" name="puan" value="3">
                            <label for="star3" title="3 yıldız">★</label>
                            
                            <input type="radio" id="star2" name="puan" value="2">
                            <label for="star2" title="2 yıldız">★</label>
                            
                            <input type="radio" id="star1" name="puan" value="1">
                            <label for="star1" title="1 yıldız">★</label>
                        </div>
                        
                        <!-- YORUM METNİ -->
                        <textarea name="yorum" class="yorum-textarea" 
                                  placeholder="<?php echo $dil == 'tr' ? 'Deneyiminizi detaylı olarak yazın... (En az 10 karakter)' : 'Write your experience in detail... (At least 10 characters)'; ?>" 
                                  minlength="10" required></textarea>
                        
                        <!-- GÖNDER BUTONU -->
                        <button type="submit" name="yorum_ekle" class="yorum-submit-btn">
                            <i class="fas fa-paper-plane"></i>
                            <?php echo $dil == 'tr' ? 'Yorumu Gönder' : 'Submit Review'; ?>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- GİRİŞ YAPMAMIŞ KULLANICILAR İÇİN -->
                <div style="background: white; border-radius: 20px; padding: 40px; text-align: center; margin-bottom: 40px; box-shadow: 0 10px 30px rgba(255, 107, 157, 0.1);">
                    <div style="font-size: 60px; color: #ff6b9d; margin-bottom: 20px;">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <h3 style="color: #333; margin-bottom: 15px;">
                        <?php echo $dil == 'tr' ? 'Yorum Yapmak İçin Giriş Yapın' : 'Login to Leave a Review'; ?>
                    </h3>
                    <p style="color: #666; margin-bottom: 25px; max-width: 500px; margin-left: auto; margin-right: auto;">
                        <?php echo $dil == 'tr' 
                            ? 'Deneyiminizi paylaşmak için lütfen giriş yapın veya hesap oluşturun.' 
                            : 'Please login or create an account to share your experience.'; 
                        ?>
                    </p>
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="auth.php" style="
                            display: inline-block;
                            padding: 15px 40px;
                            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
                            color: white;
                            border-radius: 12px;
                            text-decoration: none;
                            font-weight: 600;
                            transition: all 0.3s;
                        " onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 25px rgba(255, 107, 157, 0.3)'"
                           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="fas fa-sign-in-alt"></i>
                            <?php echo $text_selected['giris']; ?>
                        </a>
                        
                        <a href="auth.php?form=kayit" style="
                            display: inline-block;
                            padding: 15px 40px;
                            background: white;
                            color: #ff6b9d;
                            border: 2px solid #ff6b9d;
                            border-radius: 12px;
                            text-decoration: none;
                            font-weight: 600;
                            transition: all 0.3s;
                        " onmouseover="this.style.background='#ff6b9d'; this.style.color='white'"
                           onmouseout="this.style.background='white'; this.style.color='#ff6b9d'">
                            <i class="fas fa-user-plus"></i>
                            <?php echo $text_selected['uye_ol']; ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- YORUM LİSTESİ -->
            <h2 style="color: #333; margin: 40px 0 30px 0; font-size: 1.8rem; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-list"></i>
                <?php echo $dil == 'tr' ? 'Tüm Yorumlar' : 'All Reviews'; ?>
                <span style="background: #ffeef2; color: #ff6b9d; padding: 5px 15px; border-radius: 20px; font-size: 1rem;">
                    <?php echo $toplamYorum; ?> <?php echo $dil == 'tr' ? 'yorum' : 'reviews'; ?>
                </span>
            </h2>
            
            <?php if (empty($gosterilecek_yorumlar)): ?>
                <!-- BOŞ YORUM MESAJI -->
                <div class="bos-yorum">
                    <i class="fas fa-comment-slash"></i>
                    <h3><?php echo $dil == 'tr' ? 'Henüz yorum yok' : 'No reviews yet'; ?></h3>
                    <p style="color: #666; margin-bottom: 30px;">
                        <?php echo $dil == 'tr' 
                            ? 'İlk yorumu siz yaparak başlayabilirsiniz!' 
                            : 'You can start by making the first review!'; 
                        ?>
                    </p>
                    <?php if(!$is_logged_in): ?>
                        <a href="auth.php" style="
                            display: inline-block;
                            padding: 12px 30px;
                            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
                            color: white;
                            border-radius: 10px;
                            text-decoration: none;
                            font-weight: 600;
                        ">
                            <i class="fas fa-user"></i>
                            <?php echo $dil == 'tr' ? 'Yorum Yapmak İçin Giriş Yap' : 'Login to Review'; ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- YORUMLAR LİSTESİ -->
                <div class="yorumlar-listesi">
                    <?php foreach($gosterilecek_yorumlar as $yorum): ?>
                        <div class="yorum-karti">
                            <div class="yorum-ust">
                                <div class="yorumcu-bilgi">
                                    <div class="yorumcu-avatar">
                                        <?php echo strtoupper(substr($yorum['kullanici_adi'], 0, 2)); ?>
                                    </div>
                                    <div class="yorumcu-detay">
                                        <h3><?php echo htmlspecialchars($yorum['kullanici_adi']); ?></h3>
                                        <div class="yorum-tarihi">
                                            <?php echo date('d.m.Y H:i', strtotime($yorum['tarih'])); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="yorum-puan">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <span class="yildiz" style="color: <?php echo $i <= $yorum['puan'] ? '#FFD700' : '#ddd'; ?>;">★</span>
                                    <?php endfor; ?>
                                    <span style="margin-left: 10px; font-weight: 700; color: #333;">
                                        <?php echo $yorum['puan']; ?>/5
                                    </span>
                                </div>
                            </div>
                            
                            <div class="yorum-metni">
                                "<?php echo htmlspecialchars($yorum['yorum']); ?>"
                            </div>
                            
                            <?php if($is_admin || (isset($user_id) && $yorum['kullanici_id'] == $user_id)): ?>
                                <div class="yorum-aksiyon">
                                    <a href="yorumlar.php?yorum_sil=<?php echo $yorum['id']; ?>" 
                                       class="sil-btn"
                                       onclick="return confirm('<?php echo $dil == 'tr' ? "Bu yorumu silmek istediğinize emin misiniz?" : "Are you sure you want to delete this review?"; ?>');">
                                        <i class="fas fa-trash"></i>
                                        <?php echo $dil == 'tr' ? 'Yorumu Sil' : 'Delete Review'; ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- SAYFALAMA -->
                <?php if($toplam_sayfa > 1): ?>
                    <div class="sayfalama">
                        <?php if($sayfa > 1): ?>
                            <a href="yorumlar.php?sayfa=<?php echo $sayfa-1; ?>" class="sayfa-btn">
                                <i class="fas fa-chevron-left"></i> 
                                <?php echo $dil == 'tr' ? 'Önceki' : 'Previous'; ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php 
                        $baslangic_sayfa = max(1, $sayfa - 2);
                        $bitis_sayfa = min($toplam_sayfa, $sayfa + 2);
                        
                        for($i = $baslangic_sayfa; $i <= $bitis_sayfa; $i++):
                        ?>
                            <a href="yorumlar.php?sayfa=<?php echo $i; ?>" 
                               class="sayfa-btn <?php echo $i == $sayfa ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if($sayfa < $toplam_sayfa): ?>
                            <a href="yorumlar.php?sayfa=<?php echo $sayfa+1; ?>" class="sayfa-btn">
                                <?php echo $dil == 'tr' ? 'Sonraki' : 'Next'; ?>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- YILDIZ RATING JAVASCRIPT -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Yıldız rating sistemi
        const stars = document.querySelectorAll('#starRating label');
        const starInputs = document.querySelectorAll('#starRating input');
        
        stars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const rating = this.getAttribute('for').replace('star', '');
                highlightStars(rating);
            });
            
            star.addEventListener('click', function() {
                const rating = this.getAttribute('for').replace('star', '');
                setRating(rating);
            });
        });
        
        document.querySelector('#starRating').addEventListener('mouseleave', function() {
            const checkedStar = document.querySelector('#starRating input:checked');
            if (checkedStar) {
                const rating = checkedStar.id.replace('star', '');
                highlightStars(rating);
            } else {
                resetStars();
            }
        });
        
        function highlightStars(rating) {
            stars.forEach(star => {
                const starNumber = star.getAttribute('for').replace('star', '');
                if (starNumber <= rating) {
                    star.style.color = '#FFD700';
                } else {
                    star.style.color = '#ddd';
                }
            });
        }
        
        function resetStars() {
            stars.forEach(star => {
                star.style.color = '#ddd';
            });
        }
        
        function setRating(rating) {
            // Rating zaten input'ta seçili, sadece görseli güncelle
            highlightStars(rating);
        }
        
        // Başlangıçta yıldızları sıfırla
        resetStars();
        
        // Form gönderildiğinde kontrol
        const yorumForm = document.querySelector('form');
        if (yorumForm) {
            yorumForm.addEventListener('submit', function(e) {
                const puan = document.querySelector('input[name="puan"]:checked');
                const yorum = document.querySelector('textarea[name="yorum"]');
                
                if (!puan) {
                    e.preventDefault();
                    alert('<?php echo $dil == 'tr' ? "Lütfen bir puan seçin!" : "Please select a rating!"; ?>');
                    return false;
                }
                
                if (yorum.value.trim().length < 10) {
                    e.preventDefault();
                    alert('<?php echo $dil == 'tr' ? "Yorumunuz en az 10 karakter olmalıdır!" : "Your review must be at least 10 characters!"; ?>');
                    return false;
                }
            });
        }
    });
    </script>
    
    <?php 
    ob_end_flush(); // Buffer'ı temizle
    require_once 'footer.php'; 
    ?>
</body>
</html>