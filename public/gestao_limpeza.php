<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Database\Connection;

try {
    $db = Connection::getInstance();
    // Busca dados e calcula a diferença de dias
    $stmt = $db->query("SELECT *, DATEDIFF(CURDATE(), data_ultima_limpeza) as dias_passados FROM gestao_limpeza ORDER BY tipo, setor, identificacao");
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $dados = [];
}

// Filtros para as Abas
$pdvs = array_filter($dados, fn($i) => $i['tipo'] == 'pdv');
$balancas = array_filter($dados, fn($i) => $i['tipo'] == 'balanca');
$desktops = array_filter($dados, fn($i) => $i['tipo'] == 'desktop');

$mes_atual = date('m/Y');

function renderPlanilha($itens) {
    if (empty($itens)) return "<div class='p-3 text-center'>Nenhum registro encontrado.</div>";
    
    $html = '<div class="table-responsive">
                <table class="table table-bordered table-dark align-middle mb-0 custom-table">
                    <thead>
                        <tr>
                            <th>EQUIPAMENTO</th>
                            <th>SETOR</th>
                            <th>ÚLTIMA LIMPEZA</th>
                            <th>PRAZO / STATUS</th>
                            <th>RESPONSÁVEL</th>
                            <th>AÇÃO</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($itens as $item) {
        $dias_restantes = $item['frequencia_dias'] - $item['dias_passados'];
        
        // Define a cor de fundo da linha ou célula baseada no status
        $classe_status = ($dias_restantes > 0) ? 'status-limpo' : 'status-atrasado';
        $texto_status = ($dias_restantes > 0) ? "EM DIA (Faltam {$dias_restantes}d)" : "ATRASADO (" . abs($dias_restantes) . "d)";

        $html .= "<tr>
                    <td class='fw-bold'>{$item['identificacao']}</td>
                    <td>{$item['setor']}</td>
                    <td>" . date('d/m/Y', strtotime($item['data_ultima_limpeza'])) . "</td>
                    <td class='{$classe_status} text-center fw-bold text-white'>{$texto_status}</td>
                    <td class='text-center'>{$item['responsavel']}</td>
                    <td class='text-center'>
                        <button class='btn btn-sm btn-light fw-bold' onclick='confirmarLimpeza({$item['id']})'>
                            <i class='fas fa-check'></i> RESETAR
                        </button>
                    </td>
                  </tr>";
    }
    $html .= '</tbody></table></div>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão CPD - Higienização</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        body { background-color: #121212; color: #e0e0e0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Cabeçalho */
        .header-planilha { text-align: center; padding: 15px; background: #1e1e1e; border-bottom: 4px solid #b71c1c; margin-bottom: 20px; }
        .header-planilha h1 { font-size: 22px; margin: 0; letter-spacing: 1px; }

        /* Estilo da Tabela/Grade */
        .custom-table { border: 2px solid #444 !important; }
        .custom-table th { background-color: #2c2c2c !important; color: #ffc107; text-align: center; border: 1px solid #444 !important; }
        .custom-table td { border: 1px solid #444 !important; padding: 10px !important; }

        /* Cores de Fundo Dinâmicas */
        .status-limpo { background-color: #2e7d32 !important; } /* Verde escuro */
        .status-atrasado { background-color: #c62828 !important; } /* Vermelho escuro */

        /* Abas */
        .nav-tabs { border: none; gap: 5px; margin-bottom: 15px; }
        .nav-link { background: #333 !important; color: #fff !important; border: 1px solid #444 !important; font-weight: bold; }
        .nav-link.active { background: #b71c1c !important; border-color: #b71c1c !important; }
        .header-red { background: #b71c1c; color: white; text-align: center; padding: 10px; font-weight: bold; text-transform: uppercase; margin-bottom: 20px; }

        .btn-light { font-size: 11px; }
    </style>
</head>
<body>

<div class="container-fluid py-3">
    <div class="header-red">Gestão de Higienização de Equipamentos</div>
    
    <div class="mb-4">
        <a href="index.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Voltar ao Painel</a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">MÊS DE REFERÊNCIA: <span class="text-warning"><?= $mes_atual ?></span></h5>
    </div>
    
    <ul class="nav nav-tabs" id="limpezaTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="pdv-tab" data-bs-toggle="tab" data-bs-target="#tab-pdv" type="button">PDVs</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="balanca-tab" data-bs-toggle="tab" data-bs-target="#tab-balanca" type="button">Balanças</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="desktop-tab" data-bs-toggle="tab" data-bs-target="#tab-desktop" type="button">Desktops</button>
        </li>
    </ul>

    <div class="tab-content border border-secondary p-2 bg-dark rounded shadow">
        <div class="tab-pane fade show active" id="tab-pdv"><?= renderPlanilha($pdvs) ?></div>
        <div class="tab-pane fade" id="tab-balanca"><?= renderPlanilha($balancas) ?></div>
        <div class="tab-pane fade" id="tab-desktop"><?= renderPlanilha($desktops) ?></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmarLimpeza(id) {
        Swal.fire({
            title: 'Confirmar?',
            text: "O equipamento foi higienizado hoje?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2e7d32',
            confirmButtonText: 'Sim, atualizado!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Envia o ID para o banco de dados via AJAX
                $.post('atualizar_limpeza.php', { id: id }, function(response) {
                    if (response.status === 'sucesso') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: response.mensagem,
                            showConfirmButton: false,
                            timer: 1000
                        });
                        
                        // Recarrega a página para o cálculo do PHP mudar a cor para VERDE
                        setTimeout(() => { location.reload(); }, 1000);
                    } else {
                        Swal.fire('Erro', 'Erro ao gravar: ' + response.mensagem, 'error');
                    }
                }, 'json');
            }
        });
    }
</script>
</body>
</html>