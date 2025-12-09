<?php
session_start();

// JSON dosyası
$chat_file = 'chat.json';

// Dosya yoksa oluştur
if(!file_exists($chat_file)) {
    file_put_contents($chat_file, json_encode([]));
}

// Mesaj gönder
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'send') {
    if(!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Giriş yapmalısınız']);
        exit;
    }
    
    $messages = json_decode(file_get_contents($chat_file), true);
    $new_message = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['ad_soyad'] ?? 'Misafir',
        'message' => htmlspecialchars(trim($_POST['message'])),
        'time' => date('H:i:s'),
        'date' => date('Y-m-d')
    ];
    
    $messages[] = $new_message;
    
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
?>