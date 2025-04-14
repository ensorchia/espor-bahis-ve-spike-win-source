<?php
session_start();
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: giris.php");
        exit();
    }
}

function getCurrentUser() {
    global $db;
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateBalance($userId, $amount, $type) {
    global $db;
    
    try {
        $db->beginTransaction();
        
        // Kullanıcı bakiyesini güncelle
        $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
        
        // İşlem kaydı oluştur
        $stmt = $db->prepare("INSERT INTO transactions (user_id, type, amount, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $type, $amount]);
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}
?> 