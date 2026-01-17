<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;

// Aumenta o tempo limite para evitar que o PHP pare durante o processamento da lista
set_time_limit(300);

// Recebe os IPs (pode ser um único IP ou um array de IPs enviados pelo painel)
$ips = $_POST['ip'] ?? null;
if (!$ips) {
    die(json_encode(['status' => 'erro', 'mensagem' => 'IP não fornecido.']));
}

// Garante que trabalharemos com uma lista (array)
$listaIps = is_array($ips) ? $ips : [$ips];

$cracha = $_POST['cracha'] ?? "427605";
$senha  = $_POST['senha'] ?? "1212";

$host = 'http://localhost:4444'; 
$capabilities = DesiredCapabilities::chrome();

$options = new ChromeOptions();
$options->addArguments(['--headless', '--no-sandbox', '--disable-dev-shm-usage', '--disable-gpu']); 
$capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

try {
    // Abre o navegador uma única vez para todos os PDVs
    $driver = RemoteWebDriver::create($host, $capabilities);

    /**
     * FASE 1: LIMPEZA GERAL
     * Percorre todos os PDVs da lista enviando apenas comandos de ESC.
     * Isso remove mensagens de erro, menus abertos ou travamentos de interface.
     */
    foreach ($listaIps as $ip) {
        try {
            $urlPdv = "http://{$ip}:9898/normal.html";
            $driver->get($urlPdv);
            
            // Comandos de limpeza rápidos
            $driver->executeScript("pdv.putCmd('{ESC}');");
            sleep(1);
            $driver->executeScript("pdv.putCmd('{ESC}');");
            sleep(1);
            $driver->executeScript("pdv.putCmd('{ESC}');");
            sleep(1);
        } catch (Exception $e) {
            // Se um PDV falhar (ex: offline), ignora e pula para o próximo
            continue;
        }
    }

    // Pausa curta de 1 segundo entre a limpeza e o início dos fechamentos
    sleep(2);

    /**
     * FASE 2: FECHAMENTO REAL
     * Volta ao início da lista para executar os comandos de login e fechamento.
     */
    foreach ($listaIps as $ip) {
        try {
            $urlPdv = "http://{$ip}:9898/normal.html";
            $driver->get($urlPdv);

            // Verifica se há venda em andamento (3 = Vendendo, 4 = Recebendo)
            $situacao = $driver->executeScript("return vue.data.situacao;");

            if ($situacao == 3 || $situacao == 4) {
                continue; // Pula se o PDV estiver ocupado
            }

            // Sequência de login e comando de fechamento (112)
            $driver->executeScript("pdv.putCmd('112{99991}');"); // 112 + TAB
            sleep(2);
            $driver->executeScript("pdv.putCmd('{$cracha}{ENTER}');");
            sleep(1);
            $driver->executeScript("pdv.putCmd('{$senha}{ENTER}');");
            
        } catch (Exception $e) {
            continue; // Pula erros individuais para não travar a lista toda
        }
    }

    echo json_encode(['status' => 'sucesso', 'mensagem' => "Processo de limpeza e fechamento concluído."]);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => "Erro no Robô: " . $e->getMessage()]);
} finally {
    if (isset($driver)) {
        $driver->quit();
    }
}