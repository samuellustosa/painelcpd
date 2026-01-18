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
    if (empty($itens)) return "<div class='p-3 text-center text-muted'>Nenhum registro encontrado.</div>";
    
    $html = '<div class="table-responsive">
                <table class="table table-bordered table-dark align-middle mb-0 custom-table">
                    <thead>
                        <tr>
                            <th>EQUIPAMENTO</th>
                            <th>SETOR</th>
                            <th>ÚLTIMA LIMPEZA</th>
                            <th>PRAZO / STATUS</th>
                            <th>RESPONSÁVEL</th>
                            <th>AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($itens as $item) {
        $dias_restantes = $item['frequencia_dias'] - $item['dias_passados'];
        $classe_status = ($dias_restantes > 0) ? 'status-limpo' : 'status-atrasado';
        $texto_status = ($dias_restantes > 0) ? "EM DIA (Faltam {$dias_restantes}d)" : "ATRASADO (" . abs($dias_restantes) . "d)";

        $html .= "<tr>
                    <td class='fw-bold'>{$item['identificacao']}</td>
                    <td>{$item['setor']}</td>
                    <td class='text-center'>" . date('d/m/Y', strtotime($item['data_ultima_limpeza'])) . "</td>
                    <td class='{$classe_status} text-center fw-bold text-white'>{$texto_status}</td>
                    <td class='text-center'>{$item['responsavel']}</td>
                    <td class='text-center'>
                        <div class='btn-group'>
                            <button class='btn btn-sm btn-light fw-bold' onclick='confirmarLimpeza({$item['id']})' title='Resetar Data'>
                                <i class='fas fa-check'></i>
                            </button>
                            <button class='btn btn-sm btn-outline-warning' onclick='abrirModalEdicao(".json_encode($item).")' title='Editar'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <button class='btn btn-sm btn-outline-danger' onclick='excluirItem({$item['id']})' title='Excluir'>
                                <i class='fas fa-trash'></i>
                            </button>
                        </div>
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
        .header-planilha { text-align: center; padding: 15px; background: #1e1e1e; border-bottom: 4px solid #b71c1c; margin-bottom: 20px; }
        .custom-table { border: 2px solid #444 !important; }
        .custom-table th { background-color: #2c2c2c !important; color: #ffc107; text-align: center; border: 1px solid #444 !important; }
        .custom-table td { border: 1px solid #444 !important; padding: 10px !important; }
        .status-limpo { background-color: #2e7d32 !important; }
        .status-atrasado { background-color: #c62828 !important; }
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
    
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <a href="index.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Voltar</a>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrud"><i class="fas fa-plus"></i> Novo Item</button>
        </div>
        <div class="d-flex align-items-center gap-3">
            <input type="text" id="filtroTabela" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="🔍 Filtrar equipamentos...">
            <h5 class="mb-0 text-nowrap">REF: <span class="text-warning"><?= $mes_atual ?></span></h5>
        </div>
    </div>
    
    <ul class="nav nav-tabs" id="limpezaTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-pdv" type="button">PDVs</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-balanca" type="button">Balanças</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-desktop" type="button">Desktops</button></li>
    </ul>

    <div class="tab-content border border-secondary p-2 bg-dark rounded shadow">
        <div class="tab-pane fade show active" id="tab-pdv"><?= renderPlanilha($pdvs) ?></div>
        <div class="tab-pane fade" id="tab-balanca"><?= renderPlanilha($balancas) ?></div>
        <div class="tab-pane fade" id="tab-desktop"><?= renderPlanilha($desktops) ?></div>
    </div>
</div>

<div class="modal fade" id="modalCrud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dark text-white">
        <div class="modal-content bg-dark border-secondary">
            <form id="formCrud">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="modalTitulo">Gerenciar Equipamento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="crud_id">
                    <div class="mb-3">
                        <label class="form-label">Identificação (Nome)</label>
                        <input type="text" name="identificacao" id="crud_identificacao" class="form-control bg-dark text-white border-secondary" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" id="crud_tipo" class="form-select bg-dark text-white border-secondary">
                                <option value="pdv">PDV</option>
                                <option value="balanca">Balança</option>
                                <option value="desktop">Desktop</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Setor</label>
                            <input type="text" name="setor" id="crud_setor" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Frequência (Dias)</label>
                            <input type="number" name="frequencia_dias" id="crud_frequencia" class="form-control bg-dark text-white border-secondary" value="30">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Responsável</label>
                            <input type="text" name="responsavel" id="crud_responsavel" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Persistência da Aba ativa
        let activeTab = localStorage.getItem('activeTabLimpeza');
        if (activeTab) {
            let tabTrigger = new bootstrap.Tab(document.querySelector('#limpezaTabs button[data-bs-target="' + activeTab + '"]'));
            tabTrigger.show();
        }
        $('#limpezaTabs button').on('shown.bs.tab', function (e) {
            localStorage.setItem('activeTabLimpeza', $(e.target).data('bs-target'));
        });

        // FILTRO DINÂMICO
        $("#filtroTabela").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("table tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });

    // CRUD: Resetar Data (Limpeza Feita)
    function confirmarLimpeza(id) {
        Swal.fire({
            title: 'Confirmar Higienização?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2e7d32',
            confirmButtonText: 'Sim, registrar!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('atualizar_limpeza.php', { id: id, acao: 'resetar' }, function(r) {
                    location.reload();
                }, 'json');
            }
        });
    }

    // CRUD: Abrir modal para edição
    function abrirModalEdicao(item) {
        $("#crud_id").val(item.id);
        $("#crud_identificacao").val(item.identificacao);
        $("#crud_tipo").val(item.tipo);
        $("#crud_setor").val(item.setor);
        $("#crud_frequencia").val(item.frequencia_dias);
        $("#crud_responsavel").val(item.responsavel);
        $("#modalTitulo").text("Editar Equipamento");
        $("#modalCrud").modal('show');
    }

    // CRUD: Excluir
    function excluirItem(id) {
        Swal.fire({
            title: 'Excluir Item?',
            text: "Essa ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('atualizar_limpeza.php', { id: id, acao: 'excluir' }, function(r) {
                    location.reload();
                }, 'json');
            }
        });
    }

    // CRUD: Salvar (Novo ou Editar)
    $("#formCrud").on("submit", function(e) {
        e.preventDefault();
        $.post('atualizar_limpeza.php', $(this).serialize() + '&acao=salvar', function(r) {
            location.reload();
        }, 'json');
    });
</script>
</body>
</html>