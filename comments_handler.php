<?php
session_start();

// JSON dosyası
$comments_file = 'yorumlar.json';

// Dosya yoksa oluştur
if(!file_exists($comments_file)) {
    file_put_contents($comments_file, json_encode([]));
}

// Yorum ekle
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    if(!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Giriş yapmalısınız']);
        exit;
    }
    
    $comments = json_decode(file_get_contents($comments_file), true);
    $new_comment = [
        'id' => uniqid(),
        'urun_id' => $_POST['urun_id'] ?? 'anasayfa',
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['ad_soyad'] ?? 'Anonim',
        'comment' => htmlspecialchars(trim($_POST['comment'])),
        'rating' => intval($_POST['rating'] ?? 5),
        'date' => date('Y-m-d H:i:s')
    ];
    
    $comments[] = $new_comment;
    file_put_contents($comments_file, json_encode($comments, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'success']);
    exit;
}

// Yorumları getir
if(isset($_GET['action']) && $_GET['action'] == 'get' && isset($_GET['urun_id'])) {
    $comments = json_decode(file_get_contents($comments_file), true) ?: [];
    $urun_comments = array_filter($comments, function($c) {
        return $c['urun_id'] == $_GET['urun_id'];
    });
    
    echo json_encode(array_values($urun_comments));
    exit;
}
?>