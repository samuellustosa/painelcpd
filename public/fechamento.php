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
        body { background-color: #0f0f0f; color: #e0e0e0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .table { color: #e0e0e0; border-color: #2c2c2c; }
        .table-dark { --bs-table-bg: #161616; }
        
        /* Cores de Status */
        .status-sucesso { color: #4caf50 !important; font-weight: bold; text-shadow: 0 0 5px rgba(76, 175, 80, 0.2); }
        .status-erro { color: #ff5252 !important; font-weight: bold; }
        .status-pulado { color: #ffd740 !important; font-weight: bold; }
        .status-processando { color: #40c4ff !important; font-weight: bold; }
        
        /* Barra de Progresso Customizada */
        .progress { background-color: #252525; height: 16px; display: none; border-radius: 8px; overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,0.5); }
        .progress-bar { transition: width 0.6s ease; }

        .header-title { border-left: 5px solid #dc3545; padding-left: 20px; }
        .header-title small { color: #bbb; display: block; margin-top: 5px; }
        
        .form-check-input { cursor: pointer; width: 1.2em; height: 1.2em; background-color: #333; border-color: #444; }
        .form-check-input:checked { background-color: #dc3545; border-color: #dc3545; }

        tr { transition: all 0.2s; border-bottom: 1px solid #222; }
        tr:hover { background-color: #1f1f1f !important; }
        tr.selecionada { background-color: #251a1a !important; }
        
        .btn-danger { background-color: #dc3545; border: none; transition: 0.3s; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3); }
        .btn-danger:hover { background-color: #bb2d3b; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4); }
        
        code { background-color: #252525; padding: 2px 6px; border-radius: 4px; color: #ff8a80; }
    </style>
</head>
<body class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="header-title">
            <h2 class="mb-0 text-uppercase fw-bold">Automação de Fechamento</h2>
            <small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Verifique se os PDVs não possuem vendas em andamento antes de iniciar.</small>
        </div>
        <a href="index.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left me-2"></i>VOLTAR</a>
    </div>

    <div id="status-container" class="card bg-dark border-secondary mb-4" style="display:none;">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span id="status-texto" class="fw-bold text-info">Aguardando comando...</span>
                <span id="status-porcentagem" class="badge bg-info text-dark">0%</span>
            </div>
            <div class="progress">
                <div id="barra" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <button class="btn btn-secondary btn-sm me-2" onclick="selecionarTodos(true)"><i class="fas fa-check-double me-1"></i>Tudo</button>
            <button class="btn btn-outline-secondary btn-sm" onclick="selecionarTodos(false)">Limpar</button>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button class="btn btn-danger px-5 py-2 fw-bold text-uppercase" onclick="iniciarAutomacao()">
                <i class="fas fa-power-off me-2"></i> Iniciar Lote de Fechamento
            </button>
        </div>
    </div>

    <div class="table-responsive shadow-lg rounded">
        <table class="table table-dark align-middle mb-0">
            <thead class="table-black">
                <tr>
                    <th width="50" class="text-center">
                        <input type="checkbox" class="form-check-input" id="check-master" onclick="selecionarTodos(this.checked)">
                    </th>
                    <th width="120">ID CAIXA</th>
                    <th width="180">ENDEREÇO IP</th>
                    <th>STATUS DA EXECUÇÃO</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($terminais as $pdv): ?>
                <tr id="linha-<?= str_replace('.', '-', $pdv['ip_address']) ?>">
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input check-pdv" value="<?= $pdv['ip_address'] ?>" data-caixa="<?= $pdv['id_caixa'] ?>" onchange="marcarLinha(this)">
                    </td>
                    <td><span class="badge bg-secondary">PDV <?= str_pad($pdv['id_caixa'], 2, '0', STR_PAD_LEFT) ?></span></td>
                    <td><code><?= $pdv['ip_address'] ?></code></td>
                    <td class="status-col">
                        <span class="text opacity-50 small"><i class="fas fa-stopwatch me-1"></i> Pronto para processar</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function marcarLinha(checkbox) {
            $(checkbox).closest('tr').toggleClass('selecionada', checkbox.checked);
        }

        function selecionarTodos(status) {
            $('.check-pdv, #check-master').prop('checked', status).trigger('change');
        }

        async function iniciarAutomacao() {
            const selecionados = $('.check-pdv:checked');
            if (selecionados.length === 0) return Swal.fire('Atenção', 'Selecione pelo menos um PDV.', 'info');

            const confirm = await Swal.fire({
                title: 'Confirmar Fechamento?',
                text: `O robô irá limpar e fechar ${selecionados.length} PDVs selecionados.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Sim, iniciar processo',
                cancelButtonText: 'Cancelar'
            });

            if (!confirm.isConfirmed) return;

            let listaIps = [];
            selecionados.each(function() {
                listaIps.push($(this).val());
            });

            // Reset UI
            $('#status-container, .progress').fadeIn();
            $('#status-texto').html('<i class="fas fa-broom me-2"></i>FASE 1: Limpando telas (ESC)...');
            $('#barra').css('width', '25%').removeClass('bg-success').addClass('bg-info');
            $('#status-porcentagem').text('25%');

            // Atualiza status visual apenas dos PDVs que entraram no lote
            selecionados.each(function() {
                const ip = $(this).val();
                const linhaId = '#linha-' + ip.replace(/\./g, '-');
                $(linhaId).find('.status-col').html('<span class="status-processando"><i class="fas fa-sync fa-spin me-1"></i> No lote...</span>');
            });

            try {
                // Envio único para o robô processar a lista
                const response = await $.post('executar_robo.php', { ip: listaIps }, null, 'json');
                
                if(response.status === 'sucesso') {
                    $('#barra').css('width', '100%').removeClass('bg-info').addClass('bg-success');
                    $('#status-texto').html('<i class="fas fa-check-circle me-2"></i>Lote finalizado!');
                    $('#status-porcentagem').text('100%');
                    
                    //Atualiza o status apenas para os que foram selecionados
                    selecionados.each(function() {
                        const ip = $(this).val();
                        const linhaId = '#linha-' + ip.replace(/\./g, '-');
                        $(linhaId).find('.status-col').html('<span class="status-sucesso"><i class="fas fa-check-double me-1"></i> Finalizado</span>');
                    });
                    
                    Swal.fire({ title: 'Sucesso!', text: response.mensagem, icon: 'success' });
                } else {
                    throw new Error(response.mensagem);
                }
            } catch (e) {
                // Em caso de falha, marca erro apenas nos selecionados
                selecionados.each(function() {
                    const ip = $(this).val();
                    const linhaId = '#linha-' + ip.replace(/\./g, '-');
                    $(linhaId).find('.status-col').html('<span class="status-erro"><i class="fas fa-exclamation-circle me-1"></i> Erro</span>');
                });
                Swal.fire('Falha no Robô', e.message || 'Erro desconhecido', 'error');
            } finally {
                setTimeout(() => $('#status-container').fadeOut(), 5000);
            }
        }
    </script>
</body>
</html>