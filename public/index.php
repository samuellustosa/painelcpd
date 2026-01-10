<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Database\Connection;

$db = Connection::getInstance();
// Buscando dados do banco painelcpd [cite: 39, 40]
$stmt = $db->query("SELECT * FROM terminais ORDER BY setor, id_caixa");
$terminais_brutos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupa por setor para criar as divisões na tela [cite: 22]
$setores = [];
foreach ($terminais_brutos as $t) {
    $setores[$t['setor']][] = $t;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel CPD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .grid-pdv { background: #fff; min-height: 100vh; padding-bottom: 50px; }
        .sidebar { background: #f8f9fa; border-left: 1px solid #ddd; height: 100vh; position: fixed; right: 0; top: 0; padding: 20px; z-index: 1000; }
        .pdv-btn { width: 100px; margin: 5px; font-weight: bold; font-size: 13px; transition: 0.3s; }
        .setor-header { background: #eee; padding: 5px; margin-top: 20px; text-align: center; font-weight: bold; color: #555; }
        .header-red { background: #d9534f; color: white; text-align: center; padding: 10px; margin-bottom: 10px; font-weight: bold; }
        
        .selecionado { border: 3px solid #000 !important; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 grid-pdv">
            <div class="header-red">Painel CPD</div>
            
            <?php foreach ($setores as $nome_setor => $caixas): ?>
                <div class="setor-header"><?= $nome_setor ?></div>
                <div class="d-flex flex-wrap justify-content-start p-2">
                    <?php foreach ($caixas as $pdv): ?>
                        <button class="btn btn-secondary pdv-btn" 
                                id="pdv-<?= $pdv['id_caixa'] ?>"
                                onclick="selecionarPDV(this)"
                                data-id="<?= $pdv['id_caixa'] ?>" 
                                data-ip="<?= $pdv['ip_address'] ?>"
                                data-arq="<?= $pdv['arquitetura'] ?>">
                            PDV <?= str_pad($pdv['id_caixa'], 2, '0', STR_PAD_LEFT) ?> <br>
                            <small><?= $pdv['arquitetura'] ?></small>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="col-md-2 sidebar text-center">
            
            
            <button class="btn btn-primary w-100 mb-2" onclick="comando('desligar')">Desligar</button>
            <button class="btn btn-primary w-100 mb-2" onclick="comando('reboot')">Reiniciar</button>
            <button class="btn btn-primary w-100 mb-2">Acessar VNC</button>
            <button class="btn btn-primary w-100 mb-2">Acessar SSH</button>
            <button class="btn btn-primary w-100 mb-2 border-danger" onclick="comando('reiniciar_app')">Reiniciar APP</button>
            
            <div class="mt-5">
                <img src="/images/logo.jpg" width="120">
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let selecionado = null;

    $(document).ready(function() {
        // Inicia o monitoramento de status 
        verificarStatus();
        // Atualiza a cada 30 segundos conforme requisito de performance 
        setInterval(verificarStatus, 30000);
    });

    function selecionarPDV(btn) {
        $('.pdv-btn').removeClass('selecionado');
        $(btn).addClass('selecionado');
        selecionado = $(btn).data();
    }

    // Função de monitoramento em tempo real
    function verificarStatus() {
        $('.pdv-btn').each(function() {
            const btn = $(this);
            const ip = btn.data('ip');

            $.get('check_status.php', { ip: ip }, function(status) {
                if (status.trim() === 'online') {
                    btn.removeClass('btn-secondary').addClass('btn-primary'); 
                } else {
                    btn.removeClass('btn-primary').addClass('btn-secondary'); 
                }
            });
        });
    }
    
    // Envio de comandos via AJAX para o Controller
    function comando(tipo) {
        if(!selecionado) return alert("Selecione um PDV primeiro!");

        if(confirm("Deseja realmente enviar o comando '" + tipo + "' para o caixa " + selecionado.id + "?")) {
            $.post('executar_comando.php', {
                id: selecionado.id,
                ip: selecionado.ip,
                arquitetura: selecionado.arq,
                comando: tipo
            }, function(data) {
                alert("Servidor responde: " + data.mensagem);
            }, 'json').fail(function() {
                alert("Erro ao conectar com o servidor de comandos.");
            });
        }
    }
</script>
</body>
</html>