<?php
require_once 'header.php';

// Dil ayarı
$dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';

// Mevcut sayfa bilgisi
$sayfa = 'urunler';

// Kategori
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'tumu';

// Arama kelimesi
$arama_kelimesi = isset($_GET['arama']) ? trim($_GET['arama']) : '';

// Ürünleri getirme fonksiyonu
function urunleriGetir($kategori = 'tumu', $arama = '') {
    // Örnek ürün verileri - Simge ve kategori ekledim
    $urunler = [
        [
            'id' => 1,
            'ad' => 'Kırmızı Gül Buketi',
            'aciklama' => '12 adet taze kırmızı gül, zarif paketleme',
            'fiyat' => 129.99,
            'resim' => 'gul-buket.jpg',
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 15,
            'indirim' => 10,
            'puan' => 4.8
        ],
        [
            'id' => 2,
            'ad' => 'Beyaz Orkide',
            'aciklama' => 'Lüks beyaz orkide, saksılı',
            'fiyat' => 199.99,
            'resim' => 'orkide.jpg',
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 8,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 3,
            'ad' => 'Renkli Lale Demeti',
            'aciklama' => '5 renkli lale demeti, bahar havası',
            'fiyat' => 89.99,
            'resim' => 'lale.jpg',
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 20,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 4,
            'ad' => 'Doğum Günü Buketi',
            'aciklama' => 'Özel doğum günü buketi, renkli çiçekler',
            'fiyat' => 149.99,
            'resim' => 'dogum-gunu.jpg',
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 12,
            'indirim' => 5,
            'puan' => 4.6
        ],
        [
            'id' => 5,
            'ad' => 'Mini Sukulent Seti',
            'aciklama' => '3 adet minyatür sukulent, teraryum',
            'fiyat' => 69.99,
            'resim' => 'sukulent.jpg',
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 25,
            'indirim' => 20,
            'puan' => 4.5
        ],
        [
            'id' => 6,
            'ad' => 'Pembe Gül Demeti',
            'aciklama' => 'Romantik pembe gül demeti, 24 adet',
            'fiyat' => 179.99,
            'resim' => 'pembe-gul.jpg',
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 10,
            'indirim' => 0,
            'puan' => 4.8
        ],
        [
            'id' => 7,
            'ad' => 'Mor Orkide',
            'aciklama' => 'Nadir mor orkide, özel bakım',
            'fiyat' => 249.99,
            'resim' => 'mor-orkide.jpg',
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 5,
            'indirim' => 10,
            'puan' => 4.9
        ],
        [
            'id' => 8,
            'ad' => 'Sarı Lale Buketi',
            'aciklama' => 'Parlak sarı laleler, mutluluk sembolü',
            'fiyat' => 79.99,
            'resim' => 'sari-lale.jpg',
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 18,
            'indirim' => 0,
            'puan' => 4.7
        ]
    ];
    
    // Kategoriye göre filtrele
    if ($kategori != 'tumu') {
        $urunler = array_filter($urunler, function($urun) use ($kategori) {
            return $urun['kategori'] == $kategori;
        });
    }
    
    // Aramaya göre filtrele
    if (!empty($arama)) {
        $arama = strtolower($arama);
        $urunler = array_filter($urunler, function($urun) use ($arama) {
            return strpos(strtolower($urun['ad']), $arama) !== false || 
                   strpos(strtolower($urun['aciklama']), $arama) !== false;
        });
    }
    
    return array_values($urunler);
}

// Ürünleri getir
$urunler = urunleriGetir($kategori, $arama_kelimesi);

// Kategori isimleri
$kategori_isimleri = [
    'tr' => [
        'tumu' => 'Tüm Ürünler',
        'gul' => 'Güller',
        'orkide' => 'Orkideler',
        'lale' => 'Laleler',
        'buket' => 'Buketler',
        'sukulent' => 'Sukulentler'
    ],
    'en' => [
        'tumu' => 'All Products',
        'gul' => 'Roses',
        'orkide' => 'Orchids',
        'lale' => 'Tulips',
        'buket' => 'Bouquets',
        'sukulent' => 'Succulents'
    ]
];

// Favori kontrol fonksiyonu
function favoriKontrol($urun_id) {
    return isset($_SESSION['favoriler']) && in_array($urun_id, $_SESSION['favoriler']);
}
?>

<style>
    /* ÜRÜNLER SAYFASI STİLLERİ */
    .urunler-container {
        padding: 20px 0;
    }
    
    .kategori-filtreleri {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .kategori-btn {
        padding: 10px 20px;
        background: white;
        border: 2px solid #ffeef2;
        border-radius: 25px;
        color: #666;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
    }
    
    .kategori-btn:hover,
    .kategori-btn.active {
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        border-color: #ff6b9d;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 107, 157, 0.2);
    }
    
    .urun-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }
    
    .urun-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
        transition: all 0.3s;
        position: relative;
    }
    
    .urun-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(255, 107, 157, 0.2);
    }
    
    .urun-resim {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 60px;
        color: #ff6b9d;
    }
    
    .urun-bilgi {
        padding: 20px;
    }
    
    .urun-baslik {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .urun-aciklama {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 15px;
        line-height: 1.5;
    }
    
    .urun-fiyat {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .fiyat-aktuel {
        font-size: 1.3rem;
        font-weight: 700;
        color: #ff6b9d;
    }
    
    .fiyat-eski {
        font-size: 1rem;
        color: #999;
        text-decoration: line-through;
    }
    
    .indirim-badge {
        background: #ff4757;
        color: white;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .stok-bilgi {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #28a745;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }
    
    .urun-puan {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #ffc107;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }
    
    .urun-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-sepete-ekle {
        flex: 1;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        border: none;
        padding: 10px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-sepete-ekle:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 107, 157, 0.3);
    }
    
    .btn-favori {
        background: white;
        border: 2px solid #ffeef2;
        color: #ccc;
        width: 45px;
        height: 45px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        text-decoration: none;
    }
    
    .btn-favori:hover,
    .btn-favori.active {
        background: #ff6b9d;
        color: white;
        border-color: #ff6b9d;
    }
    
    .btn-favori.active {
        color: #ff6b9d;
        background: white;
    }
    
    .btn-favori.active:hover {
        background: #ff6b9d;
        color: white;
    }
    
    .arama-sonucu {
        text-align: center;
        padding: 30px;
        color: #666;
    }
    
    .arama-sonucu h3 {
        color: #ff6b9d;
        margin-bottom: 10px;
    }
    
    .urun-sayisi {
        background: #ffeef2;
        padding: 5px 15px;
        border-radius: 15px;
        color: #ff6b9d;
        font-weight: 600;
        margin-left: 10px;
    }
    
    @media (max-width: 768px) {
        .urun-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .kategori-filtreleri {
            overflow-x: auto;
            padding-bottom: 10px;
            justify-content: flex-start;
        }
    }
</style>

<div class="container">
    <div class="urunler-container">
        <!-- Kategori Filtreleri -->
        <div class="kategori-filtreleri">
            <?php foreach($kategori_isimleri[$dil] as $key => $isim): ?>
                <a href="urunler.php?kategori=<?php echo $key; ?><?php echo !empty($arama_kelimesi) ? '&arama=' . urlencode($arama_kelimesi) : ''; ?>" 
                   class="kategori-btn <?php echo $kategori == $key ? 'active' : ''; ?>">
                    <?php 
                    // Emoji ikonları
                    $emoji = [
                        'tumu' => '🌸',
                        'gul' => '🌹',
                        'orkide' => '💮',
                        'lale' => '🌷',
                        'buket' => '💐',
                        'sukulent' => '🌵'
                    ];
                    echo $emoji[$key] . ' ' . $isim;
                    ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <!-- Arama Sonucu Başlığı -->
        <?php if(!empty($arama_kelimesi)): ?>
            <div class="arama-sonucu">
                <h3>"<?php echo htmlspecialchars($arama_kelimesi); ?>" için arama sonuçları</h3>
                <p><?php echo count($urunler); ?> ürün bulundu</p>
            </div>
        <?php else: ?>
            <h2 style="color: #333; margin-bottom: 20px; display: flex; align-items: center;">
                <?php echo $kategori_isimleri[$dil][$kategori]; ?>
                <span class="urun-sayisi"><?php echo count($urunler); ?> ürün</span>
            </h2>
        <?php endif; ?>
        
        <!-- Ürünler Grid -->
        <?php if(count($urunler) > 0): ?>
            <div class="urun-grid">
                <?php foreach($urunler as $urun): 
                    $indirimli_fiyat = $urun['indirim'] > 0 ? 
                        $urun['fiyat'] * (100 - $urun['indirim']) / 100 : 
                        $urun['fiyat'];
                    $favori_durumu = favoriKontrol($urun['id']) ? 'active' : '';
                ?>
                    <div class="urun-card">
                        <!-- Ürün Resim Alanı -->
                        <div class="urun-resim">
                            <?php echo $urun['simge'] ?? '🌸'; ?>
                        </div>
                        
                        <!-- Ürün Bilgileri -->
                        <div class="urun-bilgi">
                            <div class="urun-baslik">
                                <span><?php echo htmlspecialchars($urun['ad']); ?></span>
                                <?php if($urun['indirim'] > 0): ?>
                                    <span class="indirim-badge">-%<?php echo $urun['indirim']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="urun-aciklama"><?php echo htmlspecialchars($urun['aciklama']); ?></p>
                            
                            <!-- Fiyat -->
                            <div class="urun-fiyat">
                                <?php if($urun['indirim'] > 0): ?>
                                    <span class="fiyat-aktuel"><?php echo number_format($indirimli_fiyat, 2); ?> TL</span>
                                    <span class="fiyat-eski"><?php echo number_format($urun['fiyat'], 2); ?> TL</span>
                                <?php else: ?>
                                    <span class="fiyat-aktuel"><?php echo number_format($urun['fiyat'], 2); ?> TL</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Stok Bilgisi -->
                            <div class="stok-bilgi">
                                <i class="fas fa-check-circle"></i>
                                <span><?php echo $urun['stok']; ?> adet stokta</span>
                            </div>
                            
                            <!-- Puan -->
                            <div class="urun-puan">
                                <i class="fas fa-star"></i>
                                <span><?php echo $urun['puan']; ?></span>
                                <span>(<?php echo rand(10, 100); ?> değerlendirme)</span>
                            </div>
                            
                            <!-- Butonlar -->
                            <div class="urun-actions">
                                <!-- GÜNCELLENMİŞ: Sepete ekle linki -->
                                <a href="sepet.php?action=ekle&urun_id=<?php echo $urun['id']; ?>&urun_ad=<?php echo urlencode($urun['ad']); ?>&urun_fiyat=<?php echo $urun['fiyat']; ?>&urun_simge=<?php echo urlencode($urun['simge']); ?>&urun_kategori=<?php echo $urun['kategori']; ?>" 
                                   class="btn-sepete-ekle"
                                   onclick="return confirmAddToCart('<?php echo addslashes($urun['ad']); ?>')">
                                    <i class="fas fa-shopping-cart"></i> 
                                    <?php echo $dil == 'tr' ? 'Sepete Ekle' : 'Add to Cart'; ?>
                                </a>
                                <a href="favoriler.php?action=<?php echo $favori_durumu ? 'cikar' : 'ekle'; ?>&urun_id=<?php echo $urun['id']; ?>" 
                                   class="btn-favori <?php echo $favori_durumu; ?>"
                                   title="<?php echo $favori_durumu ? ($dil == 'tr' ? 'Favorilerden Çıkar' : 'Remove from Favorites') : ($dil == 'tr' ? 'Favorilere Ekle' : 'Add to Favorites'); ?>">
                                    <i class="fas fa-heart"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Ürün bulunamadıysa -->
            <div style="text-align: center; padding: 50px; color: #666;">
                <div style="font-size: 60px; color: #ffeef2; margin-bottom: 20px;">🌸</div>
                <h3 style="color: #ff6b9d; margin-bottom: 10px;">
                    <?php echo $dil == 'tr' ? 'Ürün bulunamadı' : 'No products found'; ?>
                </h3>
                <p>
                    <?php echo $dil == 'tr' 
                        ? 'Aradığınız kriterlere uygun ürün bulunamadı.' 
                        : 'No products matching your criteria were found.'; 
                    ?>
                </p>
                <a href="urunler.php" style="
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 25px;
                    background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                ">
                    <?php echo $dil == 'tr' ? 'Tüm Ürünleri Gör' : 'View All Products'; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Sepete ekle onayı
function confirmAddToCart(productName) {
    if(confirm(productName + ' <?php echo $dil == 'tr' ? "sepete eklensin mi?" : "add to cart?" ?>')) {
        return true;
    }
    return false;
}

// Sayfa yüklendiğinde favori butonlarına click event'i ekle
document.addEventListener('DOMContentLoaded', function() {
    // Favori butonlarına tıklama
    document.querySelectorAll('.btn-favori').forEach(btn => {
        btn.addEventListener('click', function(e) {
            this.classList.toggle('active');
            
            // Butonun başlığını değiştir
            if (this.classList.contains('active')) {
                this.title = '<?php echo $dil == 'tr' ? "Favorilerden Çıkar" : "Remove from Favorites" ?>';
            } else {
                this.title = '<?php echo $dil == 'tr' ? "Favorilere Ekle" : "Add to Favorites" ?>';
            }
        });
    });
    
    // Animasyon için hover efekti
    document.querySelectorAll('.urun-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            this.style.boxShadow = '0 15px 30px rgba(255, 107, 157, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 5px 20px rgba(255, 107, 157, 0.1)';
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>