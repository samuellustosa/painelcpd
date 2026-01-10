let pdvSelecionado = null;


function atualizarGrid() {
    $('.pdv-box').each(function() {
        const btn = $(this);
        const ip = btn.data('ip');

        $.get('check_status.php?ip=' + ip, function(status) {
            if (status === 'online') {
                btn.removeClass('btn-secondary').addClass('btn-primary'); // Azul
                btn.removeClass('btn-primary').addClass('btn-secondary'); // Cinza 
            }
        });
    });
}

// 2. Seleciona o PDV para os comandos laterais
function selecionarPDV(elemento) {
    $('.pdv-box').removeClass('border-warning border-4');
    $(elemento).addClass('border-warning border-4');
    pdvSelecionado = $(elemento).data(); // Captura IP, ID e Arquitetura
}


function enviarComando(tipo) {
    if (!pdvSelecionado) return alert("Selecione um PDV primeiro!");

    $.post('executar_comando.php', {
        id: pdvSelecionado.id,
        ip: pdvSelecionado.ip,
        arquitetura: pdvSelecionado.arq,
        comando: tipo
    }, function(data) {
        alert("Comando enviado: " + data.mensagem); 
    }, 'json');
}

$(document).ready(function() {
    atualizarGrid();
    setInterval(atualizarGrid, 30000)
});