<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Como Usar</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        body { background-color: #121212; color: #fff; }
        .table { color: #fff; border-color: #333; }
        .table-dark { --bs-table-bg: #1e1e1e; }
    </style>
</head>
<body class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="header-title">
            <h2 class="mb-0">Fechamento PDV</h2>
            <p> Visualização do Painel
                    Os terminais são organizados por Setores (ex: Frente de Loja, Atacado). O sistema coloca automaticamente os setores com mais PDVs no topo para agilizar a visualização.
                    Verde (Online): O terminal está respondendo ao ping.
                    Vermelho (Offline): O terminal está desligado ou sem comunicação de rede.
                    Seleção: Clique em um PDV para selecioná-lo. Ele ficará com uma borda de destaque.
                    Funções de Suporte (Barra Lateral)
                    Após selecionar um PDV, use os botões da lateral direita para agir:
                    VNC: Copia automaticamente a senha dinâmica para o seu CTRL+V e tenta abrir o acesso remoto.
                    SSH: Abre o terminal remoto. A senha também é copiada para a área de transferência.
                    Reiniciar APP: Envia um comando SSH para reiniciar apenas o sistema de venda , sem reiniciar a máquina inteira.
                    Reiniciar/Desligar: Envia comandos diretos de sistema para o terminal.
                    Senhas
                    Facilidade: não precisa calcular de cabeça; ao clicar em VNC ou SSH, o painel já gera e copia a senha correta para você colar no login.
                    OBS
                    O status (on/off) dos PDVs é atualizado a cada 15 segundos.
                    Reboot: Quando você envia um comando de Reiniciar, o painel pode mostrar um aviso de "Conexão encerrada". Isso é normal, pois a máquina derruba a rede para reiniciar.
            </p>
            
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">Voltar ao Painel</a>
    </div>

    
    </div>