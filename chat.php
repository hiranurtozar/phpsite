<?php
session_start();

// JSON dosyası
$chat_file = 'chat.json';

// Dosya yoksa oluştur
if(!file_exists($chat_file)) {
    file_put_contents($chat_file, json_encode([]));
}

// Otomatik yanıtları belirleme fonksiyonu
function getAutoResponse($message) {
    $message = strtolower(trim($message));
    
    // Anahtar kelimeler ve yanıtlar
    $responses = [
        'iade' => [
            'keywords' => ['iade', 'geri iade', 'ürün iadesi', 'para iadesi', 'iade etmek'],
            'response' => 'İade işlemleri için lütfen "Hesabım > Siparişlerim" bölümünden iade talebi oluşturun. İade koşullarımız hakkında detaylı bilgi için yardım merkezimizi ziyaret edebilirsiniz.'
        ],
        'sipariş' => [
            'keywords' => ['sipariş', 'siparişim', 'sipariş ver', 'ürün sipariş', 'nasıl sipariş'],
            'response' => 'Sipariş vermek için ürün sayfasındaki "Sepete Ekle" butonunu kullanabilirsiniz. Sipariş süreci hakkında detaylı bilgi için "Sıkça Sorulan Sorular" bölümümüze göz atabilirsiniz.'
        ],
        'takip' => [
            'keywords' => ['takip', 'kargo takip', 'sipariş takip', 'nerede', 'kargo', 'kargom nerede'],
            'response' => 'Siparişinizin durumunu "Hesabım > Siparişlerim" bölümünden takip edebilirsiniz. Kargo takip numaranızı buradan görebilirsiniz.'
        ],
        'ürün' => [
            'keywords' => ['ürün', 'fiyat', 'indirim', 'kampanya', 'stok', 'stokta var mı'],
            'response' => 'Ürünlerimiz hakkında detaylı bilgi için ürün sayfalarını ziyaret edebilirsiniz. Stok durumu ve fiyat bilgileri ürün sayfalarında güncel olarak yer almaktadır.'
        ],
        'teslimat' => [
            'keywords' => ['teslimat', 'teslim süresi', 'ne zaman gelir', 'kargo süresi'],
            'response' => 'Standart teslimat süremiz 3-7 iş günüdür. Kargo firmasına göre değişiklik gösterebilir. Detaylı bilgi için "Teslimat Bilgileri" sayfamızı ziyaret edebilirsiniz.'
        ],
        'yardım' => [
            'keywords' => ['yardım', 'help', 'destek', 'iletişim', 'numara', 'telefon'],
            'response' => 'Size nasıl yardımcı olabilirim? İade, sipariş takibi, ürün bilgisi veya teslimat konularında sorularınız için bana yazabilirsiniz. Acil durumlarda 0850 XXX XX XX numaralı destek hattımızı arayabilirsiniz.'
        ]
    ];
    
    // Mesajı kontrol et ve uygun yanıtı bul
    foreach($responses as $category) {
        foreach($category['keywords'] as $keyword) {
            if(strpos($message, $keyword) !== false) {
                return $category['response'];
            }
        }
    }
    
    // Eşleşme yoksa varsayılan yanıt
    return 'Mesajınız için teşekkür ederiz. Size nasıl yardımcı olabilirim? İade, sipariş takibi, ürün bilgisi veya teslimat konularında sorularınız için bana yazabilirsiniz.';
}

// Mesaj gönder
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'send') {
    if(!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Giriş yapmalısınız']);
        exit;
    }
    
    $messages = json_decode(file_get_contents($chat_file), true);
    $user_message = htmlspecialchars(trim($_POST['message']));
    
    // Kullanıcı mesajını kaydet
    $new_message = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['ad_soyad'] ?? 'Misafir',
        'message' => $user_message,
        'time' => date('H:i:s'),
        'date' => date('Y-m-d'),
        'type' => 'user'
    ];
    
    $messages[] = $new_message;
    
    // Otomatik yanıt üret (mesaj boş değilse ve belirli bir uzunluktaysa)
    if(!empty($user_message) && strlen($user_message) > 2) {
        $auto_response = getAutoResponse($user_message);
        
        $bot_message = [
            'id' => uniqid('bot_'),
            'user_id' => 'bot',
            'user_name' => 'Destek Botu',
            'message' => $auto_response,
            'time' => date('H:i:s'),
            'date' => date('Y-m-d'),
            'type' => 'bot'
        ];
        
        $messages[] = $bot_message;
    }
    
    // Son 100 mesajı tut
    if(count($messages) > 100) {
        $messages = array_slice($messages, -100);
    }
    
    file_put_contents($chat_file, json_encode($messages, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success']);
    exit;
}

// Mesajları getir
if(isset($_GET['action']) && $_GET['action'] == 'get') {
    $messages = json_decode(file_get_contents($chat_file), true);
    echo json_encode($messages ?: []);
    exit;
}

// Hızlı cevap baloncukları için endpoint
if(isset($_GET['action']) && $_GET['action'] == 'quick_replies') {
    $quick_replies = [
        ['id' => 'refund', 'text' => '🔄 İade İşlemleri'],
        ['id' => 'order', 'text' => '📦 Sipariş Takibi'],
        ['id' => 'product', 'text' => '🛒 Ürün Bilgisi'],
        ['id' => 'delivery', 'text' => '🚚 Teslimat Süresi'],
        ['id' => 'contact', 'text' => '📞 İletişim Bilgileri']
    ];
    echo json_encode($quick_replies);
    exit;
}

// Baloncuk seçimine göre yanıt
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'quick_response') {
    if(!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Giriş yapmalısınız']);
        exit;
    }
    
    $quick_id = $_POST['quick_id'] ?? '';
    $responses = [
        'refund' => 'İade işlemleri için lütfen "Hesabım > Siparişlerim" bölümünden iade talebi oluşturun. İade koşulları: Ürünler orijinal paketinde ve etiketli olmalıdır. İade süresi 14 gündür.',
        'order' => 'Sipariş takibi için "Hesabım > Siparişlerim" bölümünü kullanabilirsiniz. Kargo numaranızla kargo firmasının sitesinden de detaylı takip yapabilirsiniz.',
        'product' => 'Ürünlerimiz hakkında detaylı bilgi için kategorileri gezebilir veya arama yapabilirsiniz. Stok durumu ürün sayfalarında anlık güncellenmektedir.',
        'delivery' => 'Teslimat süreleri: İstanbul 1-2 iş günü, Diğer iller 3-7 iş günü. Kargo ücreti 50 TL ve üzeri alışverişlerde ücretsizdir.',
        'contact' => 'Bize 0850 XXX XX XX numaralı telefondan ulaşabilir veya destek@firma.com adresine e-posta gönderebilirsiniz. Çalışma saatlerimiz: Hafta içi 09:00-18:00'
    ];
    
    $messages = json_decode(file_get_contents($chat_file), true);
    
    // Kullanıcının baloncuğu seçtiğini kaydet
    $quick_texts = [
        'refund' => 'İade İşlemleri',
        'order' => 'Sipariş Takibi',
        'product' => 'Ürün Bilgisi',
        'delivery' => 'Teslimat Süresi',
        'contact' => 'İletişim Bilgileri'
    ];
    
    $user_message = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['ad_soyad'] ?? 'Misafir',
        'message' => $quick_texts[$quick_id] ?? 'Hızlı Soru',
        'time' => date('H:i:s'),
        'date' => date('Y-m-d'),
        'type' => 'user',
        'quick_reply' => true
    ];
    
    $messages[] = $user_message;
    
    // Bot yanıtı
    $bot_response = $responses[$quick_id] ?? 'Size nasıl yardımcı olabilirim?';
    
    $bot_message = [
        'id' => uniqid('bot_'),
        'user_id' => 'bot',
        'user_name' => 'Destek Botu',
        'message' => $bot_response,
        'time' => date('H:i:s'),
        'date' => date('Y-m-d'),
        'type' => 'bot'
    ];
    
    $messages[] = $bot_message;
    
    // Son 100 mesajı tut
    if(count($messages) > 100) {
        $messages = array_slice($messages, -100);
    }
    
    file_put_contents($chat_file, json_encode($messages, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success', 'response' => $bot_response]);
    exit;
}
?>