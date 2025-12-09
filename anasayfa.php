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
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    /* ÖNE ÇIKANLAR Bölümü - YENİ TASARIM */
    .featured-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 60px 20px;
        border-radius: 20px;
        margin: 60px 0;
        text-align: center;
    }
    
    .section-title {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
        padding-bottom: 15px;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 3px;
        background: linear-gradient(to right, #ff6b9d, #764ba2);
        border-radius: 2px;
    }
    
    .section-subtitle {
        color: #666;
        max-width: 600px;
        margin: 0 auto 40px;
        font-size: 1.1rem;
        line-height: 1.6;
    }
    
    .featured-products-simple {
        display: flex;
        justify-content: center;
        gap: 30px;
        flex-wrap: wrap;
        margin: 30px 0 40px;
    }
    
    .featured-product-item {
        background: white;
        padding: 25px;
        border-radius: 15px;
        min-width: 250px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .featured-product-item:hover {
        transform: translateY(-10px) scale(1.05);
        box-shadow: 0 20px 35px rgba(0,0,0,0.12);
    }
    
    .featured-product-icon {
        font-size: 3.5rem;
        margin-bottom: 15px;
        animation: pulse 2s infinite;
    }
    
    .featured-product-name {
        font-size: 1.3rem;
        font-weight: bold;
        color: #333;
        text-align: center;
        margin-bottom: 10px;
    }
    
    .featured-product-price {
        font-size: 1.5rem;
        font-weight: bold;
        color: #ff6b9d;
        margin-bottom: 20px;
    }
    
    /* Sepete Ekle ve Hızlı Al Butonları */
    .product-action-buttons {
        display: flex;
        gap: 10px;
        width: 100%;
    }
    
    .btn-add-to-cart {
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
        font-size: 0.95rem;
    }
    
    .btn-add-to-cart:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }
    
    .btn-quick-buy {
        flex: 1;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        font-size: 0.95rem;
        text-decoration: none;
    }
    
    .btn-quick-buy:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }
    
    /* Yorumlar Bölümü */
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
    
    /* Özellikler Bölümü */
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
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .hosgeldin h1 {
            font-size: 2.5rem;
        }
        
        .kategoriler {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
        
        .section-title {
            font-size: 2rem;
        }
        
        .featured-products-simple {
            flex-direction: column;
            align-items: center;
        }
        
        .featured-product-item {
            width: 100%;
            max-width: 300px;
        }
        
        .product-action-buttons {
            flex-direction: column;
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

    <!-- ÖNE ÇIKAN ÜRÜNLER (YENİ TASARIM) -->
    <section class="featured-section">
        <h2 class="section-title"><?php echo $dil == 'tr' ? 'ÖNE ÇIKANLAR' : 'FEATURED PRODUCTS'; ?></h2>
        <p class="section-subtitle"><?php echo $dil == 'tr' ? 'En çok tercih edilen özel çiçeklerimiz' : 'Our most preferred special flowers'; ?></p>
        
        <div class="featured-products-simple">
            <!-- Renk Karışık Lale Demeti -->
            <div class="featured-product-item" data-product-id="1" data-price="199.99">
                <div class="featured-product-icon">🌷</div>
                <h3 class="featured-product-name"><?php echo $dil == 'tr' ? 'Renk Karışık Lale Demeti' : 'Mixed Color Tulip Bouquet'; ?></h3>
                <div class="featured-product-price">199,99 ₺</div>
                <div class="product-action-buttons">
                    <button class="btn-add-to-cart" onclick="addToCart(1)">
                        🛒 <?php echo $dil == 'tr' ? 'Sepete Ekle' : 'Add to Cart'; ?>
                    </button>
                    <a href="#" class="btn-quick-buy" onclick="quickBuy(1, event)">
                        ⚡ <?php echo $dil == 'tr' ? 'Hızlı Al' : 'Quick Buy'; ?>
                    </a>
                </div>
            </div>
            
            <!-- Aşk Buketi -->
            <div class="featured-product-item" data-product-id="4" data-price="249.99">
                <div class="featured-product-icon">💝</div>
                <h3 class="featured-product-name"><?php echo $dil == 'tr' ? 'Aşk Buketi' : 'Love Bouquet'; ?></h3>
                <div class="featured-product-price">249,99 ₺</div>
                <div class="product-action-buttons">
                    <button class="btn-add-to-cart" onclick="addToCart(4)">
                        🛒 <?php echo $dil == 'tr' ? 'Sepete Ekle' : 'Add to Cart'; ?>
                    </button>
                    <a href="#" class="btn-quick-buy" onclick="quickBuy(4, event)">
                        ⚡ <?php echo $dil == 'tr' ? 'Hızlı Al' : 'Quick Buy'; ?>
                    </a>
                </div>
            </div>
            
            <!-- Karışık Gül Buketi -->
            <div class="featured-product-item" data-product-id="1" data-price="299.99">
                <div class="featured-product-icon">🌹</div>
                <h3 class="featured-product-name"><?php echo $dil == 'tr' ? 'Karışık Gül Buketi' : 'Mixed Rose Bouquet'; ?></h3>
                <div class="featured-product-price">299,99 ₺</div>
                <div class="product-action-buttons">
                    <button class="btn-add-to-cart" onclick="addToCart(1)">
                        🛒 <?php echo $dil == 'tr' ? 'Sepete Ekle' : 'Add to Cart'; ?>
                    </button>
                    <a href="#" class="btn-quick-buy" onclick="quickBuy(1, event)">
                        ⚡ <?php echo $dil == 'tr' ? 'Hızlı Al' : 'Quick Buy'; ?>
                    </a>
                </div>
            </div>
        </div>
        
        <p style="color: #666; max-width: 600px; margin: 20px auto 0;">
            <?php echo $dil == 'tr' 
                ? 'Bu özel ürünlerimizi keşfedin ve sevdiklerinize unutulmaz bir sürpriz yapın!' 
                : 'Discover these special products and surprise your loved ones with an unforgettable gift!'; ?>
        </p>
    </section>

    <!-- Özellikler -->
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

    <!-- Yorumlar Bölümü -->
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
            <a href="#" onclick="acModal(); acModalTab('giris'); return false;" class="kategori-buton">
                <?php echo $dil == 'tr' ? 'Giriş Yap' : 'Login'; ?>
            </a>
        </div>
        <?php endif; ?>
    </section>
</div>

<script>
// Sepete ekle fonksiyonu
function addToCart(productId) {
    if(!<?php echo kullaniciGirisKontrol() ? 'true' : 'false'; ?>) {
        showNotification('<?php echo $dil == "tr" ? "Sepete ürün eklemek için giriş yapmalısınız!" : "You must login to add items to cart!"; ?>', 'warning');
        acModal();
        acModalTab('giris');
        return;
    }
    
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
    } else {
        // Sepet sayacı yoksa oluştur
        const sepetIkonu = document.querySelector('.sepet-ikonu');
        if(sepetIkonu) {
            const newCounter = document.createElement('span');
            newCounter.className = 'sepet-sayaci animated-bounce';
            newCounter.textContent = '1';
            sepetIkonu.appendChild(newCounter);
        }
    }
}

// Hızlı Al fonksiyonu
function quickBuy(productId, event) {
    event.preventDefault();
    
    if(!<?php echo kullaniciGirisKontrol() ? 'true' : 'false'; ?>) {
        showNotification('<?php echo $dil == "tr" ? "Hızlı alışveriş için giriş yapmalısınız!" : "You must login for quick buy!"; ?>', 'warning');
        acModal();
        acModalTab('giris');
        return;
    }
    
    // Ürünü sepete ekle
    fetch('sepet.php?action=add&id=' + productId)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showNotification('<?php echo $dil == "tr" ? "Ürün sepete eklendi, ödeme sayfasına yönlendiriliyorsunuz!" : "Product added to cart, redirecting to payment!"; ?>', 'success');
                
                // Ödeme sayfasına yönlendir
                setTimeout(() => {
                    window.location.href = 'odeme.php?sayfa=odeme';
                }, 1000);
            } else {
                showNotification(data.message || '<?php echo $dil == "tr" ? "Bir hata oluştu!" : "An error occurred!"; ?>', 'error');
            }
        })
        .catch(error => {
            showNotification('<?php echo $dil == "tr" ? "Bir hata oluştu!" : "An error occurred!"; ?>', 'error');
            console.error('Hızlı al hatası:', error);
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

// Yorumları yükle - HERKESE GÖSTER
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

// Yorum gönder - SADECE GİRİŞ YAPMIŞ KULLANICILAR
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
    // Yorumları yükle (HERKESE GÖSTER)
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