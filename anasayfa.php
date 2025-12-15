<?php
require_once 'header.php';

// Dil ayarı
$dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';

// Mevcut sayfa bilgisi
$sayfa = 'anasayfa';

// Favori kontrol fonksiyonu
function favoriKontrol($urun_id) {
    return isset($_SESSION['favoriler']) && in_array($urun_id, $_SESSION['favoriler']);
}

// Öne çıkan ürünler
$urunler = [
    [
        'id' => 1,
        'ad' => 'Kırmızı Gül Buketi',
        'aciklama' => '12 adet taze kırmızı gül, zarif paketleme',
        'fiyat' => 129.99,
        'indirim' => 10,
        'kategori' => 'gul',
        'renk' => 'linear-gradient(135deg, #ff6b6b 0%, #ff8fab 100%)',
        'stok' => 15,
        'degerlendirme' => 4.8
    ],
    [
        'id' => 2,
        'ad' => 'Beyaz Orkide',
        'aciklama' => 'Lüks beyaz orkide, saksılı',
        'fiyat' => 199.99,
        'indirim' => 0,
        'kategori' => 'orkide',
        'renk' => 'linear-gradient(135deg, #4ecdc4 0%, #88d3ce 100%)',
        'stok' => 8,
        'degerlendirme' => 4.9
    ],
    [
        'id' => 3,
        'ad' => 'Renkli Lale Demeti',
        'aciklama' => '5 renkli lale demeti, bahar havası',
        'fiyat' => 89.99,
        'indirim' => 15,
        'kategori' => 'lale',
        'renk' => 'linear-gradient(135deg, #ffd166 0%, #ffed99 100%)',
        'stok' => 20,
        'degerlendirme' => 4.7
    ],
    [
        'id' => 4,
        'ad' => 'Sukulent Seti',
        'aciklama' => '3 adet minyatür sukulent, teraryum',
        'fiyat' => 69.99,
        'indirim' => 20,
        'kategori' => 'sukulent',
        'renk' => 'linear-gradient(135deg, #06d6a0 0%, #83e9d3 100%)',
        'stok' => 25,
        'degerlendirme' => 4.6
    ]
];

// Özel teklifler
$teklifler = [
    [
        'baslik' => 'Aşk Buketi',
        'aciklama' => 'Romantik güllerle özel buket',
        'fiyat' => 149.99,
        'indirim' => 25,
        'renk' => 'linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%)',
        'icon' => '❤️'
    ],
    [
        'baslik' => 'Doğum Günü',
        'aciklama' => 'Renkli çiçeklerle mutluluk',
        'fiyat' => 179.99,
        'indirim' => 20,
        'renk' => 'linear-gradient(135deg, #ffd166 0%, #ffed99 100%)',
        'icon' => '🎂'
    ],
    [
        'baslik' => 'Tebrik Buketi',
        'aciklama' => 'Başarıları kutlamak için',
        'fiyat' => 159.99,
        'indirim' => 15,
        'renk' => 'linear-gradient(135deg, #4ecdc4 0%, #88d3ce 100%)',
        'icon' => '🏆'
    ],
    [
        'baslik' => 'Anma Buketi',
        'aciklama' => 'Hüzünlü anlar için',
        'fiyat' => 139.99,
        'indirim' => 10,
        'renk' => 'linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%)',
        'icon' => '🕊️'
    ]
];

// Kategoriler
$kategoriler = [
    ['id' => 'gul', 'ad' => 'Güller', 'icon' => '🌹', 'renk' => '#ff6b6b'],
    ['id' => 'orkide', 'ad' => 'Orkideler', 'icon' => '💮', 'renk' => '#4ecdc4'],
    ['id' => 'lale', 'ad' => 'Laleler', 'icon' => '🌷', 'renk' => '#ffd166'],
    ['id' => 'buket', 'ad' => 'Buketler', 'icon' => '💐', 'renk' => '#ff6b9d'],
    ['id' => 'sukulent', 'ad' => 'Sukulentler', 'icon' => '🌵', 'renk' => '#06d6a0'],
];
?>

<style>
    /* MODERN ANA SAYFA STİLLERİ - GÜNCELLENMİŞ */
    
    /* HERO SECTION - IMPROVED */
    .hero-section {
        position: relative;
        height: 85vh;
        min-height: 650px;
        overflow: hidden;
        border-radius: 0 0 50px 50px;
        margin-bottom: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-align: center;
        background: linear-gradient(135deg, 
                    rgba(255, 107, 157, 0.85), 
                    rgba(255, 143, 171, 0.85)),
                    url('https://images.unsplash.com/photo-1452827073306-6e6e661baf57?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-blend-mode: overlay;
        z-index: 2;
    }
    
    .hero-content {
        position: relative;
        z-index: 3;
        max-width: 900px;
        padding: 0 20px;
        animation: fadeInUp 1s ease-out;
    }
    
    .hero-title {
        font-family: 'Dancing Script', cursive;
        font-size: 5.5rem;
        font-weight: 700;
        margin-bottom: 25px;
        text-shadow: 3px 3px 12px rgba(0,0,0,0.3);
        animation: float 3s ease-in-out infinite;
        line-height: 1.1;
    }
    
    .hero-subtitle {
        font-size: 1.4rem;
        margin-bottom: 40px;
        opacity: 0.95;
        line-height: 1.7;
        text-shadow: 1px 1px 5px rgba(0,0,0,0.2);
        font-weight: 300;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .hero-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        animation: fadeInUp 1s ease-out 0.3s both;
    }
    
    .hero-button {
        padding: 18px 40px;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: inline-flex;
        align-items: center;
        gap: 12px;
        font-size: 1.1rem;
        position: relative;
        overflow: hidden;
        z-index: 1;
        backdrop-filter: blur(5px);
    }
    
    .hero-button::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
        transform: translateX(-100%);
        transition: transform 0.6s;
        z-index: -1;
    }
    
    .hero-button:hover::after {
        transform: translateX(100%);
    }
    
    .hero-button.primary {
        background: white;
        color: #ff6b9d;
        box-shadow: 0 15px 35px rgba(255, 107, 157, 0.3);
    }
    
    .hero-button.secondary {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(10px);
    }
    
    .hero-button:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 20px 40px rgba(255, 107, 157, 0.4);
    }
    
    /* UÇUŞAN ÇİÇEK ANİMASYONLARI */
    .floating-flowers-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        pointer-events: none;
        z-index: 1;
        overflow: hidden;
    }
    
    .floating-flower {
        position: absolute;
        font-size: 24px;
        opacity: 0;
        animation: floatUp linear infinite;
        z-index: 1;
        filter: drop-shadow(0 2px 8px rgba(0,0,0,0.2));
    }
    
    /* Her çiçek için farklı animasyon süresi ve yol */
    @keyframes floatUp {
        0% {
            transform: translateY(100vh) rotate(0deg) scale(0.5);
            opacity: 0;
        }
        10% {
            opacity: 0.7;
        }
        90% {
            opacity: 0.7;
        }
        100% {
            transform: translateY(-100px) rotate(360deg) scale(1);
            opacity: 0;
        }
    }
    
    /* Çiçek renk efektleri */
    .flower-pink { color: #ff6b9d; }
    .flower-red { color: #ff4757; }
    .flower-yellow { color: #ffd166; }
    .flower-green { color: #06d6a0; }
    .flower-purple { color: #6c5ce7; }
    .flower-blue { color: #4ecdc4; }
    .flower-white { color: #ffffff; }
    
    /* Sayfa içeriği için z-index ayarı */
    body > .container,
    .features-section,
    .products-section,
    .offers-section,
    .stats-section,
    .newsletter-section,
    .testimonials-section {
        position: relative;
        z-index: 2;
        background: white;
    }
    
    /* KATEGORİLER SECTION */
    .categories-section {
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
        position: relative;
        z-index: 2;
        background: white;
    }
    
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }
    
    .category-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 30px rgba(255, 107, 157, 0.1);
        border: 2px solid transparent;
        cursor: pointer;
        position: relative;
        z-index: 2;
    }
    
    .category-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(255, 107, 157, 0.2);
        border-color: #ff6b9d;
    }
    
    .category-icon {
        font-size: 50px;
        margin-bottom: 15px;
        display: block;
        transition: transform 0.3s;
    }
    
    .category-card:hover .category-icon {
        transform: scale(1.2);
    }
    
    .category-name {
        font-size: 1.3rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
    }
    
    .category-count {
        color: #ff6b9d;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    /* FEATURES SECTION - IMPROVED */
    .features-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 30px;
        margin: 80px auto;
        max-width: 1200px;
        padding: 0 20px;
    }
    
    .feature-card {
        background: linear-gradient(145deg, #ffffff, #f5f5f5);
        padding: 40px 30px;
        border-radius: 25px;
        text-align: center;
        box-shadow: 20px 20px 60px rgba(255, 107, 157, 0.1),
                    -20px -20px 60px rgba(255, 255, 255, 0.8);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 107, 157, 0.1);
        z-index: 2;
    }
    
    .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(90deg, #ff6b9d, #ff8fab);
    }
    
    .feature-card:hover {
        transform: translateY(-15px);
        box-shadow: 25px 25px 70px rgba(255, 107, 157, 0.15),
                    -25px -25px 70px rgba(255, 255, 255, 0.9);
    }
    
    .feature-icon {
        font-size: 65px;
        margin-bottom: 25px;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
        transition: transform 0.3s;
    }
    
    .feature-card:hover .feature-icon {
        transform: scale(1.1);
    }
    
    /* PRODUCTS SECTION - IMPROVED */
    .products-section {
        background: linear-gradient(135deg, rgba(255, 245, 247, 0.95), rgba(255, 238, 242, 0.95));
        backdrop-filter: blur(10px);
        border-radius: 40px;
        padding: 70px 40px;
        margin: 80px auto;
        max-width: 1200px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 107, 157, 0.2);
        z-index: 2;
    }
    
    .products-section::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -100px;
        width: 300px;
        height: 300px;
        background: linear-gradient(135deg, #ff6b9d22, #ff8fab22);
        border-radius: 50%;
        z-index: 0;
    }
    
    .products-section::after {
        content: '';
        position: absolute;
        bottom: -100px;
        left: -100px;
        width: 400px;
        height: 400px;
        background: linear-gradient(135deg, #4ecdc422, #88d3ce22);
        border-radius: 50%;
        z-index: 0;
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 50px;
        position: relative;
        z-index: 1;
    }
    
    .section-title {
        font-size: 2.8rem;
        font-weight: 800;
        color: #333;
        display: flex;
        align-items: center;
        gap: 15px;
        background: linear-gradient(135deg, #ff6b9d, #ff8fab);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .section-subtitle {
        color: #666;
        font-size: 1.1rem;
        margin-top: 10px;
        max-width: 600px;
    }
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        position: relative;
        z-index: 1;
    }
    
    .product-card {
        background: white;
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(255, 107, 157, 0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        border: 1px solid rgba(255, 107, 157, 0.1);
    }
    
    .product-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 25px 50px rgba(255, 107, 157, 0.2);
    }
    
    .product-image {
        height: 220px;
        background: var(--product-color, linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 80px;
        color: white;
        position: relative;
        overflow: hidden;
    }
    
    .product-image::before {
        content: '';
        position: absolute;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1%, transparent 20%);
        animation: rotate 20s linear infinite;
    }
    
    .product-badges {
        position: absolute;
        top: 15px;
        left: 15px;
        display: flex;
        gap: 8px;
        z-index: 2;
    }
    
    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
    }
    
    .badge.discount {
        background: #ff4757;
    }
    
    .badge.new {
        background: #4ecdc4;
    }
    
    .badge.stock {
        background: #ffd166;
        color: #333;
    }
    
    .product-info {
        padding: 25px;
    }
    
    .product-rating {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        color: #ffd166;
    }
    
    .rating-count {
        color: #666;
        font-size: 0.9rem;
    }
    
    .product-price {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .current-price {
        font-size: 1.8rem;
        font-weight: 800;
        color: #ff6b9d;
    }
    
    .old-price {
        font-size: 1.1rem;
        color: #999;
        text-decoration: line-through;
    }
    
    /* OFFERS SECTION - IMPROVED */
    .offers-section {
        max-width: 1200px;
        margin: 80px auto;
        padding: 0 20px;
    }
    
    .offers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
        gap: 25px;
    }
    
    .offer-card {
        border-radius: 25px;
        padding: 40px 30px;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        transition: all 0.4s;
        height: 250px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .offer-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 30px 60px rgba(0,0,0,0.15);
    }
    
    .offer-icon {
        font-size: 50px;
        margin-bottom: 15px;
        opacity: 0.9;
    }
    
    /* STATS SECTION */
    .stats-section {
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        border-radius: 40px;
        padding: 70px 40px;
        margin: 80px auto;
        max-width: 1200px;
        color: white;
        text-align: center;
        position: relative;
        z-index: 2;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 30px;
        margin-top: 50px;
    }
    
    .stat-card {
        padding: 30px;
    }
    
    .stat-number {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 10px;
    }
    
    .stat-label {
        font-size: 1.2rem;
        opacity: 0.9;
    }
    
    /* NEWSLETTER SECTION */
    .newsletter-section {
        background: linear-gradient(135deg, #4ecdc4 0%, #88d3ce 100%);
        border-radius: 40px;
        padding: 70px 40px;
        margin: 80px auto;
        max-width: 1200px;
        color: white;
        text-align: center;
        position: relative;
        z-index: 2;
    }
    
    .newsletter-form {
        max-width: 500px;
        margin: 40px auto 0;
        display: flex;
        gap: 15px;
    }
    
    .newsletter-input {
        flex: 1;
        padding: 18px 25px;
        border: none;
        border-radius: 50px;
        font-size: 1rem;
        outline: none;
    }
    
    /* TESTIMONIALS - IMPROVED */
    .testimonials-section {
        background: white;
        border-radius: 40px;
        padding: 70px 40px;
        margin: 80px auto;
        max-width: 1200px;
        box-shadow: 0 25px 70px rgba(255, 107, 157, 0.1);
        position: relative;
        overflow: hidden;
        z-index: 2;
    }
    
    .testimonials-section::before {
        content: '""';
        position: absolute;
        top: 30px;
        right: 30px;
        font-size: 200px;
        font-family: 'Dancing Script', cursive;
        color: rgba(255, 107, 157, 0.05);
        transform: rotate(15deg);
    }
    
    .testimonial-card {
        background: linear-gradient(145deg, #f8f9fa, #ffffff);
        padding: 40px;
        border-radius: 25px;
        position: relative;
        border-left: 5px solid #ff6b9d;
        box-shadow: 10px 10px 30px rgba(0,0,0,0.05);
    }
    
    .testimonial-rating {
        color: #ffd166;
        margin-bottom: 20px;
    }
    
    /* YORUM EKLE BUTONU STİLLERİ */
    .yorum-ekle-header-btn {
        display: flex;
        align-items: center;
    }
    
    .yorum-ekle-btn-header {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 25px;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        box-shadow: 0 5px 15px rgba(255, 107, 157, 0.3);
        font-size: 1rem;
    }
    
    .yorum-ekle-btn-header:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(255, 107, 157, 0.4);
    }
    
    /* ANIMATIONS */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes float {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-15px);
        }
    }
    
    @keyframes rotate {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
    
    /* RESPONSIVE */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 3.5rem;
        }
        
        .hero-section {
            height: 70vh;
            min-height: 500px;
            border-radius: 0 0 30px 30px;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
        }
        
        .hero-buttons {
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        
        .hero-button {
            width: 100%;
            max-width: 300px;
            justify-content: center;
            padding: 16px 30px;
        }
        
        .section-header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }
        
        .section-title {
            font-size: 2.2rem;
        }
        
        .products-section,
        .testimonials-section,
        .stats-section,
        .newsletter-section {
            padding: 40px 20px;
            margin: 40px auto;
            border-radius: 30px;
        }
        
        .categories-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .newsletter-form {
            flex-direction: column;
        }
        
        .floating-flower {
            font-size: 18px;
        }
        
        .yorum-ekle-header-btn {
            margin-top: 10px;
        }
        
        .yorum-ekle-baloncuk {
            bottom: -15px;
            right: 20px;
        }
        
        .yorum-ekle-baloncuk-btn {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }
    }
    
    @media (max-width: 480px) {
        .hero-title {
            font-size: 2.8rem;
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
        }
        
        .categories-grid {
            grid-template-columns: 1fr;
        }
        
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .offers-grid {
            grid-template-columns: 1fr;
        }
        
        .floating-flower {
            font-size: 16px;
        }
    }
</style>

<!-- UÇUŞAN ÇİÇEKLER KONTEYNERI -->
<div class="floating-flowers-container" id="flowersContainer"></div>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">
            <?php echo $dil == 'tr' ? 'Çiçeklerle Gelen Mutluluk' : 'Happiness Through Flowers'; ?>
        </h1>
        <p class="hero-subtitle">
            <?php echo $dil == 'tr' 
                ? 'En taze çiçekler, özenle hazırlanmış aranjmanlar ve sevdiklerinize özel anlar yaratmak için buradayız. Her sipariş bir sanat eseridir.' 
                : 'The freshest flowers, carefully prepared arrangements, and we are here to create special moments for your loved ones. Every order is a work of art.'; 
            ?>
        </p>
        <div class="hero-buttons">
            <a href="urunler.php" class="hero-button primary">
                <i class="fas fa-store"></i>
                <?php echo $dil == 'tr' ? 'Hemen Alışveriş Yap' : 'Shop Now'; ?>
            </a>
            <?php if(!$is_logged_in): ?>
                <a href="auth.php" class="hero-button secondary">
                    <i class="fas fa-user-plus"></i>
                    <?php echo $dil == 'tr' ? 'Ücretsiz Üye Ol' : 'Join Free'; ?>
                </a>
            <?php else: ?>
                <a href="profil.php" class="hero-button secondary">
                    <i class="fas fa-gift"></i>
                    <?php echo $dil == 'tr' ? 'Puanlarımı Kullan' : 'Use My Points'; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- KATEGORİLER -->
<div class="container">
    <div class="categories-section">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="fas fa-th-large"></i>
                    <?php echo $dil == 'tr' ? 'Kategoriler' : 'Categories'; ?>
                </h2>
                <p class="section-subtitle">
                    <?php echo $dil == 'tr' 
                        ? 'Favori çiçek türlerinizi keşfedin' 
                        : 'Discover your favorite flower types'; 
                    ?>
                </p>
            </div>
        </div>
        
        <div class="categories-grid">
            <?php foreach($kategoriler as $kategori): ?>
                <a href="urunler.php?kategori=<?php echo $kategori['id']; ?>" class="category-card">
                    <span class="category-icon"><?php echo $kategori['icon']; ?></span>
                    <h3 class="category-name"><?php echo $kategori['ad']; ?></h3>
                    <span class="category-count"><?php echo $dil == 'tr' ? 'Ürünleri Gör' : 'View Products'; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- AVANTAJLAR -->
    <div class="features-section">
        <div class="feature-card">
            <div class="feature-icon">🚚</div>
            <h3 class="feature-title">
                <?php echo $dil == 'tr' ? 'Ücretsiz Kargo' : 'Free Shipping'; ?>
            </h3>
            <p class="feature-desc">
                <?php echo $dil == 'tr' 
                    ? '150 TL ve üzeri tüm siparişlerde ücretsiz teslimat' 
                    : 'Free delivery on all orders over 150 TL'; 
                ?>
            </p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">💯</div>
            <h3 class="feature-title">
                <?php echo $dil == 'tr' ? 'Kalite Garantisi' : 'Quality Guarantee'; ?>
            </h3>
            <p class="feature-desc">
                <?php echo $dil == 'tr' 
                    ? 'Tüm çiçeklerimiz taze kesim, memnun kalmazsanız iade' 
                    : 'All our flowers are fresh cut, return if not satisfied'; 
                ?>
            </p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">🎁</div>
            <h3 class="feature-title">
                <?php echo $dil == 'tr' ? 'Özel Hediye Paketi' : 'Special Gift Package'; ?>
            </h3>
            <p class="feature-desc">
                <?php echo $dil == 'tr' 
                    ? 'Ücretsiz hediye paketi ve kişisel not ekleme' 
                    : 'Free gift package and personal note addition'; 
                ?>
            </p>
        </div>
    </div>

    <!-- ÖNE ÇIKAN ÜRÜNLER -->
    <div class="products-section">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="fas fa-crown"></i>
                    <?php echo $dil == 'tr' ? 'En Çok Satanlar' : 'Best Sellers'; ?>
                </h2>
                <p class="section-subtitle">
                    <?php echo $dil == 'tr' 
                        ? 'Müşterilerimizin en çok tercih ettiği çiçekler' 
                        : 'Most preferred flowers by our customers'; 
                    ?>
                </p>
            </div>
            <a href="urunler.php" class="view-all">
                <?php echo $dil == 'tr' ? 'Tüm Ürünler' : 'All Products'; ?>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="products-grid">
            <?php foreach($urunler as $urun): 
                $indirimli_fiyat = $urun['indirim'] > 0 ? 
                    $urun['fiyat'] * (100 - $urun['indirim']) / 100 : 
                    $urun['fiyat'];
                $favori_durumu = favoriKontrol($urun['id']) ? 'active' : '';
                
                // Emoji ikonları
                $urun_emoji = [
                    'gul' => '🌹',
                    'orkide' => '💮',
                    'lale' => '🌷',
                    'buket' => '💐',
                    'sukulent' => '🌵'
                ];
                $emoji = $urun_emoji[$urun['kategori']] ?? '🌸';
            ?>
                <div class="product-card">
                    <div class="product-image" style="--product-color: <?php echo $urun['renk']; ?>;">
                        <?php echo $emoji; ?>
                        
                        <div class="product-badges">
                            <?php if($urun['indirim'] > 0): ?>
                                <span class="badge discount">-%<?php echo $urun['indirim']; ?></span>
                            <?php endif; ?>
                            <?php if($urun['stok'] < 10): ?>
                                <span class="badge stock"><?php echo $dil == 'tr' ? 'Son ' . $urun['stok'] : 'Last ' . $urun['stok']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="product-info">
                        <div class="product-rating">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <?php if($i <= floor($urun['degerlendirme'])): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif($i - 0.5 <= $urun['degerlendirme']): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <span class="rating-count">(<?php echo $urun['degerlendirme']; ?>)</span>
                        </div>
                        
                        <h3 class="product-name"><?php echo htmlspecialchars($urun['ad']); ?></h3>
                        
                        <div class="product-price">
                            <?php if($urun['indirim'] > 0): ?>
                                <span class="current-price"><?php echo number_format($indirimli_fiyat, 2); ?> ₺</span>
                                <span class="old-price"><?php echo number_format($urun['fiyat'], 2); ?> ₺</span>
                            <?php else: ?>
                                <span class="current-price"><?php echo number_format($urun['fiyat'], 2); ?> ₺</span>
                            <?php endif; ?>
                        </div>
                        
                        <p style="color: #666; font-size: 0.95rem; margin-bottom: 25px; line-height: 1.5;">
                            <?php echo htmlspecialchars($urun['aciklama']); ?>
                        </p>
                        
                        <div class="product-actions">
                            <a href="sepet.php?action=ekle&urun_id=<?php echo $urun['id']; ?>" 
                               class="add-to-cart"
                               onclick="return confirmAddToCart(<?php echo $urun['id']; ?>, '<?php echo addslashes($urun['ad']); ?>')">
                                <i class="fas fa-shopping-cart"></i>
                                <?php echo $dil == 'tr' ? 'Sepete Ekle' : 'Add to Cart'; ?>
                            </a>
                            <a href="favoriler.php?action=<?php echo $favori_durumu ? 'cikar' : 'ekle'; ?>&urun_id=<?php echo $urun['id']; ?>" 
                               class="favorite-btn <?php echo $favori_durumu; ?>"
                               title="<?php echo $favori_durumu ? ($dil == 'tr' ? 'Favorilerden Çıkar' : 'Remove from Favorites') : ($dil == 'tr' ? 'Favorilere Ekle' : 'Add to Favorites'); ?>">
                                <i class="fas fa-heart"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ÖZEL TEKLİFLER -->
    <div class="offers-section">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="fas fa-gift"></i>
                    <?php echo $dil == 'tr' ? 'Özel Günler için' : 'For Special Days'; ?>
                </h2>
                <p class="section-subtitle">
                    <?php echo $dil == 'tr' 
                        ? 'Özel anlarınız için hazırlanmış teklifler' 
                        : 'Special offers prepared for your special moments'; 
                    ?>
                </p>
            </div>
        </div>
        
        <div class="offers-grid">
            <?php foreach($teklifler as $teklif): ?>
                <div class="offer-card" style="background: <?php echo $teklif['renk']; ?>;">
                    <div class="offer-icon"><?php echo $teklif['icon']; ?></div>
                    <div>
                        <h3 class="offer-title"><?php echo $teklif['baslik']; ?></h3>
                        <p><?php echo $teklif['aciklama']; ?></p>
                    </div>
                    <div class="offer-price">
                        <span style="font-size: 2.2rem; font-weight: 800;"><?php echo number_format($teklif['fiyat'] * (100 - $teklif['indirim']) / 100, 2); ?> ₺</span>
                        <span class="offer-old-price"><?php echo number_format($teklif['fiyat'], 2); ?> ₺</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- İSTATİSTİKLER -->
    <div class="stats-section">
        <h2 style="font-size: 2.5rem; margin-bottom: 20px;"><?php echo $dil == 'tr' ? 'Güvenin Rakamları' : 'Numbers of Trust'; ?></h2>
        <p style="font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">
            <?php echo $dil == 'tr' 
                ? 'Yıllardır süren tecrübemiz ve mutlu müşterilerimiz' 
                : 'Our years of experience and happy customers'; 
            ?>
        </p>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">5000+</div>
                <div class="stat-label"><?php echo $dil == 'tr' ? 'Mutlu Müşteri' : 'Happy Customers'; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number">150+</div>
                <div class="stat-label"><?php echo $dil == 'tr' ? 'Çiçek Çeşidi' : 'Flower Varieties'; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number">24/7</div>
                <div class="stat-label"><?php echo $dil == 'tr' ? 'Müşteri Desteği' : 'Customer Support'; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-number">%99</div>
                <div class="stat-label"><?php echo $dil == 'tr' ? 'Memnuniyet Oranı' : 'Satisfaction Rate'; ?></div>
            </div>
        </div>
    </div>

    <!-- BÜLTEN KAYIT -->
    <div class="newsletter-section">
        <h2 style="font-size: 2.5rem; margin-bottom: 20px;"><?php echo $dil == 'tr' ? 'Güncel Kalın' : 'Stay Updated'; ?></h2>
        <p style="font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">
            <?php echo $dil == 'tr' 
                ? 'Özel tekliflerden ve yeni ürünlerden haberdar olmak için kayıt olun' 
                : 'Sign up to be notified of special offers and new products'; 
            ?>
        </p>
        
        <form class="newsletter-form" onsubmit="return subscribeNewsletter()">
            <input type="email" class="newsletter-input" placeholder="<?php echo $dil == 'tr' ? 'E-posta adresiniz' : 'Your email address'; ?>" required>
            <button type="submit" class="hero-button primary" style="white-space: nowrap;">
                <i class="fas fa-paper-plane"></i>
                <?php echo $dil == 'tr' ? 'Abone Ol' : 'Subscribe'; ?>
            </button>
        </form>
    </div>

        <!-- MÜŞTERİ YORUMLARI -->
    <div class="testimonials-section">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    <i class="fas fa-comment-dots"></i>
                    <?php echo $dil == 'tr' ? 'Müşterilerimiz Ne Diyor?' : 'What Our Customers Say?'; ?>
                </h2>
                <p class="section-subtitle">
                    <?php echo $dil == 'tr' 
                        ? 'Binlerce mutlu müşterinin deneyimleri' 
                        : 'Experiences of thousands of happy customers'; 
                    ?>
                </p>
            </div>
            
            <!-- YORUM EKLE BUTONU - HER ZAMAN GÖRÜNÜR -->
            <div class="yorum-ekle-header-btn">
                <?php if($is_logged_in): ?>
                    <!-- Giriş yapmış kullanıcılar için -->
                    <a href="yorumlar.php" class="yorum-ekle-btn-header">
                        <i class="fas fa-plus-circle"></i>
                        <?php echo $dil == 'tr' ? 'Yorum Ekle' : 'Add Review'; ?>
                    </a>
                <?php else: ?>
                    <!-- Giriş yapmamış kullanıcılar için -->
                    <a href="auth.php" class="yorum-ekle-btn-header">
                        <i class="fas fa-sign-in-alt"></i>
                        <?php echo $dil == 'tr' ? 'Yorum Yap' : 'Write Review'; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="testimonials">
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    "<?php echo $dil == 'tr' 
                        ? 'Anneme doğum günü sürprizi yaptım. Çiçekler taze, paketleme harikaydı. Teslimat zamanında geldi. Kesinlikle tavsiye ederim!' 
                        : 'I surprised my mother for her birthday. The flowers were fresh, the packaging was amazing. Delivery arrived on time. Definitely recommend!'; 
                    ?>"
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">EZ</div>
                    <div class="author-info">
                        <h4 style="color: #333;">Elif Z.</h4>
                        <p style="color: #666;"><?php echo $dil == 'tr' ? '2 gün önce' : '2 days ago'; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
                <p class="testimonial-text">
                    "<?php echo $dil == 'tr' 
                        ? 'İş yerine gönderdim, herkes çok beğendi. Profesyonel ve hızlı hizmet. Çiçekler 1 haftadan fazla taze kaldı.' 
                        : 'I sent it to the office, everyone loved it. Professional and fast service. The flowers stayed fresh for over a week.'; 
                    ?>"
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">MA</div>
                    <div class="author-info">
                        <h4 style="color: #333;">Mehmet A.</h4>
                        <p style="color: #666;"><?php echo $dil == 'tr' ? '1 hafta önce' : '1 week ago'; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    "<?php echo $dil == 'tr' 
                        ? '3 yıldır düzenli alışveriş yapıyorum. Her seferinde mükemmel! Çiçekler haftalarca taze kaldı. Artık tek tercihim.' 
                        : 'I\'ve been shopping regularly for 3 years. Perfect every time! The flowers stayed fresh for weeks. Now my only choice.'; 
                    ?>"
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">AY</div>
                    <div class="author-info">
                        <h4 style="color: #333;">Ayşe Y.</h4>
                        <p style="color: #666;"><?php echo $dil == 'tr' ? '3 gün önce' : '3 days ago'; ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
        
        <!-- YORUM EKLE BALONCUĞU -->
        <div class="yorum-ekle-baloncuk">
            <?php if($is_logged_in): ?>
                <!-- Giriş yapmış kullanıcılar için -->
                <a href="yorumlar.php" class="yorum-ekle-baloncuk-btn">
                    <i class="fas fa-pen"></i>
                </a>
            <?php else: ?>
                <!-- Giriş yapmamış kullanıcılar için -->
                <a href="auth.php" class="yorum-ekle-baloncuk-btn">
                    <i class="fas fa-sign-in-alt"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// UÇUŞAN ÇİÇEKLER OLUŞTUR
document.addEventListener('DOMContentLoaded', function() {
    const flowersContainer = document.getElementById('flowersContainer');
    const flowerEmojis = ['💐', '🌱', '🌸', '🌻', '🌼', '🌼', '💐', '🌿', '🌿', '🌺', '🥀', '🌹', '🌼', '🌱', '🥀', '💐', '🌺', '🌹', '🥀', '🌷', '💐', '🌿', '🌿', '💐', '🥀'];
    const flowerColors = ['flower-pink', 'flower-red', 'flower-yellow', 'flower-green', 'flower-purple', 'flower-blue', 'flower-white'];
    
    // Çiçek sayısını ayarla (istediğiniz kadar artırabilirsiniz)
    const totalFlowers = 30;
    
    // Her çiçek için özel animasyon oluştur
    for (let i = 0; i < totalFlowers; i++) {
        const flower = document.createElement('div');
        flower.className = 'floating-flower';
        
        // Rastgele emoji seç
        const emojiIndex = i % flowerEmojis.length;
        flower.innerHTML = flowerEmojis[emojiIndex];
        
        // Rastgele renk seç
        const colorIndex = Math.floor(Math.random() * flowerColors.length);
        flower.classList.add(flowerColors[colorIndex]);
        
        // Rastgele pozisyon ve animasyon özellikleri
        const left = Math.random() * 100; // 0-100%
        const duration = 15 + Math.random() * 25; // 15-40 saniye
        const delay = Math.random() * 5; // 0-5 saniye gecikme
        const size = 20 + Math.random() * 30; // 20-50px
        const startX = (Math.random() * 60) - 30; // -30px ile +30px arası
        const endX = (Math.random() * 60) - 30; // -30px ile +30px arası
        
        // Stilleri ayarla
        flower.style.left = `${left}%`;
        flower.style.fontSize = `${size}px`;
        flower.style.animationDelay = `${delay}s`;
        flower.style.animationIterationCount = 'infinite';
        
        // Özel keyframes CSS oluştur
        const styleId = `flower-animation-${i}`;
        let existingStyle = document.getElementById(styleId);
        if (!existingStyle) {
            existingStyle = document.createElement('style');
            existingStyle.id = styleId;
            document.head.appendChild(existingStyle);
        }
        
        const keyframes = `
            @keyframes flowerFloat${i} {
                0% {
                    transform: translate(${startX}px, 100vh) rotate(0deg) scale(0.5);
                    opacity: 0;
                }
                10% {
                    opacity: 0.7;
                    transform: translate(${startX * 0.8}px, 80vh) rotate(${72}deg) scale(0.7);
                }
                30% {
                    opacity: 0.8;
                    transform: translate(${startX * 0.6}px, 60vh) rotate(${144}deg) scale(0.9);
                }
                50% {
                    opacity: 0.9;
                    transform: translate(${startX * 0.3}px, 40vh) rotate(${216}deg) scale(1);
                }
                70% {
                    opacity: 0.8;
                    transform: translate(${endX * 0.3}px, 20vh) rotate(${288}deg) scale(1);
                }
                90% {
                    opacity: 0.7;
                    transform: translate(${endX}px, 0vh) rotate(${360}deg) scale(0.9);
                }
                100% {
                    transform: translate(${endX}px, -100px) rotate(${360 + Math.random() * 180}deg) scale(0.8);
                    opacity: 0;
                }
            }
            
            .flower-animation-${i} {
                animation: flowerFloat${i} ${duration}s linear infinite ${delay}s;
            }
        `;
        
        existingStyle.textContent = keyframes;
        flower.classList.add(`flower-animation-${i}`);
        
        flowersContainer.appendChild(flower);
    }
    
    // Fare hareketiyle çiçeklere etkileşim
    document.addEventListener('mousemove', function(e) {
        const flowers = document.querySelectorAll('.floating-flower');
        const mouseX = e.clientX;
        const mouseY = e.clientY;
        
        flowers.forEach(flower => {
            const rect = flower.getBoundingClientRect();
            const flowerX = rect.left + rect.width / 2;
            const flowerY = rect.top + rect.height / 2;
            
            const distance = Math.sqrt(
                Math.pow(mouseX - flowerX, 2) + 
                Math.pow(mouseY - flowerY, 2)
            );
            
            if (distance < 150) {
                // Fareye doğru hafif çekim efekti
                const force = (150 - distance) / 150 * 0.3;
                const angle = Math.atan2(mouseY - flowerY, mouseX - flowerX);
                const moveX = Math.cos(angle) * force * 5;
                const moveY = Math.sin(angle) * force * 5;
                
                flower.style.transform += ` translate(${moveX}px, ${moveY}px)`;
                
                // 0.5 saniye sonra eski haline dön
                setTimeout(() => {
                    flower.style.transform = flower.style.transform.replace(/translate\([^)]+\)/g, '');
                }, 500);
            }
        });
    });
    
    // Periyodik olarak rüzgar efekti
    setInterval(() => {
        if (Math.random() > 0.8) { // %20 şans
            const flowers = document.querySelectorAll('.floating-flower');
            const windForce = (Math.random() * 15) - 7.5; // -7.5 ile +7.5 arası
            
            flowers.forEach(flower => {
                flower.style.transform += ` translateX(${windForce}px)`;
                
                setTimeout(() => {
                    flower.style.transform = flower.style.transform.replace(/translateX\([^)]+\)/g, '');
                }, 2000);
            });
        }
    }, 5000);
});

// Sepete ekle fonksiyonu
function confirmAddToCart(productId, productName) {
    <?php if(!$is_logged_in): ?>
        showNotification('<?php echo $dil == 'tr' ? "Sepete ürün eklemek için giriş yapmalısınız!" : "You must login to add products to cart!" ?>', 'info', () => {
            window.location.href = 'auth.php';
        });
        return false;
    <?php else: ?>
        if(confirm('<?php echo $dil == 'tr' ? "Sepete eklemek istediğinize emin misiniz?" : "Are you sure you want to add to cart?" ?>')) {
            // AJAX ile sepete ekle
            fetch(`sepet.php?action=ekle&urun_id=${productId}`)
                .then(response => response.text())
                .then(data => {
                    showNotification('✅ ' + productName + ' <?php echo $dil == 'tr' ? "sepete eklendi!" : "added to cart!" ?>', 'success');
                    updateCartCounter();
                })
                .catch(error => {
                    showNotification('❌ <?php echo $dil == 'tr' ? "Bir hata oluştu!" : "An error occurred!" ?>', 'error');
                });
        }
        return false;
    <?php endif; ?>
}

// Sepet sayacını güncelle
function updateCartCounter() {
    const counter = document.querySelector('.sepet-sayaci');
    if(counter) {
        let count = parseInt(counter.textContent) || 0;
        counter.textContent = count + 1;
        counter.style.display = 'inline-block';
        
        // Animasyon
        counter.style.animation = 'none';
        setTimeout(() => {
            counter.style.animation = 'bounce 0.5s';
        }, 10);
    }
}

// Bildirim göster
function showNotification(message, type = 'success', callback = null) {
    // Mevcut bildirimleri temizle
    document.querySelectorAll('.custom-notification').forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `custom-notification ${type}`;
    notification.innerHTML = `
        <div style="
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: slideInRight 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 400px;
            cursor: pointer;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
        ">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span style="flex: 1;">${message}</span>
            <i class="fas fa-times" onclick="this.parentElement.parentElement.remove()"></i>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Tıklama ile kapat
    notification.querySelector('div').addEventListener('click', function(e) {
        if(!e.target.closest('.fa-times')) {
            if(callback) callback();
            notification.remove();
        }
    });
    
    // 4 saniye sonra otomatik kapat
    setTimeout(() => {
        if(notification.parentElement) {
            notification.style.animation = 'slideOutRight 0.3s';
            setTimeout(() => notification.remove(), 300);
        }
    }, 4000);
}

// Bülten kaydı
function subscribeNewsletter() {
    const email = document.querySelector('.newsletter-input').value;
    
    if(!email) {
        showNotification('<?php echo $dil == 'tr' ? "Lütfen e-posta adresinizi girin" : "Please enter your email address" ?>', 'info');
        return false;
    }
    
    // Email validasyonu
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailRegex.test(email)) {
        showNotification('<?php echo $dil == 'tr' ? "Geçerli bir e-posta adresi girin" : "Please enter a valid email address" ?>', 'info');
        return false;
    }
    
    showNotification('✅ <?php echo $dil == 'tr' ? "Bültenimize kaydolduğunuz için teşekkürler!" : "Thank you for subscribing to our newsletter!" ?>', 'success');
    document.querySelector('.newsletter-input').value = '';
    
    return false;
}

// CSS animasyonları ekle
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.3); }
    }
    
    .animate-in {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .sepet-sayaci {
        transition: transform 0.3s;
    }
    
    .custom-notification {
        font-family: 'Poppins', sans-serif;
    }
`;
document.head.appendChild(style);

// Sayfa yüklendiğinde animasyonları başlat
setTimeout(() => {
    document.querySelectorAll('.feature-card, .product-card, .offer-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
}, 500);
</script>

<?php require_once 'footer.php'; ?>