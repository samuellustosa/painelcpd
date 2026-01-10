<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Database\Connection;

$db = Connection::getInstance();
$stmt = $db->query("SELECT * FROM terminais ORDER BY setor, id_caixa");
$terminais_brutos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .grid-pdv { background: #fff; min-height: 100vh; padding-bottom: 50px; }
        .sidebar { background: #f8f9fa; border-left: 1px solid #ddd; height: 100vh; position: fixed; right: 0; top: 0; padding: 20px; z-index: 1000; }
        .pdv-btn { width: 100px; margin: 5px; font-weight: bold; font-size: 13px; transition: 0.3s; cursor: pointer; }
        .setor-header { background: #eee; padding: 5px; margin-top: 20px; text-align: center; font-weight: bold; color: #555; }
        .header-red { background: #d9534f; color: white; text-align: center; padding: 10px; margin-bottom: 10px; font-weight: bold; }
        
        /* Cores originais */
        .btn-primary { background-color: #0d6efd !important; border-color: #0d6efd !important; } 
        .btn-secondary { background-color: #6c757d !important; border-color: #6c757d !important; }
        
        /* Destaque da seleção */
        .selecionado { border: 4px solid #000 !important; box-shadow: 0 0 10px rgba(0,0,0,0.3); }
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
            <button class="btn btn-primary w-100 mb-2" onclick="acessarVNC()">Acessar VNC</button>
            <button class="btn btn-primary w-100 mb-2" onclick="acessarSSH()">Acessar SSH</button>
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

    function selecionarPDV(btn) {
        // Remove a borda de todos e coloca apenas no clicado
        $('.pdv-btn').removeClass('selecionado');
        $(btn).addClass('selecionado');
        
        // Salva os dados do PDV clicado
        selecionado = $(btn).data();
        console.log("PDV Selecionado:", selecionado);
    }

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

    $(document).ready(function() {
        verificarStatus();
        setInterval(verificarStatus, 10000);
    });

    // Funções de acesso e comando com SweetAlert2
    function acessarSSH() {
        if(!selecionado) return Swal.fire('Atenção', 'Selecione um PDV primeiro!', 'warning');
        
        let senha = "";
        let usuario = (selecionado.arq === 'x86') ? "root" : "suporte";
        
        if (selecionado.arq === 'x86') {
            senha = "1";
        } else {
            const data = new Date();
            const somaFinal = parseInt("" + data.getDate() + (data.getMonth() + 1)) + parseInt(selecionado.id);
            senha = "pdv@" + somaFinal;
        }
        
        navigator.clipboard.writeText(senha).then(() => {
            Swal.fire({ icon: 'success', title: 'Senha Copiada!', text: 'A senha está no CTRL+V.', timer: 1500, showConfirmButton: false });
            window.location.href = "ssh://" + usuario + "@" + selecionado.ip;
        });
    }

    function acessarVNC() {
        if(!selecionado) return Swal.fire('Atenção', 'Selecione um PDV primeiro!', 'warning');
        
        let senha = "";

        if (selecionado.arq === 'x86') {
            senha = "1"; 
        } else {
            const data = new Date();

            const num = "" + data.getDate() + (data.getMonth() + 1);
            senha = "pdv@" + (parseInt(num) + parseInt(selecionado.id));
        }
        
        // Copia para a área de transferência e avisa o utilizador
        navigator.clipboard.writeText(senha).then(() => {
            Swal.fire({ 
                icon: 'success', 
                title: 'Senha VNC Copiada!', 
                text: 'A senha (' + senha + ') já está no seu CTRL+V.', 
                timer: 2000, 
                showConfirmButton: false 
            });
            
            // Abre o TightVNC após a cópia
            setTimeout(() => {
                window.location.href = "vnc://" + selecionado.ip.trim();
            }, 500);
        });
    }

    function comando(tipo) {
    if(!selecionado) return Swal.fire('Atenção', 'Selecione um PDV primeiro!', 'warning');

    Swal.fire({
        title: 'Confirmar',
        text: "Enviar '" + tipo + "' para o PDV " + selecionado.id + "?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não'
    }).then((result) => {
        if (result.isConfirmed) {
            // EXIBE A TELA DE "EXECUTANDO..."
            Swal.fire({ 
                title: 'Executando...', 
                text: 'Aguarde o retorno do terminal SSH',
                allowOutsideClick: false, 
                didOpen: () => { Swal.showLoading(); } 
            });

            // ENVIA PARA O PHP
            $.post('executar_comando.php', {
                id: selecionado.id, 
                ip: selecionado.ip, 
                arquitetura: selecionado.arq, 
                comando: tipo
            }, function(data) {
                // ESTA PARTE FECHA O "EXECUTANDO..." E MOSTRA O RESULTADO
                Swal.fire({
                    title: 'Resposta do Servidor',
                    text: data.mensagem,
                    icon: (data.status === 'sucesso') ? 'success' : 'error'
                });
            }, 'json').fail(function() {
                // FECHA SE HOUVER ERRO DE REDE
                Swal.fire('Erro', 'Falha na comunicação com o servidor PHP.', 'error');
            });
        }
    });
}
</script>
</body>
</html>