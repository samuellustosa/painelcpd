<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Database\Connection;

$db = Connection::getInstance();
$stmt = $db->query("SELECT * FROM terminais ORDER BY id_caixa");
$terminais = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fechamento Automático</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        body { background-color: #121212; color: #fff; }
        .table { color: #fff; border-color: #333; }
        .table-dark { --bs-table-bg: #1e1e1e; }
        
        /* Cores de Linha por Status */
        .status-sucesso { color: #4caf50 !important; font-weight: bold; }
        .status-erro { color: #f44336 !important; font-weight: bold; }
        .status-pulado { color: #ffeb3b !important; font-weight: bold; }
        
        .progress { background-color: #2c2c2c; height: 12px; display: none; border-radius: 6px; }
        .header-title { border-left: 4px solid #dc3545; padding-left: 15px; }
        
        .form-check-input { cursor: pointer; }
        tr { transition: background 0.3s; }
        tr:hover { background-color: #252525 !important; }
        
        .badge-status { font-size: 0.8rem; padding: 5px 10px; }
    </style>
</head>
<body class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="header-title">
            <h2 class="mb-0">Fechamento PDV</h2>
            <small class="texT">OBS: NÃO COLOQUE PARA FECHAMENTO PDV'S EM VENDA, PRIORIZE O FECHAMENTO DEPOIS QUE TUDO FINALIZAR.</small>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">Voltar ao Painel</a>
    </div>

    <div id="status-container" class="mb-4" style="display:none;">
        <div class="d-flex justify-content-between mb-1">
            <span id="status-texto" class="small text-danger fw-bold">Processando...</span>
            <span id="status-porcentagem" class="small text">0%</span>
        </div>
        <div class="progress">
            <div id="barra" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" style="width: 0%"></div>
        </div>
    </div>

    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <button class="btn btn-dark btn-sm me-2" onclick="selecionarTodos(true)">Selecionar Tudo</button>
            <button class="btn btn-dark btn-sm" onclick="selecionarTodos(false)">Limpar</button>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button class="btn btn-danger px-4 fw-bold" onclick="iniciarAutomacao()">
                <i class="fas fa-play me-2"></i> INICIAR FECHAMENTO
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle">
            <thead>
                <tr>
                    <th width="40"><input type="checkbox" class="form-check-input" id="check-master" onclick="selecionarTodos(this.checked)"></th>
                    <th width="100">PDV</th>
                    <th width="150">IP ADDRESS</th>
                    <th>STATUS DO PROCESSO</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($terminais as $pdv): ?>
                <tr id="linha-<?= str_replace('.', '-', $pdv['ip_address']) ?>">
                    <td>
                        <input type="checkbox" class="form-check-input check-pdv" value="<?= $pdv['ip_address'] ?>" data-caixa="<?= $pdv['id_caixa'] ?>">
                    </td>
                    <td><strong>PDV <?= str_pad($pdv['id_caixa'], 2, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><code class="text"><?= $pdv['ip_address'] ?></code></td>
                    <td class="status-col">
                        <span class="text italic"><i class="fas fa-clock me-1"></i> Aguardando...</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function selecionarTodos(status) {
            $('.check-pdv, #check-master').prop('checked', status);
        }

        async function iniciarAutomacao() {
            const selecionados = $('.check-pdv:checked');
            if (selecionados.length === 0) return Swal.fire('Aviso', 'Selecione os PDVs.', 'info');

            const confirm = await Swal.fire({
                title: 'Confirmar Operação?',
                text: `Deseja fechar ${selecionados.length} PDV'S agora?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Sim, executar!'
            });

            if (!confirm.isConfirmed) return;

            // Reset inicial
            $('#status-container, .progress').show();
            $('.status-col').html('<span class="text-muted small italic"><i class="fas fa-clock me-1"></i> Aguardando...</span>');
            
            let total = selecionados.length;
            let atual = 0;

            for (let check of selecionados) {
                const ip = $(check).val();
                const numCaixa = $(check).data('caixa');
                const linhaId = '#linha-' + ip.replace(/\./g, '-');
                const colStatus = $(linhaId).find('.status-col');
                
                atual++;
                let porc = Math.round((atual / total) * 100);

                $('#status-texto').text(`Processando PDV ${numCaixa}...`);
                $('#status-porcentagem').text(`${porc}%`);
                $('#barra').css('width', porc + '%');

                // Status visual de "Em andamento" na linha
                colStatus.html('<span class="text-info small fw-bold"><i class="fas fa-spinner fa-spin me-1"></i> Executando...</span>');

                try {
                    const response = await $.post('executar_robo.php', { ip: ip }, null, 'json');
                    
                    if(response.status === 'sucesso') {
                        colStatus.html('<span class="status-sucesso small"><i class="fas fa-check-circle me-1"></i> Concluído com Sucesso</span>');
                    } else if(response.status === 'pulado') {
                        colStatus.html('<span class="status-pulado small"><i class="fas fa-exclamation-triangle me-1"></i> Pulado: Caixa em Venda</span>');
                    } else {
                        colStatus.html(`<span class="status-erro small"><i class="fas fa-times-circle me-1"></i> Erro: ${response.mensagem}</span>`);
                    }
                } catch (e) {
                    colStatus.html('<span class="status-erro small"><i class="fas fa-times-circle me-1"></i> Falha na Comunicação</span>');
                }
            }

            Swal.fire('Concluído', 'fila de fechamento finalizada.', 'success');
            $('#status-container').hide();
        }
    </script>
</body>
</html>