<?php
require_once 'header.php';

// Favori işlemleri
if (isset($_GET['action']) && isset($_GET['urun_id'])) {
    $urun_id = intval($_GET['urun_id']);
    $action = $_GET['action'];
    
    if ($action == 'ekle') {
        // Favorilere ekle
        if (!in_array($urun_id, $_SESSION['favoriler'])) {
            $_SESSION['favoriler'][] = $urun_id;
            $_SESSION['message'] = 'Ürün favorilere eklendi! ❤️';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Bu ürün zaten favorilerinizde!';
            $_SESSION['message_type'] = 'info';
        }
        
    } elseif ($action == 'cikar') {
        // Favorilerden çıkar
        $key = array_search($urun_id, $_SESSION['favoriler']);
        if ($key !== false) {
            unset($_SESSION['favoriler'][$key]);
            $_SESSION['favoriler'] = array_values($_SESSION['favoriler']); // Diziyi yeniden indeksle
            $_SESSION['message'] = 'Ürün favorilerden çıkarıldı!';
            $_SESSION['message_type'] = 'success';
        }
        
    } elseif ($action == 'temizle') {
        // Tüm favorileri temizle
        $_SESSION['favoriler'] = [];
        $_SESSION['message'] = 'Tüm favorileriniz temizlendi!';
        $_SESSION['message_type'] = 'success';
    }
    
    // Geldiği sayfaya geri dön
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'favoriler.php'));
    exit();
}

// Mesajları göster
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
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

<style>
    /* FAVORİLER SAYFASI STİLLERİ */
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
</style>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 style="color: #ff6b9d;">
            <i class="fas fa-heart"></i> <?php echo $text_selected['favoriler']; ?>
        </h1>
        
        <?php if (!empty($_SESSION['favoriler'])): ?>
            <div style="display: flex; gap: 15px; align-items: center;">
                <span style="background: #ff6b9d; color: white; padding: 8px 15px; border-radius: 20px; font-weight: 600;">
                    <?php echo count($_SESSION['favoriler']); ?> ürün
                </span>
                <a href="favoriler.php?action=temizle" style="
                    background: #f44336;
                    color: white;
                    padding: 8px 15px;
                    border-radius: 8px;
                    text-decoration: none;
                    font-weight: 600;
                    transition: all 0.3s;
                " onclick="return confirm('Tüm favorilerinizi temizlemek istediğinize emin misiniz?')"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 10px rgba(244, 67, 54, 0.3)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    <i class="fas fa-trash"></i> Tümünü Temizle
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (isset($message)): ?>
        <div class="message <?php echo $message_type; ?>">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'info-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($_SESSION['favoriler'])): ?>
        <div class="empty-favorites">
            <i class="fas fa-heart"></i>
            <h3><?php echo $dil == 'tr' ? 'Favorileriniz boş' : 'Your favorites are empty'; ?></h3>
            <p>
                <?php echo $dil == 'tr' 
                    ? 'Beğendiğiniz ürünleri favorilere ekleyin ve daha sonra kolayca bulun.' 
                    : 'Add products you like to favorites and find them easily later.'; 
                ?>
            </p>
            <a href="urunler.php" style="
                display: inline-block;
                background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
                color: white;
                padding: 12px 30px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s;
            " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(255, 107, 157, 0.3)'"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                <i class="fas fa-store"></i> 
                <?php echo $dil == 'tr' ? 'Ürünleri Keşfet' : 'Explore Products'; ?>
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
                               title="<?php echo $dil == 'tr' ? 'Favorilerden Çıkar' : 'Remove from Favorites'; ?>">
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
                                    <?php echo $dil == 'tr' ? 'Sepete Ekle' : 'Add to Cart'; ?>
                                </a>
                                <a href="urunler.php?urun=<?php echo $urun['id']; ?>" class="favori-view-btn">
                                    <i class="fas fa-eye"></i>
                                    <?php echo $dil == 'tr' ? 'İncele' : 'View'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <script>
// Favori sayacını güncelle (sayfa yenilendiğinde)
document.addEventListener('DOMContentLoaded', function() {
    const favoriCounter = document.querySelector('.favori-sayaci');
    if(favoriCounter) {
        // Session'dan favori sayısını al
        fetch('get_favorites_count.php')
            .then(response => response.json())
            .then(data => {
                if(data.count > 0) {
                    favoriCounter.textContent = data.count;
                    favoriCounter.style.display = 'inline-block';
                } else {
                    favoriCounter.style.display = 'none';
                }
            });
    }
});

// Favori ekleme/çıkarma sonrası sayacı güncelle
function updateFavoriteCounter() {
    const counter = document.querySelector('.favori-sayaci');
    if(counter) {
        // Sayfayı yenile
        location.reload();
    }
}
</script>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>