<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Database\Connection;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Connection::getInstance();
        $acao = $_POST['acao'] ?? '';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

        if ($acao === 'resetar') {
            $stmt = $db->prepare("UPDATE gestao_limpeza SET data_ultima_limpeza = CURDATE() WHERE id = ?");
            $stmt->execute([$id]);
        } 
        elseif ($acao === 'excluir') {
            $stmt = $db->prepare("DELETE FROM gestao_limpeza WHERE id = ?");
            $stmt->execute([$id]);
        } 
        elseif ($acao === 'salvar') {
            $identificacao = $_POST['identificacao'];
            $tipo = $_POST['tipo'];
            $setor = $_POST['setor'];
            $frequencia = (int)$_POST['frequencia_dias'];
            $responsavel = $_POST['responsavel'];

            if ($id) {
                $stmt = $db->prepare("UPDATE gestao_limpeza SET identificacao=?, tipo=?, setor=?, frequencia_dias=?, responsavel=? WHERE id=?");
                $stmt->execute([$identificacao, $tipo, $setor, $frequencia, $responsavel, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO gestao_limpeza (identificacao, tipo, setor, frequencia_dias, responsavel, data_ultima_limpeza) VALUES (?, ?, ?, ?, ?, CURDATE())");
                $stmt->execute([$identificacao, $tipo, $setor, $frequencia, $responsavel]);
            }
        }
        echo json_encode(['status' => 'sucesso']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
    }
    exit;
}