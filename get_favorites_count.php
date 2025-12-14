<?php
session_start();
header('Content-Type: application/json');

$count = isset($_SESSION['favoriler']) ? count($_SESSION['favoriler']) : 0;
echo json_encode(['count' => $count]);
exit();