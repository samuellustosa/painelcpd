<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Terminal;

$ip = $_GET['ip'] ?? null;
if ($ip) {
    $terminal = new Terminal();
    echo $terminal->verificarStatus($ip); 
}