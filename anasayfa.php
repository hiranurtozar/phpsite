<?php
require_once 'cicek.php';
require_once 'header.php';

// Anasayfa için özel CSS
?>
<style>
    /* Anasayfa Özel Stiller */
    .hosgeldin {
        text-align: center;
        padding: 60px 20px;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        border-radius: 20px;
        margin-bottom: 40px;
        animation: fadeIn 1s ease-out;
        position: relative;
        overflow: hidden;
    }
    
    .hosgeldin::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text x="50" y="50" font-size="10" fill="white" opacity="0.1" text-anchor="middle" dominant-baseline="middle">🌸</text></svg>');
    }
    
    .hosgeldin h1 {
        font-size: 3.5rem;
        margin-bottom: 20px;
        animation: slideIn 0.8s ease-out;
    }
    
    .hosgeldin p {
        font-size: 1.2rem;
        max-width: 600px;
        margin: 0 auto 30px;
        opacity: 0.9;
    }
    
    /* Kategori kartları için animasyonlar */
    .kategoriler {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin: 60px 0;
    }
    
    .kategori-kart {
        background: white;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: 1px solid #f0f0f0;
        animation: fadeIn 0.6s ease-out;
    }
    
    .kategori-kart:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    }
    
    .kategori-ikon {
        font-size: 3rem;
        margin-bottom: 15px;
        animation: bounce 2s infinite;
    }
    
    .kategori-baslik {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
    }
    
    .kategori-aciklama {
        color: #666;
        margin-bottom: 20px;
        line-height: 1.6;
    }
    
    .kategori-buton {
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 25px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .kategori-buton:hover {
        background: white;
        color: #667eea;
        border-color: #667eea;
    }
    
    /* Animasyonlar */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideIn {
        from { transform: translateX(-100%); }
        to { transform: translateX(0); }
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    /* YENİ: Öne Çıkan Ürünler */
    .featured-products {
        margin: 60px 0;
    }
    
    .featured-products h2 {
        text-align: center;
        font-size: 2.2rem;
        margin-bottom: 40px;
        position: relative;
        padding-bottom: 15px;
    }
    
    .featured-products h2::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 3px;
        background: linear-gradient(to right, #667eea, #764ba2);
        border-radius: 2px;
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }
    
    .product-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        animation: fadeIn 0.6s ease-out;
    }
    
    .product-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .product-image {
        height: 200px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
    }
    
    .product-info {
        padding: 20px;
    }
    
    .product-title {
        font-size: 1.3rem;
        font-weight: bold;
        margin-bottom: 10px;
        color: #333;
    }
    
    .product-description {
        color: #666;
        margin-bottom: 15px;
        line-height: 1.5;
        font-size: 0.95rem;
    }
    
    .product-price {
        font-size: 1.5rem;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 15px;
    }
    
    .product-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-cart {
        flex: 1;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s;
    }
    
    .btn-cart:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }
    
    .btn-favorite {
        background: #ff4757;
        color: white;
        border: none;
        width: 45px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 1.2rem;
    }
    
    .btn-favorite:hover {
        background: #ff6b81;
        transform: scale(1.1);
    }
    
    /* YENİ: Yorumlar Bölümü */
    .comments-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 60px 20px;
        border-radius: 20px;
        margin: 60px 0;
    }
    
    .comments-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .comments-header h2 {
        font-size: 2.2rem;
        color: #333;
        margin-bottom: 15px;
    }
    
    .comments-header p {
        color: #666;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .comments-list {
        max-width: 800px;
        margin: 0 auto 40px;
    }
    
    .comment-item {
        background: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        animation: fadeIn 0.5s ease-out;
        transition: all 0.3s;
    }
    
    .comment-item:hover {
        transform: translateX(10px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    
    .comment-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    
    .comment-user {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }
    
    .user-name {
        font-weight: bold;
        color: #333;
    }
    
    .comment-date {
        color: #888;
        font-size: 0.9rem;
    }
    
    .comment-rating {
        color: #ffc107;
        font-size: 1.2rem;
    }
    
    .comment-text {
        color: #555;
        line-height: 1.6;
        font-size: 1rem;
    }
    
    .add-comment-form {
        max-width: 600px;
        margin: 40px auto 0;
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #333;
    }
    
    .comment-textarea {
        width: 100%;
        padding: 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        resize: vertical;
        min-height: 120px;
        font-family: inherit;
        transition: border-color 0.3s;
    }
    
    .comment-textarea:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .rating-stars {
        display: flex;
        gap: 5px;
        font-size: 1.8rem;
        color: #ddd;
        cursor: pointer;
    }
    
    .rating-stars .star {
        transition: all 0.3s;
    }
    
    .rating-stars .star:hover,
    .rating-stars .star.active {
        color: #ffc107;
        transform: scale(1.2);
    }
    
    .submit-comment {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        width: 100%;
        font-size: 1.1rem;
    }
    
    .submit-comment:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    
    .login-prompt {
        text-align: center;
        padding: 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    /* YENİ: Özellikler Bölümü */
    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin: 60px 0;
    }
    
    .feature-card {
        text-align: center;
        padding: 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
    }
    
    .feature-icon {
        font-size: 3rem;
        margin-bottom: 20px;
        display: inline-block;
        animation: bounce 2s infinite;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .hosgeldin h1 {
            font-size: 2.5rem;
        }
        
        .kategoriler {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
        
        .products-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container">
    <!-- Hoş Geldiniz Bölümü -->
    <section class="hosgeldin">
        <h1><?php echo $dil == 'tr' ? 'En Güzel Çiçekler Burada!' : 'The Most Beautiful Flowers Are Here!'; ?></h1>
        <p><?php echo $dil == 'tr' ? 'Taze çiçeklerle hayatınıza renk katın, sevdiklerinizi mutlu edin.' : 'Add color to your life with fresh flowers, make your loved ones happy.'; ?></p>
        <a href="urunler.php?sayfa=urunler" class="kategori-buton">
            <?php echo $dil == 'tr' ? 'Alışverişe Başla' : 'Start Shopping'; ?> →
        </a>
    </section>

    <!-- Kategoriler -->
    <section class="kategoriler">
        <div class="kategori-kart">
            <div class="kategori-ikon">🌹</div>
            <div class="kategori-baslik"><?php echo $dil == 'tr' ? 'Güller' : 'Roses'; ?></div>
            <div class="kategori-aciklama"><?php echo $dil == 'tr' ? 'Romantik ve özel güller' : 'Romantic and special roses'; ?></div>
            <a href="urunler.php?sayfa=urunler&kategori=gul" class="kategori-buton">
                <?php echo $dil == 'tr' ? 'Ürünleri Gör' : 'View Products'; ?>
            </a>
        </div>
        
        <div class="kategori-kart">
            <div class="kategori-ikon">💮</div>
            <div class="kategori-baslik"><?php echo $dil == 'tr' ? 'Orkideler' : 'Orchids'; ?></div>
            <div class="kategori-aciklama"><?php echo $dil == 'tr' ? 'Zarif orkideler' : 'Elegant orchids'; ?></div>
            <a href="urunler.php?sayfa=urunler&kategori=orkide" class="kategori-buton">
                <?php echo $dil == 'tr' ? 'Ürünleri Gör' : 'View Products'; ?>
            </a>
        </div>
        
        <div class="kategori-kart">
            <div class="kategori-ikon">🌷</div>
            <div class="kategori-baslik"><?php echo $dil == 'tr' ? 'Laleler' : 'Tulips'; ?></div>
            <div class="kategori-aciklama"><?php echo $dil == 'tr' ? 'Renkli laleler' : 'Colorful tulips'; ?></div>
            <a href="urunler.php?sayfa=urunler&kategori=lale" class="kategori-buton">
                <?php echo $dil == 'tr' ? 'Ürünleri Gör' : 'View Products'; ?>
            </a>
        </div>
        
        <div class="kategori-kart">
            <div class="kategori-ikon">💐</div>
            <div class="kategori-baslik"><?php echo $dil == 'tr' ? 'Buketler' : 'Bouquets'; ?></div>
            <div class="kategori-aciklama"><?php echo $dil == 'tr' ? 'Özel buketler' : 'Special bouquets'; ?></div>
            <a href="urunler.php?sayfa=urunler&kategori=buket" class="kategori-buton">
                <?php echo $dil == 'tr' ? 'Ürünleri Gör' : 'View Products'; ?>
            </a>
        </div>
    </section>

    <!-- YENİ: Öne Çıkan Ürünler -->
    <?php
    // Ürünleri yükle
    $urunler_dosya = 'urunler.json';
    if(file_exists($urunler_dosya)) {
        $urunler = json_decode(file_get_contents($urunler_dosya), true);
        $featured_products = array_slice($urunler, 0, 4);
        
        if(count($featured_products) > 0):
    ?>
    <section class="featured-products">
        <h2><?php echo $dil == 'tr' ? 'Öne Çıkan Ürünler' : 'Featured Products'; ?></h2>
        
        <div class="products-grid">
            <?php foreach($featured_products as $urun): 
                // Ürün adını ve açıklamayı dile göre al
                $urun_ad = ($dil == 'tr') ? ($urun['tr_ad'] ?? $urun['ad'] ?? 'Ürün') : ($urun['en_ad'] ?? $urun['ad'] ?? 'Product');
                $urun_aciklama = ($dil == 'tr') ? ($urun['tr_aciklama'] ?? $urun['aciklama'] ?? '') : ($urun['en_aciklama'] ?? $urun['aciklama'] ?? '');
                $kategori_ikon = '';
                
                switch($urun['kategori']) {
                    case 'gul': $kategori_ikon = '🌹'; break;
                    case 'orkide': $kategori_ikon = '💮'; break;
                    case 'lale': $kategori_ikon = '🌷'; break;
                    case 'buket': $kategori_ikon = '💐'; break;
                    case 'sukulent': $kategori_ikon = '🌵'; break;
                    default: $kategori_ikon = '🌸';
                }
            ?>
            <div class="product-card">
                <div class="product-image">
                    <?php echo $kategori_ikon; ?>
                </div>
                <div class="product-info">
                    <h3 class="product-title"><?php echo htmlspecialchars($urun_ad); ?></h3>
                    <p class="product-description"><?php echo htmlspecialchars($urun_aciklama); ?></p>
                    <div class="product-price"><?php echo number_format($urun['fiyat'], 2); ?> ₺</div>
                    <div class="product-actions">
                        <button class="btn-cart" onclick="addToCart(<?php echo $urun['id']; ?>)">
                            <?php echo $dil == 'tr' ? 'Sepete Ekle' : 'Add to Cart'; ?>
                        </button>
                        <button class="btn-favorite" onclick="addToFavorites(<?php echo $urun['id']; ?>)">❤️</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center;">
            <a href="urunler.php?sayfa=urunler" class="kategori-buton" style="font-size: 1.1rem; padding: 15px 40px;">
                <?php echo $dil == 'tr' ? 'Tüm Ürünleri Gör' : 'View All Products'; ?> →
            </a>
        </div>
    </section>
    <?php 
        endif;
    }
    ?>

    <!-- YENİ: Özellikler -->
    <section class="features">
        <div class="feature-card">
            <div class="feature-icon">🚚</div>
            <h3><?php echo $dil == 'tr' ? 'Hızlı Teslimat' : 'Fast Delivery'; ?></h3>
            <p><?php echo $dil == 'tr' ? 'Aynı gün teslimat' : 'Same day delivery'; ?></p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">💐</div>
            <h3><?php echo $dil == 'tr' ? 'Taze Çiçekler' : 'Fresh Flowers'; ?></h3>
            <p><?php echo $dil == 'tr' ? 'Her gün taze çiçekler' : 'Fresh flowers every day'; ?></p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">🎁</div>
            <h3><?php echo $dil == 'tr' ? 'Hediye Paketi' : 'Gift Package'; ?></h3>
            <p><?php echo $dil == 'tr' ? 'Ücretsiz hediye paketi' : 'Free gift packaging'; ?></p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">💳</div>
            <h3><?php echo $dil == 'tr' ? 'Güvenli Ödeme' : 'Secure Payment'; ?></h3>
            <p><?php echo $dil == 'tr' ? 'Güvenli ödeme seçenekleri' : 'Secure payment options'; ?></p>
        </div>
    </section>

    <!-- YENİ: Yorumlar Bölümü -->
    <section class="comments-section">
        <div class="comments-header">
            <h2><?php echo $dil == 'tr' ? 'Müşteri Yorumları' : 'Customer Reviews'; ?></h2>
            <p><?php echo $dil == 'tr' ? 'Müşterilerimizin deneyimlerini okuyun' : 'Read our customers experiences'; ?></p>
        </div>

        <div class="comments-list" id="comments-container">
            <!-- Yorumlar buraya JavaScript ile yüklenecek -->
            <p style="text-align: center; color: #666;">
                <?php echo $dil == 'tr' ? 'Yorumlar yükleniyor...' : 'Loading comments...'; ?>
            </p>
        </div>

        <?php if(kullaniciGirisKontrol()): ?>
        <div class="add-comment-form">
            <h3><?php echo $dil == 'tr' ? 'Yorum Yap' : 'Add Review'; ?></h3>
            <form id="comment-form" onsubmit="submitComment(event, 'anasayfa')">
                <div class="form-group">
                    <label class="form-label"><?php echo $dil == 'tr' ? 'Yorumunuz' : 'Your Review'; ?></label>
                    <textarea class="comment-textarea" name="comment" placeholder="<?php echo $dil == 'tr' ? 'Deneyiminizi paylaşın...' : 'Share your experience...'; ?>" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><?php echo $dil == 'tr' ? 'Puan' : 'Rating'; ?></label>
                    <div class="rating-stars">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                    <input type="hidden" name="rating" id="rating-input" value="5">
                </div>
                
                <button type="submit" class="submit-comment">
                    <?php echo $dil == 'tr' ? 'Yorumu Gönder' : 'Submit Review'; ?>
                </button>
            </form>
        </div>
        <?php else: ?>
        <div class="login-prompt">
            <p><?php echo $dil == 'tr' ? 'Yorum yapmak için giriş yapın' : 'Please login to add a review'; ?></p>
            <a href="#" onclick="acModal(); return false;" class="kategori-buton">
                <?php echo $dil == 'tr' ? 'Giriş Yap' : 'Login'; ?>
            </a>
        </div>
        <?php endif; ?>
    </section>
</div>

<script>
// Sepete ekle
function addToCart(productId) {
    fetch('sepet.php?action=add&id=' + productId)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showNotification('<?php echo $dil == "tr" ? "Ürün sepete eklendi!" : "Product added to cart!"; ?>', 'success');
                // Sepet sayacını güncelle
                const counter = document.querySelector('.sepet-sayaci');
                if(counter) {
                    counter.textContent = parseInt(counter.textContent || 0) + 1;
                }
            }
        });
}

// Favorilere ekle
function addToFavorites(productId) {
    fetch('favoriler.php?action=add&id=' + productId)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showNotification('<?php echo $dil == "tr" ? "Favorilere eklendi!" : "Added to favorites!"; ?>', 'success');
            }
        });
}

// Yıldız puanlama
document.querySelectorAll('.rating-stars .star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.getAttribute('data-rating');
        document.getElementById('rating-input').value = rating;
        
        // Aktif yıldızları güncelle
        document.querySelectorAll('.rating-stars .star').forEach((s, index) => {
            if(index < rating) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    });
});

// Yorumları yükle
function loadComments() {
    fetch('comments_handler.php?action=get&urun_id=anasayfa')
        .then(response => response.json())
        .then(comments => {
            const container = document.getElementById('comments-container');
            if(!container) return;
            
            container.innerHTML = '';
            
            if(!comments || comments.length === 0) {
                container.innerHTML = '<p class="no-comments" style="text-align: center; color: #666; padding: 40px;">' + 
                    '<?php echo $dil == "tr" ? "Henüz yorum yapılmamış. İlk yorumu siz yapın!" : "No comments yet. Be the first to comment!"; ?>' + 
                    '</p>';
                return;
            }
            
            comments.forEach(comment => {
                const firstLetter = comment.user_name ? comment.user_name.charAt(0).toUpperCase() : '?';
                const stars = '★'.repeat(comment.rating) + '☆'.repeat(5 - comment.rating);
                const commentHTML = `
                    <div class="comment-item">
                        <div class="comment-header">
                            <div class="comment-user">
                                <div class="user-avatar">${firstLetter}</div>
                                <div>
                                    <div class="user-name">${comment.user_name || 'Anonim'}</div>
                                    <div class="comment-date">${comment.date}</div>
                                </div>
                            </div>
                            <div class="comment-rating">${stars}</div>
                        </div>
                        <div class="comment-text">${comment.comment}</div>
                    </div>
                `;
                container.insertAdjacentHTML('afterbegin', commentHTML);
            });
        })
        .catch(error => {
            console.error('Yorumlar yüklenirken hata:', error);
            const container = document.getElementById('comments-container');
            if(container) {
                container.innerHTML = '<p style="text-align: center; color: #666;"><?php echo $dil == "tr" ? "Yorumlar yüklenemedi." : "Failed to load comments."; ?></p>';
            }
        });
}

// Yorum gönder
function submitComment(event, urunId = 'anasayfa') {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    formData.append('action', 'add');
    formData.append('urun_id', urunId);
    
    fetch('comments_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            form.reset();
            loadComments();
            showNotification('<?php echo $dil == "tr" ? "Yorumunuz gönderildi!" : "Your comment has been sent!"; ?>', 'success');
            // Yıldızları sıfırla
            document.querySelectorAll('.rating-stars .star').forEach((star, index) => {
                if(index < 5) star.classList.add('active');
                else star.classList.remove('active');
            });
            document.getElementById('rating-input').value = 5;
        } else {
            showNotification(data.message || '<?php echo $dil == "tr" ? "Bir hata oluştu!" : "An error occurred!"; ?>', 'error');
        }
    })
    .catch(error => {
        showNotification('<?php echo $dil == "tr" ? "Bir hata oluştu!" : "An error occurred!"; ?>', 'error');
        console.error('Yorum gönderilirken hata:', error);
    });
}

// Bildirim göster
function showNotification(message, type = 'info') {
    // Toast mesajı göster (header.php'deki toast sistemini kullan)
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

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Yorumları yükle
    loadComments();
    
    // İlk yıldızları aktif et
    document.querySelectorAll('.rating-stars .star').forEach((star, index) => {
        if(index < 5) {
            star.classList.add('active');
        }
    });
});
</script>

<?php require_once 'footer.php'; ?>