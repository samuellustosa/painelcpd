<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Como Usar - Painel CPD</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css"> </head>
<body class="bg-dark text-light">

<div class="container py-5">
    <div class="text-center mb-5">
         <h1 class="display-5 fw-bold text-danger">Guia de Uso - Painel CPD</h1>
    </div>
        <div class="d-flex justify-content-end pb-4">
        <a href="index.php" class="btn btn-danger btn-lg px-5 ">Voltar ao Painel</a>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card bg-secondary text-white h-100 border-0 shadow">
                <div class="card-body">
                    <h3 class="card-title text-warning">1. Monitoramento de Status</h3>
                    <p class="card-text">
                        O grid principal atualiza automaticamente a cada 30 segundos.
                        <ul>
                            <li><span class="badge bg-success">Verde/Online</span>: O terminal está respondendo ao ping.</li>
                            <li><span class="badge bg-danger">Vermelho/Offline</span>: O terminal está desligado ou sem rede.</li>
                        </ul>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-secondary text-white h-100 border-0 shadow">
                <div class="card-body">
                    <h3 class="card-title text-warning">2. Seleção de PDV</h3>
                    <p class="card-text">
                        Clique em qualquer caixa de PDV no grid para selecioná-lo. 
                        Uma borda branca indicará qual terminal receberá o próximo comando.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card border-danger bg-dark text-white shadow">
                <div class="card-body">
                    <h3 class="card-title text-danger">3. Execução de Comandos (SSH)</h3>
                    <div class="row mt-3">
                        <div class="col-md-4 text-center">
                            <i class="bi bi-power fs-1"></i>
                            <h5>Energia</h5>
                            <p class="small">Ligar, Reiniciar ou Desligar os terminais remotamente via rede.</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="bi bi-shield-lock fs-1"></i>
                            <h5>Segurança</h5>
                            <p class="small">Terminais x64 utilizam <strong>senha dinâmica</strong>; terminais x86 utilizam <strong>senha fixa</strong>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <div class="col-md-12">
            <div class="card border-danger bg-dark text-white shadow">
                <div class="card-body">
                    <h3 class="card-title text-danger">4. Execução de Comandos (SSH)</h3>
                    <div class="row mt-3">
                        <div class="col-md-4 text-center">
                            <i class="bi bi-power fs-1"></i>
                            <h5>Energia</h5>
                            <p class="small">Ligar, Reiniciar ou Desligar os terminais remotamente via rede.</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="bi bi-shield-lock fs-1"></i>
                            <h5>Segurança</h5>
                            <p class="small">Terminais x64 utilizam <strong>senha dinâmica</strong>; terminais x86 utilizam <strong>senha fixa</strong>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                <div class="col-md-12">
            <div class="card border-danger bg-dark text-white shadow">
                <div class="card-body">
                    <h3 class="card-title text-danger">5. Execução da automação</h3>
                        <div class="col-md-4 text-center">
                            <i class="bi bi-robot fs-1"></i>
                            <h5>Robô de Fechamento</h5>
                            <p class="small">Automatiza a função 112 para login fiscal usando Selenium.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>