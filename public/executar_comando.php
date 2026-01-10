<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Controllers\PainelController;

$id   = $_POST['id'] ?? null;
$ip   = $_POST['ip'] ?? null;
$arq  = $_POST['arquitetura'] ?? null;
$tipo = $_POST['comando'] ?? null;

if ($id && $ip && $arq && $tipo) {
    $controller = new PainelController();
    $resultado  = $controller->acao($id, $ip, $arq, $tipo);
    echo json_encode(['status' => 'sucesso', 'mensagem' => $resultado]);
} else {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados incompletos']);
}