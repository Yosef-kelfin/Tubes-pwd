<?php
session_start();

function getUsers() {
    $file = 'users.json';
    if (!file_exists($file)) return [];
    $data = file_get_contents($file);
    return json_decode($data, true) ?: [];
}

function saveUsers($users) {
    file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
}

function getItems() {
    $file = 'items.json';
    if (!file_exists($file)) return [];
    $data = file_get_contents($file);
    return json_decode($data, true) ?: [];
}

function saveItems($items) {
    file_put_contents('items.json', json_encode($items, JSON_PRETTY_PRINT));
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}
?>