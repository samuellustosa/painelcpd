let pdvSelecionado = null;


function atualizarGrid() {
    $('.pdv-box').each(function() {
        const btn = $(this);
        const ip = btn.data('ip');

        $.get('check_status.php?ip=' + ip, function(status) {
            
            if (status.trim() === 'online') {
                btn.removeClass('btn-secondary').addClass('btn-primary'); // Verde se online
            } else {
                btn.removeClass('btn-primary').addClass('btn-secondary'); // Vermelho se offline
            }
        });
    });
}

function selecionarPDV(elemento) {
    $('.pdv-btn').removeClass('selecionado border-warning border-4');
    $(elemento).addClass('selecionado border-warning border-4');
    pdvSelecionado = $(elemento).data(); 
}

function comando(tipo) { 
    if (!pdvSelecionado) return alert("Selecione um PDV primeiro!");

    $.post('executar_comando.php', {
        id: pdvSelecionado.id,
        ip: pdvSelecionado.ip,
        arquitetura: pdvSelecionado.arq,
        comando: tipo
    }, function(data) {
        alert("Resposta: " + data.mensagem); 
    }, 'json');
}

$(document).ready(function() {
    atualizarGrid();
    setInterval(atualizarGrid, 30000); 
});