<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Database\Connection;

$db = Connection::getInstance(); //
$stmt = $db->query("SELECT * FROM terminais ORDER BY id_caixa");
$terminais = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Fechamento PDV Automático</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: #fff; }
        .card-pdv { background: #1e1e1e; border: 1px solid #333; margin-bottom: 10px; padding: 10px; }
    </style>
</head>
<body class="container py-4">
    <h2 class="text-danger">Automação Fechamento</h2>
    
    <div class="mb-3">
        <button class="btn btn-warning" onclick="selecionarTodos()">Selecionar Todos</button>
        <button class="btn btn-success" onclick="iniciarAutomacao()">INICIAR FECHAMENTO</button>
    </div>

    <div id="progresso" class="progress mb-4" style="height: 30px; display:none;">
        <div id="barra" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" style="width: 0%"></div>
    </div>

    <div class="row" id="lista-pdvs">
        <?php foreach ($terminais as $pdv): ?>
            <div class="col-md-3">
                <div class="card-pdv">
                    <input type="checkbox" class="form-check-input check-pdv" value="<?= $pdv['ip_address'] ?>">
                    <span>PDV <?= str_pad($pdv['id_caixa'], 2, '0', STR_PAD_LEFT) ?></span>
                    <small class="d-block text-muted"><?= $pdv['ip_address'] ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function selecionarTodos() {
            $('.check-pdv').prop('checked', true);
        }

        async function iniciarAutomacao() {
            const selecionados = $('.check-pdv:checked');
            if (selecionados.length === 0) return alert("Selecione ao menos um PDV!");

            $('#progresso').show();
            let total = selecionados.length;
            let atual = 0;

            for (let check of selecionados) {
                const ip = $(check).val();
                atual++;
                let porc = (atual / total) * 100;
                $('#barra').css('width', porc + '%').text('Fechando ' + ip);

                // Chama o backend para executar o robô no IP
                await $.post('executar_robo.php', { ip: ip });
            }
            
            alert("Processo concluído!");
            $('#progresso').hide();
        }
    </script>
</body>
</html>