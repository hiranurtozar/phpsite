<?php
require_once 'header.php';

// Favori işlemleri
if (isset($_GET['action'])) {
    // Session'da favoriler dizisi yoksa oluştur
    if (!isset($_SESSION['favoriler'])) {
        $_SESSION['favoriler'] = [];
    }
    
    if ($_GET['action'] == 'temizle') {
        // Tüm favorileri temizle
        $_SESSION['favoriler'] = [];
        $_SESSION['message'] = 'Tüm favorileriniz temizlendi!';
        $_SESSION['message_type'] = 'success';
        
        // Favoriler sayfasına yönlendir
        header('Location: favoriler.php');
        exit();
        
    } elseif (isset($_GET['urun_id']) && $_GET['action'] == 'ekle') {
        $urun_id = intval($_GET['urun_id']);
        
        // Favorilere ekle
        if (!in_array($urun_id, $_SESSION['favoriler'])) {
            $_SESSION['favoriler'][] = $urun_id;
            $_SESSION['message'] = 'Ürün favorilere eklendi! ❤️';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Bu ürün zaten favorilerinizde!';
            $_SESSION['message_type'] = 'info';
        }
        
    } elseif (isset($_GET['urun_id']) && $_GET['action'] == 'cikar') {
        $urun_id = intval($_GET['urun_id']);
        
        // Favorilerden çıkar
        $key = array_search($urun_id, $_SESSION['favoriler']);
        if ($key !== false) {
            unset($_SESSION['favoriler'][$key]);
            $_SESSION['favoriler'] = array_values($_SESSION['favoriler']); // Diziyi yeniden indeksle
            $_SESSION['message'] = 'Ürün favorilerden çıkarıldı!';
            $_SESSION['message_type'] = 'success';
        }
    }
    
    // Eğer temizleme işlemi değilse, geldiği sayfaya geri dön
    if ($_GET['action'] != 'temizle') {
        $referer = $_SERVER['HTTP_REFERER'] ?? 'favoriler.php';
        header("Location: $referer");
        exit();
    }
}

// Mesajları göster
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Session'da favoriler dizisi yoksa oluştur
if (!isset($_SESSION['favoriler'])) {
    $_SESSION['favoriler'] = [];
}

// Örnek ürün verileri
$urunler_veritabani = [
    1 => ['id' => 1, 'ad' => 'Kırmızı Gül Buketi', 'fiyat' => 149.99, 'aciklama' => '12 adet kırmızı gül'],
    2 => ['id' => 2, 'ad' => 'Mor Orkide', 'fiyat' => 199.99, 'aciklama' => 'Özel mor orkide'],
    3 => ['id' => 3, 'ad' => 'Lale Buketi', 'fiyat' => 129.99, 'aciklama' => 'Renkli laleler'],
    4 => ['id' => 4, 'ad' => 'Sukulent Seti', 'fiyat' => 89.99, 'aciklama' => '3 adet sukulent'],
    5 => ['id' => 5, 'ad' => 'Doğum Günü Buketi', 'fiyat' => 179.99, 'aciklama' => 'Renkli çiçekler'],
    6 => ['id' => 6, 'ad' => 'Beyaz Güller', 'fiyat' => 159.99, 'aciklama' => '10 adet beyaz gül'],
];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorilerim</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* FAVORİLER SAYFASI STİLLERİ */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .favoriler-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .favori-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
            transition: all 0.3s;
            position: relative;
        }
        
        .favori-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(255, 107, 157, 0.2);
        }
        
        .favori-image {
            height: 200px;
            background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #ff6b9d;
            position: relative;
        }
        
        .favori-remove-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #ff6b9d;
            font-size: 20px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .favori-remove-btn:hover {
            background: #ff6b9d;
            color: white;
            transform: scale(1.1);
        }
        
        .favori-info {
            padding: 20px;
        }
        
        .favori-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .favori-desc {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .favori-price {
            color: #ff6b9d;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }
        
        .favori-actions {
            display: flex;
            gap: 10px;
        }
        
        .favori-add-cart {
            flex: 1;
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .favori-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 157, 0.3);
        }
        
        .favori-view-btn {
            background: #2196F3;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .favori-view-btn:hover {
            background: #1976D2;
            transform: translateY(-2px);
        }
        
        .empty-favorites {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
            margin-top: 20px;
        }
        
        .empty-favorites i {
            font-size: 80px;
            color: #ffeef2;
            margin-bottom: 20px;
        }
        
        .empty-favorites h3 {
            color: #ff6b9d;
            margin-bottom: 10px;
        }
        
        .empty-favorites p {
            color: #666;
            margin-bottom: 30px;
        }
        
        /* Mesaj stilleri */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.5s;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .message.info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .favorite-count {
            background: #ff6b9d;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .clear-all-btn {
            background: #f44336;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .clear-all-btn:hover {
            background: #d32f2f;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(244, 67, 54, 0.3);
        }
        
        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(-10px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 style="color: #ff6b9d;">
                <i class="fas fa-heart"></i> 
                <?php 
                if (isset($text_selected['favoriler'])) {
                    echo $text_selected['favoriler'];
                } else {
                    echo 'Favorilerim';
                }
                ?>
            </h1>
            
            <?php if (!empty($_SESSION['favoriler'])): ?>
                <div class="header-actions">
                    <span class="favorite-count">
                        <?php echo count($_SESSION['favoriler']); ?> ürün
                    </span>
                    <button class="clear-all-btn" onclick="clearAllFavorites()">
                        <i class="fas fa-trash"></i> Tümünü Temizle
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'info-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($_SESSION['favoriler'])): ?>
            <div class="empty-favorites">
                <i class="fas fa-heart"></i>
                <h3>
                    <?php 
                    if (isset($dil) && $dil == 'en') {
                        echo 'Your favorites are empty';
                    } else {
                        echo 'Favorileriniz boş';
                    }
                    ?>
                </h3>
                <p>
                    <?php 
                    if (isset($dil) && $dil == 'en') {
                        echo 'Add products you like to favorites and find them easily later.';
                    } else {
                        echo 'Beğendiğiniz ürünleri favorilere ekleyin ve daha sonra kolayca bulun.';
                    }
                    ?>
                </p>
                <a href="urunler.php" class="favori-add-cart" style="display: inline-flex; width: auto; padding: 12px 30px;">
                    <i class="fas fa-store"></i> 
                    <?php 
                    if (isset($dil) && $dil == 'en') {
                        echo 'Explore Products';
                    } else {
                        echo 'Ürünleri Keşfet';
                    }
                    ?>
                </a>
            </div>
        <?php else: ?>
            <div class="favoriler-grid">
                <?php foreach ($_SESSION['favoriler'] as $urun_id): ?>
                    <?php if (isset($urunler_veritabani[$urun_id])): 
                        $urun = $urunler_veritabani[$urun_id];
                    ?>
                        <div class="favori-card">
                            <div class="favori-image">
                                <?php 
                                $emoji_icons = ['🌸', '🌹', '💮', '🌷', '💐', '🌵'];
                                echo $emoji_icons[$urun_id % count($emoji_icons)];
                                ?>
                                <a href="favoriler.php?action=cikar&urun_id=<?php echo $urun['id']; ?>" 
                                   class="favori-remove-btn"
                                   title="<?php 
                                   if (isset($dil) && $dil == 'en') {
                                       echo 'Remove from Favorites';
                                   } else {
                                       echo 'Favorilerden Çıkar';
                                   }
                                   ?>"
                                   onclick="return confirm('<?php 
                                   if (isset($dil) && $dil == 'en') {
                                       echo 'Remove this product from favorites?';
                                   } else {
                                       echo 'Bu ürünü favorilerden çıkarmak istediğinize emin misiniz?';
                                   }
                                   ?>');">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                            
                            <div class="favori-info">
                                <h3 class="favori-name"><?php echo htmlspecialchars($urun['ad']); ?></h3>
                                <p class="favori-desc"><?php echo htmlspecialchars($urun['aciklama']); ?></p>
                                <div class="favori-price"><?php echo number_format($urun['fiyat'], 2); ?> TL</div>
                                
                                <div class="favori-actions">
                                    <a href="sepet.php?action=ekle&urun_id=<?php echo $urun['id']; ?>" 
                                       class="favori-add-cart"
                                       onclick="return confirm('<?php echo htmlspecialchars($urun['ad']); ?> sepete eklensin mi?')">
                                        <i class="fas fa-shopping-cart"></i>
                                        <?php 
                                        if (isset($dil) && $dil == 'en') {
                                            echo 'Add to Cart';
                                        } else {
                                            echo 'Sepete Ekle';
                                        }
                                        ?>
                                    </a>
                                    <a href="urun-detay.php?id=<?php echo $urun['id']; ?>" class="favori-view-btn">
                                        <i class="fas fa-eye"></i>
                                        <?php 
                                        if (isset($dil) && $dil == 'en') {
                                            echo 'View';
                                        } else {
                                            echo 'İncele';
                                        }
                                        ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Tüm favorileri temizleme fonksiyonu
        function clearAllFavorites() {
            if (confirm('Tüm favorilerinizi temizlemek istediğinize emin misiniz? Bu işlem geri alınamaz!')) {
                // PHP'de temizleme işlemi için sayfaya yönlendir
                window.location.href = 'favoriler.php?action=temizle';
            }
        }
        
        // Favori sayacını güncelle (sayfa yenilendiğinde)
        document.addEventListener('DOMContentLoaded', function() {
            // Eğer URL'de message parametresi varsa, sayfayı biraz kaydır
            if (window.location.href.indexOf('message=') > -1) {
                setTimeout(function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }, 100);
            }
        });
    </script>
</body>
</html>