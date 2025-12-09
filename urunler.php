<?php
require_once 'cicek.php';
require_once 'header.php';
ob_start();
$kategori = $_GET['kategori'] ?? 'tumu';
$arama = $_GET['arama'] ?? '';

// TÜM ürünleri getir (cicek.php'deki fonksiyonu kullan)
// NOT: urunleriGetir fonksiyonu varsayılan olarak tüm ürünleri döndürecek
$tum_urunler = urunleriGetir($kategori);

// Eğer kategori "tumu" ise tüm ürünleri göster
// Eğer belirli bir kategori ise sadece o kategoriye ait ürünleri göster
$urunler = $tum_urunler;

// Arama filtresi
if($arama) {
    $arama = strtolower(trim($arama));
    $urunler = array_filter($urunler, function($urun) use ($arama, $dil) {
        $ad = strtolower(($dil == 'tr' ? ($urun['tr_ad'] ?? $urun['ad'] ?? '') : ($urun['en_ad'] ?? $urun['ad'] ?? '')));
        $aciklama = strtolower(($dil == 'tr' ? ($urun['tr_aciklama'] ?? $urun['aciklama'] ?? '') : ($urun['en_aciklama'] ?? $urun['aciklama'] ?? '')));
        return strpos($ad, $arama) !== false || strpos($aciklama, $arama) !== false;
    });
    $urunler = array_values($urunler);
}
?>

<style>
    /* Ürünler Sayfası Özel Stilleri - PEMBE TEMA */
    .products-page {
        padding: 40px 20px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .page-header h1 {
        font-size: 2.8rem;
        color: #333;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .page-header p {
        color: #666;
        opacity: 0.8;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }
    
    /* Kategori Filtreleri */
    .category-filters {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 40px;
        flex-wrap: wrap;
    }
    
    .category-btn {
        padding: 12px 25px;
        border: 2px solid #e0e0e0;
        background: white;
        border-radius: 25px;
        text-decoration: none;
        color: #333;
        font-weight: 500;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .category-btn:hover {
        border-color: #ff6b9d;
        color: #ff6b9d;
        transform: translateY(-2px);
    }
    
    .category-btn.active {
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 5px 15px rgba(255, 107, 157, 0.2);
    }
    
    /* Arama Çubuğu - ANİMASYONLU */
    .search-container {
        max-width: 800px;
        margin: 0 auto 40px;
        position: relative;
    }
    
    .search-box {
        position: relative;
        animation: slideIn 0.5s ease-out;
    }
    
    .search-input {
        width: 100%;
        padding: 18px 20px 18px 60px;
        border: 2px solid #ffeef2;
        border-radius: 15px;
        font-size: 1.1rem;
        transition: all 0.3s;
        background: white;
        box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
    }
    
    .search-input:focus {
        outline: none;
        border-color: #ff6b9d;
        box-shadow: 0 8px 25px rgba(255, 107, 157, 0.15);
        transform: scale(1.02);
    }
    
    .search-icon {
        position: absolute;
        left: 25px;
        top: 50%;
        transform: translateY(-50%);
        color: #ff6b9d;
        font-size: 1.3rem;
        z-index: 2;
    }
    
    .search-results {
        text-align: center;
        color: #666;
        opacity: 0.7;
        margin-bottom: 30px;
        padding: 15px;
        background: #fff9fb;
        border-radius: 10px;
        border: 1px solid #ffeef2;
        animation: fadeIn 0.5s ease-out;
    }
    
    /* Ürün Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 60px;
    }
    
    /* Ürün Kartı */
    .product-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        animation: fadeIn 0.5s ease-out;
        position: relative;
        border: 1px solid #f0f0f0;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(255, 107, 157, 0.12);
    }
    
    .product-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: bold;
        z-index: 2;
    }
    
    .product-image {
        height: 220px;
        background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    
    .product-image-content {
        font-size: 4.5rem;
        transition: transform 0.5s;
    }
    
    .product-card:hover .product-image-content {
        transform: scale(1.1);
    }
    
    .product-info {
        padding: 25px;
    }
    
    .product-category {
        color: #ff6b9d;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .product-title {
        font-size: 1.3rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
        line-height: 1.4;
        height: 60px;
        overflow: hidden;
    }
    
    .product-description {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 20px;
        height: 60px;
        overflow: hidden;
    }
    
    .product-price {
        font-size: 1.8rem;
        font-weight: bold;
        color: #28a745;
        margin-bottom: 20px;
    }
    
    .product-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-add-cart {
        flex: 1;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        border: none;
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-add-cart:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 107, 157, 0.2);
    }
    
    .btn-favorite {
        width: 45px;
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .btn-favorite:hover {
        background: #ff4757;
        color: white;
        border-color: #ff4757;
        transform: scale(1.1);
    }
    
    .btn-favorite.active {
        background: #ff4757;
        color: white;
        border-color: #ff4757;
    }
    
    .product-stock {
        margin-top: 10px;
        font-size: 0.9rem;
        color: #666;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .stock-available {
        color: #28a745;
    }
    
    .stock-low {
        color: #ffc107;
    }
    
    /* Boş Ürün Mesajı */
    .empty-products {
        text-align: center;
        padding: 60px 20px;
        grid-column: 1 / -1;
    }
    
    .empty-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
        animation: bounce 2s infinite;
    }
    
    .empty-products h3 {
        color: #333;
        margin-bottom: 15px;
    }
    
    .empty-products p {
        color: #666;
        max-width: 400px;
        margin: 0 auto 30px;
    }
    
    /* Sayfalama */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 40px;
    }
    
    .page-btn {
        width: 40px;
        height: 40px;
        border: 2px solid #e0e0e0;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #333;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .page-btn:hover {
        border-color: #ff6b9d;
        color: #ff6b9d;
        transform: translateY(-2px);
    }
    
    .page-btn.active {
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 5px 10px rgba(255, 107, 157, 0.2);
    }
    
    /* Animasyonlar */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
        
        .category-filters {
            overflow-x: auto;
            padding-bottom: 10px;
            justify-content: flex-start;
            scrollbar-width: thin;
        }
        
        .category-filters::-webkit-scrollbar {
            height: 4px;
        }
        
        .category-btn {
            white-space: nowrap;
            padding: 10px 20px;
            font-size: 0.9rem;
        }
        
        .search-input {
            padding: 15px 20px 15px 50px;
            font-size: 1rem;
        }
        
        .product-image {
            height: 180px;
        }
        
        .product-title,
        .product-description {
            height: auto;
        }
    }
    
    @media (max-width: 576px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .page-header h1 {
            font-size: 2.2rem;
        }
        
        .search-container {
            margin: 0 10px 40px;
        }
    }
</style>

<div class="products-page">
    <!-- Sayfa Başlığı -->
    <div class="page-header">
        <h1><?php echo $dil == 'tr' ? 'Çiçekler & Buketler' : 'Flowers & Bouquets'; ?></h1>
        <p><?php echo $dil == 'tr' ? 'En güzel çiçekler ve özel buketler bir tık uzağınızda' : 'The most beautiful flowers and special bouquets are just one click away'; ?></p>
    </div>

    <!-- Kategori Filtreleri -->
    <div class="category-filters">
        <a href="urunler.php?sayfa=urunler&kategori=tumu" 
           class="category-btn <?php echo $kategori == 'tumu' ? 'active' : ''; ?>">
            🌟 <?php echo $dil == 'tr' ? 'Tüm Ürünler' : 'All Products'; ?>
        </a>
        <a href="urunler.php?sayfa=urunler&kategori=gul" 
           class="category-btn <?php echo $kategori == 'gul' ? 'active' : ''; ?>">
            🌹 <?php echo $dil == 'tr' ? 'Güller' : 'Roses'; ?>
        </a>
        <a href="urunler.php?sayfa=urunler&kategori=orkide" 
           class="category-btn <?php echo $kategori == 'orkide' ? 'active' : ''; ?>">
            💮 <?php echo $dil == 'tr' ? 'Orkideler' : 'Orchids'; ?>
        </a>
        <a href="urunler.php?sayfa=urunler&kategori=lale" 
           class="category-btn <?php echo $kategori == 'lale' ? 'active' : ''; ?>">
            🌷 <?php echo $dil == 'tr' ? 'Laleler' : 'Tulips'; ?>
        </a>
        <a href="urunler.php?sayfa=urunler&kategori=buket" 
           class="category-btn <?php echo $kategori == 'buket' ? 'active' : ''; ?>">
            💐 <?php echo $dil == 'tr' ? 'Buketler' : 'Bouquets'; ?>
        </a>
        <a href="urunler.php?sayfa=urunler&kategori=sukulent" 
           class="category-btn <?php echo $kategori == 'sukulent' ? 'active' : ''; ?>">
            🌵 <?php echo $dil == 'tr' ? 'Sukulentler' : 'Succulents'; ?>
        </a>
    </div>

    <!-- Arama Çubuğu -->
    <div class="search-container">
        <form method="get" action="urunler.php" class="search-box">
            <input type="hidden" name="sayfa" value="urunler">
            <input type="hidden" name="kategori" value="<?php echo $kategori; ?>">
            <div class="search-icon">🔍</div>
            <input type="text" 
                   name="arama" 
                   class="search-input" 
                   placeholder="<?php echo $dil == 'tr' ? 'Çiçek adı veya kategori ara...' : 'Search flower name or category...'; ?>"
                   value="<?php echo htmlspecialchars($arama); ?>"
                   autocomplete="off">
        </form>
    </div>

    <!-- Arama Sonuçları -->
    <?php if($arama): ?>
    <div class="search-results">
        <p>
            "<?php echo htmlspecialchars($arama); ?>" <?php echo $dil == 'tr' ? 'için' : 'for'; ?> 
            <strong><?php echo count($urunler); ?></strong> 
            <?php echo $dil == 'tr' ? 'ürün bulundu' : 'products found'; ?>
        </p>
        <a href="urunler.php?sayfa=urunler&kategori=<?php echo $kategori; ?>" 
           class="category-btn" style="font-size: 0.9rem; padding: 8px 15px;">
            <?php echo $dil == 'tr' ? 'Aramayı Temizle' : 'Clear Search'; ?>
        </a>
    </div>
    <?php endif; ?>

    <!-- Ürün Grid -->
    <?php if(empty($urunler)): ?>
        <div class="empty-products">
            <div class="empty-icon">🌱</div>
            <h3><?php echo $dil == 'tr' ? 'Ürün Bulunamadı' : 'No Products Found'; ?></h3>
            <p>
                <?php echo $dil == 'tr' 
                    ? 'Bu kategoride henüz ürün bulunmuyor veya aramanızla eşleşen ürün yok.' 
                    : 'No products found in this category or matching your search.'; ?>
            </p>
            <a href="urunler.php?sayfa=urunler&kategori=tumu" class="btn-add-cart" style="width: auto; padding: 12px 30px; text-decoration: none;">
                ← <?php echo $dil == 'tr' ? 'Tüm Ürünleri Gör' : 'View All Products'; ?>
            </a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach($urunler as $urun): 
                // Dil'e göre ürün bilgileri
                $urun_ad = $dil == 'tr' ? ($urun['tr_ad'] ?? $urun['ad'] ?? 'Ürün') : ($urun['en_ad'] ?? $urun['ad'] ?? 'Product');
                $urun_aciklama = $dil == 'tr' ? ($urun['tr_aciklama'] ?? $urun['aciklama'] ?? '') : ($urun['en_aciklama'] ?? $urun['aciklama'] ?? '');
                $kategori_adi = $urun['kategori'] ?? '';
                
                // Kategori ikonu
                switch($kategori_adi) {
                    case 'gul': $kategori_ikon = '🌹'; break;
                    case 'orkide': $kategori_ikon = '💮'; break;
                    case 'lale': $kategori_ikon = '🌷'; break;
                    case 'buket': $kategori_ikon = '💐'; break;
                    case 'sukulent': $kategori_ikon = '🌵'; break;
                    default: $kategori_ikon = '🌸';
                }
                
                // Stok durumu
                $stok = $urun['stok'] ?? 0;
                $stok_durumu = $stok > 10 ? 'stock-available' : ($stok > 0 ? 'stock-low' : '');
                $stok_mesaji = $stok > 10 
                    ? ($dil == 'tr' ? 'Stokta var' : 'In Stock') 
                    : ($stok > 0 
                        ? ($dil == 'tr' ? "Son {$stok} adet" : "Only {$stok} left") 
                        : ($dil == 'tr' ? 'Stokta yok' : 'Out of Stock'));
            ?>
            <div class="product-card">
                <?php if($stok < 5 && $stok > 0): ?>
                    <div class="product-badge">🔥 <?php echo $dil == 'tr' ? 'Son Ürünler' : 'Limited Stock'; ?></div>
                <?php endif; ?>
                
                <div class="product-image">
                    <div class="product-image-content">
                        <?php echo $kategori_ikon; ?>
                    </div>
                </div>
                
                <div class="product-info">
                    <div class="product-category">
                        <?php echo $kategori_ikon; ?>
                        <?php 
                        $kategori_isimleri = [
                            'tr' => [
                                'gul' => 'Gül',
                                'orkide' => 'Orkide',
                                'lale' => 'Lale',
                                'buket' => 'Buket',
                                'sukulent' => 'Sukulent'
                            ],
                            'en' => [
                                'gul' => 'Rose',
                                'orkide' => 'Orchid',
                                'lale' => 'Tulip',
                                'buket' => 'Bouquet',
                                'sukulent' => 'Succulent'
                            ]
                        ];
                        echo $kategori_isimleri[$dil][$kategori_adi] ?? ucfirst($kategori_adi);
                        ?>
                    </div>
                    
                    <h3 class="product-title"><?php echo htmlspecialchars($urun_ad); ?></h3>
                    
                    <p class="product-description"><?php echo htmlspecialchars($urun_aciklama); ?></p>
                    
                    <div class="product-price"><?php echo number_format($urun['fiyat'], 2); ?> ₺</div>
                    
                    <div class="product-stock <?php echo $stok_durumu; ?>">
                        <span>📦</span>
                        <span><?php echo $stok_mesaji; ?></span>
                    </div>
                    
                    <div class="product-actions">
                        <button class="btn-add-cart" onclick="addToCart(<?php echo $urun['id']; ?>)">
                            🛒 <?php echo $dil == 'tr' ? 'Sepete Ekle' : 'Add to Cart'; ?>
                        </button>
                        <button class="btn-favorite" onclick="addToFavorites(<?php echo $urun['id']; ?>)">❤️</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Sayfalama (basit versiyon) -->
        <?php if(count($urunler) > 12): ?>
        <div class="pagination">
            <a href="#" class="page-btn">←</a>
            <a href="#" class="page-btn active">1</a>
            <a href="#" class="page-btn">2</a>
            <a href="#" class="page-btn">3</a>
            <a href="#" class="page-btn">→</a>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
// Sepete ekle
function addToCart(productId) {
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

// Favorilere ekle
function addToFavorites(productId) {
    const btn = event.target;
    btn.classList.toggle('active');
    
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
            }
        })
        .catch(error => {
            console.error('Favori ekleme hatası:', error);
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
    }
}

// Bildirim göster
function showNotification(message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || (() => {
        const div = document.createElement('div');
        div.className = 'toast-container';
        document.body.appendChild(div);
        return div;
    })();
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    let icon = 'ℹ️';
    if(type === 'success') icon = '✅';
    if(type === 'error') icon = '❌';
    if(type === 'warning') icon = '⚠️';
    
    toast.innerHTML = `<span>${icon} ${message}</span>`;
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Arama input'u için otomatik submit
const searchInput = document.querySelector('.search-input');
let searchTimeout;
searchInput?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 500);
});

// Sayfa yüklendiğinde animasyonlar
document.addEventListener('DOMContentLoaded', function() {
    // Ürün kartlarına sırayla animasyon ekle
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
    
    // Arama input'una focus animasyonu
    if(searchInput) {
        searchInput.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    }
});
</script>

<?php
ob_end_flush(); // Tamponu temizle
require_once 'footer.php'; 
?>