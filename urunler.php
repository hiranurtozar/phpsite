<?php
require_once 'header.php';

// Dil ayarı
$dil = isset($_COOKIE['dil']) ? $_COOKIE['dil'] : 'tr';

// Kategori - HEADER.PHP'DEN GELEN DEĞERİ KULLAN, güvenli hale getir
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : 'tumu';
// Geçerli kategoriler
$gecerli_kategoriler = ['tumu', 'gul', 'orkide', 'lale', 'buket', 'sukulent', 'aranjman', 'hediye', 'doga'];
$kategori = in_array($kategori, $gecerli_kategoriler) ? $kategori : 'tumu';

// Arama kelimesi - HEADER.PHP'DEN GELEN DEĞERİ KULLAN
$arama_kelimesi = isset($_GET['arama']) ? trim($_GET['arama']) : '';

// Ürünleri getirme fonksiyonu
function urunleriGetir($kategori = 'tumu', $arama = '') {
    // Örnek ürün verileri - 100 farklı ürün
    $urunler = [
        // GÜLLER (12 ürün)
        [
            'id' => 1,
            'ad' => 'Kırmızı Gül Buketi',
            'aciklama' => '12 adet taze kırmızı gül, zarif paketleme',
            'fiyat' => 129.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 15,
            'indirim' => 10,
            'puan' => 4.8
        ],
        [
            'id' => 2,
            'ad' => 'Pembe Gül Demeti',
            'aciklama' => 'Romantik pembe gül demeti, 24 adet',
            'fiyat' => 179.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 10,
            'indirim' => 0,
            'puan' => 4.8
        ],
        [
            'id' => 3,
            'ad' => 'Sarı Gül Aranjmanı',
            'aciklama' => 'Canlı sarı güller, özel vazo',
            'fiyat' => 149.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 8,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 4,
            'ad' => 'Beyaz Gül Buketi',
            'aciklama' => 'Saf beyaz güller, düğün için ideal',
            'fiyat' => 199.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 12,
            'indirim' => 5,
            'puan' => 4.9
        ],
        [
            'id' => 5,
            'ad' => 'Karışık Gül Sepeti',
            'aciklama' => '5 farklı renk gül, sepet içinde',
            'fiyat' => 169.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 6,
            'indirim' => 20,
            'puan' => 4.6
        ],
        [
            'id' => 32,
            'ad' => 'Siyah Gül Buketi',
            'aciklama' => 'Nadir siyah güller, gizemli görünüm',
            'fiyat' => 299.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 4,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 33,
            'ad' => 'Turuncu Gül Demeti',
            'aciklama' => 'Enerjik turuncu güller, 15 adet',
            'fiyat' => 149.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 9,
            'indirim' => 10,
            'puan' => 4.7
        ],
        [
            'id' => 34,
            'ad' => 'Mor Gül Buketi',
            'aciklama' => 'Lüks mor güller, zarif paket',
            'fiyat' => 189.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 7,
            'indirim' => 15,
            'puan' => 4.8
        ],
        [
            'id' => 35,
            'ad' => 'Mini Gül Sepeti',
            'aciklama' => '8 adet mini gül, tatlı sepet',
            'fiyat' => 99.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 18,
            'indirim' => 25,
            'puan' => 4.5
        ],
        [
            'id' => 36,
            'ad' => 'Krem Gül Demeti',
            'aciklama' => 'Narin krem rengi güller, şık tasarım',
            'fiyat' => 169.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 11,
            'indirim' => 0,
            'puan' => 4.8
        ],
        [
            'id' => 37,
            'ad' => 'Gül ve Lilyum Buketi',
            'aciklama' => 'Gül ve lilyum karışımı özel buket',
            'fiyat' => 219.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 6,
            'indirim' => 10,
            'puan' => 4.9
        ],
        [
            'id' => 38,
            'ad' => 'Kalp Şeklinde Gül Aranjmanı',
            'aciklama' => 'Kalp şeklinde düzenlenmiş kırmızı güller',
            'fiyat' => 249.99,
            'kategori' => 'gul',
            'simge' => '🌹',
            'stok' => 5,
            'indirim' => 0,
            'puan' => 4.9
        ],
        
        // ORKİDELER (12 ürün)
        [
            'id' => 6,
            'ad' => 'Beyaz Orkide',
            'aciklama' => 'Lüks beyaz orkide, saksılı',
            'fiyat' => 199.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 8,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 7,
            'ad' => 'Mor Orkide',
            'aciklama' => 'Nadir mor orkide, özel bakım',
            'fiyat' => 249.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 5,
            'indirim' => 10,
            'puan' => 4.9
        ],
        [
            'id' => 8,
            'ad' => 'Pembe Orkide',
            'aciklama' => 'Pastel pembe orkide, şık saksı',
            'fiyat' => 179.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 10,
            'indirim' => 0,
            'puan' => 4.8
        ],
        [
            'id' => 9,
            'ad' => 'Sarı Orkide',
            'aciklama' => 'Parlak sarı orkide, mutluluk sembolü',
            'fiyat' => 219.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 7,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 10,
            'ad' => 'Mini Orkide Seti',
            'aciklama' => '3 adet mini orkide, ofis için ideal',
            'fiyat' => 149.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 15,
            'indirim' => 25,
            'puan' => 4.5
        ],
        [
            'id' => 39,
            'ad' => 'Çift Orkide Saksısı',
            'aciklama' => '2 adet orkide, dekoratif saksıda',
            'fiyat' => 349.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 4,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 40,
            'ad' => 'Turuncu Orkide',
            'aciklama' => 'Canlı turuncu orkide, enerjik görünüm',
            'fiyat' => 189.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 9,
            'indirim' => 10,
            'puan' => 4.7
        ],
        [
            'id' => 41,
            'ad' => 'Beyaz-Mor Orkide',
            'aciklama' => 'İki renkli orkide, nadir tür',
            'fiyat' => 279.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 3,
            'indirim' => 15,
            'puan' => 4.8
        ],
        [
            'id' => 42,
            'ad' => 'Orkide Teraryumu',
            'aciklama' => 'Mini orkide, cam teraryum içinde',
            'fiyat' => 129.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 12,
            'indirim' => 20,
            'puan' => 4.6
        ],
        [
            'id' => 43,
            'ad' => 'Dev Orkide',
            'aciklama' => 'Büyük boy orkide, gösterişli',
            'fiyat' => 399.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 2,
            'indirim' => 0,
            'puan' => 5.0
        ],
        [
            'id' => 44,
            'ad' => 'Yeşil Orkide',
            'aciklama' => 'Nadir yeşil orkide, doğal görünüm',
            'fiyat' => 229.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 6,
            'indirim' => 10,
            'puan' => 4.7
        ],
        [
            'id' => 45,
            'ad' => 'Orkide ve Sukulent Seti',
            'aciklama' => 'Orkide ve sukulent kombinasyonu',
            'fiyat' => 179.99,
            'kategori' => 'orkide',
            'simge' => '💮',
            'stok' => 8,
            'indirim' => 25,
            'puan' => 4.6
        ],
        
        // LALELER (12 ürün)
        [
            'id' => 11,
            'ad' => 'Renkli Lale Demeti',
            'aciklama' => '5 renkli lale demeti, bahar havası',
            'fiyat' => 89.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 20,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 12,
            'ad' => 'Sarı Lale Buketi',
            'aciklama' => 'Parlak sarı laleler, mutluluk sembolü',
            'fiyat' => 79.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 18,
            'indirim' => 0,
            'puan' => 4.7
        ],
        [
            'id' => 13,
            'ad' => 'Kırmızı Lale Aranjmanı',
            'aciklama' => 'Canlı kırmızı laleler, özel vazo',
            'fiyat' => 99.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 12,
            'indirim' => 10,
            'puan' => 4.6
        ],
        [
            'id' => 14,
            'ad' => 'Pembe Lale Demeti',
            'aciklama' => 'Romantik pembe laleler, zarif paket',
            'fiyat' => 89.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 15,
            'indirim' => 0,
            'puan' => 4.8
        ],
        [
            'id' => 46,
            'ad' => 'Beyaz Lale Buketi',
            'aciklama' => 'Saf beyaz laleler, zarif görünüm',
            'fiyat' => 94.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 16,
            'indirim' => 10,
            'puan' => 4.7
        ],
        [
            'id' => 47,
            'ad' => 'Mor Lale Demeti',
            'aciklama' => 'Lüks mor laleler, özel tasarım',
            'fiyat' => 109.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 10,
            'indirim' => 15,
            'puan' => 4.8
        ],
        [
            'id' => 48,
            'ad' => 'Turuncu Lale Buketi',
            'aciklama' => 'Enerjik turuncu laleler, canlı renk',
            'fiyat' => 84.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 14,
            'indirim' => 0,
            'puan' => 4.6
        ],
        [
            'id' => 49,
            'ad' => 'Lale Sepeti',
            'aciklama' => 'Çeşitli laleler, ahşap sepet içinde',
            'fiyat' => 119.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 8,
            'indirim' => 20,
            'puan' => 4.7
        ],
        [
            'id' => 50,
            'ad' => 'Mini Lale Seti',
            'aciklama' => '3 küçük lale demeti, hediye paketi',
            'fiyat' => 69.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 22,
            'indirim' => 25,
            'puan' => 4.5
        ],
        [
            'id' => 51,
            'ad' => 'Siyah Lale Buketi',
            'aciklama' => 'Nadir siyah laleler, gizemli görünüm',
            'fiyat' => 149.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 5,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 52,
            'ad' => 'Lale ve Sümbül Karışımı',
            'aciklama' => 'Lale ve sümbül bahar karışımı',
            'fiyat' => 99.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 11,
            'indirim' => 10,
            'puan' => 4.7
        ],
        [
            'id' => 53,
            'ad' => 'Hollanda Laleleri',
            'aciklama' => 'Özel Hollanda laleleri, ithal',
            'fiyat' => 129.99,
            'kategori' => 'lale',
            'simge' => '🌷',
            'stok' => 9,
            'indirim' => 15,
            'puan' => 4.8
        ],
        
        // BUKETLER (12 ürün)
        [
            'id' => 15,
            'ad' => 'Doğum Günü Buketi',
            'aciklama' => 'Özel doğum günü buketi, renkli çiçekler',
            'fiyat' => 149.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 12,
            'indirim' => 5,
            'puan' => 4.6
        ],
        [
            'id' => 16,
            'ad' => 'Düğün Buketi',
            'aciklama' => 'Gelin buketi, özel tasarım',
            'fiyat' => 299.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 5,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 17,
            'ad' => 'Anneler Günü Buketi',
            'aciklama' => 'Anneler gününe özel buket',
            'fiyat' => 129.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 20,
            'indirim' => 20,
            'puan' => 4.7
        ],
        [
            'id' => 18,
            'ad' => 'Karışık Çiçek Buketi',
            'aciklama' => '10 farklı çiçekten oluşan buket',
            'fiyat' => 179.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 8,
            'indirim' => 10,
            'puan' => 4.8
        ],
        [
            'id' => 19,
            'ad' => 'Mini Buket Seti',
            'aciklama' => '3 adet mini buket, hediye paketi',
            'fiyat' => 99.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 25,
            'indirim' => 30,
            'puan' => 4.5
        ],
        [
            'id' => 54,
            'ad' => 'Sevgililer Günü Buketi',
            'aciklama' => 'Romantik sevgililer günü buketi',
            'fiyat' => 199.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 15,
            'indirim' => 10,
            'puan' => 4.8
        ],
        [
            'id' => 55,
            'ad' => 'Modern Buket',
            'aciklama' => 'Minimalist tasarım modern buket',
            'fiyat' => 169.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 9,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 56,
            'ad' => 'Kır Buketi',
            'aciklama' => 'Doğal kır çiçeklerinden buket',
            'fiyat' => 119.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 14,
            'indirim' => 0,
            'puan' => 4.6
        ],
        [
            'id' => 57,
            'ad' => 'Lüks Buket',
            'aciklama' => 'Premium çiçeklerden lüks buket',
            'fiyat' => 349.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 4,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 58,
            'ad' => 'Pastel Buket',
            'aciklama' => 'Pastel tonlarda yumuşak buket',
            'fiyat' => 159.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 11,
            'indirim' => 20,
            'puan' => 4.7
        ],
        [
            'id' => 59,
            'ad' => 'Bahar Buketi',
            'aciklama' => 'Baharın tazeliğini yansıtan buket',
            'fiyat' => 139.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 16,
            'indirim' => 10,
            'puan' => 4.8
        ],
        [
            'id' => 60,
            'ad' => 'Kış Buketi',
            'aciklama' => 'Kış mevsimine özel buket',
            'fiyat' => 179.99,
            'kategori' => 'buket',
            'simge' => '💐',
            'stok' => 7,
            'indirim' => 15,
            'puan' => 4.6
        ],
        
        // SUKULENTLER (12 ürün)
        [
            'id' => 20,
            'ad' => 'Mini Sukulent Seti',
            'aciklama' => '3 adet minyatür sukulent, teraryum',
            'fiyat' => 69.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 25,
            'indirim' => 20,
            'puan' => 4.5
        ],
        [
            'id' => 21,
            'ad' => 'Sukulent Bahçesi',
            'aciklama' => '5 farklı sukulent, ahşap kutu',
            'fiyat' => 129.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 10,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 22,
            'ad' => 'Dev Sukulent',
            'aciklama' => 'Büyük boy sukulent, dekoratif saksı',
            'fiyat' => 89.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 7,
            'indirim' => 0,
            'puan' => 4.6
        ],
        [
            'id' => 61,
            'ad' => 'Sukulent Duvar Saksısı',
            'aciklama' => 'Duvar için sukulent saksısı',
            'fiyat' => 149.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 6,
            'indirim' => 10,
            'puan' => 4.7
        ],
        [
            'id' => 62,
            'ad' => 'Renkli Sukulent Seti',
            'aciklama' => 'Renkli sukulent çeşitleri seti',
            'fiyat' => 99.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 18,
            'indirim' => 25,
            'puan' => 4.6
        ],
        [
            'id' => 63,
            'ad' => 'Sukulent Askılık',
            'aciklama' => 'Askılı saksıda sukulentler',
            'fiyat' => 119.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 9,
            'indirim' => 15,
            'puan' => 4.8
        ],
        [
            'id' => 64,
            'ad' => 'Nadir Sukulent Türü',
            'aciklama' => 'Ender bulunan sukulent türü',
            'fiyat' => 199.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 3,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 65,
            'ad' => 'Sukulent ve Taş Seti',
            'aciklama' => 'Sukulentler ve dekoratif taşlar',
            'fiyat' => 109.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 12,
            'indirim' => 20,
            'puan' => 4.7
        ],
        [
            'id' => 66,
            'ad' => 'Mini Sukulent Bahçesi',
            'aciklama' => 'Küçük cam kavanozda sukulent bahçesi',
            'fiyat' => 79.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 20,
            'indirim' => 30,
            'puan' => 4.5
        ],
        [
            'id' => 67,
            'ad' => 'Sukulent Topiary',
            'aciklama' => 'Şekilli sukulent düzenlemesi',
            'fiyat' => 159.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 5,
            'indirim' => 10,
            'puan' => 4.8
        ],
        [
            'id' => 68,
            'ad' => 'Çiçekli Sukulent',
            'aciklama' => 'Çiçek açan özel sukulent türü',
            'fiyat' => 129.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 8,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 69,
            'ad' => 'Sukulent Kaktüs Karışımı',
            'aciklama' => 'Sukulent ve kaktüs kombinasyonu',
            'fiyat' => 89.99,
            'kategori' => 'sukulent',
            'simge' => '🌵',
            'stok' => 15,
            'indirim' => 20,
            'puan' => 4.6
        ],
        
        // ARANJMANLAR (12 ürün)
        [
            'id' => 23,
            'ad' => 'Lüks Aranjman',
            'aciklama' => 'Özel tasarım lüks çiçek aranjmanı',
            'fiyat' => 249.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 6,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 24,
            'ad' => 'Modern Aranjman',
            'aciklama' => 'Modern tasarım çiçek aranjmanı',
            'fiyat' => 189.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 9,
            'indirim' => 10,
            'puan' => 4.7
        ],
        [
            'id' => 25,
            'ad' => 'Mini Aranjman Seti',
            'aciklama' => '3 adet mini aranjman, ofis için',
            'fiyat' => 149.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 12,
            'indirim' => 25,
            'puan' => 4.6
        ],
        [
            'id' => 70,
            'ad' => 'Masaüstü Aranjman',
            'aciklama' => 'Ofis masası için mini aranjman',
            'fiyat' => 99.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 18,
            'indirim' => 30,
            'puan' => 4.5
        ],
        [
            'id' => 71,
            'ad' => 'Dikey Aranjman',
            'aciklama' => 'Dikey tasarım çiçek aranjmanı',
            'fiyat' => 199.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 7,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 72,
            'ad' => 'Asimetrik Aranjman',
            'aciklama' => 'Asimetrik tasarım modern aranjman',
            'fiyat' => 219.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 5,
            'indirim' => 10,
            'puan' => 4.8
        ],
        [
            'id' => 73,
            'ad' => 'Klasik Aranjman',
            'aciklama' => 'Geleneksel klasik çiçek aranjmanı',
            'fiyat' => 179.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 11,
            'indirim' => 20,
            'puan' => 4.6
        ],
        [
            'id' => 74,
            'ad' => 'Tropik Aranjman',
            'aciklama' => 'Tropik çiçeklerden oluşan aranjman',
            'fiyat' => 229.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 4,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 75,
            'ad' => 'Kış Aranjmanı',
            'aciklama' => 'Kış mevsimine özel aranjman',
            'fiyat' => 169.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 9,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 76,
            'ad' => 'Çift Renkli Aranjman',
            'aciklama' => 'İki renk uyumlu çiçek aranjmanı',
            'fiyat' => 189.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 8,
            'indirim' => 10,
            'puan' => 4.8
        ],
        [
            'id' => 77,
            'ad' => 'Miniature Aranjman',
            'aciklama' => 'Minik çiçeklerden miniature aranjman',
            'fiyat' => 129.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 14,
            'indirim' => 25,
            'puan' => 4.6
        ],
        [
            'id' => 78,
            'ad' => 'Yeni Yıl Aranjmanı',
            'aciklama' => 'Yeni yıla özel özel aranjman',
            'fiyat' => 199.99,
            'kategori' => 'aranjman',
            'simge' => '🏵️',
            'stok' => 6,
            'indirim' => 20,
            'puan' => 4.7
        ],
        
        // HEDİYE SETLERİ (12 ürün)
        [
            'id' => 26,
            'ad' => 'Hediye Paketi',
            'aciklama' => 'Çiçek + Çikolata hediye seti',
            'fiyat' => 159.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 15,
            'indirim' => 20,
            'puan' => 4.8
        ],
        [
            'id' => 27,
            'ad' => 'Lüks Hediye Seti',
            'aciklama' => 'Çiçek + Şarap + Çikolata seti',
            'fiyat' => 299.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 5,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 28,
            'ad' => 'Mini Hediye Paketi',
            'aciklama' => 'Mini buket + küçük hediye',
            'fiyat' => 89.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 20,
            'indirim' => 15,
            'puan' => 4.5
        ],
        [
            'id' => 79,
            'ad' => 'Doğum Günü Seti',
            'aciklama' => 'Çiçek + Pasta + Balon seti',
            'fiyat' => 229.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 8,
            'indirim' => 10,
            'puan' => 4.8
        ],
        [
            'id' => 80,
            'ad' => 'Anneler Günü Seti',
            'aciklama' => 'Çiçek + Parfüm + Kart seti',
            'fiyat' => 279.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 12,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 81,
            'ad' => 'İş Yeri Hediye Seti',
            'aciklama' => 'Ofis çiçeği + Kalem seti',
            'fiyat' => 149.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 16,
            'indirim' => 20,
            'puan' => 4.6
        ],
        [
            'id' => 82,
            'ad' => 'Romantik Hediye Seti',
            'aciklama' => 'Gül + Mum + Müzik kutusu',
            'fiyat' => 199.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 7,
            'indirim' => 25,
            'puan' => 4.8
        ],
        [
            'id' => 83,
            'ad' => 'Spa Hediye Seti',
            'aciklama' => 'Çiçek + Spa ürünleri seti',
            'fiyat' => 179.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 9,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 84,
            'ad' => 'Çocuk Hediye Seti',
            'aciklama' => 'Çiçek + Oyuncak seti',
            'fiyat' => 129.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 14,
            'indirim' => 30,
            'puan' => 4.6
        ],
        [
            'id' => 85,
            'ad' => 'Premium Hediye Kutusu',
            'aciklama' => 'Lüks hediye kutusunda çiçek seti',
            'fiyat' => 349.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 3,
            'indirim' => 0,
            'puan' => 4.9
        ],
        [
            'id' => 86,
            'ad' => 'Mini Hediye Sepeti',
            'aciklama' => 'Küçük sepet içinde hediye seti',
            'fiyat' => 109.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 18,
            'indirim' => 20,
            'puan' => 4.5
        ],
        [
            'id' => 87,
            'ad' => 'Yeni Ev Hediye Seti',
            'aciklama' => 'Ev bitkisi + Dekor ürünü',
            'fiyat' => 169.99,
            'kategori' => 'hediye',
            'simge' => '🎁',
            'stok' => 11,
            'indirim' => 15,
            'puan' => 4.7
        ],
        
        // DOĞA ÇİÇEKLERİ (16 ürün)
        [
            'id' => 29,
            'ad' => 'Kır Çiçekleri Demeti',
            'aciklama' => 'Doğal kır çiçekleri demeti',
            'fiyat' => 79.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 18,
            'indirim' => 10,
            'puan' => 4.7
        ],
        [
            'id' => 30,
            'ad' => 'Papatya Buketi',
            'aciklama' => 'Taze papatyalardan oluşan buket',
            'fiyat' => 69.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 22,
            'indirim' => 0,
            'puan' => 4.6
        ],
        [
            'id' => 31,
            'ad' => 'Karışık Doğa Çiçekleri',
            'aciklama' => '7 farklı doğal çiçekten buket',
            'fiyat' => 99.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 12,
            'indirim' => 15,
            'puan' => 4.8
        ],
        [
            'id' => 88,
            'ad' => 'Menekşe Buketi',
            'aciklama' => 'Taze mor menekşelerden buket',
            'fiyat' => 59.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 20,
            'indirim' => 25,
            'puan' => 4.5
        ],
        [
            'id' => 89,
            'ad' => 'Nergis Demeti',
            'aciklama' => 'Baharın müjdecisi nergisler',
            'fiyat' => 74.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 16,
            'indirim' => 10,
            'puan' => 4.7
        ],
        [
            'id' => 90,
            'ad' => 'Sümbül Buketi',
            'aciklama' => 'Mis kokulu mavi sümbüller',
            'fiyat' => 84.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 14,
            'indirim' => 15,
            'puan' => 4.6
        ],
        [
            'id' => 91,
            'ad' => 'Zambak Demeti',
            'aciklama' => 'Beyaz zambaklar, zarif görünüm',
            'fiyat' => 119.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 9,
            'indirim' => 0,
            'puan' => 4.8
        ],
        [
            'id' => 92,
            'ad' => 'Frezya Buketi',
            'aciklama' => 'Mis gibi kokan frezya çiçekleri',
            'fiyat' => 99.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 11,
            'indirim' => 20,
            'puan' => 4.7
        ],
        [
            'id' => 93,
            'ad' => 'Kasımpatı Demeti',
            'aciklama' => 'Renkli kasımpatı çeşitleri',
            'fiyat' => 89.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 17,
            'indirim' => 15,
            'puan' => 4.6
        ],
        [
            'id' => 94,
            'ad' => 'Geri Dönüşüm Çiçekleri',
            'aciklama' => 'Doğa dostu geri dönüşümlü çiçekler',
            'fiyat' => 69.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 21,
            'indirim' => 30,
            'puan' => 4.5
        ],
        [
            'id' => 95,
            'ad' => 'Yabani Orkide',
            'aciklama' => 'Doğal ortamda yetişen orkideler',
            'fiyat' => 149.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 6,
            'indirim' => 10,
            'puan' => 4.8
        ],
        [
            'id' => 96,
            'ad' => 'Dağ Çiçekleri',
            'aciklama' => 'Yüksek rakımlı dağ çiçekleri',
            'fiyat' => 109.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 8,
            'indirim' => 15,
            'puan' => 4.7
        ],
        [
            'id' => 97,
            'ad' => 'Su Kenarı Çiçekleri',
            'aciklama' => 'Sulak alan bitkilerinden buket',
            'fiyat' => 94.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 13,
            'indirim' => 20,
            'puan' => 4.6
        ],
        [
            'id' => 98,
            'ad' => 'Kaya Bahçesi Çiçekleri',
            'aciklama' => 'Kaya bahçelerine özel çiçekler',
            'fiyat' => 119.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 7,
            'indirim' => 10,
            'puan' => 4.8
        ],
        [
            'id' => 99,
            'ad' => 'Mevsimlik Çiçek Demeti',
            'aciklama' => 'Mevsimine göre değişen çiçekler',
            'fiyat' => 89.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 15,
            'indirim' => 25,
            'puan' => 4.7
        ],
        [
            'id' => 100,
            'ad' => 'Ekolojik Doğa Buketi',
            'aciklama' => 'Tamamen ekolojik doğal çiçekler',
            'fiyat' => 129.99,
            'kategori' => 'doga',
            'simge' => '🌼',
            'stok' => 10,
            'indirim' => 0,
            'puan' => 4.9
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

// Kategori isimleri - TÜM KATEGORİLERİ EKLEDİM
$kategori_isimleri = [
    'tr' => [
        'tumu' => 'Tüm Ürünler',
        'gul' => 'Güller',
        'orkide' => 'Orkideler',
        'lale' => 'Laleler',
        'buket' => 'Buketler',
        'sukulent' => 'Sukulentler',
        'aranjman' => 'Aranjmanlar',
        'hediye' => 'Hediye Setleri',
        'doga' => 'Doğa Çiçekleri'
    ],
    'en' => [
        'tumu' => 'All Products',
        'gul' => 'Roses',
        'orkide' => 'Orchids',
        'lale' => 'Tulips',
        'buket' => 'Bouquets',
        'sukulent' => 'Succulents',
        'aranjman' => 'Arrangements',
        'hediye' => 'Gift Sets',
        'doga' => 'Natural Flowers'
    ]
];

// Favori kontrol fonksiyonu
function favoriKontrol($urun_id) {
    return isset($_SESSION['favoriler']) && in_array($urun_id, $_SESSION['favoriler']);
}
?>

<!DOCTYPE html>
<html data-theme="<?php echo htmlspecialchars($tema); ?>" lang="<?php echo htmlspecialchars($dil); ?>">
<head>
    <title>ÇiçekBahçesi - <?php echo $dil == 'tr' ? 'Ürünler' : 'Products'; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- HEADER.PHP'DEKİ STİLLER ZATEN YÜKLENECEK -->
</head>
<body>
    <!-- HEADER.PHP NAVBAR ZATEN YÜKLENDİ -->
    
    <!-- ÜRÜNLER İÇERİĞİ -->
    <div class="container">
        <div style="padding: 20px 0;">
            <!-- Kategori Filtreleri -->
            <div style="display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap; justify-content: center;">
                <?php foreach($kategori_isimleri[$dil] as $key => $isim): ?>
                    <a href="urunler.php?kategori=<?php echo $key; ?><?php echo !empty($arama_kelimesi) ? '&arama=' . urlencode($arama_kelimesi) : ''; ?>" 
                       style="
                           padding: 10px 20px;
                           background: <?php echo $kategori == $key ? 'linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%)' : 'white'; ?>;
                           border: 2px solid #ffeef2;
                           border-radius: 25px;
                           color: <?php echo $kategori == $key ? 'white' : '#666'; ?>;
                           font-weight: 500;
                           text-decoration: none;
                           transition: all 0.3s;
                       "
                       onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 5px 15px rgba(255, 107, 157, 0.2)';"
                       onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                        <?php 
                        $emoji = [
                            'tumu' => '🌸',
                            'gul' => '🌹',
                            'orkide' => '💮',
                            'lale' => '🌷',
                            'buket' => '💐',
                            'sukulent' => '🌵',
                            'aranjman' => '🏵️',
                            'hediye' => '🎁',
                            'doga' => '🌼'
                        ];
                        echo $emoji[$key] . ' ' . $isim;
                        ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Arama Sonucu Başlığı -->
            <?php if(!empty($arama_kelimesi)): ?>
                <div style="text-align: center; padding: 30px; color: #666;">
                    <h3 style="color: #ff6b9d; margin-bottom: 10px;">
                        "<?php echo htmlspecialchars($arama_kelimesi); ?>" <?php echo $dil == 'tr' ? 'için arama sonuçları' : 'search results'; ?>
                    </h3>
                    <p><?php echo count($urunler); ?> <?php echo $dil == 'tr' ? 'ürün bulundu' : 'products found'; ?></p>
                </div>
            <?php else: ?>
                <?php 
                // Kategori başlığını güvenli şekilde al
                $kategori_baslik = isset($kategori_isimleri[$dil][$kategori]) ? 
                    $kategori_isimleri[$dil][$kategori] : 
                    ($dil == 'tr' ? 'Tüm Ürünler' : 'All Products');
                ?>
                <h2 style="color: #333; margin-bottom: 20px; display: flex; align-items: center;">
                    <?php echo $kategori_baslik; ?>
                    <span style="background: #ffeef2; padding: 5px 15px; border-radius: 15px; color: #ff6b9d; font-weight: 600; margin-left: 10px;">
                        <?php echo count($urunler); ?> <?php echo $dil == 'tr' ? 'ürün' : 'products'; ?>
                    </span>
                </h2>
            <?php endif; ?>
            
            <!-- Ürünler Grid -->
            <?php if(count($urunler) > 0): ?>
                <div style="
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                    gap: 25px;
                    margin-top: 20px;
                ">
                    <?php foreach($urunler as $urun): 
                        $indirimli_fiyat = $urun['indirim'] > 0 ? 
                            $urun['fiyat'] * (100 - $urun['indirim']) / 100 : 
                            $urun['fiyat'];
                        $favori_durumu = favoriKontrol($urun['id']) ? 'active' : '';
                    ?>
                        <div style="
                            background: white;
                            border-radius: 15px;
                            overflow: hidden;
                            box-shadow: 0 5px 20px rgba(255, 107, 157, 0.1);
                            transition: all 0.3s;
                            position: relative;
                        "
                        onmouseover="this.style.transform='translateY(-10px)';this.style.boxShadow='0 15px 30px rgba(255, 107, 157, 0.2)';"
                        onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 5px 20px rgba(255, 107, 157, 0.1)';">
                            <!-- Ürün Resim Alanı -->
                            <div style="
                                width: 100%;
                                height: 200px;
                                background: linear-gradient(135deg, #fff5f7 0%, #ffeef2 100%);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 60px;
                                color: #ff6b9d;
                            ">
                                <?php echo $urun['simge'] ?? '🌸'; ?>
                            </div>
                            
                            <!-- Ürün Bilgileri -->
                            <div style="padding: 20px;">
                                <div style="font-size: 1.2rem; font-weight: 600; color: #333; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="flex: 1;"><?php echo htmlspecialchars($urun['ad']); ?></span>
                                    <?php if($urun['indirim'] > 0): ?>
                                        <span style="background: #ff4757; color: white; padding: 3px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; margin-left: 10px;">
                                            -%<?php echo $urun['indirim']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p style="color: #666; font-size: 0.9rem; margin-bottom: 15px; line-height: 1.5;">
                                    <?php echo htmlspecialchars($urun['aciklama']); ?>
                                </p>
                                
                                <!-- Fiyat -->
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                    <?php if($urun['indirim'] > 0): ?>
                                        <span style="font-size: 1.3rem; font-weight: 700; color: #ff6b9d;">
                                            <?php echo number_format($indirimli_fiyat, 2); ?> TL
                                        </span>
                                        <span style="font-size: 1rem; color: #999; text-decoration: line-through;">
                                            <?php echo number_format($urun['fiyat'], 2); ?> TL
                                        </span>
                                    <?php else: ?>
                                        <span style="font-size: 1.3rem; font-weight: 700; color: #ff6b9d;">
                                            <?php echo number_format($urun['fiyat'], 2); ?> TL
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Stok Bilgisi -->
                                <div style="display: flex; align-items: center; gap: 5px; color: #28a745; font-size: 0.9rem; margin-bottom: 15px;">
                                    <i class="fas fa-check-circle"></i>
                                    <span><?php echo $urun['stok']; ?> <?php echo $dil == 'tr' ? 'adet stokta' : 'in stock'; ?></span>
                                </div>
                                
                                <!-- Puan -->
                                <div style="display: flex; align-items: center; gap: 5px; color: #ffc107; font-size: 0.9rem; margin-bottom: 15px;">
                                    <i class="fas fa-star"></i>
                                    <span><?php echo $urun['puan']; ?></span>
                                    <span>(<?php echo rand(10, 100); ?> <?php echo $dil == 'tr' ? 'değerlendirme' : 'reviews'; ?>)</span>
                                </div>
                                
                                <!-- Butonlar -->
                                <div style="display: flex; gap: 10px;">
                                    <!-- Sepete ekle linki -->
                                    <a href="sepet.php?action=ekle&urun_id=<?php echo $urun['id']; ?>&urun_ad=<?php echo urlencode($urun['ad']); ?>&urun_fiyat=<?php echo $urun['fiyat']; ?>&urun_simge=<?php echo urlencode($urun['simge']); ?>&urun_kategori=<?php echo $urun['kategori']; ?>" 
                                       style="
                                           flex: 1;
                                           background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
                                           color: white;
                                           border: none;
                                           padding: 10px;
                                           border-radius: 8px;
                                           font-weight: 600;
                                           text-decoration: none;
                                           display: flex;
                                           align-items: center;
                                           justify-content: center;
                                           gap: 8px;
                                           transition: all 0.3s;
                                       "
                                       onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 5px 15px rgba(255, 107, 157, 0.3)';"
                                       onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';"
                                       onclick="return confirm('<?php echo addslashes($urun['ad']); ?> <?php echo $dil == 'tr' ? "sepete eklensin mi?" : "add to cart?" ?>')">
                                        <i class="fas fa-shopping-cart"></i> 
                                        <?php echo $dil == 'tr' ? 'Sepete Ekle' : 'Add to Cart'; ?>
                                    </a>
                                    
                                    <!-- Favori butonu -->
                                    <a href="favoriler.php?action=<?php echo $favori_durumu ? 'cikar' : 'ekle'; ?>&urun_id=<?php echo $urun['id']; ?>" 
                                       style="
                                           background: <?php echo $favori_durumu ? '#ff6b9d' : 'white'; ?>;
                                           border: 2px solid #ffeef2;
                                           color: <?php echo $favori_durumu ? 'white' : '#ccc'; ?>;
                                           width: 45px;
                                           height: 45px;
                                           border-radius: 8px;
                                           text-decoration: none;
                                           display: flex;
                                           align-items: center;
                                           justify-content: center;
                                           font-size: 1.2rem;
                                           transition: all 0.3s;
                                       "
                                       onmouseover="this.style.background='#ff6b9d';this.style.color='white';this.style.borderColor='#ff6b9d';"
                                       onmouseout="this.style.background='<?php echo $favori_durumu ? '#ff6b9d' : 'white'; ?>';this.style.color='<?php echo $favori_durumu ? 'white' : '#ccc'; ?>';this.style.borderColor='#ffeef2';"
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
                        transition: all 0.3s;
                    "
                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 5px 15px rgba(255, 107, 157, 0.3)';"
                    onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none';">
                        <?php echo $dil == 'tr' ? 'Tüm Ürünleri Gör' : 'View All Products'; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Sayfa yüklendiğinde kontrol
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Ürünler sayfası yüklendi');
        console.log('Kategori:', '<?php echo $kategori; ?>');
        console.log('Arama:', '<?php echo $arama_kelimesi; ?>');
        console.log('Ürün sayısı:', <?php echo count($urunler); ?>);
        
        // Favori butonlarına tıklama
        document.querySelectorAll('[href*="favoriler.php"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Butonun mevcut rengini al
                const isActive = this.style.backgroundColor === 'rgb(255, 107, 157)' || 
                                this.getAttribute('style')?.includes('#ff6b9d');
                
                // Renkleri değiştir
                if (isActive) {
                    this.style.backgroundColor = 'white';
                    this.style.color = '#ccc';
                    this.style.borderColor = '#ffeef2';
                    this.title = '<?php echo $dil == "tr" ? "Favorilere Ekle" : "Add to Favorites"; ?>';
                } else {
                    this.style.backgroundColor = '#ff6b9d';
                    this.style.color = 'white';
                    this.style.borderColor = '#ff6b9d';
                    this.title = '<?php echo $dil == "tr" ? "Favorilerden Çıkar" : "Remove from Favorites"; ?>';
                }
            });
        });
    });
    </script>

    <?php 
    // Footer.php'yi yükle
    if (file_exists('footer.php')) {
        require_once 'footer.php';
    } else {
        // Eğer footer.php yoksa basit bir footer ekle
        echo '
        <footer style="
            margin-top: 50px;
            padding: 30px;
            background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
            color: white;
            text-align: center;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        ">
            <div style="max-width: 1200px; margin: 0 auto;">
                <p style="margin-bottom: 20px; font-size: 1.2rem; font-family: \'Dancing Script\', cursive;">
                    🌸 ÇiçekBahçesi - En Güzel Çiçekler 🌸
                </p>
                <p style="font-size: 0.9rem; opacity: 0.9;">
                    © ' . date('Y') . ' ÇiçekBahçesi - Tüm hakları saklıdır.
                </p>
            </div>
        </footer>
        ';
    }
    ?>
</body>
</html>