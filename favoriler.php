<?php
session_start();

if(!isset($_SESSION['favoriler'])) {
    $_SESSION['favoriler'] = [];
}

if(isset($_GET['action']) && $_GET['action'] == 'toggle') {
    $product_id = intval($_GET['id']);
    $index = array_search($product_id, $_SESSION['favoriler']);
    
    if($index !== false) {
        unset($_SESSION['favoriler'][$index]);
        $_SESSION['favoriler'] = array_values($_SESSION['favoriler']);
        echo json_encode(['success' => true, 'added' => false]);
    } else {
        $_SESSION['favoriler'][] = $product_id;
        echo json_encode(['success' => true, 'added' => true]);
    }
    exit;
}
?>