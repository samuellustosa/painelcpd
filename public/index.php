<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Database\Connection;

// 1. Conexão e busca de dados
try {
    $db = Connection::getInstance();
    $stmt = $db->query("SELECT * FROM terminais ORDER BY setor, id_caixa");
    $terminais_brutos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $terminais_brutos = [];
}

// 2. Organização inicial por Setores
$setores = [];
if (!empty($terminais_brutos)) {
    foreach ($terminais_brutos as $t) {
        $setores[$t['setor']][] = $t;
    }
}

// 3. Lógica de Ordenação: Setor com mais PDVs no topo
uasort($setores, function($a, $b) {
    return count($b) <=> count($a);
});
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel CPD</title>
    <link rel="icon" type="image/png" href="images/logopainel.ico">
    <link rel="manifest" href="manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Ajustes de Proporção e Dark Mode */
        body { background-color: #121212; color: #e0e0e0; overflow-x: hidden; font-family: sans-serif; }
        
        .grid-pdv { padding-right: 170px; min-height: 100vh; background: #121212; padding-bottom: 30px; }
        
        .sidebar { 
            background: #1e1e1e; border-left: 1px solid #333; height: 100vh; 
            position: fixed; right: 0; top: 0; padding: 15px; z-index: 1000; width: 160px; 
        }

        .header-red { background: #b71c1c; color: white; text-align: center; padding: 8px; font-weight: bold; text-transform: uppercase; font-size: 14px; }

        .setor-header { 
            background: #2c2c2c; padding: 4px; margin-top: 15px; text-align: center; 
            font-weight: bold; color: #aaa; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #444;
        }

        /* PDVs Compactos para caber tudo */
        .pdv-btn { 
            width: 92px; height: 72px; margin: 3px; font-weight: bold; font-size: 11px; 
            transition: 0.2s; cursor: pointer; border-radius: 6px; border: 1px solid #333; 
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            background-color: #333; color: #fff;
        }

        .btn-online { background-color: #2e7d32 !important; color: #fff !important; border-color: #1b5e20 !important; }
        .btn-offline { background-color: #c90707 !important; color: #fff !important; border-color: #8e0000 !important; }
        
        .selecionado { border: 3px solid #fff !important; box-shadow: 0 0 8px rgba(255,255,255,0.5); } 

        .sidebar .btn { font-size: 12px; font-weight: bold; padding: 8px 5px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 grid-pdv">
            <div class="header-red">Painel CPD</div>
            
            <?php if (!empty($setores)): ?>
                <?php foreach ($setores as $nome_setor => $caixas): ?>
                    <div class="setor-header"><?= htmlspecialchars($nome_setor) ?> (<?= count($caixas) ?>)</div>
                    <div class="d-flex flex-wrap justify-content-start p-1">
                        <?php foreach ($caixas as $pdv): ?>
                            <button class="btn pdv-btn" 
                                    id="pdv-<?= $pdv['id_caixa'] ?>"
                                    onclick="selecionarPDV(this)"
                                    data-id="<?= $pdv['id_caixa'] ?>" 
                                    data-ip="<?= $pdv['ip_address'] ?>"
                                    data-arq="<?= $pdv['arquitetura'] ?>">
                                <span>PDV <?= str_pad($pdv['id_caixa'], 2, '0', STR_PAD_LEFT) ?></span>
                                <small style="font-size: 9px; opacity: 0.7;"><?= $pdv['arquitetura'] ?></small> 
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="sidebar text-center">
            <a href="" class="btn btn-warning w-100 mb-4 fw-bold text-white">
                <i class="fas fa-robot me-1"></i> EM MANUTENÇÃO
            </a>


            <button class="btn btn-primary w-100 mb-2" onclick="comando('desligar')">DESLIGAR</button>
            <button class="btn btn-primary w-100 mb-2" onclick="comando('reboot')">REINICIAR</button>
            <button class="btn btn-info w-100 mb-2" onclick="acessarVNC()">VNC</button>
            <button class="btn btn-info w-100 mb-2" onclick="acessarSSH()">SSH</button>
            <button class="btn btn-danger w-100 mb-2" onclick="comando('reiniciar_app')">REINICIAR APP</button>
            
            <div class="mt-4">
                <img src="images/logo.jpg" style="max-width: 90%; border-radius: 4px; filter: grayscale(20%);">
            </div>

        
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let selecionado = null;

    if (window.matchMedia('(display-mode: standalone)').matches) {
        window.resizeTo(1100, 750);
    }

    function selecionarPDV(btn) {
        $('.pdv-btn').removeClass('selecionado');
        $(btn).addClass('selecionado');
        selecionado = $(btn).data();
    }

    function verificarStatus() {
        $('.pdv-btn').each(function() {
            const btn = $(this);
            $.get('check_status.php', { ip: btn.data('ip') }, function(status) {
                btn.removeClass('btn-online btn-offline');
                btn.addClass(status.trim() === 'online' ? 'btn-online' : 'btn-offline');
            });
        });
    }

    $(document).ready(function() {
        verificarStatus();
        setInterval(verificarStatus, 15000);
    });

    function gerarSenha(sel) {
        if (sel.arq === 'x86') return "1";
        const data = new Date();
        const num = "" + data.getDate() + (data.getMonth() + 1);
        return "pdv@" + (parseInt(num) + parseInt(sel.id));
    }

    function acessarSSH() {
        if(!selecionado) return Swal.fire('Atenção', 'Selecione um PDV!', 'warning');
        let user = (selecionado.arq === 'x86') ? "root" : "suporte";
        let pass = gerarSenha(selecionado);
        navigator.clipboard.writeText(pass).then(() => {
            Swal.fire({ icon: 'success', title: 'Senha Copiada', text: pass, timer: 1000, showConfirmButton: false });
            window.location.href = "ssh://" + user + "@" + selecionado.ip;
        });
    }

    function acessarVNC() {
        if(!selecionado) return Swal.fire('Atenção', 'Selecione um PDV!', 'warning');
        let pass = gerarSenha(selecionado);
        navigator.clipboard.writeText(pass).then(() => {
            Swal.fire({ icon: 'success', title: 'Senha VNC Copiada', timer: 1000, showConfirmButton: false });
            window.location.href = "vnc://" + selecionado.ip.trim();
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
                Swal.fire({ title: 'Executando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                $.post('executar_comando.php', {
                    id: selecionado.id, 
                    ip: selecionado.ip, 
                    arquitetura: selecionado.arq, 
                    comando: tipo
                }, function(data) {
                    Swal.fire({
                        title: 'Resposta do Servidor',
                        text: data.mensagem,
                        icon: (data.status === 'sucesso') ? 'success' : 'error'
                    });
                }, 'json').fail(function() {
                    Swal.fire('Erro', 'Falha na comunicação.', 'error');
                });
            }
        });
    }
</script>
</body>
</html>