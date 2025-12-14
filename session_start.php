<?php

// session_start.php - Güvenli session başlatma
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 24 saat
        'cookie_secure'   => false, // HTTPS için true yapın
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
    
    // CSRF token yoksa oluştur
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    // Favoriler yoksa başlat
    if (!isset($_SESSION['favoriler'])) {
        $_SESSION['favoriler'] = [];
    }
    
    // Sepet yoksa başlat
    if (!isset($_SESSION['sepet'])) {
        $_SESSION['sepet'] = [];
    }
}
?>