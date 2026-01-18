<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Database\Connection;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $db = Connection::getInstance();
        $id = (int)$_POST['id'];

        // Atualiza a data da última limpeza para o dia de hoje
        $stmt = $db->prepare("UPDATE gestao_limpeza SET data_ultima_limpeza = CURDATE() WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'sucesso', 'mensagem' => 'Limpeza registada!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
    }
}