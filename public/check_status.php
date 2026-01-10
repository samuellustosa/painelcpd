<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Terminal;

$ip = $_GET['ip'] ?? null;
if ($ip) {
    $terminal = new Terminal();
    // Retorna 'online' se o ping responder, ou 'offline'
    echo $terminal->verificarStatus($ip); 
}