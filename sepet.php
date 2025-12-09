<?php
require_once 'cicek.php';
require_once 'header.php';

// Sepet işlemleri
if(isset($_GET['action'])) {
    switch($_GET['action']) {
        case 'add':
            if(isset($_GET['id'])) {
                $product_id = intval($_GET['id']);
                $urunler = json_decode(file_get_contents('urunler.json'), true);
                $urun = null;
                
                foreach($urunler as $u) {
                    if($u['id'] == $product_id) {
                        $urun = $u;
                        break;
                    }
                }
                
                if($urun) {
                    // Sepet yoksa oluştur
                    if(!isset($_SESSION['sepet'])) {
                        $_SESSION['sepet'] = [];
                    }
                    
                    // Ürün sepette var mı kontrol et
                    $found = false;
                    foreach($_SESSION['sepet'] as &$item) {
                        if($item['id'] == $product_id) {
                            $item['miktar']++;
                            $found = true;
                            break;
                        }
                    }
                    
                    // Yoksa yeni ekle
                    if(!$found) {
                        $_SESSION['sepet'][] = [
                            'id' => $urun['id'],
                            'ad' => $dil == 'tr' ? ($urun['tr_ad'] ?? $urun['ad']) : ($urun['en_ad'] ?? $urun['ad']),
                            'fiyat' => $urun['fiyat'],
                            'resim' => $urun['resim'] ?? '',
                            'miktar' => 1
                        ];
                    }
                    
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı']);
                }
            }
            exit;
            
        case 'remove':
            if(isset($_GET['id']) && isset($_SESSION['sepet'])) {
                $product_id = intval($_GET['id']);
                foreach($_SESSION['sepet'] as $key => $item) {
                    if($item['id'] == $product_id) {
                        unset($_SESSION['sepet'][$key]);
                        $_SESSION['sepet'] = array_values($_SESSION['sepet']); // Re-index
                        break;
                    }
                }
                // JavaScript ile yönlendirme
                echo '<script>window.location.href = "sepet.php";</script>';
                exit;
            }
            break;
            
        case 'update':
            if(isset($_POST['miktar']) && isset($_SESSION['sepet'])) {
                foreach($_POST['miktar'] as $id => $quantity) {
                    $id = intval(str_replace('product_', '', $id));
                    $quantity = intval($quantity);
                    
                    foreach($_SESSION['sepet'] as &$item) {
                        if($item['id'] == $id) {
                            $item['miktar'] = max(1, $quantity);
                            break;
                        }
                    }
                }
                // JavaScript ile yönlendirme
                echo '<script>window.location.href = "sepet.php";</script>';
                exit;
            }
            break;
            
        case 'clear':
            if(isset($_SESSION['sepet'])) {
                $_SESSION['sepet'] = [];
            }
            // JavaScript ile yönlendirme
            echo '<script>window.location.href = "sepet.php";</script>';
            exit;
            
        case 'count':
            // Sepet sayısını döndür
            $count = isset($_SESSION['sepet']) ? count($_SESSION['sepet']) : 0;
            echo json_encode(['count' => $count]);
            exit;
    }
}

// Sepet toplamını hesapla
function sepetToplam() {
    if(!isset($_SESSION['sepet']) || empty($_SESSION['sepet'])) {
        return 0;
    }
    
    $toplam = 0;
    foreach($_SESSION['sepet'] as $item) {
        $toplam += $item['fiyat'] * $item['miktar'];
    }
    return $toplam;
}
?>

<style>
    /* Sepet Sayfası Özel Stilleri - PEMBE TEMA */
    .cart-page {
        padding: 40px 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .cart-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .cart-header h1 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .cart-header p {
        color: #666;
        opacity: 0.8;
    }
    
    /* Sepet Durumu */
    .cart-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        animation: fadeIn 0.5s ease-out;
    }
    
    .cart-count {
        font-size: 1.2rem;
    }
    
    .cart-total {
        font-size: 1.5rem;
        font-weight: bold;
    }
    
    /* Boş Sepet */
    .empty-cart {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        animation: fadeIn 0.5s ease-out;
    }
    
    .empty-cart-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .empty-cart h2 {
        color: #333;
        margin-bottom: 15px;
    }
    
    .empty-cart p {
        color: #666;
        opacity: 0.7;
        margin-bottom: 30px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .btn-shopping {
        display: inline-block;
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
        padding: 15px 30px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s;
        border: 2px solid transparent;
        font-size: 1.1rem;
    }
    
    .btn-shopping:hover {
        background: white;
        color: #ff6b9d;
        border-color: #ff6b9d;
        transform: translateY(-3px);
    }
    
    /* Sepet Tablosu */
    .cart-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        overflow: hidden;
        animation: fadeIn 0.5s ease-out;
    }
    
    .cart-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .cart-table thead {
        background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
        color: white;
    }
    
    .cart-table th {
        padding: 20px;
        text-align: left;
        font-weight: 600;
    }
    
    .cart-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.3s;
    }
    
    .cart-table tbody tr:hover {
        background: #f9f9f9;
    }
    
    .cart-table td {
        padding: 20px;
        vertical-align: middle;
    }
    
    /* Ürün Hücresi */
    .cart-product {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .product-image {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
    }
    
    .product-info h3 {
        margin: 0 0 5px 0;
        color: #333;
        font-size: 1.1rem;
    }
    
    .product-info .price {
        color: #ff6b9d;
        font-weight: bold;
        font-size: 1.2rem;
    }
    
    /* Miktar Kontrolleri */
    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .quantity-btn {
        width: 35px;
        height: 35px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .quantity-btn:hover {
        background: #f5f5f5;
        border-color: #ff6b9d;
    }
    
    .quantity-input {
        width: 60px;
        height: 35px;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-align: center;
        font-size: 1rem;
    }
    
    /* Kaldır Butonu */
    .remove-btn {
        background: #ff4757;
        color: white;
        border: none;
        width: 35px;
        height: 35px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1.2rem;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .remove-btn:hover {
        background: #ff6b81;
        transform: scale(1.1);
    }
    
    /* Sepet Özeti */
    .cart-summary {
        margin-top: 30px;
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        animation: fadeIn 0.5s ease-out 0.2s both;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .summary-item:last-child {
        border-bottom: none;
        font-weight: bold;
        font-size: 1.2rem;
        color: #ff6b9d;
    }
    
    /* Butonlar */
    .cart-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        flex-wrap: wrap;
    }
    
    .btn-continue {
        background: #6c757d;
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .btn-continue:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    .btn-update {
        background: #17a2b8;
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .btn-update:hover {
        background: #138496;
        transform: translateY(-2px);
    }
    
    .btn-clear {
        background: #6c757d;
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .btn-clear:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    .btn-checkout {
        flex: 1;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
        padding: 18px 30px;
        border-radius: 25px;
        font-weight: bold;
        cursor: pointer;
        font-size: 1.1rem;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .btn-checkout:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2);
    }
    
    /* Animasyonlar */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .cart-table thead {
            display: none;
        }
        
        .cart-table tbody tr {
            display: block;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
        }
        
        .cart-table td {
            display: block;
            text-align: left;
            padding: 10px 0;
            border: none;
        }
        
        .cart-table td::before {
            content: attr(data-label);
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #ff6b9d;
        }
        
        .cart-product {
            flex-direction: column;
            text-align: center;
            gap: 10px;
        }
        
        .product-image {
            width: 120px;
            height: 120px;
        }
        
        .cart-actions {
            flex-direction: column;
        }
        
        .cart-actions a,
        .cart-actions button {
            width: 100%;
            text-align: center;
        }
        
        .btn-continue,
        .btn-update,
        .btn-clear,
        .btn-checkout {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="cart-page">
    <div class="cart-header">
        <h1>🛒 <?php echo $dil == 'tr' ? 'Sepetim' : 'My Cart'; ?></h1>
        <p><?php echo $dil == 'tr' ? 'Sepetinizdeki ürünleri gözden geçirin' : 'Review the products in your cart'; ?></p>
    </div>

    <?php if(empty($_SESSION['sepet'])): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <h2><?php echo $dil == 'tr' ? 'Sepetiniz Boş' : 'Your Cart is Empty'; ?></h2>
            <p><?php echo $dil == 'tr' ? 'Harika ürünlerimizi keşfetmek için alışverişe başlayın!' : 'Start shopping to discover our amazing products!'; ?></p>
            <a href="urunler.php?sayfa=urunler" class="btn-shopping">
                <?php echo $dil == 'tr' ? 'Alışverişe Başla' : 'Start Shopping'; ?> →
            </a>
        </div>
    <?php else: ?>
        <!-- Sepet Durumu -->
        <div class="cart-status">
            <div class="cart-count">
                <?php 
                $toplam_urun = 0;
                foreach($_SESSION['sepet'] as $item) {
                    $toplam_urun += $item['miktar'];
                }
                echo $dil == 'tr' 
                    ? "{$toplam_urun} ürün" 
                    : "{$toplam_urun} items";
                ?>
            </div>
            <div class="cart-total">
                <?php echo number_format(sepetToplam(), 2); ?> ₺
            </div>
        </div>

        <!-- Sepet Tablosu -->
        <form method="post" action="sepet.php?action=update">
            <div class="cart-container">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th><?php echo $dil == 'tr' ? 'Ürün' : 'Product'; ?></th>
                            <th><?php echo $dil == 'tr' ? 'Fiyat' : 'Price'; ?></th>
                            <th><?php echo $dil == 'tr' ? 'Miktar' : 'Quantity'; ?></th>
                            <th><?php echo $dil == 'tr' ? 'Toplam' : 'Total'; ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $toplam = 0;
                        foreach($_SESSION['sepet'] as $item): 
                            $urun_toplam = $item['fiyat'] * $item['miktar'];
                            $toplam += $urun_toplam;
                        ?>
                        <tr>
                            <td data-label="<?php echo $dil == 'tr' ? 'Ürün' : 'Product'; ?>">
                                <div class="cart-product">
                                    <div class="product-image">
                                        <?php 
                                        // Kategoriye göre ikon
                                        $kategori_ikon = '🌸';
                                        switch($item['kategori'] ?? '') {
                                            case 'gul': $kategori_ikon = '🌹'; break;
                                            case 'orkide': $kategori_ikon = '💮'; break;
                                            case 'lale': $kategori_ikon = '🌷'; break;
                                            case 'buket': $kategori_ikon = '💐'; break;
                                            case 'sukulent': $kategori_ikon = '🌵'; break;
                                        }
                                        echo $kategori_ikon;
                                        ?>
                                    </div>
                                    <div class="product-info">
                                        <h3><?php echo htmlspecialchars($item['ad']); ?></h3>
                                        <div class="price"><?php echo number_format($item['fiyat'], 2); ?> ₺</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="<?php echo $dil == 'tr' ? 'Fiyat' : 'Price'; ?>">
                                <?php echo number_format($item['fiyat'], 2); ?> ₺
                            </td>
                            <td data-label="<?php echo $dil == 'tr' ? 'Miktar' : 'Quantity'; ?>">
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn minus" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                    <input type="number" 
                                           name="miktar[product_<?php echo $item['id']; ?>]" 
                                           value="<?php echo $item['miktar']; ?>" 
                                           min="1" 
                                           class="quantity-input"
                                           onchange="this.form.submit()">
                                    <button type="button" class="quantity-btn plus" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                </div>
                            </td>
                            <td data-label="<?php echo $dil == 'tr' ? 'Toplam' : 'Total'; ?>">
                                <?php echo number_format($urun_toplam, 2); ?> ₺
                            </td>
                            <td>
                                <a href="sepet.php?action=remove&id=<?php echo $item['id']; ?>" 
                                   class="remove-btn" 
                                   onclick="return confirm('<?php echo $dil == 'tr' ? 'Bu ürünü sepetten çıkarmak istediğinize emin misiniz?' : 'Are you sure you want to remove this item?'; ?>')">
                                    ×
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sepet Özeti -->
            <div class="cart-summary">
                <h3><?php echo $dil == 'tr' ? 'Sepet Özeti' : 'Cart Summary'; ?></h3>
                
                <div class="summary-item">
                    <span><?php echo $dil == 'tr' ? 'Ara Toplam' : 'Subtotal'; ?></span>
                    <span><?php echo number_format($toplam, 2); ?> ₺</span>
                </div>
                
                <div class="summary-item">
                    <span><?php echo $dil == 'tr' ? 'Kargo' : 'Shipping'; ?></span>
                    <span><?php echo $dil == 'tr' ? 'Ücretsiz' : 'Free'; ?></span>
                </div>
                
                <div class="summary-item">
                    <span><?php echo $dil == 'tr' ? 'Toplam' : 'Total'; ?></span>
                    <span><?php echo number_format($toplam, 2); ?> ₺</span>
                </div>
                
                <div class="cart-actions">
                    <a href="urunler.php?sayfa=urunler" class="btn-continue">
                        ← <?php echo $dil == 'tr' ? 'Alışverişe Devam Et' : 'Continue Shopping'; ?>
                    </a>
                    
                    <button type="submit" class="btn-update">
                        🔄 <?php echo $dil == 'tr' ? 'Sepeti Güncelle' : 'Update Cart'; ?>
                    </button>
                    
                    <a href="sepet.php?action=clear" 
                       class="btn-clear"
                       onclick="return confirm('<?php echo $dil == 'tr' ? 'Sepetinizi tamamen boşaltmak istediğinize emin misiniz?' : 'Are you sure you want to clear your cart?'; ?>')">
                        🗑️ <?php echo $dil == 'tr' ? 'Sepeti Temizle' : 'Clear Cart'; ?>
                    </a>
                    
                    <a href="odeme.php?sayfa=odeme" class="btn-checkout">
                        ✅ <?php echo $dil == 'tr' ? 'Ödemeye Geç' : 'Proceed to Checkout'; ?> →
                    </a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
// Miktar güncelleme
function updateQuantity(productId, change) {
    const input = document.querySelector(`input[name="miktar[product_${productId}]"]`);
    if(!input) return;
    
    let newValue = parseInt(input.value) + change;
    
    if(newValue < 1) newValue = 1;
    
    input.value = newValue;
    
    // Otomatik submit
    setTimeout(() => {
        if(input.form) {
            input.form.submit();
        }
    }, 300);
}

// Sepet güncelleme animasyonu
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        if(this.value < 1) this.value = 1;
    });
});

// Miktar değişikliğinde otomatik submit
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        if(this.form && this.value >= 1) {
            this.form.submit();
        }
    });
});
</script>

<?php require_once 'footer.php'; ?>